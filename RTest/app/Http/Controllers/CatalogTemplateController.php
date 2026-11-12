<?php

namespace App\Http\Controllers;

use App\Exports\CatalogTemplateExport;
use App\Services\CatalogSpreadsheetService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CatalogTemplateController extends Controller
{
    public function __invoke(string $catalog, CatalogSpreadsheetService $spreadsheets): BinaryFileResponse|RedirectResponse
    {
        $label = match ($catalog) {
            'devices' => 'Dispositivos',
            'buildings' => 'Edificios',
            'classrooms' => 'Aulas',
        };

        return $spreadsheets->export(
            new CatalogTemplateExport($catalog),
            'plantilla-'.mb_strtolower($label).'.xlsx',
            $label,
            'template',
        );
    }
}
