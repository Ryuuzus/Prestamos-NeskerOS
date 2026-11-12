<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class CatalogSpreadsheetService
{
    public function import(Request $request, object $import, string $catalog): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => ['bail', 'required', 'file', 'extensions:xlsx,xls,csv', 'mimes:xlsx,xls,csv,txt', 'max:2048'],
        ], [
            'file.required' => 'Selecciona un archivo para importar.',
            'file.file' => 'No se pudo leer el archivo seleccionado.',
            'file.uploaded' => 'No se pudo subir el archivo. Verifica que no supere los 2 MB.',
            'file.extensions' => 'El archivo debe tener extensión .xlsx, .xls o .csv.',
            'file.mimes' => 'El contenido del archivo debe corresponder a Excel o CSV.',
            'file.max' => 'El archivo no puede superar los 2 MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('reports.excel')->withErrors($validator, 'import'.$catalog);
        }

        try {
            DB::transaction(fn () => Excel::import($import, $request->file('file')));
        } catch (ValidationException $exception) {
            $messages = ['No se guardaron registros nuevos. Corrige las filas indicadas y vuelve a subir el archivo.'];
            foreach (array_slice($exception->failures(), 0, 10) as $failure) {
                $messages[] = 'Fila '.$failure->row().' ('.$failure->attribute().'): '.implode(' ', $failure->errors());
            }

            return redirect()->route('reports.excel')->withErrors(['file' => $messages], 'import'.$catalog);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('reports.excel')->withErrors([
                'file' => 'No se pudo importar el archivo. No se guardaron registros. Verifica que no esté dañado y que tenga las columnas indicadas; si persiste, contacta al administrador.',
            ], 'import'.$catalog);
        }

        if ($import->importedRows === 0 && $import->duplicateRows === 0) {
            return redirect()->route('reports.excel')->withErrors([
                'file' => 'El archivo no contiene filas de datos para importar. Agrega registros debajo de los encabezados.',
            ], 'import'.$catalog);
        }

        $summary = $catalog.': '.$import->importedRows.($import->importedRows === 1 ? ' importado' : ' importados');
        if ($import->duplicateRows > 0) {
            $summary .= ', '.$import->duplicateRows.($import->duplicateRows === 1 ? ' duplicado omitido' : ' duplicados omitidos');
        }

        return redirect()->route('reports.excel')->with('success', $summary.'.');
    }

    public function export(object $export, string $filename, string $catalog, string $operation = 'export'): BinaryFileResponse|RedirectResponse
    {
        try {
            return Excel::download($export, $filename);
        } catch (Throwable $exception) {
            report($exception);

            $action = $operation === 'template' ? 'descargar la plantilla de' : 'exportar';

            return redirect()->route('reports.excel')->withErrors([
                $operation => 'No se pudo '.$action.' '.$catalog.'. Inténtalo de nuevo; si el problema persiste, contacta al administrador.',
            ], $operation.$catalog);
        }
    }
}
