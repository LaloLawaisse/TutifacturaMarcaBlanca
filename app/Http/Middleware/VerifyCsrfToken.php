<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/install/details',
        '/install/post-details',
        '/install/install-alternate',
        '/api/ecom/customers',
        '/api/ecom/orders',
        '/webhook/*',
        '/crear-factura',
        '/sale_pos/generar-factura-pos',
        'superadmin/business',
        '/renovar-token',
        '/get-taxpayer-details',
    ];
}
