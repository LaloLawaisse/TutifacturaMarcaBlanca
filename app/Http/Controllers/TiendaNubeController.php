<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;



class TiendaNubeController extends Controller
{
    public function handleCallback(Request $request)
    {
    
        $code = $request->query('code');
        if (!$code) {
            return redirect('/home')->with('error', 'Código de autorización no recibido.');
        }
    
        $response = Http::post('https://www.tiendanube.com/apps/authorize/token', [
            'client_id' => env('TIENDANUBE_CLIENT_ID'),
            'client_secret' => env('TIENDANUBE_CLIENT_SECRET'),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'https://app.trevitsoft.com/tiendanube/oauth/callback',
        ]);
        
        Log::info('Redirect URI generado por route()', [
            'uri' => route('tiendanube.callback')
        ]);

    
        if (!$response->ok()) {
            Log::error('Error al obtener token de Tiendanube', ['response' => $response->body()]);
            return redirect('/home')->with('error', 'Error al obtener el token de acceso.');
        }
    
        $data = $response->json();
        Log::info('Token de Tiendanube recibido', $data);
    
        if (!isset($data['user_id']) || !isset($data['access_token'])) {
            Log::error('Datos incompletos del token recibido', ['data' => $data]);
            return redirect('/home')->with('error', 'Datos incompletos al autorizar la tienda.');
        }
    
        $storeId = $data['user_id'];
        $accessToken = $data['access_token'];
    
        $business = auth()->user()->business;
    
        if (!$business) {
            Log::error('El usuario no tiene un business asociado.', ['user_id' => auth()->id()]);
            return redirect('/home')->with('error', 'No se pudo asociar la tienda a tu cuenta.');
        }
    
        $business->tiendanube_id = $storeId;
        $business->tiendanube_access_token = $accessToken;
    
        if (!$business->save()) {
            Log::error('Error al guardar los datos en el modelo Business');
            return redirect('/home')->with('error', 'No se pudo guardar la tienda.');
        }
    
        Log::info('Tienda Tiendanube asociada correctamente', [
            'business_id' => $business->id,
            'store_id' => $storeId
        ]);
    
        return redirect('/home')->with('success', 'Tienda conectada exitosamente a Tiendanube.');
    }

    public function obtenerOrdenes(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
    
        // Verificar si el usuario tiene un negocio asociado
        $business = $user->business;
    
        if (!$business || !$business->tiendanube_id || !$business->tiendanube_access_token) {
            Log::warning('Faltan credenciales de Tiendanube para el usuario.', ['user_id' => $user->id]);
            return response()->json(['error' => 'Credenciales de Tiendanube no encontradas.'], 400);
        }
    
        $storeId = $business->tiendanube_id;
        $accessToken = $business->tiendanube_access_token;
    
        $url = "https://api.tiendanube.com/v1/{$storeId}/orders";
    
        $response = Http::withHeaders([
            'Authentication' => 'bearer ' . $accessToken, // ← Asegurate que esto esté bien formateado
            'User-Agent' => 'Trevitsoft (contacto@trevitsoft.com)',
        ])->get("https://api.tiendanube.com/v1/{$storeId}/orders");

    
        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'orders' => $response->json()
            ]);
        } else {
            Log::error('Error al obtener órdenes de Tiendanube.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json([
                'error' => 'No se pudieron obtener las órdenes',
                'detalle' => $response->body()
            ], $response->status());
        }
    }



    
    public function uninstall(Request $request)
    {
        Log::info('Webhook recibido: desinstalación', ['payload' => $request->all()]);
    
        $storeId = $request->input('store_id');
    
        if ($storeId) {
            $business = Business::where('tiendanube_id', $storeId)->first();
    
            if ($business) {
                $business->tiendanube_id = null;
                $business->tiendanube_access_token = null;
                $business->save();
    
                Log::info("Datos Tiendanube eliminados para business ID: {$business->id}");
            } else {
                Log::warning("No se encontró ningún business con tiendanube_id = {$storeId}");
            }
        } else {
            Log::error('No se recibió store_id en el webhook de desinstalación');
        }
    
        return response()->json(['status' => 'ok']);
    }


    public function eliminate(Request $request)
    {
        Log::info('Webhook recibido: eliminar datos cliente', ['payload' => $request->all()]);
                Log::info('Webhook recibido: desinstalación', ['payload' => $request->all()]);
    
        $storeId = $request->input('store_id');
    
        if ($storeId) {
            $business = Business::where('tiendanube_id', $storeId)->first();
    
            if ($business) {
                $business->tiendanube_id = null;
                $business->tiendanube_access_token = null;
                $business->save();
    
                Log::info("Datos Tiendanube eliminados para business ID: {$business->id}");
            } else {
                Log::warning("No se encontró ningún business con tiendanube_id = {$storeId}");
            }
        } else {
            Log::error('No se recibió store_id en el webhook de desinstalación');
        }
    
        return response()->json(['status' => 'ok']);

    }

    public function dataRequest(Request $request)
    {
        Log::info('Webhook recibido: solicitud de datos cliente', ['payload' => $request->all()]);
    
        $storeId = $request->input('store_id');
    
        if ($storeId) {
            $business = Business::where('tiendanube_id', $storeId)->first();
    
            if ($business) {
                return response()->json([
                    'tiendanube_id' => $business->tiendanube_id,
                    'tiendanube_access_token' => $business->tiendanube_access_token,
                    'business_id' => $business->id,
                ]);
            } else {
                Log::warning("No se encontró ningún business con tiendanube_id = {$storeId}");
                return response()->json(['message' => 'Negocio no encontrado'], 404);
            }
        } else {
            Log::error('No se recibió store_id en el webhook de dataRequest');
            return response()->json(['message' => 'store_id requerido'], 400);
        }
    }
    
    
}
