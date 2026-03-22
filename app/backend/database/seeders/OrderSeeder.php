<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {            
            //
        }

        if (env('DEMO_SEEDING') == 'true') {        
            Order::factory()->count(10)->create()->each(function (Order $order): void {
                $itemsCount = random_int(1, 4);
                $total = 0.0;
    
                for ($i = 0; $i < $itemsCount; $i++) {
                    $product = Product::factory()->create();
                    $quantity = random_int(1, 3);
                    $unitPrice = (float) ($product->original_price ?? 0);
    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                    ]);
    
                    $total += $quantity * $unitPrice;
                }
    
                $order->update([
                    'total_price' => round($total, 2),
                ]);
            });
        }
    }
}
