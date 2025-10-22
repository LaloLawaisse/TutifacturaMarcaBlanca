<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    public function callback(Request $request)
    {
        Log::info('Iniciando callback de MercadoLibre', ['query_params' => $request->query()]);
    
        $code = $request->query('code');
    
        if (!$code) {
            Log::error('No se recibió el código de autorización.');
            return redirect()->route('codigoAprobado')->with('error', 'No se recibió el código de autorización.');
        }
    
        Log::info('Código de autorización recibido', ['code' => $code]);
    
        $client_id = env('MERCADOLIBRE_CLIENT_ID');
        $client_secret = env('MERCADOLIBRE_CLIENT_SECRET');
        $redirect_uri = env('MERCADOLIBRE_REDIRECT_URI');
    
        Log::info('Variables de entorno obtenidas', [
            'client_id' => $client_id,
            'client_secret' => $client_secret ? 'OK' : 'NO DEFINIDO',
            'redirect_uri' => $redirect_uri,
        ]);
    
        // Intercambiar el código por un access token
        $response = Http::post('https://api.mercadolibre.com/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $code,
            'redirect_uri' => $redirect_uri,
        ]);
    
        if ($response->failed()) {
            Log::error('Error en la solicitud de token a MercadoLibre', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
    
            return redirect()->route('codigoAprobado')->with('error', 'Error al obtener el token de acceso.');
        }
    
        $data = $response->json();
        Log::info('Respuesta de MercadoLibre', ['response' => $data]);
    
        // Guardar el access token en la base de datos
        $user = Auth::user();
        if ($user && $user->business) {
            Log::info('Guardando tokens en la base de datos', [
                'user_id' => $user->id,
                'business_id' => $user->business->id,
                'mercadolibre_access_token' => substr($data['access_token'], 0, 10) . '...', // Ocultar parte del token por seguridad
                'mercadolibre_refresh_token' => substr($data['refresh_token'], 0, 10) . '...',
                'expires_in' => $data['expires_in'],
            ]);
    
            $user->business->update([
                'mercadolibre_access_token' => $data['access_token'],
                'mercadolibre_refresh_token' => $data['refresh_token'],
                'mercadolibre_token_expires' => now()->addSeconds($data['expires_in']),
            ]);
        } else {
            Log::error('No se pudo guardar el token porque el usuario o el negocio no están definidos.');
        }
    
        Log::info('Autorización completada correctamente');
    
        return redirect()->route('codigoAprobado', ['code' => $code])
             ->with('success', 'Autorización completada correctamente.');

    }
    
    public function callbackMP(Request $request)
    {
        Log::info('Iniciando callback de MercadoLibre', ['query_params' => $request->query()]);
    
        $code = $request->query('code');
    
        if (!$code) {
            Log::error('No se recibió el código de autorización.');
            return redirect()->route('codigoAprobado')->with('error', 'No se recibió el código de autorización.');
        }
    
        Log::info('Código de autorización recibido', ['code' => $code]);
    
        $client_id = env('MERCADOPAGO_CLIENT_ID');
        $client_secret = env('MERCADOPAGO_CLIENT_SECRET');
        $redirect_uri = env('MERCADOPAGO_REDIRECT_URI');
    
        Log::info('Variables de entorno obtenidas', [
            'client_id' => $client_id,
            'client_secret' => $client_secret ? 'OK' : 'NO DEFINIDO',
            'redirect_uri' => $redirect_uri,
        ]);
    
        // Intercambiar el código por un access token
        $response = Http::post('https://api.mercadopago.com/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $code,
            'redirect_uri' => $redirect_uri,
        ]);
    
        if ($response->failed()) {
            Log::error('Error en la solicitud de token a MercadoLibre', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
    
            return redirect()->route('codigoAprobado')->with('error', 'Error al obtener el token de acceso.');
        }
    
        $data = $response->json();
        Log::info('Respuesta de MercadoLibre', ['response' => $data]);
    
        // Guardar el access token en la base de datos
        $user = Auth::user();
        if ($user && $user->business) {
            Log::info('Guardando tokens en la base de datos', [
                'user_id' => $user->id,
                'business_id' => $user->business->id,
                'mercadopago_access_token' => substr($data['access_token'], 0, 10) . '...', // Ocultar parte del token por seguridad
                'mercadopago_refresh_token' => substr($data['refresh_token'], 0, 10) . '...',
                'expires_in' => $data['expires_in'],
            ]);
    
            $user->business->update([
                'mercadopago_access_token' => $data['access_token'],
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
    
    public function codigoAprobado(Request $request)
    {
        // Obtener el código desde la URL
        $code = $request->query('code');
    
        if (!$code) {
            Log::error('codigoAprobado: No se recibió el código de autorización.');
            return redirect('/')->with('error', 'No se recibió el código de autorización.');
        }
    
        // Obtener el usuario autenticado y su negocio
        $user = Auth::user();
        if (!$user || !$user->business) {
            Log::error('codigoAprobado: Usuario no autenticado o sin negocio asociado.');
            return redirect('/')->with('error', 'No se encontró un negocio asociado al usuario.');
        }
    
        // Guardar el código en la base de datos del negocio
        $user->business->update(['tg_token' => $code]);
        Log::info("codigoAprobado: Token guardado en la BD: $code");
    
        // Datos de la API de MercadoLibre
        $client_id = env('MERCADOLIBRE_CLIENT_ID');
        $client_secret = env('MERCADOLIBRE_CLIENT_SECRET');
        $redirect_uri = env('MERCADOLIBRE_REDIRECT_URI');
    
        // Hacer la solicitud para intercambiar el código por el token de acceso
        $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'code'          => $code,
            'redirect_uri'  => $redirect_uri,
        ]);
    
        if ($response->failed()) {
            Log::error('codigoAprobado: Error en la solicitud a MercadoLibre.', ['response' => $response->body()]);
            return redirect('/')->with('error', 'Error al obtener el token de acceso.');
        }
    
        // Guardar la respuesta en la base de datos
        $data = $response->json();
        $user->business->update([
            'mercadolibre_access_token' => $data['mercadolibre_access_token'],
            'mercadolibre_refresh_token' => $data['mercadolibre_refresh_token'],
            'mercadolibre_token_expires' => now()->addSeconds($data['expires_in']),
        ]);
    
        Log::info("codigoAprobado: Token de acceso guardado correctamente.");
        return redirect('/')->with('success', 'Autorización completada correctamente.');
    }
    
    public function codigoAprobadoMP(Request $request)
    {
        // Obtener el código desde la URL
        $code = $request->query('code');
    
        if (!$code) {
            Log::error('codigoAprobado: No se recibió el código de autorización.');
            return redirect('/')->with('error', 'No se recibió el código de autorización.');
        }
    
        // Obtener el usuario autenticado y su negocio
        $user = Auth::user();
        if (!$user || !$user->business) {
            Log::error('codigoAprobado: Usuario no autenticado o sin negocio asociado.');
            return redirect('/')->with('error', 'No se encontró un negocio asociado al usuario.');
        }
    
        // Guardar el código en la base de datos del negocio
        $user->business->update(['tg_token_mp' => $code]);
        Log::info("codigoAprobado: Token guardado en la BD: $code");
    
        // Datos de la API de MercadoLibre
        $client_id = env('MERCADOPAGO_CLIENT_ID');
        $client_secret = env('MERCADOPAGO_CLIENT_SECRET');
        $redirect_uri = env('MERCADOPAGO_REDIRECT_URI');
    
        // Hacer la solicitud para intercambiar el código por el token de acceso
        $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'code'          => $code,
            'redirect_uri'  => $redirect_uri,
        ]);
    
        if ($response->failed()) {
            Log::error('codigoAprobado: Error en la solicitud a MercadoLibre.', ['response' => $response->body()]);
            return redirect('/')->with('error', 'Error al obtener el token de acceso.');
        }
    
        // Guardar la respuesta en la base de datos
        $data = $response->json();
        $user->business->update([
            'mercadopago_access_token' => $data['mercadopago_access_token'],
            'mercadopago_refresh_token' => $data['mercadopago_refresh_token'],
            'mercadopago_token_expires' => now()->addSeconds($data['expires_in']),
        ]);
    
        Log::info("codigoAprobado: Token de acceso guardado correctamente.");
        return redirect('/')->with('success', 'Autorización completada correctamente.');
    }
    
    public function renovarToken(Request $request)
    {
        Log::info("Entramos a renovarToken");
    
        $user = Auth::user();
    
        if (!$user || !$user->business) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }
    
        $business = $user->business;
    
        // 1. Renovar el token
        $refreshToken = $business->mercadolibre_refresh_token;
        Log::info('Usando refresh_token para renovar', ['refresh_token' => $refreshToken]);
    
        try {
            $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
                'grant_type' => 'refresh_token',
                'client_id' => env('MERCADOLIBRE_CLIENT_ID'),
                'client_secret' => env('MERCADOLIBRE_CLIENT_SECRET'),
                'refresh_token' => $refreshToken
            ]);
    
            $tokenData = $response->json();
    
            // 🔍 Validar que se recibió access_token
            if (!$response->ok() || !isset($tokenData['access_token'])) {
                Log::error('La respuesta de renovación no contiene un access_token.', ['response' => $tokenData]);
    
                return response()->json([
                    'success' => false,
                    'message' => 'Renovación fallida. Verificá el refresh token.',
                    'response' => $tokenData
                ], 400);
            }
    
            // 2. Obtener seller ID
            $userResponse = Http::withToken($tokenData['access_token'])
                ->get('https://api.mercadolibre.com/users/me');
    
            $userData = $userResponse->json();
    
            // 3. Actualizar base de datos
            $business->update([
                'mercadolibre_access_token' => $tokenData['access_token'],
                'mercadolibre_refresh_token' => $tokenData['refresh_token'],
                'mercadolibre_token_expires' => now()->addSeconds($tokenData['expires_in']),
                'mercadolibre_seller_id' => $userData['id'] ?? null
            ]);
    
            Log::info("Token renovado exitosamente");
    
            $logData = [
                'success' => true,
                'access_token' => $tokenData['access_token'],
                'seller_id' => $userData['id'] ?? null
            ];
    
            Log::info('Datos de respuesta:', $logData);
    
            return response()->json($logData);
    
        } catch (\Exception $e) {
            Log::error('Error renovando token: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function renovarTokenMP(Request $request)
    {
        Log::info("Entramos a renovarToken");
        $user = Auth::user();
        
        if (!$user || !$user->business) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }
    
        $business = $user->business;
        
        // 1. Renovar el token
        $refreshToken = $business->mercadopago_refresh_token;
        
        try {
            $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
                'grant_type' => 'refresh_token',
                'client_id' => env('MERCADOPAGO_CLIENT_ID'),
                'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
                'refresh_token' => $refreshToken
            ]);
    
            $tokenData = $response->json();
            
            // 2. Obtener seller ID
            $userResponse = Http::withToken($tokenData['access_token'])
                ->get('https://api.mercadopago.com/users/me');
                
            $userData = $userResponse->json();
            
            // 3. Actualizar base de datos
            $business->update([
                'mercadopago_access_token' => $tokenData['access_token'], // ← Corregido
                'mercadopago_refresh_token' => $tokenData['refresh_token'], // ← Corregido
                'mercadopago_token_expires' => now()->addSeconds($tokenData['expires_in']),
                'mercadopago_seller_id' => $userData['id'] // ← Corregido
            ]);
            
            Log::info("Llegamos a la response");
            $logData = [
                'success' => true,
                'access_token' => $tokenData['access_token'],
                'seller_id' => $userData['id']
            ];
            
            Log::info('Datos de respuesta:', $logData);
            
            return response()->json($logData);
    
        } catch (\Exception $e) {
            Log::error('Error renovando token: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor'
            ], 500);
        }
    }
    
    public function obtenerOrdenes(Request $request)
    {
        Log::info('Entramos a Obtener ordenes');
        $user = Auth::user();
        
        if (!$user || !$user->business) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }
    
        $business = $user->business;
    
        try {
            $response = Http::withToken($business->mercadolibre_access_token) // ← Corregido
                ->get('https://api.mercadolibre.com/orders/search', [
                    'seller' => $business->mercadolibre_seller_id, // ← Corregido
                    'order.status' => 'paid'
                ]);
            
            Log::info('Response de Obtener ordenes');
            return response()->json([
                'success' => true,
                'orders' => $response->json()
            ]);
    
        } catch (\Exception $e) {
            Log::error('Error obteniendo órdenes: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener órdenes'
            ], 500);
        }
    }
    
    public function obtenerOrdenesMP(Request $request)
    {
        Log::info('MP: obtener órdenes pagadas (status=closed)');
        $user = Auth::user();
    
        if (!$user || !$user->business) {
            return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
        }
    
        $business = $user->business;
    
        try {
            $params = [
                'status' => 'closed', // órdenes pagadas
                // 'limit' => 50,      // opcional: paginación
                // 'offset' => 0,      // opcional
            ];
    
            $response = Http::withToken($business->mercadopago_access_token)
                ->get('https://api.mercadopago.com/merchant_orders/search', $params);
    
            if ($response->failed()) {
                Log::error('MP: error en /merchant_orders/search', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return response()->json(['success' => false, 'message' => 'Error en MercadoPago'], 502);
            }
    
            $raw = $response->json();
    
            // Normalizar y extraer datos clave, incluyendo items(title, unit_price, quantity)
            $orders = [];
            foreach (($raw['elements'] ?? []) as $mo) {
                $items = [];
                foreach (($mo['items'] ?? []) as $it) {
                    $items[] = [
                        'title'      => $it['title']      ?? null,
                        'unit_price' => $it['unit_price'] ?? null,
                        'quantity'   => $it['quantity']   ?? null,
                    ];
                }
    
                $payments = $mo['payments'] ?? [];
                $mainPaymentId = $payments[0]['id'] ?? null;
    
                $orders[] = [
                    'merchant_order_id' => $mo['id']          ?? null,
                    'status'            => $mo['status']      ?? null,   // debería venir "closed"
                    'date_created'      => $mo['date_created']?? null,
                    'items'             => $items,
                    'main_payment_id'   => $mainPaymentId,
                ];
            }
    
            return response()->json(['success' => true, 'orders' => [
                'count'    => $raw['paging']['total'] ?? count($orders),
                'elements' => $orders
            ]]);
    
        } catch (\Exception $e) {
            Log::error('MP: excepción obteniendo órdenes', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al obtener órdenes'], 500);
        }
    }

    
    public function obtenerOrdenesFecha(Request $request)
    {
        Log::info('Entramos a obtener órdenes por fecha');
        $user = Auth::user();
        
        if (!$user || !$user->business) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }
    
        $business = $user->business;
    
        // Obtener las fechas del request; si no se envían, se usan valores por defecto o se omiten los parámetros
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
    
        // Formatear las fechas al formato requerido por la API de MercadoLibre
        // Ejemplo: '2015-07-01T00:00:00.000-00:00'
        $params = [
            'seller' => $business->mercadolibre_seller_id,
            'order.status' => 'paid'
        ];
    
        if ($date_from) {
            $params['order.date_created.from'] = date('Y-m-d\T00:00:00.000-00:00', strtotime($date_from));
        }
        if ($date_to) {
            $params['order.date_created.to'] = date('Y-m-d\T00:00:00.000-00:00', strtotime($date_to));
        }
    
        try {
            $response = Http::withToken($business->mercadolibre_access_token)
                ->get('https://api.mercadolibre.com/orders/search', $params);
            
            Log::info('Response de obtener órdenes por fecha');
            return response()->json([
                'success' => true,
                'orders' => $response->json()
            ]);
    
        } catch (\Exception $e) {
            Log::error('Error obteniendo órdenes por fecha: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener órdenes'
            ], 500);
        }
    }
    
    public function obtenerOrdenesFechaMP(Request $request)
    {
        Log::info('MP: obtener órdenes pagadas por fecha');
        $user = Auth::user();
    
        if (!$user || !$user->business) {
            return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
        }
    
        $business = $user->business;
    
        // Fechas (YYYY-MM-DD) -> ISO (UTC)
        $date_from = $request->input('date_from'); // ej: 2025-08-01
        $date_to   = $request->input('date_to');   // ej: 2025-08-08
    
        $params = [
            'status' => 'closed', // solo pagadas
        ];
    
        if ($date_from) {
            // 00:00:00Z del día desde
            $params['date_created.from'] = gmdate('Y-m-d\T00:00:00.000\Z', strtotime($date_from));
        }
        if ($date_to) {
            // 23:59:59Z del día hasta (opcional: ajustar a tope del día)
            $params['date_created.to'] = gmdate('Y-m-d\T23:59:59.999\Z', strtotime($date_to));
        }
    
        try {
            $response = Http::withToken($business->mercadopago_access_token)
                ->get('https://api.mercadopago.com/merchant_orders/search', $params);
    
            if ($response->failed()) {
                Log::error('MP: error en /merchant_orders/search (fecha)', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return response()->json(['success' => false, 'message' => 'Error en MercadoPago'], 502);
            }
    
            $raw = $response->json();
    
            $orders = [];
            foreach (($raw['elements'] ?? []) as $mo) {
                $items = [];
                foreach (($mo['items'] ?? []) as $it) {
                    $items[] = [
                        'title'      => $it['title']      ?? null,
                        'unit_price' => $it['unit_price'] ?? null,
                        'quantity'   => $it['quantity']   ?? null,
                    ];
                }
    
                $payments = $mo['payments'] ?? [];
                $mainPaymentId = $payments[0]['id'] ?? null;
    
                $orders[] = [
                    'merchant_order_id' => $mo['id']          ?? null,
                    'status'            => $mo['status']      ?? null,
                    'date_created'      => $mo['date_created']?? null,
                    'items'             => $items,
                    'main_payment_id'   => $mainPaymentId,
                ];
            }
    
            return response()->json(['success' => true, 'orders' => [
                'count'    => $raw['paging']['total'] ?? count($orders),
                'elements' => $orders
            ]]);
    
        } catch (\Exception $e) {
            Log::error('MP: excepción obteniendo órdenes por fecha', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al obtener órdenes'], 500);
        }
    }

}

