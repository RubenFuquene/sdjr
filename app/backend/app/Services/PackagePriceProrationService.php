<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Prorrateo P3 (SCRUM-361/323, ticket derivado): reparte el precio
 * realmente cobrado por un pack entre sus componentes, escalando
 * uniformemente el precio de venta vigente de cada uno por el mismo
 * factor — mismo algoritmo que `priceWithinPack()` en el frontend
 * (product-form.utils.ts), aquí aplicado a la venta real en vez de a una
 * vista previa.
 */
class PackagePriceProrationService
{
    /**
     * @return Collection<int, array{product: Product, quantity: int, unit_price: float}>
     */
    public function prorate(Product $package, float $packageUnitPrice, int $packagesSold): Collection
    {
        $package->loadMissing('packageItems');
        $components = $package->packageItems;

        if ($components->isEmpty() || $packagesSold <= 0) {
            return collect();
        }

        $ceiling = $components->sum(
            fn (Product $component) => $component->currentSalePrice() * (int) $component->pivot->quantity
        );

        $lines = $components->values()->map(function (Product $component) use ($ceiling, $packageUnitPrice, $packagesSold) {
            $componentSalePrice = $component->currentSalePrice();
            $factor = $ceiling > 0 ? $packageUnitPrice / $ceiling : 1.0;

            return [
                'product' => $component,
                'quantity' => (int) $component->pivot->quantity * $packagesSold,
                'unit_price' => round($componentSalePrice * $factor, 2),
            ];
        });

        return $this->applyRemainder($lines, round($packageUnitPrice * $packagesSold, 2));
    }

    /**
     * El redondeo por componente puede dejar la suma de subtotales a unos
     * centavos del total realmente cobrado por el pack. La diferencia se
     * asigna a la línea de mayor subtotal — pero por unidades completas, no
     * promediada entre la cantidad de esa línea: dividir el residuo entre
     * una cantidad que no lo divide exacto (ej. 1 centavo entre 2 unidades)
     * sobrepasa el objetivo al redondear y multiplicar de vuelta. Si la
     * línea elegida no puede absorber todo el residuo en unidades enteras
     * sin partirse, se parte en dos filas del mismo producto — cantidad
     * total y dinero total quedan iguales, cada centavo cae en una unidad
     * concreta en vez de fraccionarse.
     *
     * @param  Collection<int, array{product: Product, quantity: int, unit_price: float}>  $lines
     * @return Collection<int, array{product: Product, quantity: int, unit_price: float}>
     */
    private function applyRemainder(Collection $lines, float $targetTotal): Collection
    {
        $currentTotal = round($lines->sum(fn (array $line) => $line['unit_price'] * $line['quantity']), 2);
        $remainingCents = (int) round(($targetTotal - $currentTotal) * 100);

        if ($remainingCents === 0) {
            return $lines->values();
        }

        $result = collect();

        foreach ($lines->sortByDesc(fn (array $line) => $line['unit_price'] * $line['quantity'])->values() as $line) {
            if ($remainingCents === 0) {
                $result->push($line);

                continue;
            }

            $direction = $remainingCents > 0 ? 1 : -1;
            $unitsToBump = min(abs($remainingCents), $line['quantity']);
            $bumpedUnitPrice = round($line['unit_price'] + ($direction * 0.01), 2);
            $remainingCents -= $direction * $unitsToBump;

            $result->push([
                'product' => $line['product'],
                'quantity' => $unitsToBump,
                'unit_price' => $bumpedUnitPrice,
            ]);

            if ($unitsToBump < $line['quantity']) {
                $result->push([
                    'product' => $line['product'],
                    'quantity' => $line['quantity'] - $unitsToBump,
                    'unit_price' => $line['unit_price'],
                ]);
            }
        }

        return $result;
    }
}
