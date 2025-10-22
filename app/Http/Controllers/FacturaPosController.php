<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\BusinessLocation;


class FacturaPosController extends Controller
{
    /**
     * Muestra la vista para generar la factura POS.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function FacturaPos(Request $request)
    {
        $price_total = $request->input('price_total');
        $business_id = $request->session()->get('user.business_id');
    
        // Obtener locaciones comerciales como array
        $business_locations = \App\BusinessLocation::forDropdown($business_id, false)->toArray();
    
        // Obtener primera locación como default
        $default_location = null;
        if (!empty($business_locations)) {
            $first_key = array_key_first($business_locations);
            $default_location = (object)['id' => $first_key];
        }
    
        return view('sale_pos.generar_factura_pos', compact(
            'price_total',
            'business_locations',
            'default_location'
        ));
    }


    
    public function MasivaPos(Request $request)
    {
        return view('sale_pos.carga_masiva_pos');
    }

}
