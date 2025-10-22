<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    /**
     * Intercambia el código de autorización por los tokens de acceso.
     */
    public function callback(Request $request)
    {
        Log::info('Iniciando callback de MercadoPago', ['query_params' => $request->query()]);
        
        $code = $request->query('code');
        if (!$code) {
            Log::error('No se recibió el código de autorización.');
            return redirect()->route('codigoAprobado')->with('error', 'No se recibió el código de autorización.');
        }
        
        $client_id     = env('MERCADOPAGO_CLIENT_ID');
        $client_secret = env('MERCADOPAGO_CLIENT_SECRET');
        $redirect_uri  = env('MERCADOPAGO_REDIRECT_URI');
        
        Log::info('Variables de entorno obtenidas', [
            'client_id'     => $client_id,
            'client_secret' => $client_secret ? 'OK' : 'NO DEFINIDO',
            'redirect_uri'  => $redirect_uri,
        ]);
        
        // Intercambiar el código por tokens en MercadoPago
        $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'code'          => $code,
            'redirect_uri'  => $redirect_uri,
        ]);
        
        if ($response->failed()) {
            Log::error('Error en la solicitud de token a MercadoPago', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return redirect()->route('codigoAprobado')->with('error', 'Error al obtener el token de acceso.');
        }
        
        $data = $response->json();
        Log::info('Respuesta de MercadoPago', ['response' => $data]);
        
        // Guardar el access token y refresh token en la base de datos
        $user = Auth::user();
        if ($user && $user->business) {
            Log::info('Guardando tokens en la base de datos', [
                'user_id'                         => $user->id,
                'business_id'                     => $user->business->id,
                'mercadopago_access_token'        => substr($data['access_token'], 0, 10) . '...',
                'mercadopago_refresh_token'       => substr($data['refresh_token'], 0, 10) . '...',
                'expires_in'                      => $data['expires_in'],
            ]);
            
            $user->business->update([
                'mercadopago_access_token'  => $data['access_token'],
                'mercadopago_refresh_token' => $data['refresh_token'],
                'mercadopago_token_expires' => now()->addSeconds($data['expires_in']),
            ]);
        } else {
            Log::error('No se pudo guardar el token porque el usuario o el negocio no están definidos.');
        }
        
        Log::info('Autorización completada correctamente');
        return redirect()->route('codigoAprobado', ['code' => $code])
             ->with('success', 'Autorización completada correctamente.');
    }
    
    /**
     * Renueva el token de acceso utilizando el refresh token.
     */
    public function renovarToken(Request $request)
    {
        Log::info("Iniciando renovación de token en MercadoPago");
        $user = Auth::user();
        if (!$user || !$user->business) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }
        
        $business = $user->business;
        $refreshToken = $business->mercadopago_refresh_token;
        
        try {
            $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
                'grant_type'    => 'refresh_token',
                'client_id'     => env('MERCADOPAGO_CLIENT_ID'),
                'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
                'refresh_token' => $refreshToken,
            ]);
            
            $tokenData = $response->json();
            
            // Actualizar la base de datos con el nuevo token y fecha de expiración
            $business->update([
                'mercadopago_access_token'  => $tokenData['access_token'],
                'mercadopago_refresh_token' => $tokenData['refresh_token'],
                'mercadopago_token_expires' => now()->addSeconds($tokenData['expires_in']),
            ]);
            
            Log::info('Token renovado correctamente en MercadoPago', [
                'access_token' => $tokenData['access_token'],
            ]);
            
            return response()->json([
                'success'      => true,
                'access_token' => $tokenData['access_token']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error renovando token en MercadoPago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor'
            ], 500);
        }
    }
    
    /**
     * Busca órdenes de MercadoPago utilizando el endpoint merchant_orders/search.
     */
    public function searchMerchantOrders(Request $request)
    {
        Log::info('Buscando órdenes en MercadoPago');
        $user = Auth::user();
        if (!$user || !$user->business) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }
        
        $business = $user->business;
        $accessToken = $business->mercadopago_access_token;
        
        // Puedes leer parámetros de filtrado desde la solicitud
        $status   = $request->input('status');      // por ejemplo, "closed" o "pending"
        $dateFrom = $request->input('date_from');     // Formato: YYYY-MM-DD
        $dateTo   = $request->input('date_to');       // Formato: YYYY-MM-DD
        
        // Preparar parámetros para la consulta
        $params = [];
        if ($status) {
            $params['status'] = $status;
        }
        if ($dateFrom) {
            // Ajusta el formato según la documentación de MercadoPago si es necesario
            $params['date_created.from'] = date('Y-m-d\T00:00:00.000-00:00', strtotime($dateFrom));
        }
        if ($dateTo) {
            $params['date_created.to'] = date('Y-m-d\T00:00:00.000-00:00', strtotime($dateTo));
        }
        
        // Agregar el access token puede hacerse mediante el header (Bearer) o como parámetro
        try {
            $response = Http::withToken($accessToken)
                ->get('https://api.mercadopago.com/merchant_orders/search', $params);
            
            Log::info('Respuesta de órdenes de MercadoPago', ['response' => $response->json()]);
            return response()->json([
                'success' => true,
                'orders'  => $response->json()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo órdenes de MercadoPago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener órdenes'
            ], 500);
        }
    }
    
    
    public function obtenerDatosFiscales(Request $request)
    {
        $paymentId = $request->input('payment_id');
    
        if (!$paymentId) {
            return response()->json([
                'error' => true,
                'message' => 'Falta el payment_id'
            ], 400);
        }
    
        $token = auth()->user()->business->mercadopago_access_token;
    
        $response = Http::withToken($token)
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");
    
        if ($response->successful()) {
            $data = $response->json();
    
            return response()->json([
                'identification' => $data['payer']['identification'] ?? [],
                'taxpayer_type' => $data['payer']['type'] ?? null
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'No se pudo obtener los datos fiscales del comprador.',
                'status' => $response->status(),
                'body' => $response->body(),
            ], $response->status());
        }
    }

}
