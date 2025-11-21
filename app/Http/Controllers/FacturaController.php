<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DateTime;

//require 'vendor/autoload.php';  // Asegúrate de que autoload.php esté en la ubicación correcta
require_once '/www/wwwroot/app.tutifactura.com/vendor/afipsdk/afip.php/src/Afip.php';  // Usar require_once para cargar la clase Afip

use Afip;
use TCPDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use ZipArchive;
use Mpdf\Mpdf;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Http;


use App\Transaction;
use App\Contact;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



class FacturaController extends Controller
{
    public function crearFactura(Request $request)
    {
        // Validar los datos recibidos desde el frontend
        $data = $request->validate([
            'invoice_type' => 'required|integer',
            'invoice_concept' => 'required|integer',
            'buyer_document_type' => 'required|integer',
            'buyer_document_number' => 'required|integer',
            'transaction_date' => 'required|date',
            'denomination_total_amount' => 'required|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'payment_due_date' => 'nullable|date',
            'business_name' => 'required|string',
            'products' => 'required|array', 
            'products.*.product_name' => 'nullable|string',
            'products.*.quantity' => 'nullable|numeric', 
            'products.*.unit_price' => 'nullable|numeric', 
            'products.*.discount' => 'nullable|numeric', 
            'products.*.subtotal' => 'nullable|numeric', 
            'cuitEmpresa' => 'required|string',
            'direccion' => 'required|string',
            'fechaInicio' => 'required|date',
            'invoice_iva' => 'nullable|string',
            'tipoTransferencia' => 'nullable|string',
            'cbu_cliente_val' => 'nullable|numeric',
            'num_factura_val' => 'nullable|numeric',
            'logoEmpresa' => 'nullable|string',
            'iva_receptor' => 'required|numeric',
            'locacion_comercial' => 'required|string',
            'pto_vta' => 'required|numeric',
            'customer_cuit' => 'nullable|string',
            'customer_name' => 'nullable|string',
            'customer_address' => 'nullable|string',
            'payment_condition' => 'nullable|string'
            
        ]);
        Log::debug('Datos recibidos en el controlador:', $data);
        // Extraer los datos del request
        $invoice_type = $data['invoice_type'];
        $invoice_concept = $data['invoice_concept'];
        $buyer_document_type = $data['buyer_document_type'];
        $buyer_document_number = $data['buyer_document_number'];
        $transaction_date = $data['transaction_date'];
        $denomination_total_amount = $data['denomination_total_amount'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $payment_due_date = $data['payment_due_date'];
        $business_name = $data['business_name'];
        $products = $data['products'];
        $cuitEmpresa = $data['cuitEmpresa'];
        $direccion = $data['direccion'];
        $fechaInicio = $data['fechaInicio'];
        $invoice_iva = $data['invoice_iva'];
        $tipoTransferencia = $data['tipoTransferencia'];
        $cbu_cliente_val = $data['cbu_cliente_val'];
        $num_factura_val = $data['num_factura_val'];
        $logoEmpresa = $data['logoEmpresa'];
        $iva_receptor = $data['iva_receptor'];
        $locacion_comercial = $data['locacion_comercial'];
        $pto_vta = $data['pto_vta'];
        $customer_cuit = $data['customer_cuit'];
        $customer_name = $data['customer_name'];
        $customer_address = $data['customer_address'];
        $payment_condition = $data['payment_condition'];
        
        
        
            // Ahora puedes iterar sobre los productos si los necesitas para cálculos adicionales
        foreach ($products as $product) {
            // Aquí puedes trabajar con los datos de cada producto
            $product_name = $product['product_name'];
            $quantity = $product['quantity'];
            $unit_price = $product['unit_price'];
            $discount = $product['discount'];
            $subtotal = $product['subtotal'];
    
            // Puedes hacer cálculos o ajustes con estos datos si es necesario
        }
        
        //CERTIFICADOS DE TESTING
        /*$cert ="-----BEGIN CERTIFICATE-----
MIIDSDCCAjCgAwIBAgIIA+PIYhIXNqAwDQYJKoZIhvcNAQENBQAwODEaMBgGA1UEAwwRQ29tcHV0
YWRvcmVzIFRlc3QxDTALBgNVBAoMBEFGSVAxCzAJBgNVBAYTAkFSMB4XDTI0MTEyNjA2MTkxNVoX
DTI2MTEyNjA2MTkxNVowLjERMA8GA1UEAwwIY2VydFdzZmUxGTAXBgNVBAUTEENVSVQgMjA0MzAx
NjYwMzUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDUH13nAWrQGcgOS6GURZT8szAL
Mf4B+sQ4oLYA0jWHpuAFKRXU6ExjK/DKJ0KYIppUlpH2NVrIoJi3IhFI74q6xNu+HfFhicZZVz76
Xukpm43uidsm9Hs86LicKA64ZWmUNJvoMCdOng5g077r4WwZRb2U0l9t/Wsvu4GEGNiF/m/Oykj7
mnRFcKOZbOMuez4cHe6It5DgKUQuNVBqvgpplwYHO98gmmMbX/lXAd+7eBukl9JfLkii1umjcrM2
OJcjpWhguqJCaODgeACnr5VDPdb7lfR+dGJOPNRmrulXKsWlv9Fj8DszrqPBLr4x8WICJOr5c8ie
KL8CW8FLBmG7AgMBAAGjYDBeMAwGA1UdEwEB/wQCMAAwHwYDVR0jBBgwFoAUs7LT//3put7eja8R
IZzWIH3yT28wHQYDVR0OBBYEFN4UnQPqhhlHE93fmxGuuI4mX6AJMA4GA1UdDwEB/wQEAwIF4DAN
BgkqhkiG9w0BAQ0FAAOCAQEAbILLDNvGLnLFDBBHimisE8wJoVz/BIHnX35AWH+46zURuwnkLDZL
OJyaE94BVqeDqspfnbgOg56oEeMUDqJty1YcwWzYPsiTr7tkwnzJpjTDoUkLA0zqDnyJB96FTwMj
WcxrmIe4AFG0MMjUR7uHDg8OCzg+ZWai5WXlVJjJpX5NMR/7Pm/iKdhbKZlvbUKZdYIUt1EzxxGL
Ofg0F/y25gIJPdA5RbND8rrP6REZjWqLpiAOhtjNPP6TCaE2Se8T1dbIqWIZ9uQ0bEGwvAhYzvby
Qpa/GCQUUbF00NpDs9/tbZrakL5a/apEe+7vFz6VYbpto9gfDWhWvFnqN8uDeQ==
-----END CERTIFICATE-----";
        $key = "-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA1B9d5wFq0BnIDkuhlEWU/LMwCzH+AfrEOKC2ANI1h6bgBSkV
1OhMYyvwyidCmCKaVJaR9jVayKCYtyIRSO+KusTbvh3xYYnGWVc++l7pKZuN7onb
JvR7POi4nCgOuGVplDSb6DAnTp4OYNO+6+FsGUW9lNJfbf1rL7uBhBjYhf5vzspI
+5p0RXCjmWzjLns+HB3uiLeQ4ClELjVQar4KaZcGBzvfIJpjG1/5VwHfu3gbpJfS
Xy5Iotbpo3KzNjiXI6VoYLqiQmjg4HgAp6+VQz3W+5X0fnRiTjzUZq7pVyrFpb/R
Y/A7M66jwS6+MfFiAiTq+XPInii/AlvBSwZhuwIDAQABAoIBADvPiFutc5+v1U/q
lWnIYOUL5V0SwItwWMmazxbWLs/MBtiNqCE7Suafqipl/YoGH7wAJLHmg22Uktr4
WSVWnahh/4/Qw5H8Fhh02EYiYt4fhVqgNlH6l5EqEXu+c8AcoDNwzhEfGsY5HNbC
fc/m5OMPXhBLbSsHTKTN2wwTMWI+Q4Bo36uB0kWNaFgrLwbEL+CGm+U2G1fOo3QP
IehtAajmnYBG6+jO3aeFJCHtiKxNwUq1LgLI+qY96bOisCOTWOub9I8DQ5iv0CyL
dPgIJXC1617ZdGY5O5/2od1tHPugzY63pw4SzqpQR1GxXZAuNJQMoVa1KcdHvL/t
Z49FNEkCgYEA6TH4cVKCYGsmQQtI3iZg+qqHLmNbvRunLUaRz37CkS+xq6ZWLKVh
SAhrQR7yVRmivxtDX/9pqnCpY+3bP5jKA+y8fUolycRyB1CT/NMaaqt2akG4ToNQ
X89MD9MSem/vILXhfi7MO9Zy/1EOHS46h1G6+dDQaUc3zTjtpUFpoKUCgYEA6N3X
1wSamFRDBC1BYvIWGTVKmwVX1Gfz6CAjpkbyLTaDB1sIBhPWSCl8EuAxMoaXZmvl
+3u5g+YdLQZr9+mwZntgOQ53N+NsDYyuiHi0Se5/UNeOcbAyrso3moClVdwOXkbP
fbND7wBfkdjPMbUZN8gSpTV1qav0D6Vacc85Ct8CgYEAld19cya5j0mNTiP4cnxr
uuy478D/BiutZtWBg75NQI1MO6osm4i1Wlu+wh0nVDWjd/oHdLxqphS9Z/FHBDon
KhqMkGCEpITRW44XbVYmFgOXmHYgAqU1lD1e/pSBvZoOLhF1l2hv7MzHHvpyfaJm
Du0hosbmCaKxY/yADcJaJdkCgYEAunul9RA/yYt5G6guO9HItqlBtMFjo7sXzaWn
Rup72I9WARb6ZvuN1745GVimrWKxbhksVOexGhq29K622hMv6/ITjb2y5XPfvT4T
K0EWiDpRhOkKrqq++9D/FGC/hvGyI/erBGwCFC0FW+P3kUQJDO3RWLJmJtmImtr9
gjTD5psCgYBR5febSu3JkLlkx+gKkJK5HeNN/u4eXuf0OBkPZHs0TRKAGEIpVOXW
GrnTajwiKF6DMUY0JQvUzA5ZWtgqXBg7v5LsCWIYMZp+tWeEb96R4iQlQHXISN8y
y3LUb5yOjH1vjeXfD85UFV9j3s0L8zEgjwjmFQ2y0+gHxsTkeZ+B0g==
-----END RSA PRIVATE KEY-----";*/

        //CERTTIFICADOS DE PRODU
        $cert = "-----BEGIN CERTIFICATE-----
MIIDRjCCAi6gAwIBAgIINv3QVdMufY4wDQYJKoZIhvcNAQENBQAwMzEVMBMGA1UEAwwMQ29tcHV0
YWRvcmVzMQ0wCwYDVQQKDARBRklQMQswCQYDVQQGEwJBUjAeFw0yNTA0MDcwNDE5MzZaFw0yNzA0
MDcwNDE5MzZaMDExFDASBgNVBAMMC2NzckFmaXBQcm9kMRkwFwYDVQQFExBDVUlUIDMwNzE4ODY2
Nzk3MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApg+x71ivDMyRhoEo5kkVOBoB6ef9
IJ4XMZhYB02r5b7512D5jtgxUyAAeFqvof/dQUW6w/UGzPts8B9EAWuTQtqUZgFSriZID1Vp7lXJ
HgOAYUYfSdoSMfWUlxht9QYWnK9m5JVKHR/elMXm5sn42JITuU2/3LP576caqfq9J3xgcVT0U0aO
fbz75BdaVGC+NJs5aDj1snzntZP4361v8sDrpsCg3tEolo1EiJ8g8mBAu2PQJqFxPwtNrvTbrxQ/
ncbHJRHgIcFunTBAMYL/8hU7vhK/HpKuTuXzHRgTNqq0kL7kz0VF6w3e8LiCfDkUp/dCXtkHE5Ut
Tr+HTXB/oQIDAQABo2AwXjAMBgNVHRMBAf8EAjAAMB8GA1UdIwQYMBaAFCsNL8jfYf0IyU4R0DWT
BG2OW9BuMB0GA1UdDgQWBBT6xzVWJIq44df7haRPQpRO5N4e6jAOBgNVHQ8BAf8EBAMCBeAwDQYJ
KoZIhvcNAQENBQADggEBAGrmGITFmIP7vsqCbKWM2aSXU1qeOyGq7v07iHPCWYvLuqeZuhUHHRtc
U028YVZHO/6wTXCRyfHIzMU2SPPVK+IiQRjhllyx9tiK6xvDQZCmynHG/0bhPw2oochc/K9pd9WR
LaxX+ZDPL40pReOh9d5T6CfLUQcb9a7T1/2D7KDeAxUKVg2HQ4Os0jcvKgfP25dmid3IvXXFpzLq
JI3lMdsacHkXj0BH8hQzK6J4O4Ls/kr+ZV9ep1nzVll+jD7Xjq9SbNjZoRvZ8K5LCxpIQxWVG/j5
N+QTz+pX33EeVQNxRFKGn/hkzWkkdpe+PZEiMxalKJGCyUIfOsPVBlh3XUI=
-----END CERTIFICATE-----";
        $key = "-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEApg+x71ivDMyRhoEo5kkVOBoB6ef9IJ4XMZhYB02r5b7512D5
jtgxUyAAeFqvof/dQUW6w/UGzPts8B9EAWuTQtqUZgFSriZID1Vp7lXJHgOAYUYf
SdoSMfWUlxht9QYWnK9m5JVKHR/elMXm5sn42JITuU2/3LP576caqfq9J3xgcVT0
U0aOfbz75BdaVGC+NJs5aDj1snzntZP4361v8sDrpsCg3tEolo1EiJ8g8mBAu2PQ
JqFxPwtNrvTbrxQ/ncbHJRHgIcFunTBAMYL/8hU7vhK/HpKuTuXzHRgTNqq0kL7k
z0VF6w3e8LiCfDkUp/dCXtkHE5UtTr+HTXB/oQIDAQABAoIBAQCev6RPdg0qBCoe
Vjz+IOF7F0m7DT6rvtXXYJbOrI7m+/Gl1xvbVYuZtp8cbz01df9S1dEcYZIVxUm4
J3uAjBFkOMbi2OLsfsYmenXhFPtFiP1igIGKvUDlp6pb40vCkAZv1TJc1Obb/spu
mN4KxQvyJXtbixseZqNJWgsgcgX/xtnHuYAa8jPI480rCgEZjfGpTGgV0O08m3Sy
LPlBFMOu6pjMut5Z/oZmpCRxcJk7twCcT0G4HCENT2YMgn5UBKAH2u/xQm6RQD9+
ZsALl6NZE3f6/7VjFm6JSMtChAHtuzIgFVExO6dg95V4AadrFWWLA6I0Fd2yXepd
NjQQXf8dAoGBAM8gqZ9CoJr2eiSb26XdN/woRLiq4cZyfF4VDtpXVcGEfWIap1JM
GkL/3xRkXwkUz+uJkWxIxNPxyizY9nIdU42q/1ZuNik51r7Lha1xChFoEEaKsC1i
s6cKF1k0x17XvAhfldtn5HID4m55oZXYI/ei4G1Hf14SrdbxyIrbFrM7AoGBAM0+
duRA3g0DXumTj27nr9k9dPvcpf9iB06c0spsavb7MXcrrP7frVqmwdJRUOvWD4px
mk3FfiNwe7BuVa4zsss9UX66mYDpwqXauFyy+zXGFhDynO4LXyRttTQlMYFS6yi9
eu8SEIlzgxGSFP52LPMDRz9+wOWnKV/WnmbeF/LTAoGAH9+R2p0hI9eMAjgtu79o
DxiPQq+hIK2bb8rjxzurSaU+uisJ1oM5g1fmlze+3CHkwQUhaL1l9It/phkZC4Rx
yBopQr6g/QUWg7PD9WWwu+78mi5bYBCRPsIxx5t0lqayG8PBxsqc/+KTJJb5A5sI
JeJdkNNwySQstifsyVlfi+MCgYB69tGELtjaMwrKJf3pFCXmoUtVGfzZ5IE8N/iS
DiKHljcsjp42lQvnI8E7oDw7DySAbU9lCM1prhpsua3bkF2uJi6NHn6ZHXPgqgmK
gDDBJsxRmwkqFOYLjS7i7uWZ9XOuFNdyJACEWRBNlpe9XE/RyEUHZYlsDZpcMNj2
CFmQwQKBgQCrwv5QmTI5GoFj5GJl/ztG46+AyZeUaItQp0gsGVEQ2S+bZYYx1T+e
WDuolM9wd08q0e+K3sFMv64C+LN34uQBwAlgPQ+ea+NHZ8b4zmbpW8fv5VvU1x/v
2F1DSH1UvuGZdvmpx2Jmb/9C3olO/HhoybCxVvA96Vf4ufnbc+esoA==
-----END RSA PRIVATE KEY-----";

        $tax_id = $cuitEmpresa;
        //$tax_id = 20430166035;
        // Crear la instancia de AFIP dentro del método
        $afip = new Afip(array(
            'CUIT' => $tax_id, 
            'cert' => $cert,  // Ruta al archivo de certificado
            'key' => $key,          // Ruta al archivo de clave privada
            'access_token' => 'gi72RbXfkm4DcfnmLSAOl1xnS1DvAMZfP6uFwQciVerrDlI3uJSEkgYU090AoGIw'
        ));

        //$ta = $afip->ElectronicBilling->getTokenAuthorization(TRUE);
        //var_dump($ta);

        try {
            // Obtener el último número de factura
            $last_voucher = $afip->ElectronicBilling->GetLastVoucher($pto_vta, $invoice_type);  // Punto de venta 1

            // Verificar si obtuvimos un número válido
            if (isset($last_voucher)) {
                // Obtener el número de factura y aumentar 1
                $numero_de_factura = $last_voucher + 1;
            } else {
                throw new \Exception('No se pudo obtener el último número de factura autorizado.');
            }
            
            
            $ivaRates = [
                'iva_21' => 0.21,
                'iva_27' => 0.27,
                'iva_10' => 0.105,
                'excento_iva' =>0,
            ];
            Log::debug('Valor de invoice_iva: ' . $invoice_iva);
            Log::debug('Valores de ivaRates: ' . json_encode($ivaRates));
            $valIva = $ivaRates[$invoice_iva];
            Log::debug('Valor de valIva: ' . $valIva);
            // Cálculos para los campos relacionados
            $ImpTotConc = 0;  // Si no hay conceptos totales, se asume como 0
            $ImpNeto = $denomination_total_amount;  // Monto neto
            $ImpOpEx = 0;  // No se aplica en este caso, asumiéndolo como 0
            $ImpTrib = 0;
            $ImpIVA = 0;// No se aplica en este caso, asumiéndolo como 0
            if ($invoice_type === 8 || $invoice_type === 13){
                $ImpIVA = 0;
            }else{
                $ImpIVA = round($denomination_total_amount * $valIva, 2);}// IVA del 21
            
            
            // Configurar las fechas para el servicio y vencimiento de pago
            if ($invoice_concept === 2 || $invoice_concept === 3) {
                $fecha_servicio_desde = intval(str_replace('-', '', $start_date));
                $fecha_servicio_hasta = intval(str_replace('-', '', $end_date));
                $fecha_vencimiento_pago = intval(str_replace('-', '', $payment_due_date));
            } else {
                $fecha_servicio_desde = null;
                $fecha_servicio_hasta = null;
                $fecha_vencimiento_pago = null;
            }

            
            $idIva = null;
            
            if ($valIva === 0.21){
                $idIva = 5;
            }elseif ($valIva === 0.27){
                $idIva = 6; 
            }elseif ($valIva === 0.105){
                $idIva = 4; 
            }else{
                $idIva = 3; 
            }
            
            
            // Calculamos el ImpTotal como la suma de los componentes
            $ImpTotal = number_format($ImpTotConc + $ImpNeto + $ImpOpEx + $ImpTrib + $ImpIVA, 2, '.', '');
            
            // Luego, ajustamos los datos de la factura
            $invoice_data = [
                'CantReg'    => 1,
                'PtoVta'    => $pto_vta,  // Punto de venta
                'CbteTipo'    => $invoice_type,
                'Concepto'    => $invoice_concept,
                'DocTipo'    => $buyer_document_type,
                'DocNro'    => $buyer_document_number,
                'CbteDesde' => $numero_de_factura,
                'CbteHasta' => $numero_de_factura,
                'CbteFch'    => intval(str_replace('-', '', $transaction_date)),
                'FchServDesde' => $fecha_servicio_desde,
                'FchServHasta' => $fecha_servicio_hasta,
                'FchVtoPago'   => $fecha_vencimiento_pago,
                'ImpTotal'    => $ImpTotal,  // Aseguramos que ImpTotal sea correcto
                'ImpTotConc'  => $ImpTotConc,
                'ImpNeto'    => $ImpNeto,
                'ImpOpEx'    => $ImpOpEx,
                'ImpIVA'    => $ImpIVA,
                'ImpTrib'    => $ImpTrib,
                'CondicionIVAReceptorId' => $iva_receptor,
                'MonId'    => 'PES',  // Moneda: Pesos Argentinos
                'MonCotiz'    => 1,  // Cotización
            ];
            
            // Solo agrega 'Iva' si el concepto no es 11
            if ($invoice_type === 1 || $invoice_type === 3 || $invoice_type === 6 || $invoice_type === 8 || $invoice_type === 4 || $invoice_type === 9 || $valIva === 0.27 || $valIva === 0.105) {
                $invoice_data['Iva'] = [
                    [
                        'Id'        => $idIva,  // 21% IVA
                        'BaseImp'    => $ImpNeto,
                        'Importe'    => $ImpIVA,  // IVA calculado al 21%
                    ]
                ];
            }
            if ($invoice_type === 3 || $invoice_type === 8 || $invoice_type === 13) {
                $invoice_data['CbtesAsoc'] = [
                    [
                        'Tipo' => $invoice_type - 2, // Tipo de comprobante (ejemplo: Factura A)
                        'PtoVta' => $pto_vta, // Punto de venta asociado
                        'Nro' => $num_factura_val 
                    ]
                ];
            }
            
            Log::info('Contenido de $invoice_data: ' . print_r($invoice_data, true));
            // Llamar a la API de AFIP para crear la factura
            $res = $afip->ElectronicBilling->CreateVoucher($invoice_data);
            Log::info('Se llamo correctamente a la API');
            
            // Verificar si la respuesta contiene errores
            if (isset($res['Errors']) && count($res['Errors']) > 0) {
                $errorMsg = implode(', ', $res['Errors']);
                return response()->json([
                    'error' => "Error al crear la factura: " . $errorMsg
                ], 400); // Retorna código 400
            }
            
            // Obtenemos el CAE que es necesario para el código QR
            $cae = $res['CAE'];
            $cae_due_date = $res['CAEFchVto'];
            Log::info('Crear el JSON');
            // Crear el JSON con los datos del comprobante (incluyendo el CAE)
            $invoice_json = [
                'ver' => 1,
                'fecha' => $transaction_date,
                'cuit' => $tax_id,
                'PtoVta' => $pto_vta,  // Punto de venta
                'tipoCmp' => $invoice_type,
                'nroCmp' => $numero_de_factura,
                'importe' => $ImpTotal,
                'moneda' => 'PES',  // Moneda Pesos Argentinos
                'ctz' => 1,  // Cotización (1 para pesos)
                'tipoDocRec' => $buyer_document_type,
                'nroDocRec' => $buyer_document_number,
                'tipoCodAut' => 'E',  // Autorizado por CAE
                'codAut' => $cae,  // Usamos el CAE aquí
                
            ];
            // Codificar el JSON en base64
            $datos_base64 = base64_encode(json_encode($invoice_json));

            // Crear la URL
            $url_qr = 'https://www.afip.gob.ar/fe/qr/?p=' . $datos_base64;

            $qr_data = 'https://www.afip.gob.ar/fe/qr/?p=' . base64_encode(json_encode($invoice_json));
            
            // Generar el código QR con logo y label
            $writer = new PngWriter();
            // Crear QR Code con configuraciones personalizadas
            $qrCode = new QrCode(
                data: $url_qr,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 300,  // Tamaño del QR
                margin: 10, // Margen
                roundBlockSizeMode: RoundBlockSizeMode::Margin, // Estilo de bloque redondeado
                foregroundColor: new Color(0, 0, 0),  // Color del QR
                backgroundColor: new Color(255, 255, 255) // Color de fondo
            );
            $qrImage = 'data:image/png;base64,' . base64_encode($writer->write($qrCode)->getString());
            // Crear el logo
            $logo = new Logo(
                path: __DIR__.'/assets/logo.png', // Asegúrate de poner la ruta correcta de tu logo
                resizeToWidth: 50, // Tamaño del logo
                punchoutBackground: true // Quitar el fondo del logo
            );
            // Crear el label
            $label = new Label(
                text: 'Factura QR',
                textColor: new Color(255, 0, 0), // Color del texto
            );

            // Combinar el QR con el logo y el label
            $result = $writer->write($qrCode, $logo, $label);
            
            // Obtener la URL de la imagen del QR como Data URI
            $dataUri = $result->getDataUri();
            
            $tipoFactura = "A";
            
            if ($invoice_type === 6) {
                $tipoFactura = "B";
            } elseif ($invoice_type === 11) {
                $tipoFactura = "C";
            } elseif ($invoice_type === 3) {
                $tipoFactura = "A";
            } elseif ($invoice_type === 8) {
                $tipoFactura = "B";
            } elseif ($invoice_type === 13) {
                $tipoFactura = "C";
            } elseif ($invoice_type === 4) {
                $tipoFactura = "A";
            } elseif ($invoice_type === 9) {
                $tipoFactura = "B";
            } elseif ($invoice_type === 15) {
                $tipoFactura = "C";
            }
            
            Log::info('Crear HTML de factura');
            // Crear HTML de factura
            $html = view('sell.factura', [
                'invoice_number' => $invoice_number,
                'transaction_date' => $data['transaction_date'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'payment_due_date' => $data['payment_due_date'],
                'buyer_document_number' => $data['buyer_document_number'],
                'ImpIVA' => $ImpIVA,
                'ImpNeto' => $ImpNeto,
                'denomination_total_amount' => $ImpTotal,
                'cae' => $cae,
                'cae_due_date' => $cae_due_date,
                'qrImage' => $qrImage,
                'invoice_type' => $tipoFactura,
                'recibo' => $invoice_type,
                'business_name' => $business_name,
                'products' => $products,
                'cuitEmpresa' => $cuitEmpresa,
                'direccion' => $direccion,
                'numFact' => $numero_de_factura,
                'logoEmpresa' => $logoEmpresa,
                'iva_receptor' => $iva_receptor,
                'valIva' => $valIva,
                'locacion_comercial' => $locacion_comercial,
                'customer_cuit' => $customer_cuit,
                'customer_name' => $customer_name,
                'customer_address' => $customer_address,
                'payment_condition' => $payment_condition,
                'PtoVta' => $pto_vta,
            ])->render();
            Log::info('Iniciando la creación del HTML para la factura.', [
                'invoice_number' => $invoice_number,
                'transaction_date' => $data['transaction_date'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'payment_due_date' => $data['payment_due_date'],
                'buyer_document_number' => $data['buyer_document_number'],
                'ImpIVA' => $ImpIVA,
                'ImpNeto' => $ImpNeto,
                'denomination_total_amount' => $ImpTotal,
                'cae' => $cae,
                'cae_due_date' => $cae_due_date,
                'invoice_type' => $tipoFactura,
                'business_name' => $business_name,
                'products' => count($products),
                'cuitEmpresa' => $cuitEmpresa,
                'direccion' => $direccion,
                'fechaInicio' => $fechaInicio,
                'numFact' => $numero_de_factura,
                'logoEmpresa' => $logoEmpresa,
                'customer_cuit' => $customer_cuit,
                'customer_name' => $customer_name,
                'customer_address' => $customer_address,
                'payment_condition' => $payment_condition,
                'PtoVta' => $pto_vta,
            ]);
            
            // Configurar Dompdf
            /*
            $options = new Options();
            $options->set('isRemoteEnabled', true); // Permitir cargar imágenes externas, si es necesario
            $mpdf = new Mpdf($options);

            // Cargar el contenido HTML en Dompdf
            $mpdf->loadHtml($html);

            // Configurar el tamaño del papel y la orientación
            $mpdf->setPaper('A4', 'portrait'); // A4 y orientación vertical

            // Renderizar el PDF
            $mpdf->render();
            
            // Guardar el archivo temporalmente
            //$pdfFile = tempnam(sys_get_temp_dir(), 'factura') . '.pdf';
            $directory = storage_path('app/public/facturas_afip/');
            $nombre_empresa = Str::slug($business_name, '_'); 
            $filename = "FACTURA_{$nombre_empresa}_{$numero_de_factura}.pdf";
            Storage::disk('public')->put("facturas_afip/{$filename}", $mpdf->output());
            $pdfFile = $directory . $filename;
            
            file_put_contents($pdfFile, $mpdf->output());
            */
            
            $directory = storage_path('app/public/facturas_afip/');
            $nombre_empresa = Str::slug($business_name, '_');
            $filename = "FACTURA_{$nombre_empresa}_{$numero_de_factura}.pdf";
            $pdfPath = $directory . $filename;
            
            Browsershot::html($html)
                ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                ->setOption('executablePath', '/usr/bin/chromium') // o donde esté instalado
                ->format('A4')
                ->save($pdfPath);

            
            // (Opcional) Actualiza la base de datos con la ruta si lo necesitas
            $businessId = auth()->user()->business_id;
            if ($businessId) {
                $updated = DB::table('business')
                    ->where('id', $businessId)
                    ->update(['facturaTemp' => $pdfPath]);
            
                if (!$updated) {
                    Log::error("No se pudo actualizar la columna facturaTemp para el business con ID $businessId.");
                }
            }
            
            // Descargar el archivo PDF generado
            if (file_exists($pdfPath)) {
                return response()->download($pdfPath);
            }
            
            $businessId = auth()->user()->business_id; // O la forma que se ajuste a tu lógica
            if ($businessId) {
                $updated = DB::table('business')
                    ->where('id', $businessId)
                    ->update(['facturaTemp' => $pdfFile]);
            
                if (!$updated) {
                    Log::error("No se pudo actualizar la columna facturaTemp para el business con ID $businessId.");
                }
            }
            
            if (file_exists($pdfFile)) {
                //return response()->download($pdfFile, "factura_{$numero_de_factura}.pdf");
                return response()->download(storage_path('app/public/facturas_afip/' . $filename));
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Hubo un error al generar la factura: ' . $e->getMessage(),
            ], 400); // Código 400 en caso de error
        }
    }
    
    public function crearFacturaInt(Request $request)
    {
        // Validar los datos recibidos desde el frontend
        $data = $request->validate([
            'invoice_type' => 'required|integer',
            'invoice_concept' => 'required|integer',
            'buyer_document_type' => 'required|integer',
            'buyer_document_number' => 'required|integer',
            'transaction_date' => 'required|date',
            'denomination_total_amount' => 'required|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'payment_due_date' => 'nullable|date',
            'business_name' => 'required|string',
            'products' => 'required|array', 
            'products.*.product_name' => 'nullable|string',
            'products.*.quantity' => 'nullable|numeric', 
            'products.*.unit_price' => 'nullable|numeric', 
            'products.*.discount' => 'nullable|numeric', 
            'products.*.subtotal' => 'nullable|numeric', 
            'cuitEmpresa' => 'required|string',
            'direccion' => 'required|string',
            'fechaInicio' => 'required|date',
            'invoice_iva' => 'nullable|string',
            'tipoTransferencia' => 'nullable|string',
            'cbu_cliente_val' => 'nullable|numeric',
            'num_factura_val' => 'nullable|numeric',
            'logoEmpresa' => 'nullable|string',
            'iva_receptor' => 'required|numeric',
            'locacion_comercial' => 'required|string',
            'pto_vta' => 'required|numeric'
            
        ]);
        Log::debug('Datos recibidos en el controlador:', $data);
        // Extraer los datos del request
        $invoice_type = $data['invoice_type'];
        $invoice_concept = $data['invoice_concept'];
        $buyer_document_type = $data['buyer_document_type'];
        $buyer_document_number = $data['buyer_document_number'];
        $transaction_date = $data['transaction_date'];
        $denomination_total_amount = $data['denomination_total_amount'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $payment_due_date = $data['payment_due_date'];
        $business_name = $data['business_name'];
        $products = $data['products'];
        $cuitEmpresa = $data['cuitEmpresa'];
        $direccion = $data['direccion'];
        $fechaInicio = $data['fechaInicio'];
        $invoice_iva = $data['invoice_iva'];
        $tipoTransferencia = $data['tipoTransferencia'];
        $cbu_cliente_val = $data['cbu_cliente_val'];
        $num_factura_val = $data['num_factura_val'];
        $logoEmpresa = $data['logoEmpresa'];
        $iva_receptor = $data['iva_receptor'];
        $locacion_comercial = $data['locacion_comercial'];
        $pto_vta = $data['pto_vta'];
        
        
        
            // Ahora puedes iterar sobre los productos si los necesitas para cálculos adicionales
        foreach ($products as $product) {
            // Aquí puedes trabajar con los datos de cada producto
            $product_name = $product['product_name'];
            $quantity = $product['quantity'];
            $unit_price = $product['unit_price'];
            $discount = $product['discount'];
            $subtotal = $product['subtotal'];
    
            // Puedes hacer cálculos o ajustes con estos datos si es necesario
        }
        
        //CERTIFICADOS DE TESTING
        /*$cert ="-----BEGIN CERTIFICATE-----
MIIDSDCCAjCgAwIBAgIIA+PIYhIXNqAwDQYJKoZIhvcNAQENBQAwODEaMBgGA1UEAwwRQ29tcHV0
YWRvcmVzIFRlc3QxDTALBgNVBAoMBEFGSVAxCzAJBgNVBAYTAkFSMB4XDTI0MTEyNjA2MTkxNVoX
DTI2MTEyNjA2MTkxNVowLjERMA8GA1UEAwwIY2VydFdzZmUxGTAXBgNVBAUTEENVSVQgMjA0MzAx
NjYwMzUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDUH13nAWrQGcgOS6GURZT8szAL
Mf4B+sQ4oLYA0jWHpuAFKRXU6ExjK/DKJ0KYIppUlpH2NVrIoJi3IhFI74q6xNu+HfFhicZZVz76
Xukpm43uidsm9Hs86LicKA64ZWmUNJvoMCdOng5g077r4WwZRb2U0l9t/Wsvu4GEGNiF/m/Oykj7
mnRFcKOZbOMuez4cHe6It5DgKUQuNVBqvgpplwYHO98gmmMbX/lXAd+7eBukl9JfLkii1umjcrM2
OJcjpWhguqJCaODgeACnr5VDPdb7lfR+dGJOPNRmrulXKsWlv9Fj8DszrqPBLr4x8WICJOr5c8ie
KL8CW8FLBmG7AgMBAAGjYDBeMAwGA1UdEwEB/wQCMAAwHwYDVR0jBBgwFoAUs7LT//3put7eja8R
IZzWIH3yT28wHQYDVR0OBBYEFN4UnQPqhhlHE93fmxGuuI4mX6AJMA4GA1UdDwEB/wQEAwIF4DAN
BgkqhkiG9w0BAQ0FAAOCAQEAbILLDNvGLnLFDBBHimisE8wJoVz/BIHnX35AWH+46zURuwnkLDZL
OJyaE94BVqeDqspfnbgOg56oEeMUDqJty1YcwWzYPsiTr7tkwnzJpjTDoUkLA0zqDnyJB96FTwMj
WcxrmIe4AFG0MMjUR7uHDg8OCzg+ZWai5WXlVJjJpX5NMR/7Pm/iKdhbKZlvbUKZdYIUt1EzxxGL
Ofg0F/y25gIJPdA5RbND8rrP6REZjWqLpiAOhtjNPP6TCaE2Se8T1dbIqWIZ9uQ0bEGwvAhYzvby
Qpa/GCQUUbF00NpDs9/tbZrakL5a/apEe+7vFz6VYbpto9gfDWhWvFnqN8uDeQ==
-----END CERTIFICATE-----";
        $key = "-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA1B9d5wFq0BnIDkuhlEWU/LMwCzH+AfrEOKC2ANI1h6bgBSkV
1OhMYyvwyidCmCKaVJaR9jVayKCYtyIRSO+KusTbvh3xYYnGWVc++l7pKZuN7onb
JvR7POi4nCgOuGVplDSb6DAnTp4OYNO+6+FsGUW9lNJfbf1rL7uBhBjYhf5vzspI
+5p0RXCjmWzjLns+HB3uiLeQ4ClELjVQar4KaZcGBzvfIJpjG1/5VwHfu3gbpJfS
Xy5Iotbpo3KzNjiXI6VoYLqiQmjg4HgAp6+VQz3W+5X0fnRiTjzUZq7pVyrFpb/R
Y/A7M66jwS6+MfFiAiTq+XPInii/AlvBSwZhuwIDAQABAoIBADvPiFutc5+v1U/q
lWnIYOUL5V0SwItwWMmazxbWLs/MBtiNqCE7Suafqipl/YoGH7wAJLHmg22Uktr4
WSVWnahh/4/Qw5H8Fhh02EYiYt4fhVqgNlH6l5EqEXu+c8AcoDNwzhEfGsY5HNbC
fc/m5OMPXhBLbSsHTKTN2wwTMWI+Q4Bo36uB0kWNaFgrLwbEL+CGm+U2G1fOo3QP
IehtAajmnYBG6+jO3aeFJCHtiKxNwUq1LgLI+qY96bOisCOTWOub9I8DQ5iv0CyL
dPgIJXC1617ZdGY5O5/2od1tHPugzY63pw4SzqpQR1GxXZAuNJQMoVa1KcdHvL/t
Z49FNEkCgYEA6TH4cVKCYGsmQQtI3iZg+qqHLmNbvRunLUaRz37CkS+xq6ZWLKVh
SAhrQR7yVRmivxtDX/9pqnCpY+3bP5jKA+y8fUolycRyB1CT/NMaaqt2akG4ToNQ
X89MD9MSem/vILXhfi7MO9Zy/1EOHS46h1G6+dDQaUc3zTjtpUFpoKUCgYEA6N3X
1wSamFRDBC1BYvIWGTVKmwVX1Gfz6CAjpkbyLTaDB1sIBhPWSCl8EuAxMoaXZmvl
+3u5g+YdLQZr9+mwZntgOQ53N+NsDYyuiHi0Se5/UNeOcbAyrso3moClVdwOXkbP
fbND7wBfkdjPMbUZN8gSpTV1qav0D6Vacc85Ct8CgYEAld19cya5j0mNTiP4cnxr
uuy478D/BiutZtWBg75NQI1MO6osm4i1Wlu+wh0nVDWjd/oHdLxqphS9Z/FHBDon
KhqMkGCEpITRW44XbVYmFgOXmHYgAqU1lD1e/pSBvZoOLhF1l2hv7MzHHvpyfaJm
Du0hosbmCaKxY/yADcJaJdkCgYEAunul9RA/yYt5G6guO9HItqlBtMFjo7sXzaWn
Rup72I9WARb6ZvuN1745GVimrWKxbhksVOexGhq29K622hMv6/ITjb2y5XPfvT4T
K0EWiDpRhOkKrqq++9D/FGC/hvGyI/erBGwCFC0FW+P3kUQJDO3RWLJmJtmImtr9
gjTD5psCgYBR5febSu3JkLlkx+gKkJK5HeNN/u4eXuf0OBkPZHs0TRKAGEIpVOXW
GrnTajwiKF6DMUY0JQvUzA5ZWtgqXBg7v5LsCWIYMZp+tWeEb96R4iQlQHXISN8y
y3LUb5yOjH1vjeXfD85UFV9j3s0L8zEgjwjmFQ2y0+gHxsTkeZ+B0g==
-----END RSA PRIVATE KEY-----";*/

        //CERTTIFICADOS DE PRODU
        $cert = "-----BEGIN CERTIFICATE-----
MIIDRjCCAi6gAwIBAgIINv3QVdMufY4wDQYJKoZIhvcNAQENBQAwMzEVMBMGA1UEAwwMQ29tcHV0
YWRvcmVzMQ0wCwYDVQQKDARBRklQMQswCQYDVQQGEwJBUjAeFw0yNTA0MDcwNDE5MzZaFw0yNzA0
MDcwNDE5MzZaMDExFDASBgNVBAMMC2NzckFmaXBQcm9kMRkwFwYDVQQFExBDVUlUIDMwNzE4ODY2
Nzk3MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApg+x71ivDMyRhoEo5kkVOBoB6ef9
IJ4XMZhYB02r5b7512D5jtgxUyAAeFqvof/dQUW6w/UGzPts8B9EAWuTQtqUZgFSriZID1Vp7lXJ
HgOAYUYfSdoSMfWUlxht9QYWnK9m5JVKHR/elMXm5sn42JITuU2/3LP576caqfq9J3xgcVT0U0aO
fbz75BdaVGC+NJs5aDj1snzntZP4361v8sDrpsCg3tEolo1EiJ8g8mBAu2PQJqFxPwtNrvTbrxQ/
ncbHJRHgIcFunTBAMYL/8hU7vhK/HpKuTuXzHRgTNqq0kL7kz0VF6w3e8LiCfDkUp/dCXtkHE5Ut
Tr+HTXB/oQIDAQABo2AwXjAMBgNVHRMBAf8EAjAAMB8GA1UdIwQYMBaAFCsNL8jfYf0IyU4R0DWT
BG2OW9BuMB0GA1UdDgQWBBT6xzVWJIq44df7haRPQpRO5N4e6jAOBgNVHQ8BAf8EBAMCBeAwDQYJ
KoZIhvcNAQENBQADggEBAGrmGITFmIP7vsqCbKWM2aSXU1qeOyGq7v07iHPCWYvLuqeZuhUHHRtc
U028YVZHO/6wTXCRyfHIzMU2SPPVK+IiQRjhllyx9tiK6xvDQZCmynHG/0bhPw2oochc/K9pd9WR
LaxX+ZDPL40pReOh9d5T6CfLUQcb9a7T1/2D7KDeAxUKVg2HQ4Os0jcvKgfP25dmid3IvXXFpzLq
JI3lMdsacHkXj0BH8hQzK6J4O4Ls/kr+ZV9ep1nzVll+jD7Xjq9SbNjZoRvZ8K5LCxpIQxWVG/j5
N+QTz+pX33EeVQNxRFKGn/hkzWkkdpe+PZEiMxalKJGCyUIfOsPVBlh3XUI=
-----END CERTIFICATE-----";
        $key = "-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEApg+x71ivDMyRhoEo5kkVOBoB6ef9IJ4XMZhYB02r5b7512D5
jtgxUyAAeFqvof/dQUW6w/UGzPts8B9EAWuTQtqUZgFSriZID1Vp7lXJHgOAYUYf
SdoSMfWUlxht9QYWnK9m5JVKHR/elMXm5sn42JITuU2/3LP576caqfq9J3xgcVT0
U0aOfbz75BdaVGC+NJs5aDj1snzntZP4361v8sDrpsCg3tEolo1EiJ8g8mBAu2PQ
JqFxPwtNrvTbrxQ/ncbHJRHgIcFunTBAMYL/8hU7vhK/HpKuTuXzHRgTNqq0kL7k
z0VF6w3e8LiCfDkUp/dCXtkHE5UtTr+HTXB/oQIDAQABAoIBAQCev6RPdg0qBCoe
Vjz+IOF7F0m7DT6rvtXXYJbOrI7m+/Gl1xvbVYuZtp8cbz01df9S1dEcYZIVxUm4
J3uAjBFkOMbi2OLsfsYmenXhFPtFiP1igIGKvUDlp6pb40vCkAZv1TJc1Obb/spu
mN4KxQvyJXtbixseZqNJWgsgcgX/xtnHuYAa8jPI480rCgEZjfGpTGgV0O08m3Sy
LPlBFMOu6pjMut5Z/oZmpCRxcJk7twCcT0G4HCENT2YMgn5UBKAH2u/xQm6RQD9+
ZsALl6NZE3f6/7VjFm6JSMtChAHtuzIgFVExO6dg95V4AadrFWWLA6I0Fd2yXepd
NjQQXf8dAoGBAM8gqZ9CoJr2eiSb26XdN/woRLiq4cZyfF4VDtpXVcGEfWIap1JM
GkL/3xRkXwkUz+uJkWxIxNPxyizY9nIdU42q/1ZuNik51r7Lha1xChFoEEaKsC1i
s6cKF1k0x17XvAhfldtn5HID4m55oZXYI/ei4G1Hf14SrdbxyIrbFrM7AoGBAM0+
duRA3g0DXumTj27nr9k9dPvcpf9iB06c0spsavb7MXcrrP7frVqmwdJRUOvWD4px
mk3FfiNwe7BuVa4zsss9UX66mYDpwqXauFyy+zXGFhDynO4LXyRttTQlMYFS6yi9
eu8SEIlzgxGSFP52LPMDRz9+wOWnKV/WnmbeF/LTAoGAH9+R2p0hI9eMAjgtu79o
DxiPQq+hIK2bb8rjxzurSaU+uisJ1oM5g1fmlze+3CHkwQUhaL1l9It/phkZC4Rx
yBopQr6g/QUWg7PD9WWwu+78mi5bYBCRPsIxx5t0lqayG8PBxsqc/+KTJJb5A5sI
JeJdkNNwySQstifsyVlfi+MCgYB69tGELtjaMwrKJf3pFCXmoUtVGfzZ5IE8N/iS
DiKHljcsjp42lQvnI8E7oDw7DySAbU9lCM1prhpsua3bkF2uJi6NHn6ZHXPgqgmK
gDDBJsxRmwkqFOYLjS7i7uWZ9XOuFNdyJACEWRBNlpe9XE/RyEUHZYlsDZpcMNj2
CFmQwQKBgQCrwv5QmTI5GoFj5GJl/ztG46+AyZeUaItQp0gsGVEQ2S+bZYYx1T+e
WDuolM9wd08q0e+K3sFMv64C+LN34uQBwAlgPQ+ea+NHZ8b4zmbpW8fv5VvU1x/v
2F1DSH1UvuGZdvmpx2Jmb/9C3olO/HhoybCxVvA96Vf4ufnbc+esoA==
-----END RSA PRIVATE KEY-----";

        $tax_id = $cuitEmpresa;
        //$tax_id = 20430166035;
        //Crear la instancia de AFIP dentro del método
        $afip = new Afip(array(
            'CUIT' => $tax_id, 
            'cert' => $cert,  // Ruta al archivo de certificado
            'key' => $key,          // Ruta al archivo de clave privada
            'access_token' => 'gi72RbXfkm4DcfnmLSAOl1xnS1DvAMZfP6uFwQciVerrDlI3uJSEkgYU090AoGIw'
        ));

        //$ta = $afip->ElectronicBilling->getTokenAuthorization(TRUE);
        //var_dump($ta);

        try {
            // Obtener el último número de factura
            $last_voucher = $afip->ElectronicBilling->GetLastVoucher($pto_vta, $invoice_type);  

            // Verificar si obtuvimos un número válido
            if (isset($last_voucher)) {
                // Obtener el número de factura y aumentar 1
                $numero_de_factura = $last_voucher + 1;
            } else {
                throw new \Exception('No se pudo obtener el último número de factura autorizado.');
            }
            
            
            $ivaRates = [
                'iva_21' => 0.21,
                'iva_27' => 0.27,
                'iva_10' => 0.105,
                'excento_iva' =>0,
            ];
            Log::debug('Valor de invoice_iva: ' . $invoice_iva);
            Log::debug('Valores de ivaRates: ' . json_encode($ivaRates));
            $valIva = $ivaRates[$invoice_iva];
            
            // Ajustar precios unitarios y subtotales a valores sin IVA
            foreach ($products as &$product) {
                $original_unit_price = $product['unit_price'];
                $quantity = $product['quantity'];
            
                if ($valIva > 0) {
                    $unit_price_sin_iva = round($original_unit_price / (1 + $valIva), 2);
                } else {
                    $unit_price_sin_iva = $original_unit_price;
                }
            
                $product['unit_price'] = $unit_price_sin_iva;
                $product['subtotal'] = round($unit_price_sin_iva * $quantity, 2);
            }
            unset($product); // buenas prácticas para no dejar referencias abiertas

            Log::debug('Valor de valIva: ' . $valIva);
            // Cálculos para los campos relacionados
            $ImpTotConc = 0;  // Si no hay conceptos totales, se asume como 0
            $ImpNeto = 0;  // Monto neto
            $ImpOpEx = 0;  // No se aplica en este caso, asumiéndolo como 0
            $ImpTrib = 0;
            $ImpIVA = 0;// No se aplica en este caso, asumiéndolo como 0
            if ($invoice_type === 8 || $invoice_type === 13){
                $ImpIVA = 0;
            }else{
                $ImpIVA = round($denomination_total_amount * $valIva, 2);}// IVA del 21%
            
            if ($valIva > 0) {
                $ImpNeto = round($denomination_total_amount / (1 + $valIva), 2);
                $ImpIVA = round($denomination_total_amount - $ImpNeto, 2);
            } else {
                $ImpNeto = $denomination_total_amount;
                $ImpIVA = 0;
            }

            
            // Configurar las fechas para el servicio y vencimiento de pago
            if ($invoice_concept === 2 || $invoice_concept === 3) {
                $fecha_servicio_desde = intval(str_replace('-', '', $start_date));
                $fecha_servicio_hasta = intval(str_replace('-', '', $end_date));
                $fecha_vencimiento_pago = intval(str_replace('-', '', $payment_due_date));
            } else {
                $fecha_servicio_desde = null;
                $fecha_servicio_hasta = null;
                $fecha_vencimiento_pago = null;
            }
            $idIva = null;
            
            if ($valIva === 0.21){
                $idIva = 5;
            }elseif ($valIva === 0.27){
                $idIva = 6; 
            }elseif ($valIva === 0.105){
                $idIva = 4; 
            }else{
                $idIva = 3; 
            }
            
            
            // Calculamos el ImpTotal como la suma de los componentes
            $ImpTotal = number_format($ImpTotConc + $ImpNeto + $ImpOpEx + $ImpTrib + $ImpIVA, 2, '.', '');
            
            // Luego, ajustamos los datos de la factura
            $invoice_data = [
                'CantReg'    => 1,
                'PtoVta'    => $pto_vta,  // Punto de venta
                'CbteTipo'    => $invoice_type,
                'Concepto'    => $invoice_concept,
                'DocTipo'    => $buyer_document_type,
                'DocNro'    => $buyer_document_number,
                'CbteDesde' => $numero_de_factura,
                'CbteHasta' => $numero_de_factura,
                'CbteFch'    => intval(str_replace('-', '', $transaction_date)),
                'FchServDesde' => $fecha_servicio_desde,
                'FchServHasta' => $fecha_servicio_hasta,
                'FchVtoPago'   => $fecha_vencimiento_pago,
                'ImpTotal'    => $ImpTotal,  // Aseguramos que ImpTotal sea correcto
                'ImpTotConc'  => $ImpTotConc,
                'ImpNeto'    => $ImpNeto,
                'ImpOpEx'    => $ImpOpEx,
                'ImpIVA'    => $ImpIVA,
                'ImpTrib'    => $ImpTrib,
                'CondicionIVAReceptorId' => $iva_receptor,
                'MonId'    => 'PES',  // Moneda: Pesos Argentinos
                'MonCotiz'    => 1,  // Cotización
            ];
            
            // Solo agrega 'Iva' si el concepto no es 11
            if ($invoice_type === 1 || $invoice_type === 3 || $invoice_type === 6 || $invoice_type === 8 || $invoice_type === 4 || $invoice_type === 9 || $valIva === 0.27 || $valIva === 0.105) {
                $invoice_data['Iva'] = [
                    [
                        'Id'        => $idIva,  // 21% IVA
                        'BaseImp'    => $ImpNeto,
                        'Importe'    => $ImpIVA,  // IVA calculado al 21%
                    ]
                ];
            }
            if ($invoice_type === 3 || $invoice_type === 8 || $invoice_type === 13) {
                $invoice_data['CbtesAsoc'] = [
                    [
                        'Tipo' => $invoice_type - 2, // Tipo de comprobante (ejemplo: Factura A)
                        'PtoVta' => $pto_vta, // Punto de venta asociado
                        'Nro' => $num_factura_val 
                    ]
                ];
            }
            
            Log::info('Contenido de $invoice_data: ' . print_r($invoice_data, true));
            // Llamar a la API de AFIP para crear la factura
            $res = $afip->ElectronicBilling->CreateVoucher($invoice_data);
            Log::info('Se llamo correctamente a la API');
            
            // Verificar si la respuesta contiene errores
            if (isset($res['Errors']) && count($res['Errors']) > 0) {
                $errorMsg = implode(', ', $res['Errors']);
                return response()->json([
                    'error' => "Error al crear la factura: " . $errorMsg
                ], 400); // Retorna código 400
            }
            
            // Obtenemos el CAE que es necesario para el código QR
            $cae = $res['CAE'];
            $cae_due_date = $res['CAEFchVto'];
            Log::info('Crear el JSON');
            // Crear el JSON con los datos del comprobante (incluyendo el CAE)
            $invoice_json = [
                'ver' => 1,
                'fecha' => $transaction_date,
                'cuit' => $tax_id,
                'PtoVta' => $pto_vta,  // Punto de venta
                'tipoCmp' => $invoice_type,
                'nroCmp' => $numero_de_factura,
                'importe' => $ImpTotal,
                'moneda' => 'PES',  // Moneda Pesos Argentinos
                'ctz' => 1,  // Cotización (1 para pesos)
                'tipoDocRec' => $buyer_document_type,
                'nroDocRec' => $buyer_document_number,
                'tipoCodAut' => 'E',  // Autorizado por CAE
                'codAut' => $cae,  // Usamos el CAE aquí
                
            ];
            // Codificar el JSON en base64
            $datos_base64 = base64_encode(json_encode($invoice_json));

            // Crear la URL
            $url_qr = 'https://www.afip.gob.ar/fe/qr/?p=' . $datos_base64;

            $qr_data = 'https://www.afip.gob.ar/fe/qr/?p=' . base64_encode(json_encode($invoice_json));
            
            // Generar el código QR con logo y label
            $writer = new PngWriter();
            // Crear QR Code con configuraciones personalizadas
            $qrCode = new QrCode(
                data: $url_qr,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 300,  // Tamaño del QR
                margin: 10, // Margen
                roundBlockSizeMode: RoundBlockSizeMode::Margin, // Estilo de bloque redondeado
                foregroundColor: new Color(0, 0, 0),  // Color del QR
                backgroundColor: new Color(255, 255, 255) // Color de fondo
            );
            $qrImage = 'data:image/png;base64,' . base64_encode($writer->write($qrCode)->getString());
            // Crear el logo
            $logo = new Logo(
                path: __DIR__.'/assets/logo.png', // Asegúrate de poner la ruta correcta de tu logo
                resizeToWidth: 50, // Tamaño del logo
                punchoutBackground: true // Quitar el fondo del logo
            );
            // Crear el label
            $label = new Label(
                text: 'Factura QR',
                textColor: new Color(255, 0, 0), // Color del texto
            );

            // Combinar el QR con el logo y el label
            $result = $writer->write($qrCode, $logo, $label);
            
            // Obtener la URL de la imagen del QR como Data URI
            $dataUri = $result->getDataUri();
            
            $tipoFactura = "A";
            
            if ($invoice_type === 6) {
                $tipoFactura = "B";
            } elseif ($invoice_type === 11) {
                $tipoFactura = "C";
            } elseif ($invoice_type === 3) {
                $tipoFactura = "A";
            } elseif ($invoice_type === 8) {
                $tipoFactura = "B";
            } elseif ($invoice_type === 13) {
                $tipoFactura = "C";
            } elseif ($invoice_type === 4) {
                $tipoFactura = "A";
            } elseif ($invoice_type === 9) {
                $tipoFactura = "B";
            } elseif ($invoice_type === 15) {
                $tipoFactura = "C";
            }
            
            Log::info('Crear HTML de factura');
            // Crear HTML de factura
            $html = view('sell.factura', [
                'invoice_number' => $invoice_number,
                'transaction_date' => $data['transaction_date'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'payment_due_date' => $data['payment_due_date'],
                'buyer_document_number' => $data['buyer_document_number'],
                'ImpIVA' => $ImpIVA,
                'ImpNeto' => $ImpNeto,
                'denomination_total_amount' => $ImpTotal,
                'cae' => $cae,
                'cae_due_date' => $cae_due_date,
                'qrImage' => $qrImage,
                'invoice_type' => $tipoFactura,
                'recibo' => $invoice_type,
                'business_name' => $business_name,
                'products' => $products,
                'cuitEmpresa' => $cuitEmpresa,
                'direccion' => $direccion,
                'fechaInicio' => $fechaInicio,
                'numFact' => $numero_de_factura,
                'logoEmpresa' => $logoEmpresa,
                'iva_receptor' => $iva_receptor,
                'valIva' => $valIva,
                'locacion_comercial' => $locacion_comercial,
            ])->render();
            Log::info('Iniciando la creación del HTML para la factura.', [
                'invoice_number' => $invoice_number,
                'transaction_date' => $data['transaction_date'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'payment_due_date' => $data['payment_due_date'],
                'buyer_document_number' => $data['buyer_document_number'],
                'ImpIVA' => $ImpIVA,
                'ImpNeto' => $ImpNeto,
                'denomination_total_amount' => $ImpTotal,
                'cae' => $cae,
                'cae_due_date' => $cae_due_date,
                'invoice_type' => $tipoFactura,
                'business_name' => $business_name,
                'products' => count($products),
                'cuitEmpresa' => $cuitEmpresa,
                'direccion' => $direccion,
                'fechaInicio' => $fechaInicio,
                'numFact' => $numero_de_factura,
                'logoEmpresa' => $logoEmpresa,
            ]);
            
            // Configurar Dompdf
            /*
            $options = new Options();
            $options->set('isRemoteEnabled', true); // Permitir cargar imágenes externas, si es necesario
            $mpdf = new Mpdf($options);

            // Cargar el contenido HTML en Dompdf
            $mpdf->loadHtml($html);

            // Configurar el tamaño del papel y la orientación
            $mpdf->setPaper('A4', 'portrait'); // A4 y orientación vertical

            // Renderizar el PDF
            $mpdf->render();
            
            // Guardar el archivo temporalmente
            //$pdfFile = tempnam(sys_get_temp_dir(), 'factura') . '.pdf';
            $directory = storage_path('app/public/facturas_afip/');
            $nombre_empresa = Str::slug($business_name, '_'); 
            $filename = "FACTURA_{$nombre_empresa}_{$numero_de_factura}.pdf";
            Storage::disk('public')->put("facturas_afip/{$filename}", $mpdf->output());
            $pdfFile = $directory . $filename;
            
            file_put_contents($pdfFile, $mpdf->output());
            */
            
            $directory = storage_path('app/public/facturas_afip/');
            $nombre_empresa = Str::slug($business_name, '_');
            $filename = "FACTURA_{$nombre_empresa}_{$numero_de_factura}.pdf";
            $pdfPath = $directory . $filename;
            
            Browsershot::html($html)
                ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                ->setOption('executablePath', '/usr/bin/chromium') // o donde esté instalado
                ->format('A4')
                ->save($pdfPath);

            
            // (Opcional) Actualiza la base de datos con la ruta si lo necesitas
            $businessId = auth()->user()->business_id;
            if ($businessId) {
                $updated = DB::table('business')
                    ->where('id', $businessId)
                    ->update(['facturaTemp' => $pdfPath]);
            
                if (!$updated) {
                    Log::error("No se pudo actualizar la columna facturaTemp para el business con ID $businessId.");
                }
            }
            
            // Descargar el archivo PDF generado
            if (file_exists($pdfPath)) {
                return response()->download($pdfPath);
            }
            
            $businessId = auth()->user()->business_id; // O la forma que se ajuste a tu lógica
            if ($businessId) {
                $updated = DB::table('business')
                    ->where('id', $businessId)
                    ->update(['facturaTemp' => $pdfFile]);
            
                if (!$updated) {
                    Log::error("No se pudo actualizar la columna facturaTemp para el business con ID $businessId.");
                }
            }
            
            if (file_exists($pdfFile)) {
                //return response()->download($pdfFile, "factura_{$numero_de_factura}.pdf");
                return response()->download(storage_path('app/public/facturas_afip/' . $filename));
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Hubo un error al generar la factura: ' . $e->getMessage(),
            ], 400); // Código 400 en caso de error
        }
    }
    
    public function crearFacturaPOS(Request $request)
    {
        // Validar los datos recibidos desde el frontend
        $data = $request->validate([
            'invoice_type' => 'required|integer',
            'invoice_concept' => 'required|integer',
            'buyer_document_type' => 'required|integer',
            'buyer_document_number' => 'required|integer',
            'transaction_date' => 'required|date',
            'denomination_total_amount' => 'required|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'payment_due_date' => 'nullable|date',
            'business_name' => 'required|string',
            'products' => 'required|array', 
            'products.*.product_name' => 'nullable|string',
            'products.*.quantity' => 'nullable|numeric', 
            'products.*.unit_price' => 'nullable|numeric', 
            'products.*.discount' => 'nullable|numeric', 
            'products.*.subtotal' => 'nullable|numeric', 
            'cuitEmpresa' => 'required|string',
            'direccion' => 'required|string',
            'fechaInicio' => 'required|date',
            'invoice_iva' => 'nullable|string',
            'tipoTransferencia' => 'nullable|string',
            'cbu_cliente_val' => 'nullable|numeric',
            'num_factura_val' => 'nullable|numeric',
            'logoEmpresa' => 'nullable|string',
            'iva_receptor' => 'required|numeric',
            'locacion_comercial' => 'required|string',
            'pto_vta' => 'required|numeric',
            'customer_cuit' => 'nullable|string',
            'customer_name' => 'nullable|string',
            'customer_address' => 'nullable|string',
            'payment_condition' => 'nullable|string'
            
        ]);
        Log::debug('Datos recibidos en el controlador:', $data);
        // Extraer los datos del request
        $invoice_type = $data['invoice_type'];
        $invoice_concept = $data['invoice_concept'];
        $buyer_document_type = $data['buyer_document_type'];
        $buyer_document_number = $data['buyer_document_number'];
        $transaction_date = $data['transaction_date'];
        $denomination_total_amount = $data['denomination_total_amount'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $payment_due_date = $data['payment_due_date'];
        $business_name = $data['business_name'];
        $products = $data['products'];
        $cuitEmpresa = $data['cuitEmpresa'];
        $direccion = $data['direccion'];
        $fechaInicio = $data['fechaInicio'];
        $invoice_iva = $data['invoice_iva'];
        $tipoTransferencia = $data['tipoTransferencia'];
        $cbu_cliente_val = $data['cbu_cliente_val'];
        $num_factura_val = $data['num_factura_val'];
        $logoEmpresa = $data['logoEmpresa'];
        $iva_receptor = $data['iva_receptor'];
        $locacion_comercial = $data['locacion_comercial'];
        $pto_vta = $data['pto_vta'];
        $customer_cuit = $data['customer_cuit'];
        $customer_name = $data['customer_name'];
        $customer_address = $data['customer_address'];
        $payment_condition = $data['payment_condition'];
        
        
        
            // Ahora puedes iterar sobre los productos si los necesitas para cálculos adicionales
        foreach ($products as $product) {
            // Aquí puedes trabajar con los datos de cada producto
            $product_name = $product['product_name'];
            $quantity = $product['quantity'];
            $unit_price = $product['unit_price'];
            $discount = $product['discount'];
            $subtotal = $product['subtotal'];
    
            // Puedes hacer cálculos o ajustes con estos datos si es necesario
        }
        
        //CERTIFICADOS DE TESTING
        $cert ="-----BEGIN CERTIFICATE-----
MIIDSDCCAjCgAwIBAgIIA+PIYhIXNqAwDQYJKoZIhvcNAQENBQAwODEaMBgGA1UEAwwRQ29tcHV0
YWRvcmVzIFRlc3QxDTALBgNVBAoMBEFGSVAxCzAJBgNVBAYTAkFSMB4XDTI0MTEyNjA2MTkxNVoX
DTI2MTEyNjA2MTkxNVowLjERMA8GA1UEAwwIY2VydFdzZmUxGTAXBgNVBAUTEENVSVQgMjA0MzAx
NjYwMzUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDUH13nAWrQGcgOS6GURZT8szAL
Mf4B+sQ4oLYA0jWHpuAFKRXU6ExjK/DKJ0KYIppUlpH2NVrIoJi3IhFI74q6xNu+HfFhicZZVz76
Xukpm43uidsm9Hs86LicKA64ZWmUNJvoMCdOng5g077r4WwZRb2U0l9t/Wsvu4GEGNiF/m/Oykj7
mnRFcKOZbOMuez4cHe6It5DgKUQuNVBqvgpplwYHO98gmmMbX/lXAd+7eBukl9JfLkii1umjcrM2
OJcjpWhguqJCaODgeACnr5VDPdb7lfR+dGJOPNRmrulXKsWlv9Fj8DszrqPBLr4x8WICJOr5c8ie
KL8CW8FLBmG7AgMBAAGjYDBeMAwGA1UdEwEB/wQCMAAwHwYDVR0jBBgwFoAUs7LT//3put7eja8R
IZzWIH3yT28wHQYDVR0OBBYEFN4UnQPqhhlHE93fmxGuuI4mX6AJMA4GA1UdDwEB/wQEAwIF4DAN
BgkqhkiG9w0BAQ0FAAOCAQEAbILLDNvGLnLFDBBHimisE8wJoVz/BIHnX35AWH+46zURuwnkLDZL
OJyaE94BVqeDqspfnbgOg56oEeMUDqJty1YcwWzYPsiTr7tkwnzJpjTDoUkLA0zqDnyJB96FTwMj
WcxrmIe4AFG0MMjUR7uHDg8OCzg+ZWai5WXlVJjJpX5NMR/7Pm/iKdhbKZlvbUKZdYIUt1EzxxGL
Ofg0F/y25gIJPdA5RbND8rrP6REZjWqLpiAOhtjNPP6TCaE2Se8T1dbIqWIZ9uQ0bEGwvAhYzvby
Qpa/GCQUUbF00NpDs9/tbZrakL5a/apEe+7vFz6VYbpto9gfDWhWvFnqN8uDeQ==
-----END CERTIFICATE-----";
        $key = "-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA1B9d5wFq0BnIDkuhlEWU/LMwCzH+AfrEOKC2ANI1h6bgBSkV
1OhMYyvwyidCmCKaVJaR9jVayKCYtyIRSO+KusTbvh3xYYnGWVc++l7pKZuN7onb
JvR7POi4nCgOuGVplDSb6DAnTp4OYNO+6+FsGUW9lNJfbf1rL7uBhBjYhf5vzspI
+5p0RXCjmWzjLns+HB3uiLeQ4ClELjVQar4KaZcGBzvfIJpjG1/5VwHfu3gbpJfS
Xy5Iotbpo3KzNjiXI6VoYLqiQmjg4HgAp6+VQz3W+5X0fnRiTjzUZq7pVyrFpb/R
Y/A7M66jwS6+MfFiAiTq+XPInii/AlvBSwZhuwIDAQABAoIBADvPiFutc5+v1U/q
lWnIYOUL5V0SwItwWMmazxbWLs/MBtiNqCE7Suafqipl/YoGH7wAJLHmg22Uktr4
WSVWnahh/4/Qw5H8Fhh02EYiYt4fhVqgNlH6l5EqEXu+c8AcoDNwzhEfGsY5HNbC
fc/m5OMPXhBLbSsHTKTN2wwTMWI+Q4Bo36uB0kWNaFgrLwbEL+CGm+U2G1fOo3QP
IehtAajmnYBG6+jO3aeFJCHtiKxNwUq1LgLI+qY96bOisCOTWOub9I8DQ5iv0CyL
dPgIJXC1617ZdGY5O5/2od1tHPugzY63pw4SzqpQR1GxXZAuNJQMoVa1KcdHvL/t
Z49FNEkCgYEA6TH4cVKCYGsmQQtI3iZg+qqHLmNbvRunLUaRz37CkS+xq6ZWLKVh
SAhrQR7yVRmivxtDX/9pqnCpY+3bP5jKA+y8fUolycRyB1CT/NMaaqt2akG4ToNQ
X89MD9MSem/vILXhfi7MO9Zy/1EOHS46h1G6+dDQaUc3zTjtpUFpoKUCgYEA6N3X
1wSamFRDBC1BYvIWGTVKmwVX1Gfz6CAjpkbyLTaDB1sIBhPWSCl8EuAxMoaXZmvl
+3u5g+YdLQZr9+mwZntgOQ53N+NsDYyuiHi0Se5/UNeOcbAyrso3moClVdwOXkbP
fbND7wBfkdjPMbUZN8gSpTV1qav0D6Vacc85Ct8CgYEAld19cya5j0mNTiP4cnxr
uuy478D/BiutZtWBg75NQI1MO6osm4i1Wlu+wh0nVDWjd/oHdLxqphS9Z/FHBDon
KhqMkGCEpITRW44XbVYmFgOXmHYgAqU1lD1e/pSBvZoOLhF1l2hv7MzHHvpyfaJm
Du0hosbmCaKxY/yADcJaJdkCgYEAunul9RA/yYt5G6guO9HItqlBtMFjo7sXzaWn
Rup72I9WARb6ZvuN1745GVimrWKxbhksVOexGhq29K622hMv6/ITjb2y5XPfvT4T
K0EWiDpRhOkKrqq++9D/FGC/hvGyI/erBGwCFC0FW+P3kUQJDO3RWLJmJtmImtr9
gjTD5psCgYBR5febSu3JkLlkx+gKkJK5HeNN/u4eXuf0OBkPZHs0TRKAGEIpVOXW
GrnTajwiKF6DMUY0JQvUzA5ZWtgqXBg7v5LsCWIYMZp+tWeEb96R4iQlQHXISN8y
y3LUb5yOjH1vjeXfD85UFV9j3s0L8zEgjwjmFQ2y0+gHxsTkeZ+B0g==
-----END RSA PRIVATE KEY-----";

        //CERTTIFICADOS DE PRODU
        /*$cert = "-----BEGIN CERTIFICATE-----
MIIDRjCCAi6gAwIBAgIINv3QVdMufY4wDQYJKoZIhvcNAQENBQAwMzEVMBMGA1UEAwwMQ29tcHV0
YWRvcmVzMQ0wCwYDVQQKDARBRklQMQswCQYDVQQGEwJBUjAeFw0yNTA0MDcwNDE5MzZaFw0yNzA0
MDcwNDE5MzZaMDExFDASBgNVBAMMC2NzckFmaXBQcm9kMRkwFwYDVQQFExBDVUlUIDMwNzE4ODY2
Nzk3MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApg+x71ivDMyRhoEo5kkVOBoB6ef9
IJ4XMZhYB02r5b7512D5jtgxUyAAeFqvof/dQUW6w/UGzPts8B9EAWuTQtqUZgFSriZID1Vp7lXJ
HgOAYUYfSdoSMfWUlxht9QYWnK9m5JVKHR/elMXm5sn42JITuU2/3LP576caqfq9J3xgcVT0U0aO
fbz75BdaVGC+NJs5aDj1snzntZP4361v8sDrpsCg3tEolo1EiJ8g8mBAu2PQJqFxPwtNrvTbrxQ/
ncbHJRHgIcFunTBAMYL/8hU7vhK/HpKuTuXzHRgTNqq0kL7kz0VF6w3e8LiCfDkUp/dCXtkHE5Ut
Tr+HTXB/oQIDAQABo2AwXjAMBgNVHRMBAf8EAjAAMB8GA1UdIwQYMBaAFCsNL8jfYf0IyU4R0DWT
BG2OW9BuMB0GA1UdDgQWBBT6xzVWJIq44df7haRPQpRO5N4e6jAOBgNVHQ8BAf8EBAMCBeAwDQYJ
KoZIhvcNAQENBQADggEBAGrmGITFmIP7vsqCbKWM2aSXU1qeOyGq7v07iHPCWYvLuqeZuhUHHRtc
U028YVZHO/6wTXCRyfHIzMU2SPPVK+IiQRjhllyx9tiK6xvDQZCmynHG/0bhPw2oochc/K9pd9WR
LaxX+ZDPL40pReOh9d5T6CfLUQcb9a7T1/2D7KDeAxUKVg2HQ4Os0jcvKgfP25dmid3IvXXFpzLq
JI3lMdsacHkXj0BH8hQzK6J4O4Ls/kr+ZV9ep1nzVll+jD7Xjq9SbNjZoRvZ8K5LCxpIQxWVG/j5
N+QTz+pX33EeVQNxRFKGn/hkzWkkdpe+PZEiMxalKJGCyUIfOsPVBlh3XUI=
-----END CERTIFICATE-----";
        $key = "-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEApg+x71ivDMyRhoEo5kkVOBoB6ef9IJ4XMZhYB02r5b7512D5
jtgxUyAAeFqvof/dQUW6w/UGzPts8B9EAWuTQtqUZgFSriZID1Vp7lXJHgOAYUYf
SdoSMfWUlxht9QYWnK9m5JVKHR/elMXm5sn42JITuU2/3LP576caqfq9J3xgcVT0
U0aOfbz75BdaVGC+NJs5aDj1snzntZP4361v8sDrpsCg3tEolo1EiJ8g8mBAu2PQ
JqFxPwtNrvTbrxQ/ncbHJRHgIcFunTBAMYL/8hU7vhK/HpKuTuXzHRgTNqq0kL7k
z0VF6w3e8LiCfDkUp/dCXtkHE5UtTr+HTXB/oQIDAQABAoIBAQCev6RPdg0qBCoe
Vjz+IOF7F0m7DT6rvtXXYJbOrI7m+/Gl1xvbVYuZtp8cbz01df9S1dEcYZIVxUm4
J3uAjBFkOMbi2OLsfsYmenXhFPtFiP1igIGKvUDlp6pb40vCkAZv1TJc1Obb/spu
mN4KxQvyJXtbixseZqNJWgsgcgX/xtnHuYAa8jPI480rCgEZjfGpTGgV0O08m3Sy
LPlBFMOu6pjMut5Z/oZmpCRxcJk7twCcT0G4HCENT2YMgn5UBKAH2u/xQm6RQD9+
ZsALl6NZE3f6/7VjFm6JSMtChAHtuzIgFVExO6dg95V4AadrFWWLA6I0Fd2yXepd
NjQQXf8dAoGBAM8gqZ9CoJr2eiSb26XdN/woRLiq4cZyfF4VDtpXVcGEfWIap1JM
GkL/3xRkXwkUz+uJkWxIxNPxyizY9nIdU42q/1ZuNik51r7Lha1xChFoEEaKsC1i
s6cKF1k0x17XvAhfldtn5HID4m55oZXYI/ei4G1Hf14SrdbxyIrbFrM7AoGBAM0+
duRA3g0DXumTj27nr9k9dPvcpf9iB06c0spsavb7MXcrrP7frVqmwdJRUOvWD4px
mk3FfiNwe7BuVa4zsss9UX66mYDpwqXauFyy+zXGFhDynO4LXyRttTQlMYFS6yi9
eu8SEIlzgxGSFP52LPMDRz9+wOWnKV/WnmbeF/LTAoGAH9+R2p0hI9eMAjgtu79o
DxiPQq+hIK2bb8rjxzurSaU+uisJ1oM5g1fmlze+3CHkwQUhaL1l9It/phkZC4Rx
yBopQr6g/QUWg7PD9WWwu+78mi5bYBCRPsIxx5t0lqayG8PBxsqc/+KTJJb5A5sI
JeJdkNNwySQstifsyVlfi+MCgYB69tGELtjaMwrKJf3pFCXmoUtVGfzZ5IE8N/iS
DiKHljcsjp42lQvnI8E7oDw7DySAbU9lCM1prhpsua3bkF2uJi6NHn6ZHXPgqgmK
gDDBJsxRmwkqFOYLjS7i7uWZ9XOuFNdyJACEWRBNlpe9XE/RyEUHZYlsDZpcMNj2
CFmQwQKBgQCrwv5QmTI5GoFj5GJl/ztG46+AyZeUaItQp0gsGVEQ2S+bZYYx1T+e
WDuolM9wd08q0e+K3sFMv64C+LN34uQBwAlgPQ+ea+NHZ8b4zmbpW8fv5VvU1x/v
2F1DSH1UvuGZdvmpx2Jmb/9C3olO/HhoybCxVvA96Vf4ufnbc+esoA==
-----END RSA PRIVATE KEY-----";*/

        //$tax_id = $cuitEmpresa;
        $tax_id = 20430166035;
        // Crear la instancia de AFIP dentro del método
        $afip = new Afip(array(
            'CUIT' => $tax_id, 
            'cert' => $cert,  // Ruta al archivo de certificado
            'key' => $key,          // Ruta al archivo de clave privada
            'access_token' => 'gi72RbXfkm4DcfnmLSAOl1xnS1DvAMZfP6uFwQciVerrDlI3uJSEkgYU090AoGIw'
        ));

        //$ta = $afip->ElectronicBilling->getTokenAuthorization(TRUE);
        //var_dump($ta);

        try {
            // Obtener el último número de factura
            $last_voucher = $afip->ElectronicBilling->GetLastVoucher($pto_vta, $invoice_type);  // Punto de venta 1

            // Verificar si obtuvimos un número válido
            if (isset($last_voucher)) {
                // Obtener el número de factura y aumentar 1
                $numero_de_factura = $last_voucher + 1;
            } else {
                throw new \Exception('No se pudo obtener el último número de factura autorizado.');
            }
            
            
            $ivaRates = [
                'iva_21' => 0.21,
                'iva_27' => 0.27,
                'iva_10' => 0.105,
                'excento_iva' =>0,
            ];
            Log::debug('Valor de invoice_iva: ' . $invoice_iva);
            Log::debug('Valores de ivaRates: ' . json_encode($ivaRates));
            $valIva = $ivaRates[$invoice_iva];
            Log::debug('Valor de valIva: ' . $valIva);
            // Cálculos para los campos relacionados
            $ImpTotConc = 0;  // Si no hay conceptos totales, se asume como 0
            $ImpNeto = $denomination_total_amount;  // Monto neto
            $ImpOpEx = 0;  // No se aplica en este caso, asumiéndolo como 0
            $ImpTrib = 0;
            $ImpIVA = 0;// No se aplica en este caso, asumiéndolo como 0
            if ($invoice_type === 8 || $invoice_type === 13){
                $ImpIVA = 0;
            }else{
                $ImpIVA = round($denomination_total_amount * $valIva, 2);}// IVA del 21
            
            
            // Configurar las fechas para el servicio y vencimiento de pago
            if ($invoice_concept === 2 || $invoice_concept === 3) {
                $fecha_servicio_desde = intval(str_replace('-', '', $start_date));
                $fecha_servicio_hasta = intval(str_replace('-', '', $end_date));
                $fecha_vencimiento_pago = intval(str_replace('-', '', $payment_due_date));
            } else {
                $fecha_servicio_desde = null;
                $fecha_servicio_hasta = null;
                $fecha_vencimiento_pago = null;
            }

            
            $idIva = null;
            
            if ($valIva === 0.21){
                $idIva = 5;
            }elseif ($valIva === 0.27){
                $idIva = 6; 
            }elseif ($valIva === 0.105){
                $idIva = 4; 
            }else{
                $idIva = 3; 
            }
            
            
            // Calculamos el ImpTotal como la suma de los componentes
            $ImpTotal = number_format($ImpTotConc + $ImpNeto + $ImpOpEx + $ImpTrib + $ImpIVA, 2, '.', '');
            
            // Luego, ajustamos los datos de la factura
            $invoice_data = [
                'CantReg'    => 1,
                'PtoVta'    => $pto_vta,  // Punto de venta
                'CbteTipo'    => $invoice_type,
                'Concepto'    => $invoice_concept,
                'DocTipo'    => $buyer_document_type,
                'DocNro'    => $buyer_document_number,
                'CbteDesde' => $numero_de_factura,
                'CbteHasta' => $numero_de_factura,
                'CbteFch'    => intval(str_replace('-', '', $transaction_date)),
                'FchServDesde' => $fecha_servicio_desde,
                'FchServHasta' => $fecha_servicio_hasta,
                'FchVtoPago'   => $fecha_vencimiento_pago,
                'ImpTotal'    => $ImpTotal,  // Aseguramos que ImpTotal sea correcto
                'ImpTotConc'  => $ImpTotConc,
                'ImpNeto'    => $ImpNeto,
                'ImpOpEx'    => $ImpOpEx,
                'ImpIVA'    => $ImpIVA,
                'ImpTrib'    => $ImpTrib,
                'CondicionIVAReceptorId' => $iva_receptor,
                'MonId'    => 'PES',  // Moneda: Pesos Argentinos
                'MonCotiz'    => 1,  // Cotización
            ];
            
            // Solo agrega 'Iva' si el concepto no es 11
            if ($invoice_type === 1 || $invoice_type === 3 || $invoice_type === 6 || $invoice_type === 8 || $invoice_type === 4 || $invoice_type === 9 || $valIva === 0.27 || $valIva === 0.105) {
                $invoice_data['Iva'] = [
                    [
                        'Id'        => $idIva,  // 21% IVA
                        'BaseImp'    => $ImpNeto,
                        'Importe'    => $ImpIVA,  // IVA calculado al 21%
                    ]
                ];
            }
            if ($invoice_type === 3 || $invoice_type === 8 || $invoice_type === 13) {
                $invoice_data['CbtesAsoc'] = [
                    [
                        'Tipo' => $invoice_type - 2, // Tipo de comprobante (ejemplo: Factura A)
                        'PtoVta' => $pto_vta, // Punto de venta asociado
                        'Nro' => $num_factura_val 
                    ]
                ];
            }
            
            Log::info('Contenido de $invoice_data: ' . print_r($invoice_data, true));
            // Llamar a la API de AFIP para crear la factura
            $res = $afip->ElectronicBilling->CreateVoucher($invoice_data);
            Log::info('Se llamo correctamente a la API');
            
            // Verificar si la respuesta contiene errores
            if (isset($res['Errors']) && count($res['Errors']) > 0) {
                $errorMsg = implode(', ', $res['Errors']);
                return response()->json([
                    'error' => "Error al crear la factura: " . $errorMsg
                ], 400); // Retorna código 400
            }
            
            // Obtenemos el CAE que es necesario para el código QR
            $cae = $res['CAE'];
            $cae_due_date = $res['CAEFchVto'];
            Log::info('Crear el JSON');
            // Crear el JSON con los datos del comprobante (incluyendo el CAE)
            $invoice_json = [
                'ver' => 1,
                'fecha' => $transaction_date,
                'cuit' => $tax_id,
                'PtoVta' => $pto_vta,  // Punto de venta
                'tipoCmp' => $invoice_type,
                'nroCmp' => $numero_de_factura,
                'importe' => $ImpTotal,
                'moneda' => 'PES',  // Moneda Pesos Argentinos
                'ctz' => 1,  // Cotización (1 para pesos)
                'tipoDocRec' => $buyer_document_type,
                'nroDocRec' => $buyer_document_number,
                'tipoCodAut' => 'E',  // Autorizado por CAE
                'codAut' => $cae,  // Usamos el CAE aquí
                
            ];
            // Codificar el JSON en base64
            $datos_base64 = base64_encode(json_encode($invoice_json));

            // Crear la URL
            $url_qr = 'https://www.afip.gob.ar/fe/qr/?p=' . $datos_base64;

            $qr_data = 'https://www.afip.gob.ar/fe/qr/?p=' . base64_encode(json_encode($invoice_json));
            
            // Generar el código QR con logo y label
            $writer = new PngWriter();
            // Crear QR Code con configuraciones personalizadas
            $qrCode = new QrCode(
                data: $url_qr,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 300,  // Tamaño del QR
                margin: 10, // Margen
                roundBlockSizeMode: RoundBlockSizeMode::Margin, // Estilo de bloque redondeado
                foregroundColor: new Color(0, 0, 0),  // Color del QR
                backgroundColor: new Color(255, 255, 255) // Color de fondo
            );
            $qrImage = 'data:image/png;base64,' . base64_encode($writer->write($qrCode)->getString());
            // Crear el logo
            $logo = new Logo(
                path: __DIR__.'/assets/logo.png', // Asegúrate de poner la ruta correcta de tu logo
                resizeToWidth: 50, // Tamaño del logo
                punchoutBackground: true // Quitar el fondo del logo
            );
            // Crear el label
            $label = new Label(
                text: 'Factura QR',
                textColor: new Color(255, 0, 0), // Color del texto
            );

            // Combinar el QR con el logo y el label
            $result = $writer->write($qrCode, $logo, $label);
            
            // Obtener la URL de la imagen del QR como Data URI
            $dataUri = $result->getDataUri();
            
            $tipoFactura = "A";
            
            if ($invoice_type === 6) {
                $tipoFactura = "B";
            } elseif ($invoice_type === 11) {
                $tipoFactura = "C";
            } elseif ($invoice_type === 3) {
                $tipoFactura = "A";
            } elseif ($invoice_type === 8) {
                $tipoFactura = "B";
            } elseif ($invoice_type === 13) {
                $tipoFactura = "C";
            } elseif ($invoice_type === 4) {
                $tipoFactura = "A";
            } elseif ($invoice_type === 9) {
                $tipoFactura = "B";
            } elseif ($invoice_type === 15) {
                $tipoFactura = "C";
            }
            
            Log::info('Crear HTML de factura');
            // Crear HTML de factura
            $html = view('sell.ticket', [
                'invoice_number' => $invoice_number,
                'transaction_date' => $data['transaction_date'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'payment_due_date' => $data['payment_due_date'],
                'buyer_document_number' => $data['buyer_document_number'],
                'ImpIVA' => $ImpIVA,
                'ImpNeto' => $ImpNeto,
                'denomination_total_amount' => $ImpTotal,
                'cae' => $cae,
                'cae_due_date' => $cae_due_date,
                'qrImage' => $qrImage,
                'invoice_type' => $tipoFactura,
                'recibo' => $invoice_type,
                'business_name' => $business_name,
                'products' => $products,
                'cuitEmpresa' => $cuitEmpresa,
                'direccion' => $direccion,
                'fechaInicio' => $fechaInicio,
                'numFact' => $numero_de_factura,
                'logoEmpresa' => $logoEmpresa,
                'iva_receptor' => $iva_receptor,
                'valIva' => $valIva,
                'locacion_comercial' => $locacion_comercial,
                'customer_cuit' => $customer_cuit,
                'customer_name' => $customer_name,
                'customer_address' => $customer_address,
                'payment_condition' => $payment_condition,
                'PtoVta' => $pto_vta, 
            ])->render();
            Log::info('Iniciando la creación del HTML para la factura.', [
                'invoice_number' => $invoice_number,
                'transaction_date' => $data['transaction_date'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'payment_due_date' => $data['payment_due_date'],
                'buyer_document_number' => $data['buyer_document_number'],
                'ImpIVA' => $ImpIVA,
                'ImpNeto' => $ImpNeto,
                'denomination_total_amount' => $ImpTotal,
                'cae' => $cae,
                'cae_due_date' => $cae_due_date,
                'invoice_type' => $tipoFactura,
                'business_name' => $business_name,
                'products' => count($products),
                'cuitEmpresa' => $cuitEmpresa,
                'direccion' => $direccion,
                'fechaInicio' => $fechaInicio,
                'numFact' => $numero_de_factura,
                'logoEmpresa' => $logoEmpresa,
                'customer_cuit' => $customer_cuit,
                'customer_name' => $customer_name,
                'customer_address' => $customer_address,
                'payment_condition' => $payment_condition,
                'PtoVta' => $pto_vta, 
            ]);
            
            // Configurar Dompdf
            /*
            $options = new Options();
            $options->set('isRemoteEnabled', true); // Permitir cargar imágenes externas, si es necesario
            $mpdf = new Mpdf($options);

            // Cargar el contenido HTML en Dompdf
            $mpdf->loadHtml($html);

            // Configurar el tamaño del papel y la orientación
            $mpdf->setPaper('A4', 'portrait'); // A4 y orientación vertical

            // Renderizar el PDF
            $mpdf->render();
            
            // Guardar el archivo temporalmente
            //$pdfFile = tempnam(sys_get_temp_dir(), 'factura') . '.pdf';
            $directory = storage_path('app/public/facturas_afip/');
            $nombre_empresa = Str::slug($business_name, '_'); 
            $filename = "FACTURA_{$nombre_empresa}_{$numero_de_factura}.pdf";
            Storage::disk('public')->put("facturas_afip/{$filename}", $mpdf->output());
            $pdfFile = $directory . $filename;
            
            file_put_contents($pdfFile, $mpdf->output());
            */
            
            $directory = storage_path('app/public/facturas_afip/');
            $nombre_empresa = Str::slug($business_name, '_');
            $filename = "FACTURA_{$nombre_empresa}_{$numero_de_factura}.pdf";
            $pdfPath = $directory . $filename;
            
            Browsershot::html($html)
                ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                ->setOption('executablePath', '/usr/bin/chromium') // o donde esté instalado
                ->format('A4')
                ->save($pdfPath);

            
            // (Opcional) Actualiza la base de datos con la ruta si lo necesitas
            $businessId = auth()->user()->business_id;
            if ($businessId) {
                $updated = DB::table('business')
                    ->where('id', $businessId)
                    ->update(['facturaTemp' => $pdfPath]);
            
                if (!$updated) {
                    Log::error("No se pudo actualizar la columna facturaTemp para el business con ID $businessId.");
                }
            }
            
            // Descargar el archivo PDF generado
            if (file_exists($pdfPath)) {
                return response()->download($pdfPath);
            }
            
            $businessId = auth()->user()->business_id; // O la forma que se ajuste a tu lógica
            if ($businessId) {
                $updated = DB::table('business')
                    ->where('id', $businessId)
                    ->update(['facturaTemp' => $pdfFile]);
            
                if (!$updated) {
                    Log::error("No se pudo actualizar la columna facturaTemp para el business con ID $businessId.");
                }
            }
            
            if (file_exists($pdfFile)) {
                //return response()->download($pdfFile, "factura_{$numero_de_factura}.pdf");
                return response()->download(storage_path('app/public/facturas_afip/' . $filename));
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Hubo un error al generar la factura: ' . $e->getMessage(),
            ], 400); // Código 400 en caso de error
        }
    }
    
    //ESTE ES EL NUEVO 
    public function procesarCargaMasiva(Request $request)
    {
        // Obtiene los datos enviados en la solicitud
        $data = $request->input('preview_data');
        Log::info('DATOS RECIBIDOS EN preview_data:', $data);
        
        // Ignorar los primeros dos objetos (cabeceras) y quedarte con el resto de los datos
        $datosSinCabeceras = array_slice($data, 2);
        
        // Ordenar los datos (esto los ordenará por el valor de la columna 0 de cada fila)
        usort($datosSinCabeceras, function($a, $b) {
            return $a['column_0'] <=> $b['column_0'];
        });
        
        // Crear un array para almacenar las facturas procesadas
        $facturas = [];
        
        // Variables adicionales
        $currentDate = now(); // Fecha actual
        $businessName = session('business.name'); // Nombre de la empresa
        $businessCuit = session('business.tax_number_1'); // CUIT
        $businessAddress = session('business.tax_number_2'); // Dirección
        $businessStartDate = session('business.start_date'); // Fecha de inicio
        $businessLogo = session('business.logo'); // Logo de la empresa
        
        // Recorrer cada fila y transformarla en el formato adecuado para el controlador
        foreach ($datosSinCabeceras as $index => $row) {
            switch (strtoupper($row['column_15'])) { // Convertir a mayúsculas por si acaso
                case 'A':
                    $invoiceType = 1;
                    break;
                case 'B':
                    $invoiceType = 6;
                    break;
                case 'C':
                    $invoiceType = 11;
                    break;
                default:
                    $invoiceType = null; // Valor por defecto si no coincide
                    Log::warning("Valor desconocido para column_15: {$row['column_15']}");
                    break;
            }
            $invoiceConceptMap = [
                'productos' => 1,
                'servicios' => 2,
                'productos y servicios' => 3,
            ];
            
            $invoiceConcept = $invoiceConceptMap[strtolower($row['column_16'])] ?? null; // Valor por defecto si no coincide
            if (is_null($invoiceConcept)) {
                Log::warning("Valor desconocido para column_16: {$row['column_16']}");
            }
            
            if ($invoiceConcept !== 1) {
                $startDate = Carbon::parse($row['column_19'])->format('Y-m-d');
                $endDate = Carbon::parse($row['column_20'])->format('Y-m-d');
                $paymentDueDate = Carbon::parse($row['column_21'])->format('Y-m-d');
            } else {
                // Si el concepto es "productos", no es necesario modificar las fechas
                $startDate = null;
                $endDate = null;
                $paymentDueDate = null;
            }
            
            // Transformar buyer_document_type según la lógica
            $buyerDocumentType = (strtolower($row['column_17']) === 'dni') ? 96 : 80;

            // Preparar el array de productos
            $productos = [
                [
                    'product_name' => $row['column_6'], // Nombre del producto
                    'quantity' => $row['column_8'], // Cantidad
                    'unit_price' => $row['column_10'], // Precio unitario
                    'discount' => $row['column_12'], // Descuento
                    'subtotal' => $row['column_8'] * $row['column_10'], // Subtotal
                ]
            ];
    
            // Convertir el valor de transaction_date al formato 'Ymd' (20250122)
            $transactionDate = Carbon::parse($row['column_5'])->format('Ymd');
        
            // Transformar el valor de invoice_iva a los valores correspondientes
            switch ($row['column_11']) {
                case 'IVA21':
                    $invoiceIva = 'iva_21';
                    break;
                case 'IVA27':
                    $invoiceIva = 'iva_27';
                    break;
                case 'IVA10.5':
                    $invoiceIva = 'iva_10';
                    break;
                case 'excento':
                    $invoiceIva = 'excento_iva';
                    break;
                default:
                    $invoiceIva = $row['column_11']; // Si no se encuentra, lo dejamos tal cual
                    break;
            }
    
            // Crear el array para la factura
            $factura = [
                'invoice_type' => $invoiceType, // Este valor puede variar según lo que necesites
                'invoice_concept' => $invoiceConcept, // Igualmente, ajusta según tu lógica
                'buyer_document_type' => $buyerDocumentType, // Tipo de documento (ejemplo, 80 para CUIT)
                'buyer_document_number' => $row['column_18'], // Número de documento
                'transaction_date' => $transactionDate, // Fecha de la transacción transformada
                'denomination_total_amount' => $row['column_8'] * $row['column_10'], // Monto total
                'start_date' => $startDate, // Fecha de inicio
                'end_date' => $endDate, // Fecha de fin
                'payment_due_date' => $paymentDueDate, // Fecha de vencimiento
                'business_name' => $businessName, // Nombre de la empresa
                'products' => $productos, // Productos
                'cuitEmpresa' => $businessCuit, // CUIT de la empresa
                'direccion' => $businessAddress, // Dirección de la empresa
                'fechaInicio' => $businessStartDate, // Fecha de inicio de la empresa
                'invoice_iva' => $invoiceIva, // IVA transformado
                'tipoTransferencia' => 'Transferencia', // Tipo de transferencia
                'cbu_cliente_val' => null, // CBU del cliente (si lo tienes)
                'num_factura_val' => null, // Número de factura
                'logoEmpresa' => $businessLogo, // Logo de la empresa
            ];
            // Log de la factura transformada para verificar los datos
            Log::info('Factura transformada: ', $factura);
        
            // Agregar la factura transformada al arreglo
            $facturas[] = $factura;
        }
    
        // Log del proceso de las facturas
        Log::info('Archivo procesado completamente.');
    
        // Procesar las facturas
        $pdfFiles = [];
        foreach ($facturas as $facturaData) {
            Log::info('Procesando factura: ', $facturaData);
            try {
                // Crear la factura y obtener la respuesta con el archivo PDF
                $response = $this->crearFacturaInt(new Request($facturaData));
                
                // Verificar si la respuesta contiene un archivo
                if ($response) {
                    $facturaPDFPath = $response->getFile()->getPathname(); // Obtener la ruta del archivo generado
                    if (file_exists($facturaPDFPath)) {
                        $pdfFiles[] = $facturaPDFPath;
                        Log::info('Factura creada con éxito: ' . $facturaPDFPath);
                    } else {
                        Log::error('Archivo PDF no encontrado: ' . $facturaPDFPath);
                    }
                } else {
                    Log::warning('La respuesta no contiene un archivo válido.');
                }
            } catch (\Exception $e) {
                Log::error('Error al crear factura: ' . $e->getMessage(), $facturaData);
            }
        }
    
        // Crear un archivo ZIP con todas las facturas
        $zipFile = storage_path('app/facturas.zip');
        $zip = new ZipArchive;
            
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($pdfFiles as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file)); // Agregar el archivo al ZIP
                    Log::info("Se encontro el archivo: {$file}");
                } else {
                    Log::error("Archivo no encontrado: {$file}");
                }
            }
            $zip->close();
            Log::info('Archivo ZIP creado exitosamente.');
        } else {
            Log::error('No se pudo crear el archivo ZIP.');
            return back()->with('error', 'No se pudo crear el archivo ZIP.');
        }
            

        if (file_exists($zipFile)) {
            Log::info('Archivo ZIP encontrado: ' . $zipFile);
            return response()->download($zipFile, 'facturas.zip')->deleteFileAfterSend(true);
        } else {
            Log::error('Archivo ZIP no encontrado: ' . $zipFile);
            return back()->with('error', 'No se pudo encontrar el archivo ZIP.');
        };
    }
    
    // Función para mapear tipo de factura
    private function mapFacturaType($tipo)
    {
        $tipos = [
            'A' => 1,
            'B' => 6,
            'C' => 11,
            'Nota de credito A' => 3,
            'Nota de credito B' => 8,
            'Nota de credito C' => 13
        ];
        $resultado = $tipos[$tipo] ?? 0;
        Log::info("Mapeo de tipo de factura: {$tipo} => {$resultado}");
        return $resultado;
    }
    
    // Función para mapear conceptos
    private function mapConcepto($concepto)
    {
        $conceptos = [
            'Productos' => 1,
            'Servicios' => 2,
            'Productos y Servicios' => 3
        ];
        $resultado = $conceptos[$concepto] ?? 0;
        Log::info("Mapeo de concepto: {$concepto} => {$resultado}");
        return $resultado;
    }
    
    // Función para mapear tipo de documento
    private function mapTipoDoc($tipo)
    {
        $tipos = [
            'CUIT' => 80,
            'CUIL' => 86,
            'DNI' => 96,
            'Consumidor Final' => 9
        ];
        $resultado = $tipos[$tipo] ?? 0;
        Log::info("Mapeo de tipo de documento: {$tipo} => {$resultado}");
        return $resultado;
    }
    
    // Función para formatear fechas
    private function formatDate($fecha)
    {
        if (empty($fecha)) {
            Log::info('Fecha vacía, se retorna null.');
            return null;
        }
        $date = DateTime::createFromFormat('d/m/Y', $fecha);
        $resultado = $date ? $date->format('Y-m-d') : null;
        Log::info("Formateo de fecha: {$fecha} => {$resultado}");
        return $resultado;
    }
        
      /**
     * Devuelve el link público para ver la factura AFIP.
     * Esta función puede ser invocada vía AJAX cuando se hace clic en el botón.
     */
    public function generarEnlaceFactura($transaction_id)
    {
        $transaction = Transaction::findOrFail($transaction_id);
        
        if ($transaction->business_id !== Auth::user()->business_id) {
            abort(403, 'Acceso no autorizado');
        }
    
        // Buscar el número de WhatsApp del contacto asociado
        $numero_whatsapp = null;
        if ($transaction->contact_id) {
            $contact = Contact::find($transaction->contact_id);
            if ($contact) {
                $numero_whatsapp = $contact->mobile;
            }
        }
    
        // Remover la parte absoluta de la ruta del servidor
        $facturaPath = str_replace('/www/wwwroot/app.tutifactura.com/storage/app/public/', '', $transaction->factura_afip);
        // Generar la URL pública correcta
        $enlace = asset('storage/' . $facturaPath);
    
        return view('sell.partials.enviar_whatsapp')
               ->with('enlace', $enlace)
               ->with('transaction_id', $transaction_id)
               ->with('numero_whatsapp', $numero_whatsapp);
    }



    public function procesarCargaMasivaMercadoLibre(Request $request)
    {
        $orders = $request->input('orders');
        Log::info('Órdenes recibidas desde MercadoLibre:', $orders);
        
        $facturas = [];
        $pdfFiles = [];
    
        // Datos del negocio desde sesión
        $businessName = session('business.name');
        $businessCuit = session('business.tax_number_1');
        $businessAddress = session('business.tax_number_2');
        $businessStartDate = session('business.start_date');
        $businessLogo = session('business.logo');
            
        $cuitEmpresa = $request->input('cuit_empresa');
        Log::info('CUIT recibido desde el frontend:', ['cuitEmpresa' => $cuitEmpresa]);

        foreach ($orders as $order) {
            
            //$invoiceType = $this->determinarTipoFacturaAfip($businessCuit, $order['taxpayer_type']);
            $invoiceType = 6;
            $docTypeMap = [
                'DNI' => 96,
                'CUIT' => 80,
                'CUIL' => 86,
                'CDI' => 87
            ];
            $ivaReceptorMap = [
                'IVA Responsable Inscripto' => 1,
                'Monotributo' => 6,
                'Monotributista Social' => 13,
                'Monotributo Trabajador Independiente Promovido' => 16,
                'IVA Exento' => 4,
                'Consumidor Final' => 5,
                'Sujeto No Categorizado' => 7,
                'Proveedor del exterior' => 8,
                'Cliente del exterior' => 9,
                'IVA Liberado – Ley N° 19.640' => 10,
                'IVA No Alcanzado' => 15
            ];

        
            $docType = $docTypeMap[$order['identification']['type'] ?? 'DNI'] ?? 96;
            $docNumber = $order['identification']['number'] ?? 0;
            $ivaReceptor = $ivaReceptorMap[$order['taxpayer_type'] ?? 'Consumidor Final'] ?? 5;
            
            if (empty($docNumber) || $docNumber == 0) {
                $docType = 99; // Consumidor Final
                $docNumber = 0;
            }
            
            $cert = "-----BEGIN CERTIFICATE-----
        MIIDQjCCAiqgAwIBAgIIQH35DDMS3v0wDQYJKoZIhvcNAQENBQAwMzEVMBMGA1UEAwwMQ29tcHV0
        YWRvcmVzMQ0wCwYDVQQKDARBRklQMQswCQYDVQQGEwJBUjAeFw0yNTAxMDkwNjExNDVaFw0yNzAx
        MDkwNjExNDVaMC0xEDAOBgNVBAMMB0NTUnByb2QxGTAXBgNVBAUTEENVSVQgMjA0NDcwMDk4NzEw
        ggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCwbtCSudQ9aGxyVxkD80x3/BBE50gsl0aH
        S7Pa2h/LQ1WhihhgLtasgU4WgO0n3DCFUhlKGImOAW/dZol5XnMK3q+YyVZHI27YSL+TYHhaUrNl
        59Ay/zKWdcPtMzCeRJIqfmKcSdTrbF2LCXMv8y0PyBO1fV2vQGpwSs8qJ0Q9iNPU3+fYhtYTuyED
        NsgNvrC9BEaoYleVoXggk7Z2PycqDORu0DTSPmANK+Smqr6kmZRzbKUBMKmAqnDIB6TPU8PX9V6l
        NVtfB6OdK0+VU4cPrWw5nOV1pFU/iZpWeAHFLooFPThYQpj2D+/aAiuKLx/l1m3t/NN8KQ12m92R
        6oxhAgMBAAGjYDBeMAwGA1UdEwEB/wQCMAAwHwYDVR0jBBgwFoAUKw0vyN9h/QjJThHQNZMEbY5b
        0G4wHQYDVR0OBBYEFNTuhFbTPais55fj4/nx6O+QMQrMMA4GA1UdDwEB/wQEAwIF4DANBgkqhkiG
        9w0BAQ0FAAOCAQEAdnQj+lRp5qV+uhJYFcI1wrM8ofWJPnBc3uglKTRJSCdRIivzvHQ2VHmfaa0r
        p8Tp6rRwwPz1/YHCub7A3tSzOrgfYsVxi/kSBWDnLtjwLNCL7KfFIZGYl26f1d++3jhphPBW2USp
        /d0viHKauW2NXsq4U7RXzM22iNSIt/xG22SzxAlpR7J5OpNCudkUxB5H5jPJdnTfM4ynVt8CHGQr
        5eK0HGu/jFBa2/mOx7/GPcc7NjuK8Uie8hyb2S1wtFRS23+fptSshKPigq/IH6JVC6K/9C8o33EK
        6ZdtkWL2jAtRNXRTHBmYF55pftKIVcxjzQ6xgB8vm/nm7FojhLWaTw==
        -----END CERTIFICATE-----";
            $key = "-----BEGIN RSA PRIVATE KEY-----
        MIIEogIBAAKCAQEAsG7QkrnUPWhsclcZA/NMd/wQROdILJdGh0uz2tofy0NVoYoY
        YC7WrIFOFoDtJ9wwhVIZShiJjgFv3WaJeV5zCt6vmMlWRyNu2Ei/k2B4WlKzZefQ
        Mv8ylnXD7TMwnkSSKn5inEnU62xdiwlzL/MtD8gTtX1dr0BqcErPKidEPYjT1N/n
        2IbWE7shAzbIDb6wvQRGqGJXlaF4IJO2dj8nKgzkbtA00j5gDSvkpqq+pJmUc2yl
        ATCpgKpwyAekz1PD1/VepTVbXwejnStPlVOHD61sOZzldaRVP4maVngBxS6KBT04
        WEKY9g/v2gIrii8f5dZt7fzTfCkNdpvdkeqMYQIDAQABAoIBAEqes6w62B8YuTUq
        zTO0hRUCEMdzojzpxjSWufzH4EaRtffadjDH3pebILG32ZXQNN1OOc/yzMq4pN1Y
        7q/cWH+ya0YNcWYpAEqNiikE1iItdB6XiqDm+jhUmocJ7XgivEcoqQA84caoUG/r
        kUIocViJYHyUBzmF3v6bRT8o2F3FeojiIifSjqhUwC9ir7WH4w59D/EKpIxDRhFx
        xGHDOf0HJOWDzP8fvNqlTnWQe08+3giiSFAGbp9OvyolMgsaq2m73044Wr8d7KkY
        xR+dk+ayEKv/q/G/1KsWGDnf4r7QmDGzlHyOqxwFUXGiZyoOvnndHt8cuyIlPaQY
        sN07ziECgYEA6Ch/Ar5q7XSv13FQ/yx4S/OHU5KHhlXFeAVuwZWUQ9lBASEuQqTx
        qlIL2CrWmTepXZuKz4aLebwNx2aPy6mXRUoTyXymMOiWXJo01HIiNmWxKO0vQQ1w
        AHM8HZ4AiOA38gTBcBlJRyDZMwBa0BxyM5ZDZFWb2tePLirI6VewymUCgYEAwo1I
        zFTvWbJ2nzjfyivGeK172xI13lGvrwInUpIXoinUPOKZ0E9H6E2WErNdU+vknuF8
        C7pPfXsn14iNEg5c6q8jDi87DhhN4ze96yDNpV94fcLUPB7ykWU5J/jzVoyGz9bZ
        rCF2vvUlxGC9M6xW74TxZyJPn7+yFzdfbD02PE0CgYAbT9hRXFF8CgQ91kjvHC4y
        0T2UaWKkPSb0Db+P6qb8rGxnU537nSgJH4nhLnj8oX29U3UrIOr7sVH1qW01SohP
        UH2vcIikrFg0ILgiXiMEP0ZuSCkNACWzB9uEqHfromUyhbi0FWV3hrfGtj34yb/t
        rbnT7fZd8ooXplTojrZvuQKBgDngHc/rVGiylmc3XBugeiJcOYQqHqxMUJ8XXOtL
        Wox7NRAHuQonC5Unv4VApnrYqeI54dT7T7pccLXFgXHlo+6Muzj1A/RldoYphu3z
        jDmt9QI0ez6HVlG6cw5ZGMqec1xQ5eA29ERKWTnaLW65zgLi4Y10v4seU7RkXJau
        fvvhAoGAIgDy0LPFHlqaCJoWOrCinmqpnpKdGN9rQgKH/zxI6Vf2UuwF48Ks/War
        ODARi9KODQYIAV5EDrIcdgNt/K4kkQHTnTalW+VuLyIBgQngNWDTHA9pYG/gyP3O
        BGLV/CmnU4U5TkdNw0PpAm2BhuzhDy/NC8/nOdUEjShtAxnl9o0=
        -----END RSA PRIVATE KEY-----";
            
            $afip = new Afip(array(
                'CUIT' => 20447009871,
                'cert' => $cert,  // Certificado en formato de cadena
                'key'  => $key,   // Clave privada en formato de cadena
                'access_token' => 'gi72RbXfkm4DcfnmLSAOl1xnS1DvAMZfP6uFwQciVerrDlI3uJSEkgYU090AoGIw'
            ));
            // Consulta a taxpayer_details para saber si la empresa es monotributo y no responsable inscripto
            try {
                $taxpayerDetails = $afip->RegisterInscriptionProof->GetTaxpayerDetails($cuitEmpresa);
            
                // Buscar la descripción del impuesto
                $descripcionImpuesto = null;
            
                if (isset($taxpayerDetails->datosRegimenGeneral->impuesto)) {
                    if (is_array($taxpayerDetails->datosRegimenGeneral->impuesto)) {
                        $descripcionImpuesto = $taxpayerDetails->datosRegimenGeneral->impuesto[0]->descripcionImpuesto ?? null;
                    } else {
                        $descripcionImpuesto = $taxpayerDetails->datosRegimenGeneral->impuesto->descripcionImpuesto ?? null;
                    }
                } elseif (isset($taxpayerDetails->datosMonotributo->impuesto)) {
                    if (is_array($taxpayerDetails->datosMonotributo->impuesto)) {
                        $descripcionImpuesto = $taxpayerDetails->datosMonotributo->impuesto[0]->descripcionImpuesto ?? null;
                    } else {
                        $descripcionImpuesto = $taxpayerDetails->datosMonotributo->impuesto->descripcionImpuesto ?? null;
                    }
                }
            
                Log::info('Consulta directa AFIP:', [
                    'cuit' => $cuitEmpresa,
                    'descripcionImpuestoEmpresa' => $descripcionImpuesto,
                    'datos' => $taxpayerDetails
                ]);
            } catch (\Throwable $e) {
                Log::error('Error al consultar AFIP directamente:', [
                    'cuit' => $cuitEmpresa,
                    'error' => $e->getMessage()
                ]);
                $descripcionImpuesto = null;
            }


            
            if ($descripcionImpuesto === 'MONOTRIBUTO' || $descripcionImpuesto === 'REG. SIMPLIFICADO IIBB ARBA') {
                $invoiceType = 11; // Factura C
            } 
            else {
                switch ($ivaReceptor) {
                    case 1: // IVA Responsable Inscripto
                        $invoiceType = 1; // Factura A
                        break;
                
                    case 4: // IVA Exento
                        $invoiceType = 11; // Factura C
                        break;
                
                    case 6: // Monotributo
                    default:
                        $invoiceType = 6; // Factura B
                        break;
                }
            }
            
            
            $productos = [
                [
                    'product_name' => $order['product'],
                    'quantity' => $order['quantity'],
                    'unit_price' => $order['unit_price'],
                    'discount' => 0,
                    'subtotal' => $order['quantity'] * $order['unit_price'],
                ]
            ];
        
            $facturas[] = [
                'invoice_type' => $invoiceType,
                'invoice_concept' => 1, // Siempre es 1 porque es productos
                'buyer_document_type' => $docType,
                'buyer_document_number' => $docNumber,
                'transaction_date' => now()->format('Ymd'),
                'denomination_total_amount' => $order['quantity'] * $order['unit_price'],
                'start_date' => null,
                'end_date' => null,
                'payment_due_date' => null,
                'business_name' => $businessName,
                'products' => $productos,
                'cuitEmpresa' => $businessCuit,
                'direccion' => $businessAddress,
                'fechaInicio' => $businessStartDate,
                'invoice_iva' => $order['invoice_iva'] ?? 'iva_21',
                'tipoTransferencia' => 'Transferencia',
                'cbu_cliente_val' => null,
                'num_factura_val' => null,
                'logoEmpresa' => $businessLogo,
                'iva_receptor' => $ivaReceptor,
                'locacion_comercial' => 'Sucursal Central',
                'pto_vta' => $order['pto_vta'] ?? 1,
            ];
        }

    
        foreach ($facturas as $facturaData) {
            
            try {
                $response = $this->crearFacturaInt(new Request($facturaData));
                if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
                    $facturaPDFPath = $response->getFile()->getPathname();
                    if (file_exists($facturaPDFPath)) {
                        $pdfFiles[] = $facturaPDFPath;
                    }
                } else {
                    Log::error('La respuesta de crearFacturaInt no es un archivo. Respuesta:', [
                        'type' => get_class($response),
                        'content' => method_exists($response, 'getContent') ? $response->getContent() : 'N/A'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error al crear factura ML: ' . $e->getMessage(), $facturaData);
            }
        }
        
        $accessToken = auth()->user()->business->mercadolibre_access_token;

        foreach ($orders as $index => $order) {
            if (!isset($order['order_id']) || !isset($pdfFiles[$index])) {
                continue;
            }
        
            $orderId = $order['order_id'];
            $pdfPath = $pdfFiles[$index];
        
            // Obtener pack_id desde la orden
            $orderInfo = Http::withToken($accessToken)
                ->get("https://api.mercadolibre.com/orders/{$orderId}");
        
            if (!$orderInfo->successful()) {
                Log::warning("No se pudo obtener info de orden $orderId", ['status' => $orderInfo->status()]);
                continue;
            }
        
            $packId = $orderInfo->json('pack_id') ?? $orderId;
        
            // Subir el archivo PDF
            if (file_exists($pdfPath)) {
                $upload = Http::withToken($accessToken)
                    ->attach('fiscal_document', file_get_contents($pdfPath), basename($pdfPath))
                    ->post("https://api.mercadolibre.com/packs/{$packId}/fiscal_documents");
        
                if ($upload->successful()) {
                    Log::info("Factura subida a MercadoLibre para orden $orderId", ['response' => $upload->json()]);
                } else {
                    Log::warning("Error al subir factura para orden $orderId", [
                        'status' => $upload->status(),
                        'body' => $upload->body()
                    ]);
                }
            } else {
                Log::warning("No se encontró el PDF para orden $orderId en $pdfPath");
            }
        
            // 🔁 Renovar token después de usarlo (embebido internamente)
            $refreshToken = auth()->user()->business->mercadolibre_refresh_token;
            
            try {
                Log::info("Renovando token de MercadoLibre directamente desde la función.");
                
                $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
                    'grant_type' => 'refresh_token',
                    'client_id' => env('MERCADOLIBRE_CLIENT_ID'),
                    'client_secret' => env('MERCADOLIBRE_CLIENT_SECRET'),
                    'refresh_token' => $refreshToken
                ]);
            
                $tokenData = $response->json();
            
                if (!$response->ok() || !isset($tokenData['access_token'])) {
                    Log::error("Error al renovar token: no se recibió access_token", ['response' => $tokenData]);
                    break;
                }
            
                // Obtener seller_id
                $userResponse = Http::withToken($tokenData['access_token'])->get('https://api.mercadolibre.com/users/me');
                $userData = $userResponse->json();
            
                // Actualizar en base de datos
                $business = auth()->user()->business;
                $business->update([
                    'mercadolibre_access_token' => $tokenData['access_token'],
                    'mercadolibre_refresh_token' => $tokenData['refresh_token'],
                    'mercadolibre_token_expires' => now()->addSeconds($tokenData['expires_in']),
                    'mercadolibre_seller_id' => $userData['id'] ?? null
                ]);
            
                $accessToken = $tokenData['access_token'];
                Log::info("Access token renovado correctamente.");
            
            } catch (\Exception $e) {
                Log::error("Excepción al renovar token directamente: " . $e->getMessage());
                break;
            }

        }

    
        // Crear ZIP con los PDFs
        $zipFile = storage_path('app/facturas_mercadolibre.zip');
        $zip = new \ZipArchive;
    
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            foreach ($pdfFiles as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
        }
    
        if (file_exists($zipFile)) {
            return response()->download($zipFile, 'facturas_mercadolibre.zip')->deleteFileAfterSend(true);
        } else {
            return response()->json(['error' => 'No se pudo generar el archivo ZIP.'], 500);
        }
    }
    
    
    
    private function determinarTipoFacturaAfip($cuitEmisor, $condicionReceptor)
    {
        Log::info("Obteniendo tipo de factura AFIP. CUIT emisor: {$cuitEmisor}, condición receptor: {$condicionReceptor}");
    
        $response = Http::post(url('/get-taxpayer-details'), [
            'tax_id' => $cuitEmisor,
        ]);

        
        Log::info('Llamada a API AFIP para obtener datos del emisor', [
            'url' => 'https://app.tutifactura.com/get-taxpayer-details',
            'params' => ['tax_id' => $cuitEmisor],
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        
        if ($response->failed()) {
            Log::error("Error al obtener datos del emisor desde API de AFIP.");
            return 6; // Por defecto, factura B
        }
    
        $datosEmisor = $response->json();
        Log::info('Respuesta completa del emisor desde AFIP:', $datosEmisor);
    
        $condicionEmisor = $datosEmisor['taxpayer_type']['description'] ?? null;
        Log::info("Condición fiscal del emisor: {$condicionEmisor}");
    
        $map = [
            'Responsable Inscripto' => [
                'IVA Responsable Inscripto' => 1, // A
                'Monotributista' => 6,           // B
                'Consumidor Final' => 6,         // B
                'Exento' => 6                    // B
            ],
            'Monotributista' => [
                'default' => 11                  // C
            ],
            'Exento' => [
                'default' => 11                  // C
            ]
        ];
    
        if (isset($map[$condicionEmisor])) {
            $resultado = $map[$condicionEmisor][$condicionReceptor] ?? ($map[$condicionEmisor]['default'] ?? 6);
            Log::info("Tipo de factura determinado según mapeo: {$resultado}");
            return $resultado;
        }
    
        Log::warning("Condición del emisor '{$condicionEmisor}' no está en el mapa. Se devuelve 6 por defecto.");
        return 6; // fallback B
    }
    
    public function obtenerTiposImpuestosAfip(Request $request)
    {
        try {
    
            //CERTIFICADOS DE TESTING
        /*$cert ="-----BEGIN CERTIFICATE-----
MIIDSDCCAjCgAwIBAgIIA+PIYhIXNqAwDQYJKoZIhvcNAQENBQAwODEaMBgGA1UEAwwRQ29tcHV0
YWRvcmVzIFRlc3QxDTALBgNVBAoMBEFGSVAxCzAJBgNVBAYTAkFSMB4XDTI0MTEyNjA2MTkxNVoX
DTI2MTEyNjA2MTkxNVowLjERMA8GA1UEAwwIY2VydFdzZmUxGTAXBgNVBAUTEENVSVQgMjA0MzAx
NjYwMzUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDUH13nAWrQGcgOS6GURZT8szAL
Mf4B+sQ4oLYA0jWHpuAFKRXU6ExjK/DKJ0KYIppUlpH2NVrIoJi3IhFI74q6xNu+HfFhicZZVz76
Xukpm43uidsm9Hs86LicKA64ZWmUNJvoMCdOng5g077r4WwZRb2U0l9t/Wsvu4GEGNiF/m/Oykj7
mnRFcKOZbOMuez4cHe6It5DgKUQuNVBqvgpplwYHO98gmmMbX/lXAd+7eBukl9JfLkii1umjcrM2
OJcjpWhguqJCaODgeACnr5VDPdb7lfR+dGJOPNRmrulXKsWlv9Fj8DszrqPBLr4x8WICJOr5c8ie
KL8CW8FLBmG7AgMBAAGjYDBeMAwGA1UdEwEB/wQCMAAwHwYDVR0jBBgwFoAUs7LT//3put7eja8R
IZzWIH3yT28wHQYDVR0OBBYEFN4UnQPqhhlHE93fmxGuuI4mX6AJMA4GA1UdDwEB/wQEAwIF4DAN
BgkqhkiG9w0BAQ0FAAOCAQEAbILLDNvGLnLFDBBHimisE8wJoVz/BIHnX35AWH+46zURuwnkLDZL
OJyaE94BVqeDqspfnbgOg56oEeMUDqJty1YcwWzYPsiTr7tkwnzJpjTDoUkLA0zqDnyJB96FTwMj
WcxrmIe4AFG0MMjUR7uHDg8OCzg+ZWai5WXlVJjJpX5NMR/7Pm/iKdhbKZlvbUKZdYIUt1EzxxGL
Ofg0F/y25gIJPdA5RbND8rrP6REZjWqLpiAOhtjNPP6TCaE2Se8T1dbIqWIZ9uQ0bEGwvAhYzvby
Qpa/GCQUUbF00NpDs9/tbZrakL5a/apEe+7vFz6VYbpto9gfDWhWvFnqN8uDeQ==
-----END CERTIFICATE-----";
        $key = "-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA1B9d5wFq0BnIDkuhlEWU/LMwCzH+AfrEOKC2ANI1h6bgBSkV
1OhMYyvwyidCmCKaVJaR9jVayKCYtyIRSO+KusTbvh3xYYnGWVc++l7pKZuN7onb
JvR7POi4nCgOuGVplDSb6DAnTp4OYNO+6+FsGUW9lNJfbf1rL7uBhBjYhf5vzspI
+5p0RXCjmWzjLns+HB3uiLeQ4ClELjVQar4KaZcGBzvfIJpjG1/5VwHfu3gbpJfS
Xy5Iotbpo3KzNjiXI6VoYLqiQmjg4HgAp6+VQz3W+5X0fnRiTjzUZq7pVyrFpb/R
Y/A7M66jwS6+MfFiAiTq+XPInii/AlvBSwZhuwIDAQABAoIBADvPiFutc5+v1U/q
lWnIYOUL5V0SwItwWMmazxbWLs/MBtiNqCE7Suafqipl/YoGH7wAJLHmg22Uktr4
WSVWnahh/4/Qw5H8Fhh02EYiYt4fhVqgNlH6l5EqEXu+c8AcoDNwzhEfGsY5HNbC
fc/m5OMPXhBLbSsHTKTN2wwTMWI+Q4Bo36uB0kWNaFgrLwbEL+CGm+U2G1fOo3QP
IehtAajmnYBG6+jO3aeFJCHtiKxNwUq1LgLI+qY96bOisCOTWOub9I8DQ5iv0CyL
dPgIJXC1617ZdGY5O5/2od1tHPugzY63pw4SzqpQR1GxXZAuNJQMoVa1KcdHvL/t
Z49FNEkCgYEA6TH4cVKCYGsmQQtI3iZg+qqHLmNbvRunLUaRz37CkS+xq6ZWLKVh
SAhrQR7yVRmivxtDX/9pqnCpY+3bP5jKA+y8fUolycRyB1CT/NMaaqt2akG4ToNQ
X89MD9MSem/vILXhfi7MO9Zy/1EOHS46h1G6+dDQaUc3zTjtpUFpoKUCgYEA6N3X
1wSamFRDBC1BYvIWGTVKmwVX1Gfz6CAjpkbyLTaDB1sIBhPWSCl8EuAxMoaXZmvl
+3u5g+YdLQZr9+mwZntgOQ53N+NsDYyuiHi0Se5/UNeOcbAyrso3moClVdwOXkbP
fbND7wBfkdjPMbUZN8gSpTV1qav0D6Vacc85Ct8CgYEAld19cya5j0mNTiP4cnxr
uuy478D/BiutZtWBg75NQI1MO6osm4i1Wlu+wh0nVDWjd/oHdLxqphS9Z/FHBDon
KhqMkGCEpITRW44XbVYmFgOXmHYgAqU1lD1e/pSBvZoOLhF1l2hv7MzHHvpyfaJm
Du0hosbmCaKxY/yADcJaJdkCgYEAunul9RA/yYt5G6guO9HItqlBtMFjo7sXzaWn
Rup72I9WARb6ZvuN1745GVimrWKxbhksVOexGhq29K622hMv6/ITjb2y5XPfvT4T
K0EWiDpRhOkKrqq++9D/FGC/hvGyI/erBGwCFC0FW+P3kUQJDO3RWLJmJtmImtr9
gjTD5psCgYBR5febSu3JkLlkx+gKkJK5HeNN/u4eXuf0OBkPZHs0TRKAGEIpVOXW
GrnTajwiKF6DMUY0JQvUzA5ZWtgqXBg7v5LsCWIYMZp+tWeEb96R4iQlQHXISN8y
y3LUb5yOjH1vjeXfD85UFV9j3s0L8zEgjwjmFQ2y0+gHxsTkeZ+B0g==
-----END RSA PRIVATE KEY-----";*/

        //CERTTIFICADOS DE PRODU
        $cert = "-----BEGIN CERTIFICATE-----
MIIDRjCCAi6gAwIBAgIINv3QVdMufY4wDQYJKoZIhvcNAQENBQAwMzEVMBMGA1UEAwwMQ29tcHV0
YWRvcmVzMQ0wCwYDVQQKDARBRklQMQswCQYDVQQGEwJBUjAeFw0yNTA0MDcwNDE5MzZaFw0yNzA0
MDcwNDE5MzZaMDExFDASBgNVBAMMC2NzckFmaXBQcm9kMRkwFwYDVQQFExBDVUlUIDMwNzE4ODY2
Nzk3MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApg+x71ivDMyRhoEo5kkVOBoB6ef9
IJ4XMZhYB02r5b7512D5jtgxUyAAeFqvof/dQUW6w/UGzPts8B9EAWuTQtqUZgFSriZID1Vp7lXJ
HgOAYUYfSdoSMfWUlxht9QYWnK9m5JVKHR/elMXm5sn42JITuU2/3LP576caqfq9J3xgcVT0U0aO
fbz75BdaVGC+NJs5aDj1snzntZP4361v8sDrpsCg3tEolo1EiJ8g8mBAu2PQJqFxPwtNrvTbrxQ/
ncbHJRHgIcFunTBAMYL/8hU7vhK/HpKuTuXzHRgTNqq0kL7kz0VF6w3e8LiCfDkUp/dCXtkHE5Ut
Tr+HTXB/oQIDAQABo2AwXjAMBgNVHRMBAf8EAjAAMB8GA1UdIwQYMBaAFCsNL8jfYf0IyU4R0DWT
BG2OW9BuMB0GA1UdDgQWBBT6xzVWJIq44df7haRPQpRO5N4e6jAOBgNVHQ8BAf8EBAMCBeAwDQYJ
KoZIhvcNAQENBQADggEBAGrmGITFmIP7vsqCbKWM2aSXU1qeOyGq7v07iHPCWYvLuqeZuhUHHRtc
U028YVZHO/6wTXCRyfHIzMU2SPPVK+IiQRjhllyx9tiK6xvDQZCmynHG/0bhPw2oochc/K9pd9WR
LaxX+ZDPL40pReOh9d5T6CfLUQcb9a7T1/2D7KDeAxUKVg2HQ4Os0jcvKgfP25dmid3IvXXFpzLq
JI3lMdsacHkXj0BH8hQzK6J4O4Ls/kr+ZV9ep1nzVll+jD7Xjq9SbNjZoRvZ8K5LCxpIQxWVG/j5
N+QTz+pX33EeVQNxRFKGn/hkzWkkdpe+PZEiMxalKJGCyUIfOsPVBlh3XUI=
-----END CERTIFICATE-----";
        $key = "-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEApg+x71ivDMyRhoEo5kkVOBoB6ef9IJ4XMZhYB02r5b7512D5
jtgxUyAAeFqvof/dQUW6w/UGzPts8B9EAWuTQtqUZgFSriZID1Vp7lXJHgOAYUYf
SdoSMfWUlxht9QYWnK9m5JVKHR/elMXm5sn42JITuU2/3LP576caqfq9J3xgcVT0
U0aOfbz75BdaVGC+NJs5aDj1snzntZP4361v8sDrpsCg3tEolo1EiJ8g8mBAu2PQ
JqFxPwtNrvTbrxQ/ncbHJRHgIcFunTBAMYL/8hU7vhK/HpKuTuXzHRgTNqq0kL7k
z0VF6w3e8LiCfDkUp/dCXtkHE5UtTr+HTXB/oQIDAQABAoIBAQCev6RPdg0qBCoe
Vjz+IOF7F0m7DT6rvtXXYJbOrI7m+/Gl1xvbVYuZtp8cbz01df9S1dEcYZIVxUm4
J3uAjBFkOMbi2OLsfsYmenXhFPtFiP1igIGKvUDlp6pb40vCkAZv1TJc1Obb/spu
mN4KxQvyJXtbixseZqNJWgsgcgX/xtnHuYAa8jPI480rCgEZjfGpTGgV0O08m3Sy
LPlBFMOu6pjMut5Z/oZmpCRxcJk7twCcT0G4HCENT2YMgn5UBKAH2u/xQm6RQD9+
ZsALl6NZE3f6/7VjFm6JSMtChAHtuzIgFVExO6dg95V4AadrFWWLA6I0Fd2yXepd
NjQQXf8dAoGBAM8gqZ9CoJr2eiSb26XdN/woRLiq4cZyfF4VDtpXVcGEfWIap1JM
GkL/3xRkXwkUz+uJkWxIxNPxyizY9nIdU42q/1ZuNik51r7Lha1xChFoEEaKsC1i
s6cKF1k0x17XvAhfldtn5HID4m55oZXYI/ei4G1Hf14SrdbxyIrbFrM7AoGBAM0+
duRA3g0DXumTj27nr9k9dPvcpf9iB06c0spsavb7MXcrrP7frVqmwdJRUOvWD4px
mk3FfiNwe7BuVa4zsss9UX66mYDpwqXauFyy+zXGFhDynO4LXyRttTQlMYFS6yi9
eu8SEIlzgxGSFP52LPMDRz9+wOWnKV/WnmbeF/LTAoGAH9+R2p0hI9eMAjgtu79o
DxiPQq+hIK2bb8rjxzurSaU+uisJ1oM5g1fmlze+3CHkwQUhaL1l9It/phkZC4Rx
yBopQr6g/QUWg7PD9WWwu+78mi5bYBCRPsIxx5t0lqayG8PBxsqc/+KTJJb5A5sI
JeJdkNNwySQstifsyVlfi+MCgYB69tGELtjaMwrKJf3pFCXmoUtVGfzZ5IE8N/iS
DiKHljcsjp42lQvnI8E7oDw7DySAbU9lCM1prhpsua3bkF2uJi6NHn6ZHXPgqgmK
gDDBJsxRmwkqFOYLjS7i7uWZ9XOuFNdyJACEWRBNlpe9XE/RyEUHZYlsDZpcMNj2
CFmQwQKBgQCrwv5QmTI5GoFj5GJl/ztG46+AyZeUaItQp0gsGVEQ2S+bZYYx1T+e
WDuolM9wd08q0e+K3sFMv64C+LN34uQBwAlgPQ+ea+NHZ8b4zmbpW8fv5VvU1x/v
2F1DSH1UvuGZdvmpx2Jmb/9C3olO/HhoybCxVvA96Vf4ufnbc+esoA==
-----END RSA PRIVATE KEY-----";

        $tax_id = $cuitEmpresa;
        //$tax_id = 20430166035;
        // Crear la instancia de AFIP dentro del método
        $afip = new Afip(array(
            'CUIT' => $tax_id, 
            'cert' => $cert,  // Ruta al archivo de certificado
            'key' => $key,          // Ruta al archivo de clave privada
            'access_token' => 'gi72RbXfkm4DcfnmLSAOl1xnS1DvAMZfP6uFwQciVerrDlI3uJSEkgYU090AoGIw'
        ));
    
            $tax_types = $afip->ElectronicBilling->GetTaxTypes();
    
            return response()->json([
                'success' => true,
                'data' => $tax_types
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


}

