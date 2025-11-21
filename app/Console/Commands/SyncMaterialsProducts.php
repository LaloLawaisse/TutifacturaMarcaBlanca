<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Material;
use App\Product;
use Illuminate\Support\Facades\DB;

class SyncMaterialsProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'materials:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza products.materiales usando materiales.productos_linkeados existentes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando sincronización de insumos y productos...');

        $totalMaterials = 0;
        $totalProductsTouched = 0;

        Material::chunk(200, function ($materials) use (&$totalMaterials, &$totalProductsTouched) {
            foreach ($materials as $material) {
                $totalMaterials++;

                $productIds = is_array($material->productos_linkeados) ? $material->productos_linkeados : [];
                $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
                if (empty($productIds)) {
                    continue;
                }

                $businessId = $material->business_id;

                $products = Product::where('business_id', $businessId)
                    ->whereIn('id', $productIds)
                    ->get();

                foreach ($products as $product) {
                    $currentMaterials = is_array($product->materiales) ? $product->materiales : [];
                    $currentMaterials = array_values(array_unique(array_filter(array_map('intval', $currentMaterials))));

                    if (!in_array($material->ID, $currentMaterials, true)) {
                        $currentMaterials[] = $material->ID;
                        $product->materiales = $currentMaterials;
                        $product->save();
                        $totalProductsTouched++;
                    }
                }
            }
        });

        $this->info('Materiales revisados: ' . $totalMaterials);
        $this->info('Productos actualizados: ' . $totalProductsTouched);

        $this->info('Sincronización completada.');

        return 0;
    }
}

