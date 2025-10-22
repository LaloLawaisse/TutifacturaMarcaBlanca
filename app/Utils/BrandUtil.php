<?php

namespace App\Utils;

use App\Brands;
use App\Transaction;
use DB;

class BrandsUtil
{
    /**
     * Crea la marca por defecto para un negocio.
     *
     * @param int $business_id
     * @param int $user_id
     * @return void
     */
    public static function createDefaultBrand($business_id, $user_id)
    {
        $brand = [
            'business_id' => $business_id,
            'name'        => 'Default',
            'description' => 'Marca Predeterminada',
            'created_by'  => $user_id,
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];

        Brands::create($brand);
    }
}
