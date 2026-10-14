@include('includes.header')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
    {{-- Tabla de informacion sobre equipos, solicitudes pendientes sobre el equipo o edificio prestado --}}

    <table>
        <thead>
            <tr> 
                <th>Nombre</th>
                <th>Prestamo</th>
                <th>Fecha de inicio</th>
                <th>Fecha de finalizacion</th>
            </tr>    
        </thead>
        
        <tbody>
            <tr>
                <td>{{ Auth::user()->name }}</td>
                <td>Aula 1</td>
                <td>2022-01-01</td>
                <td>2022-01-02</td>
            </tr>
        </tbody>
    </table>
</body>
</html>