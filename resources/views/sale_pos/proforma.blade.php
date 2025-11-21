<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

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

<body>
    <div class="wrapper text-center bold text-20" style="width:100%;border-bottom: 0;">
        ORIGINAL
    </div>

    <div class="flex relative">
        <div class="wrapper inline-block w50 flex" style="border-right: 0">
            <h3 class="text-center" style="font-size:24px;margin-bottom: 3px;width: 100%;">{{ $receipt['location']['name'] ?? 'EMPRESA' }}</h3>
            <p style="font-size: 13px;line-height: 1.5;margin-bottom: 0;align-self: flex-end;">
                <b>Razón Social:</b> {{ $receipt['location']['name'] ?? 'EMPRESA' }}
                <br><b>Domicilio Comercial:</b> {{ $receipt['location']['city'] ?? '-' }}
                <br><b>Condición frente al IVA:</b>
            </p>
        </div>
        <div class="wrapper inline-block w50">
            <h3 class="text-center" style="font-size:24px;margin-bottom: 3px;">FACTURA</h3>
            <p style="font-size: 13px;line-height: 1.5;margin-bottom: 0;">
                <b>Punto de Venta: 00001 Comp. Nro: {{ $receipt['transaction']['invoice_no'] ?? '00000000' }}</b>
                <br><b>Fecha de Emisión: {{ \Carbon\Carbon::parse($receipt['transaction']['transaction_date'])->format('d/m/Y H:i') }}</b>
                <br><b>CUIT:</b> X
                <br><b>Ingresos Brutos:</b>
                <br><b>Fecha de Inicio de Actividades:</b>
            </p>
        </div>
        <div class="wrapper floating-mid">
            <h3 class="no-margin text-center" style="font-size: 36px;">X</h3>
            <h5 class="no-margin text-center">COD. 007</h5>
        </div>
    </div>
    

    <div class="wrapper" style="margin-top: 2px;font-size: 12px;">
        <div class="flex" style="margin-bottom: 15px;">
            <span style="width:30%"><b>CUIT:</b> X </span>
            <span><b>Apellido y Nombre / Razón Social:</b> JANE DOE</span>
        </div>
        <div class="flex" style="flex-wrap: nowrap;margin-bottom: 5px;">
            <span style="width:70%"><b>Condición frente al IVA:</b> </span>
            <span><b>Domicilio:</b> X</span>
        </div>
        <div class="flex">
            <span><b>Condición de venta:</b> Otra</span>
        </div>
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
            @foreach($receipt['sell_lines'] as $index => $line)
                <tr>
                    <td class="text-left">{{ $index + 1 }}</td>
                    <td class="text-left">{{ $line['product'] ?? 'Producto' }}</td>
                    <td class="text-right">{{ number_format($line['quantity'], 2, ',', '.') }}</td>
                    <td class="text-center">otras unidades</td>
                    <td class="text-right">{{ number_format($line['unit_price'], 2, ',', '.') }}</td>
                    <td class="text-center">0,00</td>
                    <td class="text-center">{{ number_format($line['line_total'], 2, ',', '.') }}</td>
                    <td class="text-right">0,00</td>
                    <td class="text-right">{{ number_format($line['line_total'], 2, ',', '.') }}</td>
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
                        <tr><td>Per./Ret. de Impuesto a las Ganancias</td><td></td><td></td><td class="text-right">0,00</td></tr>
                        <tr><td>Per./Ret. de IVA</td><td></td><td></td><td class="text-right">0,00</td></tr>
                        <tr><td>Impuestos Internos</td><td></td><td></td><td class="text-right">0,00</td></tr>
                        <tr><td>Impuestos Municipales</td><td></td><td></td><td class="text-right">0,00</td></tr>
                    </tbody>
                </table>
            </div>
            <div style="width:40%;margin-top: 40px;" class="flex wrapper">
                <span class="text-right" style="width:60%"><b>Importe Neto Gravado: $</b></span>
                <span class="text-right" style="width:40%"><b>0,00</b></span>
                <!-- Otros IVAs -->
                <span class="text-right" style="width:60%"><b>Importe Otros Tributos: $</b></span>
                <span class="text-right" style="width:40%"><b>0,00</b></span>
                <span class="text-right" style="width:60%"><b>Importe Total: $</b></span>
                <span class="text-right" style="width:40%"><b>{{ number_format($receipt['transaction']['final_total'], 2, ',', '.') }}</b></span>
            </div>
        </div>
        <div class="flex relative" style="margin-top: 20px;">
            <div class="qr-container" style="padding: 0 20px 20px 20px;width: 20%;"><img src="qr.png" style="max-width: 100%;"></div>
            <div style="padding-left: 10px;width: 45%;">
                <h4 class="italic bold">Comprobante Autorizado</h4>
                <p class="small italic bold" style="font-size: 9px;">Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación</p>
            </div>
            <div class="flex" style="align-self: flex-start;width: 35%;">
                <span class="text-right" style="width:50%"><b>CAE N°:</b></span><span class="text-left" style="padding-left: 10px;"></span>
                <span class="text-right" style="width:50%"><b>Fecha de Vto. de CAE:</b></span><span class="text-left" style="padding-left: 10px;"></span>
            </div>
            <span class="floating-mid bold">Pág 1/1</span>
        </div>
    </div>
    <div class="powered-by">
        <span>Powered by: Tutifactura</span>
    </div>
</body>
</html>
