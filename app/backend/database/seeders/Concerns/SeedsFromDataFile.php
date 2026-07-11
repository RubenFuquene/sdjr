<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

/**
 * Seeders de catálogo geográfico: cargan datos desde archivo JSON versionado
 * (database/data/geo/*.json) y los siembran de forma idempotente por `code`,
 * en lotes (evita exceder el límite de variables de SQLite en los tests).
 */
trait SeedsFromDataFile
{
    /**
     * @return array<int, array<string, mixed>>
     */
    protected function loadDataFile(string $filename): array
    {
        $path = database_path("data/geo/{$filename}");

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Upsert idempotente por `code`, en chunks. Añade timestamps automáticamente.
     *
     * @param  class-string  $modelClass
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $updateColumns  Columnas a actualizar en conflicto (sin updated_at)
     */
    protected function upsertChunked(string $modelClass, array $rows, array $updateColumns, int $chunkSize = 100): void
    {
        if ($rows === []) {
            return;
        }

        $now = now();
        $timestamped = array_map(
            static fn (array $row): array => $row + ['created_at' => $now, 'updated_at' => $now],
            $rows,
        );

        foreach (array_chunk($timestamped, $chunkSize) as $chunk) {
            $modelClass::upsert($chunk, ['code'], [...$updateColumns, 'updated_at']);
        }
    }
}
