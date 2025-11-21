<?php

namespace App\Http\Controllers;

use App\Material;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->user()->can('product.view') && ! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $business_id = $request->session()->get('user.business_id');

            $query = Material::query()
                ->where('business_id', $business_id)
                ->select([
                    DB::raw('`ID` as id'),
                    'nombre',
                    'precio',
                    'unidades_en_stock',
                    'productos_linkeados',
                ]);

            return DataTables::of($query)
                ->addColumn('acciones', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('product.update')) {
                        $editUrl = route('materials.edit', ['material' => $row->id]);
                        $buttons .= '<button data-id="'.e($row->id).'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info adjust_stock_btn">Ajustar stock</button> ';
                        $buttons .= '<a href="'.e($editUrl).'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary">'.__('messages.edit').'</a> ';
                    }
                    if (auth()->user()->can('product.delete')) {
                        $deleteUrl = route('materials.destroy', ['material' => $row->id]);
                        $buttons .= '<button data-href="'.e($deleteUrl).'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-danger delete_material_btn">'.__('messages.delete').'</button>';
                    }
                    return $buttons;
                })
                ->editColumn('precio', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true" data-orig-value="'.e($row->precio).'">'.e($row->precio).'</span>';
                })
                ->addColumn('productos', function ($row) use ($business_id) {
                    $ids = is_array($row->productos_linkeados) ? $row->productos_linkeados : [];
                    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
                    if (empty($ids)) {
                        return '-';
                    }
                    $names = Product::where('business_id', $business_id)
                        ->whereIn('id', $ids)
                        ->pluck('name')
                        ->toArray();
                    if (empty($names)) {
                        return '-';
                    }
                    return e(implode(', ', $names));
                })
                ->rawColumns(['acciones', 'precio'])
                ->make(true);
        }

        return view('material.index');
    }

    public function create()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        return view('material.create');
    }

    public function store(Request $request)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'nullable|numeric',
            'unidades_en_stock' => 'nullable|integer',
            'productos_linkeados' => 'nullable|array',
            'productos_linkeados.*' => 'integer',
        ]);

        $validated['business_id'] = $request->session()->get('user.business_id');

        // Ensure JSON array
        if (isset($validated['productos_linkeados']) && !is_array($validated['productos_linkeados'])) {
            $validated['productos_linkeados'] = [];
        }

        $material = Material::create($validated);

        $productIds = isset($validated['productos_linkeados']) && is_array($validated['productos_linkeados'])
            ? array_values(array_unique(array_filter(array_map('intval', $validated['productos_linkeados']))))
            : [];

        if (!empty($productIds)) {
            // Sincroniza JSON products.materiales
            $this->syncProductsForMaterial($material, [], $productIds, $validated['business_id']);

            // Sincroniza tabla pivot para costo de insumos usando cantidades
            $productQuantities = $request->input('productos_qty', []);
            $businessId = $validated['business_id'];

            // Elimina vínculos existentes para este insumo
            DB::table('material_product')
                ->where('business_id', $businessId)
                ->where('material_id', $material->ID)
                ->delete();

            // Inserta/actualiza con la cantidad indicada por producto
            foreach ($productIds as $productId) {
                $qty = 1;
                if (is_array($productQuantities) && isset($productQuantities[$productId])) {
                    $qtyVal = (float) $productQuantities[$productId];
                    if ($qtyVal > 0) {
                        $qty = $qtyVal;
                    }
                }

                DB::table('material_product')->updateOrInsert(
                    [
                        'business_id' => $businessId,
                        'product_id'  => $productId,
                        'material_id' => $material->ID,
                    ],
                    [
                        'quantity'   => $qty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return redirect()->action([self::class, 'index'])->with('status', ['success' => 1, 'msg' => __('messages.success')]);
    }

    public function edit($id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $material = Material::where('business_id', $business_id)->findOrFail($id);

        // Cantidades de insumo por producto desde la tabla pivot
        $product_quantities = DB::table('material_product')
            ->where('business_id', $business_id)
            ->where('material_id', $material->ID)
            ->pluck('quantity', 'product_id')
            ->toArray();

        return view('material.edit', compact('material', 'product_quantities'));
    }

    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'nullable|numeric',
            'unidades_en_stock' => 'nullable|integer',
            'productos_linkeados' => 'nullable|array',
            'productos_linkeados.*' => 'integer',
        ]);

        $business_id = $request->session()->get('user.business_id');
        $material = Material::where('business_id', $business_id)->findOrFail($id);

        $oldProductIds = is_array($material->productos_linkeados) ? $material->productos_linkeados : [];
        $oldProductIds = array_values(array_unique(array_filter(array_map('intval', $oldProductIds))));

        if (!isset($validated['productos_linkeados'])) {
            $validated['productos_linkeados'] = [];
        }

        $material->update($validated);

        $newProductIds = is_array($material->productos_linkeados) ? $material->productos_linkeados : [];
        $newProductIds = array_values(array_unique(array_filter(array_map('intval', $newProductIds))));

          // Actualiza JSON products.materiales
          $this->syncProductsForMaterial($material, $oldProductIds, $newProductIds, $business_id);

          // Sincroniza pivot para costo de insumos usando cantidades
          $productQuantities = $request->input('productos_qty', []);
          $businessId = $business_id;
          DB::table('material_product')
              ->where('business_id', $businessId)
              ->where('material_id', $material->ID)
              ->when(!empty($newProductIds), function ($q) use ($newProductIds) {
                  $q->whereNotIn('product_id', $newProductIds);
              })
              ->delete();

          foreach ($newProductIds as $productId) {
              $qty = 1;
              if (is_array($productQuantities) && isset($productQuantities[$productId])) {
                  $qtyVal = (float) $productQuantities[$productId];
                  if ($qtyVal > 0) {
                      $qty = $qtyVal;
                  }
              }

              DB::table('material_product')->updateOrInsert(
                  [
                      'business_id' => $businessId,
                      'product_id'  => $productId,
                      'material_id' => $material->ID,
                  ],
                  [
                      'quantity'   => $qty,
                      'created_at' => now(),
                      'updated_at' => now(),
                  ]
              );
          }

        return redirect()->action([self::class, 'index'])->with('status', ['success' => 1, 'msg' => __('messages.updated_success')]);
    }

    public function destroy($id)
    {
        if (! auth()->user()->can('product.delete')) {
            \Log::warning('Material destroy aborted: unauthorized', [
                'material_id' => $id,
                'user_id' => optional(auth()->user())->id,
            ]);
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        \Log::info('Material destroy called', [
            'material_id_param' => $id,
            'business_id' => $business_id,
            'user_id' => optional(auth()->user())->id,
        ]);

        $material = Material::where('business_id', $business_id)
            ->where('ID', $id)
            ->first();
        if (!$material) {
            \Log::error('Material destroy: material not found', [
                'material_id' => $id,
                'business_id' => $business_id,
            ]);
            return ['success' => false, 'msg' => 'Material no encontrado'];
        }

        \Log::info('Material destroy: material loaded', [
            'material_id_param' => $id,
            'material_id_model' => $material->ID ?? null,
            'material_nombre' => $material->nombre,
        ]);

        $oldProductIds = is_array($material->productos_linkeados) ? $material->productos_linkeados : [];
        $oldProductIds = array_values(array_unique(array_filter(array_map('intval', $oldProductIds))));

        \Log::info('Material destroy: old linked products', [
            'material_id_param' => $id,
            'material_id_model' => $material->ID ?? null,
            'old_product_ids' => $oldProductIds,
        ]);

        // Al eliminar el insumo, quitarlo de los productos vinculados (JSON products.materiales)
        if (!empty($oldProductIds)) {
            \Log::info('Material destroy: calling syncProductsForMaterial to detach from products', [
                'material_id_param' => $id,
                'material_id_model' => $material->ID ?? null,
            ]);
            $this->syncProductsForMaterial($material, $oldProductIds, [], $business_id);
        } else {
            \Log::info('Material destroy: no linked products to detach', [
                'material_id' => $material->ID,
            ]);
        }

        // Eliminar vínculos de costo en la pivot material_product (si existe)
        try {
            if (\Schema::hasTable('material_product')) {
                \Log::info('Material destroy: deleting pivot rows from material_product', [
                    'material_id_param' => $id,
                    'material_id_model' => $material->ID ?? null,
                    'business_id' => $business_id,
                ]);

                DB::table('material_product')
                    ->where('business_id', $business_id)
                    ->where('material_id', $id)
                    ->delete();
            } else {
                \Log::info('Material destroy: material_product table does not exist, skipping pivot delete');
            }
        } catch (\Throwable $e) {
            \Log::warning('Material destroy: error deleting material_product rows', [
                'material_id_param' => $id,
                'material_id_model' => $material->ID ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        \Log::info('Material destroy: deleting material record', [
            'material_id_param' => $id,
            'material_id_model' => $material->ID ?? null,
        ]);

        // Eliminamos usando query directa sobre la PK real
        Material::where('business_id', $business_id)
            ->where('ID', $id)
            ->delete();

        \Log::info('Material destroy: completed successfully', [
            'material_id_param' => $id,
        ]);

        return [ 'success' => true ];
    }

    public function adjustStock(Request $request, $id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'action' => 'required|in:increase,decrease,set',
            'quantity' => 'required|integer|min:0',
        ]);

        $business_id = $request->session()->get('user.business_id');

        $qty = (int) $validated['quantity'];

        if ($validated['action'] === 'increase') {
            Material::where('business_id', $business_id)
                ->where('ID', $id)
                ->increment('unidades_en_stock', $qty);
        } elseif ($validated['action'] === 'decrease') {
            // Clamp to non-negative using SQL GREATEST
            \DB::table('materiales')
                ->where('business_id', $business_id)
                ->where('ID', $id)
                ->update(['unidades_en_stock' => \DB::raw('GREATEST(0, unidades_en_stock - '.($qty).')')]);
        } else {
            Material::where('business_id', $business_id)
                ->where('ID', $id)
                ->update(['unidades_en_stock' => $qty]);
        }

        $new = Material::where('business_id', $business_id)
            ->where('ID', $id)
            ->value('unidades_en_stock');

        return [ 'success' => true, 'stock' => (int)$new ];
    }

    public function productsOptions(Request $request)
    {
        if (! auth()->user()->can('product.view') && ! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $term = $request->get('q');
        $query = Product::where('business_id', $business_id)->select('id', 'name as text')->orderBy('name');
        if (!empty($term)) {
            $query->where('name', 'like', '%'.$term.'%');
        }
        return [ 'results' => $query->limit(20)->get() ];
    }

    public function materialsOptions(Request $request)
    {
        if (! auth()->user()->can('product.view') && ! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $ids = $request->get('ids');
        $term = $request->get('q');
        $query = \DB::table('materiales')->where('business_id', $business_id);
        if (!empty($ids)) {
            $ids_array = array_map('intval', is_array($ids) ? $ids : explode(',', $ids));
            $query->whereIn('ID', $ids_array);
        }
        if (!empty($term)) {
            $query->where('nombre', 'like', '%'.$term.'%');
        }
        $results = $query->select([\DB::raw('ID as id'), 'nombre as text'])->orderBy('nombre')->limit(50)->get();
        return ['results' => $results];
    }

    /**
     * Sincroniza la relación insumo <-> productos desde el lado del insumo.
     *
     * @param  \App\Material  $material
     * @param  array  $oldProductIds
     * @param  array  $newProductIds
     * @param  int  $business_id
     * @return void
     */
    protected function syncProductsForMaterial(Material $material, array $oldProductIds, array $newProductIds, $business_id)
    {
        $oldProductIds = array_values(array_unique(array_filter(array_map('intval', $oldProductIds))));
        $newProductIds = array_values(array_unique(array_filter(array_map('intval', $newProductIds))));

        $toUpdate = array_values(array_unique(array_merge($oldProductIds, $newProductIds)));
        if (empty($toUpdate)) {
            return;
        }

        $products = Product::where('business_id', $business_id)
            ->whereIn('id', $toUpdate)
            ->get();

        foreach ($products as $product) {
            $materialsForProduct = is_array($product->materiales) ? $product->materiales : [];
            $materialsForProduct = array_values(array_unique(array_filter(array_map('intval', $materialsForProduct))));

            if (in_array($product->id, $newProductIds, true)) {
                if (!in_array($material->ID, $materialsForProduct, true)) {
                    $materialsForProduct[] = $material->ID;
                }
            } else {
                $materialsForProduct = array_values(array_diff($materialsForProduct, [$material->ID]));
            }

            $product->materiales = $materialsForProduct;
            $product->save();
        }
    }

    /**
     * Sincroniza la tabla pivot material_product para un insumo.
     *
     * @param  int    $business_id
     * @param  int    $materialId
     * @param  array  $productIds
     * @return void
     */
    protected function syncMaterialProductsPivot($business_id, $materialId, array $productIds): void
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));

        // Borrar vínculos que ya no están
        DB::table('material_product')
            ->where('business_id', $business_id)
            ->where('material_id', $materialId)
            ->when(!empty($productIds), function ($q) use ($productIds) {
                $q->whereNotIn('product_id', $productIds);
            })
            ->delete();

        // Insertar/actualizar con cantidad por defecto = 1
        foreach ($productIds as $productId) {
            DB::table('material_product')->updateOrInsert(
                [
                    'business_id' => $business_id,
                    'product_id'  => $productId,
                    'material_id' => $materialId,
                ],
                [
                    'quantity'   => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

