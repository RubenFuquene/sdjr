<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\CatalogSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

/**
 * Reinicia el entorno desde cero: borra todo el esquema (migrate:fresh) y vuelve a
 * sembrar el catálogo idempotente, opcionalmente con datos demo.
 *
 * Pensado para volver un entorno de prueba (staging) a un estado limpio bajo demanda,
 * NO para el flujo automático de deploy (eso lo cubre app:deploy-release, que nunca
 * borra datos). Bloqueado en producción como seguro adicional.
 */
class ResetEnvironment extends Command
{
    protected $signature = 'app:reset-environment
        {--with-demo : Sembrar también datos demo (DemoSeeder) después del catálogo}
        {--force : Omitir la confirmación interactiva}';

    protected $description = 'Borra todo el esquema (migrate:fresh) y resiembra el catálogo. No disponible en producción.';

    public function handle(): int
    {
        if (app()->environment(['production', 'prod', 'prd'])) {
            $this->error('Bloqueado: app:reset-environment no puede ejecutarse cuando APP_ENV es production/prod/prd.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm(
            'Esto BORRARÁ todos los datos del entorno actual (APP_ENV='.app()->environment().'). ¿Continuar?'
        )) {
            $this->warn('Operación cancelada.');

            return self::SUCCESS;
        }

        $this->warn('==> Nota: esto no borra archivos en almacenamiento (S3/MinIO); límpialos manualmente si aplica.');

        $this->info('==> Eliminando todas las tablas y re-ejecutando migraciones (migrate:fresh)...');
        $freshCode = $this->call('migrate:fresh', ['--force' => true]);

        if ($freshCode !== self::SUCCESS) {
            $this->error('migrate:fresh falló. Se aborta el reset.');

            return $freshCode;
        }

        $this->info('==> Sembrando catálogo idempotente (CatalogSeeder)...');
        $seedCode = $this->call('db:seed', [
            '--class' => CatalogSeeder::class,
            '--force' => true,
        ]);

        if ($seedCode !== self::SUCCESS) {
            $this->error('El seeding de catálogo falló.');

            return $seedCode;
        }

        if ($this->option('with-demo')) {
            $this->info('==> Sembrando datos demo (DemoSeeder)...');
            $demoCode = $this->call('db:seed', [
                '--class' => DemoSeeder::class,
                '--force' => true,
            ]);

            if ($demoCode !== self::SUCCESS) {
                $this->error('El seeding de datos demo falló.');

                return $demoCode;
            }
        }

        $this->info('==> Reset de entorno completado.');

        return self::SUCCESS;
    }
}
