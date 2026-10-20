<?php

namespace Database\Seeders\Concerns;

use RuntimeException;

trait OnlyForDevelopment
{
    /**
     * No instalar cuentas con contraseñas conocidas ni datos de prueba en producción.
     * Esta comprobación también protege la ejecución por --class.
     */
    protected function assertDevelopmentEnvironment(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException(
                'Los seeders DEMO de NeskerOS solo se pueden ejecutar con APP_ENV=local o testing.'
            );
        }
    }
}
