<?php

namespace App\Utils;

use App\TaxRate;
use App\Transaction;
use DB;

class TaxRatesUtil
{
    /**
     * Crea los TaxRates por defecto para un negocio.
     *
     * @param int $business_id
     * @param int $user_id
     * @return void
     */
    public static function createDefaultTaxRates($business_id, $user_id)
    {
        $taxRates = [
            [
                'business_id'   => $business_id,
                'name'          => 'IVA21',
                'amount'        => 21,
                'is_tax_group'  => 0,
                'for_tax_group' => 0,
                'created_by'    => $user_id,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'business_id'   => $business_id,
                'name'          => 'IVA27',
                'amount'        => 27,
                'is_tax_group'  => 0,
                'for_tax_group' => 0,
                'created_by'    => $user_id,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'business_id'   => $business_id,
                'name'          => 'IVA10.5',
                'amount'        => 10.5,
                'is_tax_group'  => 0,
                'for_tax_group' => 0,
                'created_by'    => $user_id,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
        ];

        foreach ($taxRates as $taxRate) {
            TaxRates::create($taxRate);
        }
    }
}
