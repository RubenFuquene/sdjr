<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\CatalogSeeder;
use Illuminate\Console\Command;

/**
 * Comando de release de base de datos para el deploy.
 *
 * Encapsula el paso idempotente que debe correr en cada despliegue:
 *  1. Migraciones pendientes (`migrate --force`).
 *  2. Catálogo de referencia idempotente (`CatalogSeeder`).
 *
 * Se invoca desde el Pre-deploy Command de Railway (ver railway.json) como un único
 * comando sin operadores de shell ni backslashes, evitando problemas de tokenización.
 */
class DeployRelease extends Command
{
    protected $signature = 'app:deploy-release';

    protected $description = 'Ejecuta el release de BD del deploy: migraciones + catálogo idempotente';

    public function handle(): int
    {
        $this->info('==> Ejecutando migraciones pendientes...');
        $migrateCode = $this->call('migrate', ['--force' => true]);

        if ($migrateCode !== self::SUCCESS) {
            $this->error('Las migraciones fallaron. Se aborta el release.');

            return $migrateCode;
        }

        $this->info('==> Sembrando catálogo idempotente (CatalogSeeder)...');
        $seedCode = $this->call('db:seed', [
            '--class' => CatalogSeeder::class,
            '--force' => true,
        ]);

        if ($seedCode !== self::SUCCESS) {
            $this->error('El seeding de catálogo falló. Se aborta el release.');

            return $seedCode;
        }

        $this->info('==> Release de base de datos completado.');

        return self::SUCCESS;
    }
}
