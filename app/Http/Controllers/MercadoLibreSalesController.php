<?php

namespace App\Http\Controllers;

require_once base_path('vendor/autoload.php');

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Common\RequestOptions;



use App\Contact;
use App\Transaction;
use App\Utils\TransactionUtil;
use App\Utils\BusinessUtil;
use App\Utils\ProductUtil;
use Illuminate\Http\Request;
use MercadoPago\SDK;
use MercadoPago\Payment;
use DB;
use Illuminate\Support\Facades\Http;




class MercadoLibreSalesController extends Controller
{
    protected $transactionUtil;
    protected $businessUtil;
    protected $productUtil;
    
    public function __construct(
        TransactionUtil $transactionUtil,
        BusinessUtil $businessUtil,
        ProductUtil $productUtil
    ) {
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->productUtil = $productUtil;
    }

    public function import(Request $request)
    {
        \Log::info('Iniciando importación de ventas desde MercadoLibre.', ['request' => $request->all()]);

        if (! auth()->user()->can('sell.create')) {
            \Log::warning('Usuario sin permisos intenta importar ventas desde MercadoLibre.');
            abort(403, 'Unauthorized action.');
        }
        
        $orders = $request->input('orders');
        $business_id = $request->session()->get('user.business_id');
        $location_id = 1; // Puedes obtener este dato de la solicitud si es necesario

        \Log::info('Datos recibidos para importación', ['orders' => $orders]);

        DB::beginTransaction();
        try {
            foreach ($orders as $order) {
                \Log::info('Procesando orden', ['order' => $order]);
                
                // Buscar o crear el cliente predeterminado para MercadoLibre
                $contact = Contact::firstOrCreate(
                    ['business_id' => $business_id, 'name' => $order['customer_name']],
                    ['type' => 'customer', 'email' => '', 'mobile' => '', 'created_by' => auth()->user()->id]
                );
                \Log::info('Contacto obtenido/creado', ['contact' => $contact]);

                // Armar los datos de la venta
                $sale_data = [
                    'invoice_no'       => $order['invoice_no'],
                    'location_id'      => $location_id,
                    'status'           => 'final',
                    'contact_id'       => $contact->id,
                    'final_total'      => $order['order_total'],
                    'transaction_date' => $order['date'],
                    'discount_amount'  => 0,
                ];
                \Log::info('Datos de la venta armados', ['sale_data' => $sale_data]);

                $invoice_total = [
                    'total_before_tax' => $order['order_total'],
                    'tax'              => 0,
                ];
                \Log::info('Datos totales de la factura', ['invoice_total' => $invoice_total]);

                // Crear la transacción de venta
                $transaction = $this->transactionUtil->createSellTransaction(
                    $business_id,
                    $sale_data,
                    $invoice_total,
                    auth()->user()->id,
                    false
                );
                \Log::info('Transacción de venta creada', ['transaction_id' => $transaction->id]);

                // Preparar la línea de venta (se asume que cada orden es de un único producto)
                $sell_lines = [
                    [
                        'product_id'           => $this->getProductIdByName($order['product'], $order['sku'] ?? null, $business_id),
                        'variation_id'         => $this->getVariationIdByName($order['product'], $business_id),
                        'quantity'             => $order['quantity'],
                        'unit_price'           => $order['unit_price'],
                        'unit_price_inc_tax'   => $order['unit_price'],
                        'line_discount_type'   => 'fixed',
                        'line_discount_amount' => 0,
                        'item_tax'             => 0,
                        'tax_id'               => null,
                        'sell_line_note'       => '',
                        'product_unit_id'      => 1, // Ajusta este valor según la unidad predeterminada de tu producto
                        'enable_stock'         => 1,
                        'type'                 => 'single', // O el tipo que corresponda
                        'combo_variations'     => [],
                    ]
                ];
                \Log::info('Líneas de venta preparadas', ['sell_lines' => $sell_lines]);

                // Registrar las líneas de venta
                $this->transactionUtil->createOrUpdateSellLines(
                    $transaction,
                    $sell_lines,
                    $location_id,
                    false,
                    null,
                    [],
                    false
                );
                \Log::info('Líneas de venta registradas para la transacción', ['transaction_id' => $transaction->id]);

                // Descontar el stock para cada línea que tenga habilitado el stock
                foreach ($sell_lines as $line) {
                    if (!empty($line['enable_stock'])) {
                        \Log::info('Disminuyendo stock para producto', [
                            'product_id'   => $line['product_id'],
                            'variation_id' => $line['variation_id'],
                            'location_id'  => $location_id,
                            'quantity'     => $line['quantity']
                        ]);
                        $this->productUtil->decreaseProductQuantity(
                            $line['product_id'],
                            $line['variation_id'],
                            $location_id,
                            $line['quantity']
                        );
                        \Log::info('Stock disminuido para producto', [
                            'product_id'   => $line['product_id'],
                            'variation_id' => $line['variation_id']
                        ]);
                    }
                }
            }
            DB::commit();
            \Log::info('Importación de ventas desde MercadoLibre completada exitosamente.');
            return response()->json(['success' => 1, 'msg' => 'Ventas importadas correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error importando ventas desde MercadoLibre', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine()
            ]);
            return response()->json(['success' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Método para obtener el product_id a partir del nombre.
     */
    protected function getProductIdByName($productName, $sku, $business_id)
    {
        
        \Log::info('Buscando producto por SKU o nombre', ['sku' => $sku]);

        if (!empty($sku)) {
            $product = \App\Product::where('business_id', $business_id)
                ->where('sku', $sku)
                ->first();
    
            if ($product) {
                \Log::info('Producto encontrado por SKU', ['product_id' => $product->id]);
                return $product->id;
            }
        }
    
        $errorMsg = "Producto no encontrado: SKU = $sku, Nombre = $productName";
        \Log::error($errorMsg);
        throw new \Exception($errorMsg);
    }

    /**
     * Método para obtener el variation_id a partir del nombre del producto.
     */
    protected function getVariationIdByName($productName, $business_id)
    {
        \Log::info('Buscando variación para producto', ['productName' => $productName]);
        $product = \App\Product::where('business_id', $business_id)
                        ->where('name', $productName)
                        ->first();
        if ($product && $product->variations->first()) {
            $variationId = $product->variations->first()->id;
            \Log::info('Variación encontrada', ['variation_id' => $variationId]);
            return $variationId;
        }
        $errorMsg = "Variación no encontrada para el producto: " . $productName;
        \Log::error($errorMsg);
        throw new \Exception($errorMsg);
    }
    
    
    public function processPayment(Request $request) {
        MercadoPagoConfig::setAccessToken('APP_USR-5043808394529851-123117-6f4072928b9bcb1f01eaad1f3371b57e-221745631');
        //MercadoPagoConfig::setAccessToken('APP_USR-7229588378581834-020701-2cc6240cd1ee88d818e9cdbc5b38d4cc-2253423125'); // Token de prueba
        $payment_data = $request->all();
        
        
        try {
            $client = new PaymentClient();
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders([
                "X-Idempotency-Key: " . uniqid() // Genera una clave única
            ]);
    
            // Preparamos el pago y lo logueamos antes de enviarlo a la API
            $payment_payload = [
                "transaction_amount" => (float)$payment_data['transaction_amount'],
                "token" => $payment_data['token'],
                "payment_method_id" => $payment_data['payment_method_id'],
                "description" => "Pago de prueba", // Campo recomendado
                "installments" => 1, // Campo obligatorio para tarjetas
                "payer" => [
                    "email" => $payment_data['payer']['email'],
                    "identification" => [
                        "type" => $payment_data['payer']['identification']['type'],
                        "number" => $payment_data['payer']['identification']['number']
                    ]
                ]
            ];
    
            // Log para verificar la carga del pago antes de enviarlo a la API

    
            // Realizamos la creación del pago
            $payment = $client->create($payment_payload, $request_options);
    

    
            return response()->json(['status' => 'success', 'payment_id' => $payment->id]);
        
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $content = $apiResponse->getContent();
            
            // Log para capturar detalles del error de la API de Mercado Pago
            \Log::error('Error MercadoPago:', [
                'status' => $statusCode,
                'content' => $content
            ]);
            
            return response()->json([
                'status' => 'error',
                'code' => $content['error'] ?? 'internal_error',
                'message' => $content['message'] ?? 'Error interno del servidor',
                'details' => $content['cause'] ?? []
            ], $statusCode);
        }
    }

    
    public function showPaymentPage(Request $request)
    {
        $planId = $request->query('planId');
        $preapprovalPlan = $request->query('preapprovalPlan');
    
        return view('mercadopago', ['amount' => $packagePrice]);
    }
    
    public function billingInfo(Request $request)
    {
        $orderId = $request->input('order_id');
        $token = auth()->user()->business->mercadolibre_access_token;
    
        $response = Http::withToken($token)
            ->withHeaders(['x-version' => '2'])
            ->get("https://api.mercadolibre.com/orders/{$orderId}/billing_info");
    
        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json([
                'error' => true,
                'message' => 'No se pudo obtener billing info.',
                'status' => $response->status(),
                'body' => $response->body(),
            ], $response->status());
        }
    }
}
