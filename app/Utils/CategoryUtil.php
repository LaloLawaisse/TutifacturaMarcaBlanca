<?php

namespace App\Utils;

use App\Category;
use App\Transaction;
use DB;

class CategoryUtil
{
    /**
     * Crea la categoría por defecto para un negocio.
     *
     * @param int $business_id
     * @param int $user_id
     * @return void
     */
    public static function createDefaultCategory($business_id, $user_id)
    {
        $category = [
            'name'        => 'Default',
            'business_id' => $business_id,
            'short_code'  => 999,
            'parent_id'   => 0,
            'created_by'  => $user_id,
            'description' => 'Categoria Predeterminada',
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];

        Category::create($category);
    }
}
