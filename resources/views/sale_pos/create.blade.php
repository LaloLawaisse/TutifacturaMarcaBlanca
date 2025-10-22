@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_pos_sell_form',
        ]) !!}
        <div class="row mb-12">
            <div class="col-md-12 tw-pt-0 tw-mb-14">
                <div class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
                    {{-- <div class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12"> --}}
                    <div class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 @if(empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[60%]  @else lg:tw-w-[100%] @endif">

                        <div class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-mb-2 md:tw-mb-8 tw-p-2">

                            {{-- <div class="box box-solid mb-12 @if (!isMobile()) mb-40 @endif"> --}}
                                <div class="box-body pb-0">
                                    {!! Form::hidden('location_id', $default_location->id ?? null, [
                                        'id' => 'location_id',
                                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                            ? $default_location->receipt_printer_type
                                            : 'browser',
                                        'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                                    ]) !!}
                                    <!-- sub_type -->
                                    {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                    <input type="hidden" id="item_addition_method"
                                        value="{{ $business_details->item_addition_method }}">
                                    @include('sale_pos.partials.pos_form')

                                    @include('sale_pos.partials.pos_form_totals')

                                    @include('sale_pos.partials.payment_modal')

                                    @if (empty($pos_settings['disable_suspend']))
                                        @include('sale_pos.partials.suspend_note_modal')
                                    @endif

                                    @if (empty($pos_settings['disable_recurring_invoice']))
                                        @include('sale_pos.partials.recurring_invoice_modal')
                                    @endif
                                </div>
                            {{-- </div> --}}
                        </div>
                    </div>
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="md:tw-no-padding tw-w-full lg:tw-w-[40%] tw-px-5">
                            @include('sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}
    </section>

    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

    <div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- Modal Generar Factura -->
    <div class="modal fade" id="modalGenerarFactura" tabindex="-1" role="dialog" aria-labelledby="modalGenerarFacturaLabel" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-body">

                </div>
            </div>
        </div>
    </div>
    <div id="modalCargaMasiva" class="modal" style="display: none;">
      <div class="modal-content">
        <div class="modal-header">
        </div>
        <div class="modal-body">
          <!-- Aquí se cargará dinámicamente el contenido -->
        </div>
        <div class="modal-footer">
          <button onclick="cerrarModal()">Cancelar</button>
        </div>
      </div>
    </div>





    @include('sale_pos.partials.configure_search_modal')

    @include('sale_pos.partials.recent_transactions_modal')

    @include('sale_pos.partials.weighing_scale_modal')

@stop
@section('css')
    <!-- include module css -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    <script>
        @include('sale_pos.partials.keyboard_shortcuts')
    </script>

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('generar-factura');
            console.log('Botón encontrado:', button);
            
            if (button) {
                button.addEventListener('click', function () {
                    console.log('Evento activado correctamente');
                });
            } else {
                console.error('El botón no está en el DOM o no se encuentra con este ID.');
            }
        });
    </script>
    
    <script>
    (function () {
        // Normaliza lectura numérica con o sin helpers globales
        function readNumber($el) {
            if (typeof window.__read_number === 'function') {
                return __read_number($el);
            }
            var v = ($el.val() || '').toString().replace(/\./g, '').replace(',', '.');
            var n = parseFloat(v);
            return isNaN(n) ? 0 : n;
        }
        function writeNumber($el, val, precision) {
            precision = (typeof precision === 'number') ? precision : 2;
            if (typeof window.__write_number === 'function') {
                __write_number($el, val);
            } else {
                $el.val(val.toFixed(precision));
            }
        }
    
        // Detecta inputs de subtotal que el usuario puede editar (ajustá selectores si hiciera falta)
        var SUBTOTAL_SELECTOR = '.row_subtotal, .pos_line_total, input[name$="[line_total]"]';
        var QTY_SELECTOR      = '.pos_quantity, input[name$="[quantity]"]';
        var PRICE_SELECTOR    = '.pos_unit_price_inc_tax, .pos_unit_price, input[name$="[unit_price]"], input[name$="[unit_price_inc_tax]"]';
    
        // Guarda cantidad original al enfocar el subtotal
        $(document).on('focusin', SUBTOTAL_SELECTOR, function () {
            var $row = $(this).closest('tr, .product_row, .pos_product_row');
            var $qty = $row.find(QTY_SELECTOR).first();
            var qty  = readNumber($qty);
            $row.data('origQty', qty);
        });
    
        // Al cambiar/blur del subtotal: recalcular precio unitario y NO tocar cantidad
        $(document).on('change blur', SUBTOTAL_SELECTOR, function (e) {
            var $subtotal = $(this);
            var $row      = $subtotal.closest('tr, .product_row, .pos_product_row');
    
            // Cantidad original (si no está, usa la actual)
            var origQty = $row.data('origQty');
            var $qty    = $row.find(QTY_SELECTOR).first();
            var qtyNow  = readNumber($qty);
            var qty     = (typeof origQty === 'number' && origQty > 0) ? origQty : qtyNow;
    
            if (!qty || qty <= 0) {
                // Sin cantidad no podemos derivar precio
                return;
            }
    
            // Lee el nuevo subtotal
            var newSubtotal = readNumber($subtotal);
            if (!isFinite(newSubtotal)) return;
    
            // Calcular nuevo precio unitario: subtotal / cantidad
            var newUnitPrice = newSubtotal / qty;
    
            // Seleccionar input de precio y escribir nuevo valor
            var $price = $row.find(PRICE_SELECTOR).first();
            if ($price.length === 0) return;
    
            // Reponer cantidad original por si otro handler la cambió
            writeNumber($qty, qty, 4);
    
            // Escribir nuevo precio unitario
            writeNumber($price, newUnitPrice, 4);
    
            // Forzar recálculo estándar (impuestos, descuentos, totales)
            // Dispará change en precio; muchos POS recalculan desde este evento
            $price.trigger('change');
    
            // Si tu POS expone una función de recálculo de línea, podés llamarla aquí.
            // Ejemplos comunes (descomentar el que corresponda):
            // if (typeof pos_product_row === 'function') { pos_product_row($row); }
            // if (typeof update_line_total === 'function') { update_line_total($row); }
    
            // Evita que otros handlers posteriores re-modifiquen la cantidad
            e.stopImmediatePropagation();
        });
    
        // Seguridad adicional: si alguien intenta cambiar cantidad "por efecto colateral" cuando se está editando subtotal,
        // la revertimos al valor original capturado.
        $(document).on('change', QTY_SELECTOR, function () {
            var $qty = $(this);
            var $row = $qty.closest('tr, .product_row, .pos_product_row');
            var origQty = $row.data('origQty');
            if (typeof origQty === 'number' && origQty > 0) {
                // Detecta si el cambio proviene de una edición de subtotal reciente (window flag corto)
                if ($row.data('editingSubtotal')) {
                    writeNumber($qty, origQty, 4);
                    $qty.trigger('change'); // recalcular con la cantidad correcta
                }
            }
        });
    
        // Flag para pequeñas ventanas de tiempo durante la edición de subtotal
        $(document).on('focusin', SUBTOTAL_SELECTOR, function () {
            var $row = $(this).closest('tr, .product_row, .pos_product_row');
            $row.data('editingSubtotal', true);
        });
        $(document).on('blur', SUBTOTAL_SELECTOR, function () {
            var $row = $(this).closest('tr, .product_row, .pos_product_row');
            // limpiar el flag con un pequeño delay por si quedan handlers asincrónicos
            setTimeout(function(){ $row.removeData('editingSubtotal'); }, 50);
        });
    })();
    </script>


@endsection