<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ticket de Venta</title>
  <style>
    :root {
      /* Cambiar a 58mm si corresponde */
      --ticket-width: 80mm;
      --font-size: 12px;
      --line-height: 1.35;
      --border-color: #000;
      --muted: #555;
      --bg-muted: #f2f2f2;
    }

    * { box-sizing: border-box; }

    html, body {
      margin: 0;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: var(--font-size);
      line-height: var(--line-height);
      color: #000;
      background: #fff;
      width: var(--ticket-width);
    }

    .ticket {
      width: var(--ticket-width);
      max-width: 100%;
      margin: 0 auto;
      padding: 6px 8px;
      border: 1px solid var(--border-color);
    }

    .center { text-align: center; }
    .right { text-align: right; }
    .left { text-align: left; }
    .bold { font-weight: 700; }
    .small { font-size: 10px; }
    .mt-4 { margin-top: 4px; }
    .mt-8 { margin-top: 8px; }
    .mb-4 { margin-bottom: 4px; }
    .mb-8 { margin-bottom: 8px; }
    .sep { border-top: 1px solid var(--border-color); margin: 6px 0; }
    .row { display: flex; align-items: flex-start; }
    .row > * { flex: 1 1 auto; }

    .header h1 {
      font-size: 14px; margin: 0; padding: 0;
    }
    .header .title { font-size: 13px; }
    /* Estilos para formato tipo AFIP */
    .doc-head { display: grid; grid-template-columns: 1fr auto; align-items: end; border-bottom: 1px solid var(--border-color); padding-bottom: 6px; margin-bottom: 6px; }
    .doc-head .title { font-size: 18px; font-weight: 700; letter-spacing: 0.5px; }
    .doc-head .subtitle { font-size: 10px; color: var(--muted); }
    .doc-head .code { font-size: 11px; }

    .box { border: 1px solid var(--border-color); padding: 6px; margin: 6px 0; }
    .box .label { color: var(--muted); }
    .kv { display: grid; grid-template-columns: auto 1fr; gap: 2px 6px; }

    .bar { background: var(--bg-muted); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 4px 6px; font-weight: 700; }

    .kv { display: grid; grid-template-columns: auto 1fr; gap: 2px 6px; }

    /* Tabla de items en formato tickets */
    .items { width: 100%; border-collapse: collapse; margin: 6px 0; border: 1px solid var(--border-color); table-layout: fixed; }
    .items thead th { border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 2px 4px; font-weight: 700; }
    .items tbody tr { page-break-inside: avoid; }
    .items td { padding: 2px 4px; vertical-align: top; }
    .col-qtyxprice { white-space: nowrap; width: 20%; }
    .col-desc { width: 50%; white-space: normal; word-break: break-word; overflow-wrap: anywhere; }
    .col-importe { white-space: nowrap; text-align: right; width: 30%; overflow: hidden; text-overflow: ellipsis; }
    .siva { white-space: normal !important; text-overflow: initial; overflow: visible; }

    .totals { width: 100%; border-collapse: collapse; margin: 6px 0; border: 1px solid var(--border-color); }
    .totals td { padding: 2px 0; }
    .totals .label { width: 60%; }
    .totals .amount { width: 40%; text-align: right; }

    .foot { page-break-inside: avoid; }

    /* Forzar ancho de pagina de impresora termica (usar valor fijo) */
    @page { size: 80mm auto; margin: 0; }
    @media print {
      html, body { width: var(--ticket-width); }
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .no-print { display: none !important; }
      .ticket { padding: 6px 8px; }
    }
  </style>
</head>
<body>
  <div class="ticket">
        <!-- ENCABEZADO TIPO FACTURA -->
    @php
      $doc_letter = $doc_letter ?? ($doc_type ?? 'B');
      $doc_code = $doc_code ?? '06';
    @endphp
    <div class="doc-head">
      <div>
        <div class="title">FACTURA {{ $doc_letter }}</div>
        <div class="subtitle">ORIGINAL</div>
      </div>
      <div class="right code">Cod.: {{ $doc_code }}</div>
    </div>

    <!-- DATOS EMPRESA -->
    <div class="box">
      @if(!empty($logoEmpresa))
        <div class="center mb-4">
          <img src="{{ asset('uploads/business_logos/' . $logoEmpresa) }}" alt="logo" style="max-width: 60%; height: auto;" />
        </div>
      @endif
      <div class="bold">{{ $business_name }}</div>
      @if(!empty($direccion)) <div>DIRECCION: {{ $direccion }}</div> @endif
      @if(!empty($localidadProvincia ?? null)) <div>LOCALIDAD: {{ $localidadProvincia }}</div> @endif
      @if(!empty($cuitEmpresa)) <div>CUIT: {{ $cuitEmpresa }}</div> @endif
      @if(!empty($iibb ?? null)) <div>Ing. Brutos: {{ $iibb }}</div> @endif
      @if(!empty($fechaInicio)) <div>Inicio Actividad: {{ $fechaInicio }}</div> @endif
    </div>

    <!-- DATOS COMPROBANTE -->
    <div class="box small">
      <div class="kv">
        <div class="label">FECHA:</div>
        <div>{{ \Carbon\Carbon::parse($transaction_date)->format('d/m/Y H:i') }}</div>
        <div class="label">Pto Vta / N&ordm;:</div>
        <div> - {{ $PtoVta }}</div>
      </div>
    </div>

        <!-- CLIENTE (opcional) -->
    @php
      $nombreCliente = isset($customer_name) ? preg_replace('/\s*\(.*?\)/', '', $customer_name) : '';
      $nomFinal = $nombreCliente ?: 'Consumidor Final';
    @endphp
    <div class="box small">
      <div><span class="bold">Cliente:</span> {{ $nomFinal }}</div>
      @if(!empty($buyer_document_number))
        <div><span class="bold">CUIT/DNI:</span> {{ $buyer_document_number }}</div>
      @endif
      @if(!empty($customer_address))
        <div><span class="bold">Domicilio:</span> {{ $customer_address }}</div>
      @endif
      @if(!empty($customer_vat_condition ?? null))
        <div><span class="bold">Cond. Ante IVA:</span> {{ $customer_vat_condition }}</div>
      @else
        <div><span class="bold">Cond. Ante IVA:</span> Consumidor final</div>
      @endif
    </div>

    <div class="sep"></div>

    <!-- ?TEMS -->
        <!-- ?TEMS -->
    <table class="items">
      <thead>
        <tr>
          <th class="left" colspan="2">Cantidad / Precio Uni.</th>
          <th class="right">Importe</th>
        </tr>
        <tr>
          <th class="left" colspan="2">Descripcion</th>
          <th class="right">&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($products as $product)
          @php
            $qty = (float)($product['quantity'] ?? 0);
            $price = (float)($product['unit_price'] ?? 0);
            $lineSubtotal = (float)($product['subtotal'] ?? ($qty * $price));
            $ivaRate = (float)($valIva ?? 0);
            $unitPriceWithIva = $price * (1 + $ivaRate);
            $lineTotal = $lineSubtotal * (1 + $ivaRate);
          @endphp
          <!-- Fila 1: Cantidad x Precio (con IVA) + Importe total -->
          <tr class="small">
            <td class="col-qtyxprice" colspan="2">{{ number_format($qty, 2) }} x $ {{ number_format($unitPriceWithIva, 2) }} @if(!empty($product['product_code'])) Cod: {{ $product['product_code'] }} @endif</td>
            <td class="col-importe bold">$ {{ number_format($lineTotal, 2) }}</td>
          </tr>
          <!-- Fila 2: Descripción + Importe sin IVA -->
          <tr class="small">
            <td class="col-desc" colspan="2">{{ $product['product_name'] }}</td>
            <td class="col-importe siva">s/IVA: $ {{ number_format($lineSubtotal, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="sep"></div>

    <!-- TOTALES -->
    @php
      // Cálculos de totales con redondeo por renglón
      $otherTaxes = (float)($other_taxes_total ?? 0);
      $ivaRateGlobal = isset($valIva) ? (float)$valIva : 0.0;
      $descuento = (float)($discount_total ?? $discount_amount ?? 0);

      // Valores por defecto si vienen dados externamente
      $neto = isset($ImpNeto) ? (float)$ImpNeto : 0.0;
      $ivaMonto = round($neto * $ivaRateGlobal, 2);
      $total = isset($denomination_total_amount) ? (float)$denomination_total_amount : round(($neto + $ivaMonto + $otherTaxes), 2);

      // Si hay productos, recalcular para que coincida con lo impreso (redondeo a 2 por línea)
      if (!empty($products) && (is_array($products) || $products instanceof Traversable)) {
        $netoSum = 0.0; $ivaSum = 0.0; $totalSum = 0.0;
        foreach ($products as $p) {
          $q = (float)($p['quantity'] ?? 0);
          $pr = (float)($p['unit_price'] ?? 0);
          $lineSub = (float)($p['subtotal'] ?? ($q * $pr));
          $lineSub = round($lineSub, 2);
          $lineIva = round($lineSub * $ivaRateGlobal, 2);
          $netoSum += $lineSub;
          $ivaSum += $lineIva;
          $totalSum += round($lineSub + $lineIva, 2);
        }
        $neto = round($netoSum, 2);
        $ivaMonto = round($ivaSum, 2);
        $total = round($totalSum + $otherTaxes, 2);
      }
    @endphp

    <table class="totals">
      <tbody>        <tr>
          <td class="label">Descuento</td>
          <td class="amount">$ {{ number_format($descuento, 2) }}</td>
        </tr>
        <tr>
          <td class="label">Importe Neto Gravado</td>
          <td class="amount">$ {{ number_format($neto, 2) }}</td>
        </tr>
        <tr>
          <td class="label">IVA {{ $ivaRateGlobal==0 ? '0%' : ($ivaRateGlobal*100) . '%' }}</td>
          <td class="amount">$ {{ number_format($ivaMonto, 2) }}</td>
        </tr>
        @if($otherTaxes > 0)
        <tr>
          <td class="label">Otros Tributos</td>
          <td class="amount">$ {{ number_format($otherTaxes, 2) }}</td>
        </tr>
        @endif
        <tr>
          <td class="label bold">TOTAL</td>
          <td class="amount bold">$ {{ number_format($total, 2) }}</td>
        </tr>
      </tbody>
    </table>

    <div class="sep"></div>

    <!-- PAGOS (opcional) -->
    @if(!empty($payment_method) || !empty($paid_amount))
      @php
        $paid = (float)($paid_amount ?? 0);
        $change = max(0, $paid - $total);
      @endphp
      <table class="totals">
        <tbody>
          @if(!empty($payment_method))
          <tr>
            <td class="label">Forma de pago</td>
            <td class="amount">{{ $payment_method }}</td>
          </tr>
          @endif
          @if($paid > 0)
          <tr>
            <td class="label">Pagado</td>
            <td class="amount">$ {{ number_format($paid, 2) }}</td>
          </tr>
          <tr>
            <td class="label">Vuelto</td>
            <td class="amount">$ {{ number_format($change, 2) }}</td>
          </tr>
          @endif
        </tbody>
      </table>
      <div class="sep"></div>
    @endif

        <!-- CAE / VENCIMIENTO CAE -->
    @if(!empty($cae ?? null) || !empty($cae_vto ?? null))
      <div class="box small">
        @if(!empty($cae)) <div><span class="bold">CAE:</span> {{ $cae }}</div> @endif
        @if(!empty($cae_vto)) <div><span class="bold">VTO. CAE:</span> {{ $cae_vto }}</div> @endif
      </div>
    @endif

    <!-- PIE -->
    <div class="foot center small">
      <div>Comprobante Autorizado</div>
      <div>Esta Administracion Federal no se responsabiliza por los datos ingresados en el detalle de la operacion</div>
    </div>
    <!-- Zona QR opcional (si se desea incluir el mismo QR de AFIP) -->
    @if(!empty($qrImage))
      <div class="box center mt-8">
        <img src="{{ $qrImage }}" alt="QR" style="width: 120px; height: auto;" />
      </div>
    @endif

    <!-- L?nea de corte visual -->
    <div class="sep"></div>
    <div class="center small">----------</div>
  </div>

  <!-- Controles solo para vista previa en navegador -->
  <div class="no-print center" style="margin: 12px 0;">
    <button onclick="window.print()">Imprimir</button>
  </div>
</body>
</html>
