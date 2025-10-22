<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            width: 21cm;
            min-height: 27cm;
            max-height: 29.7cm;
            font-size: 13px;
        }

        .wrapper {
            border: 1.5px solid #333;
            padding: 5px;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .italic {
            font-style: italic;
        }

        .inline-block {
            display: inline-block;
        }

        .flex {
            display: flex;
            flex-wrap: wrap;
        }

        .no-margin {
            margin: 0;
        }

        .relative {
            position: relative;
        }

        .floating-mid {
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
            width: 75px;
            position: absolute;
            top: 1px;
            background: #fff;
        }

        .space-around {
            justify-content: space-around;
        }

        .space-between {
            justify-content: space-between;
        }

        .w50 {
            width: 50%;
        }

        th {
            border: 1px solid #000;
            background: #ccc;
            padding: 5px;
        }

        td {
            padding: 5px;
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .text-20 {
            font-size: 20px;
        }
        
        .powered-by {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #888;
            opacity: 0.7;
        }
        
        .powered-by img {
            height: 14px;
            vertical-align: middle;
            margin-left: 5px;
        }
    </style>
</head>

<body>

    @php
        $ivaReceptorMap = [
            1 => 'IVA Responsable Inscripto',
            6 => 'Responsable Monotributo',
            13 => 'Monotributista Social',
            16 => 'Monotributo Trabajador Independiente Promovido',
            4 => 'IVA Sujeto Exento',
            5 => 'Consumidor Final',
            7 => 'Sujeto No Categorizado',
            8 => 'Proveedor del exterior',
            9 => 'Cliente del exterior',
            10 => 'IVA Liberado – Ley N° 19.640',
            15 => 'IVA No Alcanzado',
        ];
    @endphp

    <div class="wrapper text-center bold text-20" style="width:100%;border-bottom: 0;">
        ORIGINAL
    </div>

    <div class="flex relative">
        <div class="wrapper inline-block w50 flex" style="border-right: 0">
            <h3 class="text-center" style="font-size:24px;margin-bottom: 3px;width: 100%;">{{ $business_name }}</h3>
            <p style="font-size: 13px;line-height: 1.5;margin-bottom: 0;align-self: flex-end;">
                <b>Razón Social:</b> {{ $business_name }}
                <br><b>Domicilio Comercial:</b> {{ $direccion }}
                <br><b>Condición frente al IVA: {{ $ivaReceptorMap[$iva_receptor] ?? 'Responsable Monotributo' }}</b>
            </p>
        </div>
        <div class="wrapper inline-block w50">
            <h3 class="text-center" style="font-size:24px;margin-bottom: 3px;">FACTURA</h3>
            <p style="font-size: 13px;line-height: 1.5;margin-bottom: 0;">
                <b>Punto de Venta: {{ $PtoVta }}</b>
                <br><b>Comp. Nro:</b> {{ $numFact }}
                <br><b>Fecha de Emisión: </b> {{ \Carbon\Carbon::parse($transaction_date)->format('d/m/Y') }}
                <br><b>CUIT:</b> {{ $cuitEmpresa }}
                <br><b>Ingresos Brutos:</b> {{ $cuitEmpresa }}
                <br><b>Fecha de Inicio de Actividades:</b> {{ $fechaInicio }}
            </p>
        </div>
        <div class="wrapper floating-mid">
            <h3 class="no-margin text-center" style="font-size: 36px;">{{ $invoice_type }}</h3>
            <h5 class="no-margin text-center">COD. 007</h5>
        </div>
    </div>

    <div class="wrapper flex space-around" style="margin-top: 1px;">
        <span><b>Período Facturado Desde:</b> {{ $start_date }} </span>
        <span><b>Hasta:</b> {{ $end_date }} </span>
        <span><b>Fecha de Vto. para el pago:</b> {{ $payment_due_date }} </span>
    </div>

    <div class="wrapper" style="margin-top: 2px;font-size: 12px;">
        <div class="flex" style="margin-bottom: 15px;">
            <span style="width:30%"><b>CUIT:</b> {{ $buyer_document_number }} </span>
            <span><b>Apellido y Nombre / Razón Social:</b> {{ preg_replace('/\s*\(.*?\)/', '', $customer_name ?? 'N/A') }} </span>
        </div>
        @if(!empty($customer_address))
        <div class="flex" style="flex-wrap: nowrap;margin-bottom: 5px;">
            <span style="width:70%"><b>Condición frente al IVA:</b> {{ $ivaReceptorMap[$iva_receptor] }}</span>
            <span><b>Domicilio:</b> {{ $customer_address }} </span>
        </div>
        @endif
        @if(!empty($payment_condition))
        <div class="flex">
            <span><b>Condición de venta:</b> {{ $payment_condition }}</span>
        </div>
        @endif
    </div>

    <table style="margin-top: 5px;">
        <thead>
            <th class="text-left">Código</th>
            <th class="text-left">Producto / Servicio</th>
            <th>Cantidad</th>
            <th>U. Medida</th>
            <th>Precio Unit.</th>
            <th>% Bonif</th>
            <th>Subtotal</th>
            <th>Alicuota IVA</th>
            <th>Subtotal c/IVA</th>
        </thead>
        <tbody>
            @foreach ($products as $product)
            <tr>
                <td class="text-left">{{ $product['product_code'] ?? '' }}</td>
                <td class="text-left">{{ $product['product_name'] }}</td>
                <td class="text-right">{{ number_format($product['quantity'], 2) }}</td>
                <td class="text-center">Unidad</td>
                <td class="text-right">{{ number_format($product['unit_price'], 2) }}</td>
                <td class="text-center">0,00</td>
                <td class="text-right">{{ number_format($product['subtotal'], 2) }}</td>
                <td class="text-right">
                    @if($valIva==0) Exento
                    @elseif($valIva==0.21) 21%
                    @elseif($valIva==0.105) 10.5%
                    @elseif($valIva==0.27) 27%
                    @endif
                </td>
                <td class="text-right">{{ number_format($product['subtotal']*(1+$valIva), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer" style="margin-top: 300px;">
        <div class="flex wrapper space-between">
            <div style="width:55%">
                <p class="bold">Otros tributos</p>
                <table>
                    <thead>
                        <th>Descripción</th>
                        <th>Detalle</th>
                        <th class="text-right">Alíc. %</th>
                        <th class="text-right">Importe</th>
                    </thead>
                    <tbody>
                        @if(isset($other_taxes) && count($other_taxes))
                            @foreach($other_taxes as $tax)
                            <tr>
                                <td>{{ $tax['description'] }}</td>
                                <td>{{ $tax['detail'] }}</td>
                                <td class="text-right">{{ $tax['rate'] }}</td>
                                <td class="text-right">{{ number_format($tax['amount'], 2) }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="text-center">No hay otros tributos</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3"><strong>Importe Otros Tributos:</strong></td>
                            <td class="text-right"><strong>{{ number_format($other_taxes_total ?? 0, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="width:40%;margin-top: 40px;" class="flex wrapper">
                @php
                    $brackets = [
                        ['label'=>'Importe Neto Gravado','rate'=>null,'value'=>$ImpNeto],
                        ['label'=>'IVA 27%','rate'=>0.27],
                        ['label'=>'IVA 21%','rate'=>0.21],
                        ['label'=>'IVA 10.5%','rate'=>0.105],
                        ['label'=>'IVA 5%','rate'=>0.05],
                        ['label'=>'IVA 2.5%','rate'=>0.025],
                        ['label'=>'IVA 0%','rate'=>0],
                    ];
                @endphp
                @foreach($brackets as $b)
                    <span class="text-right" style="width:60%"><b>{{ $b['label'] }}: $</b></span>
                    <span class="text-right" style="width:40%">
                        <b>
                        @if(is_null($b['rate']))
                            {{ number_format($b['value'],2) }}
                        @else
                            {{ $valIva==$b['rate'] ? number_format($ImpNeto*$b['rate'],2) : '0,00' }}
                        @endif
                        </b>
                    </span>
                @endforeach
                <span class="text-right" style="width:60%"><b>Importe Otros Tributos: $</b></span>
                <span class="text-right" style="width:40%"><b>{{ number_format($other_taxes_total ?? 0,2) }}</b></span>
                <span class="text-right" style="width:60%"><b>Importe Total: $</b></span>
                <span class="text-right" style="width:40%"><b>{{ number_format($denomination_total_amount,2) }}</b></span>
            </div>
        </div>
        <div class="flex relative" style="margin-top: 20px;">
            <div class="qr-container" style="padding: 0 20px 20px 20px;width: 20%;"><img src="{{ $qrImage }}"
                    style="max-width: 100%;"></div>
            <div style="padding-left: 10px;width: 45%;">
                <img src="{{ asset('uploads/business_logos/' . $logoEmpresa) }}" style="max-width:130px">
                <h4 class="italic bold">Comprobante Autorizado</h4>
                <p class="small italic bold" style="font-size: 9px;">Esta Administración Federal no se responsabiliza
                    por los datos ingresados en el detalle de la operación</p>
            </div>
            <div class="flex" style="align-self: flex-start;width: 35%;">
                <span class="text-right" style="width:50%"><b>CAE N°:</b></span><span class="text-left"
                    style="padding-left: 10px;">{{ $cae }}</span>
                <span class="text-right" style="width:50%"><b>Fecha de Vto. de CAE:</b></span><span class="text-left"
                    style="padding-left: 10px;">{{ $cae_due_date }}</span>
            </div>
            <span class="floating-mid bold">Pág 1/1</span>
        </div>
    </div>
    <div class="powered-by">
        <span>Powered by: Trevitsoft</span>
    </div>
</body>
</html>