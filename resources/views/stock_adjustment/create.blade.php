@extends('layouts.app')
@section('title', __('stock_adjustment.add'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <br>
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('stock_adjustment.add')</h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\StockAdjustmentController::class, 'store']),
            'method' => 'post',
            'id' => 'stock_adjustment_form',
        ]) !!}
        @component('components.widget', ['class' => 'box-solid'])
            <div class="row">
                <!-- Otros campos existentes -->
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
                        {!! Form::select('location_id', $business_locations, null, [
                            'class' => 'form-control select2',
                            'placeholder' => __('messages.please_select'),
                            'required',
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                        {!! Form::text('ref_no', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('transaction_date', __('messages.date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('adjustment_type', __('stock_adjustment.adjustment_type') . ':*') !!} @show_tooltip(__('tooltip.adjustment_type'))
                        {!! Form::select(
                            'adjustment_type',
                            ['normal' => __('stock_adjustment.normal'), 'abnormal' => __('stock_adjustment.abnormal')],
                            null,
                            ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'],
                        ) !!}
                    </div>
                </div>
            </div>
            <div class="col-sm-12 text-center" style="margin-top: 20px">
                <!-- Botón para MercadoLibre -->
                <button type="button" class="btn btn-light d-flex align-items-center justify-content-center" 
                        data-toggle="modal" data-target="#mercadolibreModal" 
                        style="border: 1px solid #ccc; margin-right: 20px; padding: 10px 15px;">
                    <img src="{{ asset('images/mercadolibre-logo.png') }}" alt="MercadoLibre" style="width: 50px; height: auto; margin-left: 50px;">
                    <span>Cargar MercadoLibre</span>
                </button>
                
                <!-- Botón para MercadoPago -->
                <button type="button" class="btn btn-light d-flex align-items-center justify-content-center" 
                        data-toggle="modal" data-target="#mercadopagoModal" 
                        style="border: 1px solid #ccc; padding: 10px 15px;">
                    <img src="{{ asset('images/mercadopago-logo.png') }}" alt="MercadoPago" style="width: 50px; height: auto; margin-left: 50px;">
                    <span>Cargar MercadoPago</span>
                </button>
                
                <!-- Botón para Tiendanube -->
                <button type="button" class="btn btn-light d-flex align-items-center justify-content-center" 
                        data-toggle="modal" data-target="#tiendanubeModal" 
                        style="border: 1px solid #ccc; padding: 10px 15px; margin-left: 20px;">
                    <img src="{{ asset('images/tiendanube-logo.png') }}" alt="Tiendanube" style="width: 50px; height: auto; margin-left: 50px;">
                    <span>Cargar Tiendanube</span>
                </button>
                
                <!-- Botón para WooCommerce -->
                <button type="button" onclick="window.open('https://app.trevitsoft.com/woocommerce', '_blank')"
                    class="btn btn-light d-flex align-items-center justify-content-center"
                    style="border: 1px solid #ccc; padding: 10px 15px; margin-left: 20px;">
                    <img src="{{ asset('images/woocommerce-logo.png') }}" alt="WooCommerce"
                         style="width: 65px; height: auto; margin-left: 50px;">
                    <span style="margin-left: 10px;">Cargar WooCommerce</span>
                </button>




            </div>

        @endcomponent

        @component('components.widget', ['class' => 'box-solid'])
            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            {!! Form::text('search_product', null, [
                                'class' => 'form-control',
                                'id' => 'search_product_for_srock_adjustment',
                                'placeholder' => __('stock_adjustment.search_product'),
                                'disabled',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-10 col-sm-offset-1">
                    <input type="hidden" id="product_row_index" value="0">
                    <input type="hidden" id="total_amount" name="final_total" value="0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-condensed" id="stock_adjustment_product_table">
                            <thead>
                                <tr>
                                    <th class="col-sm-4 text-center">
                                        @lang('sale.product')
                                    </th>
                                    <th class="col-sm-2 text-center">
                                        @lang('sale.qty')
                                    </th>
                                    <th class="col-sm-2 text-center show_price_with_permission">
                                        @lang('sale.unit_price')
                                    </th>
                                    <th class="col-sm-2 text-center show_price_with_permission">
                                        @lang('sale.subtotal')
                                    </th>
                                    <th class="col-sm-2 text-center"><i class="fa fa-trash" aria-hidden="true"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr class="text-center show_price_with_permission">
                                    <td colspan="3"></td>
                                    <td>
                                        <div class="pull-right"><b>@lang('stock_adjustment.total_amount'):</b> <span
                                                id="total_adjustment">0.00</span></div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-solid'])
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('total_amount_recovered', __('stock_adjustment.total_amount_recovered') . ':') !!} @show_tooltip(__('tooltip.total_amount_recovered'))
                        {!! Form::text('total_amount_recovered', 0, [
                            'class' => 'form-control input_number',
                            'placeholder' => __('stock_adjustment.total_amount_recovered'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('additional_notes', __('stock_adjustment.reason_for_stock_adjustment') . ':') !!}
                        {!! Form::textarea('additional_notes', null, [
                            'class' => 'form-control',
                            'placeholder' => __('stock_adjustment.reason_for_stock_adjustment'),
                            'rows' => 3,
                        ]) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 text-center">
                    <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">@lang('messages.save')</button>
                </div>
            </div>
        @endcomponent
        {!! Form::close() !!}
    </section>

    <!-- Modal para MercadoLibre -->
    <div class="modal fade" id="mercadolibreModal" tabindex="-1" role="dialog" aria-labelledby="mercadolibreModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="mercadolibreModalLabel">Integración con MercadoLibre</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Sección de autorización de MercadoLibre -->
                    <div id="authSectionML" style="display: none;">
                        <p>Para conectar tu cuenta de MercadoLibre, necesitas autorizar la aplicación.</p>
                        <a href="https://auth.mercadolibre.com.ar/authorization?response_type=code&client_id=776528488475123&redirect_uri=https://app.trevitsoft.com/callback/"
                           class="btn btn-warning btn-block" target="_blank">
                            Autorizar aplicación
                        </a>
                    </div>
    
                    <!-- Sección de datos de MercadoLibre -->
                    <form id="mlForm">
                        <div id="dataSectionML" style="display: none;">
                            <div class="row" style="margin-bottom: 15px;">
                                <div class="col-md-6">
                                    <label for="date_from_ml">Fecha Desde:</label>
                                    <input type="date" id="date_from_ml" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="date_to_ml">Fecha Hasta:</label>
                                    <input type="date" id="date_to_ml" class="form-control">
                                </div>
                            </div>
                            <button id="fetchOrdersButtonML" type="button" class="btn btn-primary btn-block">Traer órdenes</button>
                            <div class="form-group">
                            </div>
                            <hr>
                            <!-- Contenedor para la tabla de órdenes de MercadoLibre -->
                            <div id="ordersContainerML" style="display: none;">
                                <h4>Órdenes obtenidas</h4>
                                <table id="ordersTableML" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%; text-align: center;">
                                                <input type="checkbox" id="select_all_orders_ml">
                                            </th>
                                            <th>Producto</th>
                                            <th>SKU</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unitario</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <!-- Filas agregadas dinámicamente -->
                                    </tbody>
                                </table>
                                <div class="modal-footer px-3">
                                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
                                        <!-- Contenedor Selectores -->
                                        <div class="d-flex flex-wrap gap-3 align-items-center">
                                            <!-- Selector Tasa de IVA -->
                                            <div class="form-group mb-0">
                                                <label for="iva_rate_selector" class="form-label mb-0 small">IVA:</label>
                                                <select id="iva_rate_selector" class="form-control form-control-sm">
                                                    <option value="">Seleccione</option>
                                                    <option value="iva_21">IVA 21%</option>
                                                    <option value="iva_27">IVA 27%</option>
                                                    <option value="iva_10">IVA 10.5%</option>
                                                    <option value="excento_iva">Exento IVA%</option>
                                                </select>
                                            </div>
                                
                                            <!-- Selector Punto de Venta -->
                                            <div class="form-group mb-0">
                                                <label for="pto_vta_selector" class="form-label mb-0 small">Punto de Venta:</label>
                                                <select id="pto_vta_selector" class="form-control form-control-sm">
                                                    <option value="">Seleccione</option>
                                                    @for ($i = 1; $i <= 20; $i++)
                                                        <option value="{{ $i }}">Punto {{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                
                                        <!-- Contenedor Botones -->
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" id="loadMercadolibreArca" class="btn btn-success btn-sm px-3">
                                                Crear Facturas ARCA
                                            </button>
                                            <button type="button" id="loadMercadolibreProforma" class="btn btn-success btn-sm px-3">
                                                Crear Facturas Proforma
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">
                                                Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para MercadoPago -->
    <div class="modal fade" id="mercadopagoModal" tabindex="-1" role="dialog" aria-labelledby="mercadopagoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="mercadopagoModalLabel">Integración con MercadoPago</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Sección de autorización de MercadoPago -->
                    <div id="authSectionMP" style="display: none;">
                        <p>Para conectar tu cuenta de MercadoPago, necesitas autorizar la aplicación.</p>
                        <a href="https://auth.mercadopago.com.ar/authorization?response_type=code&client_id=6815763552830505&redirect_uri=https://app.trevitsoft.com/callbackMP/"
                           class="btn btn-warning btn-block" target="_blank">
                            Autorizar aplicación
                        </a>
                    </div>
                    <!-- Sección de datos de MercadoPago -->
                    <form id="mpForm">
                        <div id="dataSectionMP" style="display: none;">
                            <div class="row" style="margin-bottom: 15px;">
                                <div class="col-md-6">
                                    <label for="date_from_mp">Fecha Desde:</label>
                                    <input type="date" id="date_from_mp" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="date_to_mp">Fecha Hasta:</label>
                                    <input type="date" id="date_to_mp" class="form-control">
                                </div>
                            </div>
                            <button id="fetchOrdersButtonMP" type="button" class="btn btn-primary btn-block">Traer órdenes</button>
                            <hr>
                            <!-- Contenedor para la tabla de órdenes de MercadoPago -->
                            <div id="ordersContainerMP" style="display: none;">
                                <h4>Órdenes obtenidas</h4>
                                <table id="ordersTableMP" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%; text-align: center;">
                                                <input type="checkbox" id="select_all_orders_mp">
                                            </th>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unitario</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Filas agregadas dinámicamente -->
                                    </tbody>
                                </table>
                                <div class="modal-footer">
                                    
                                    <!-- <button type="button" id="loadMercadopagoSales" class="btn btn-success">Cargar ventas seleccionadas</button> -->
                                    <div class="form-group mb-0">
                                        <label for="iva_rate_selector" class="form-label mb-0 small">IVA:</label>
                                        <select id="iva_rate_selector" class="form-control form-control-sm">
                                            <option value="">Seleccione</option>
                                            <option value="iva_21">IVA 21%</option>
                                            <option value="iva_27">IVA 27%</option>
                                            <option value="iva_10">IVA 10.5%</option>
                                            <option value="excento_iva">Exento IVA%</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="pto_vta_selector">Seleccionar punto de venta:</label>
                                        <select id="pto_vta_selector" class="form-control" required>
                                            <option value="">Seleccione</option>
                                            @for ($i = 1; $i <= 20; $i++)
                                                <option value="{{ $i }}">Punto de Venta {{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <button type="button" id="loadMercadopagoArca" class="btn btn-success">Crear Facturas ARCA</button>
                                    <button type="button" id="loadMercadopagoProforma" class="btn btn-success">Crear Facturas Proforma</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </form>
    
                </div>
            </div>
        </div>

    </div>
    
    <!-- Modal para Tiendanube -->
    <div class="modal fade" id="tiendanubeModal" tabindex="-1" role="dialog" aria-labelledby="tiendanubeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="tiendanubeModalLabel">Integración con Tiendanube</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Sección de autorización de Tiendanube -->
                    <div id="authSectionTN" style="display: none;">
                        <p>Para conectar tu tienda de Tiendanube, necesitas autorizar la aplicación.</p>
                        <a href="https://www.tiendanube.com/apps/17845/authorize"
                           target="_blank" class="btn btn-warning btn-block">
                            Autorizar aplicación
                        </a>
                    </div>
    
                    <!-- Sección de datos de Tiendanube -->
                    <form id="tnForm">
                        <div id="dataSectionTN" style="display: none;">
                            <div class="row" style="margin-bottom: 15px;">
                                <div class="col-md-6">
                                    <label for="date_from_tn">Fecha Desde:</label>
                                    <input type="date" id="date_from_tn" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="date_to_tn">Fecha Hasta:</label>
                                    <input type="date" id="date_to_tn" class="form-control">
                                </div>
                            </div>
                            <button id="fetchOrdersButtonTN" type="button" class="btn btn-primary btn-block">Traer órdenes</button>
                            <hr>
                            <!-- Contenedor para la tabla de órdenes de Tiendanube -->
                            <div id="ordersContainerTN" style="display: none;">
                                <h4>Órdenes obtenidas</h4>
                                <table id="ordersTableTN" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%; text-align: center;">
                                                <input type="checkbox" id="select_all_orders_tn">
                                            </th>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unitario</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Filas agregadas dinámicamente -->
                                    </tbody>
                                </table>
                                <div class="modal-footer px-3">
                                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
                                        <!-- Contenedor Selectores -->
                                        <div class="d-flex flex-wrap gap-3 align-items-center">
                                            <!-- Selector Tasa de IVA -->
                                            <div class="form-group mb-0">
                                                <label for="iva_rate_selector_tn" class="form-label mb-0 small">IVA:</label>
                                                <select id="iva_rate_selector_tn" class="form-control form-control-sm">
                                                    <option value="">Seleccione</option>
                                                    <option value="iva_21">IVA 21%</option>
                                                    <option value="iva_27">IVA 27%</option>
                                                    <option value="iva_10">IVA 10.5%</option>
                                                    <option value="excento_iva">Exento IVA%</option>
                                                </select>
                                            </div>
    
                                            <!-- Selector Punto de Venta -->
                                            <div class="form-group mb-0">
                                                <label for="pto_vta_selector_tn" class="form-label mb-0 small">Punto de Venta:</label>
                                                <select id="pto_vta_selector_tn" class="form-control form-control-sm">
                                                    <option value="">Seleccione</option>
                                                    @for ($i = 1; $i <= 20; $i++)
                                                        <option value="{{ $i }}">Punto {{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
    
                                        <!-- Contenedor Botones -->
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" id="loadTiendanubeArca" class="btn btn-success btn-sm px-3">
                                                Crear Facturas ARCA
                                            </button>
                                            <button type="button" id="loadTiendanubeProforma" class="btn btn-success btn-sm px-3">
                                                Crear Facturas Proforma
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">
                                                Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>  
                </div>
            </div>
        </div>
        <label id="cuitEmpresa" style="display:none;">{{ session('business.tax_number_1') }}</label>
    </div>

@stop

@section('javascript')
    <script src="{{ asset('js/stock_adjustment.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        __page_leave_confirmation('#stock_adjustment_form');
    </script>
    
    <!-- Scripts para MercadoLibre -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let refreshTokenML = @json(auth()->user()->business->mercadolibre_refresh_token ?? null);
        
            if (!refreshTokenML) {
                document.getElementById("authSectionML").style.display = "block";
                document.getElementById("dataSectionML").style.display = "none";
            } else {
                document.getElementById("authSectionML").style.display = "none";
                document.getElementById("dataSectionML").style.display = "block";
            }
        
            const paramsML = new URLSearchParams(window.location.search);
            if (paramsML.has("code")) {
                document.getElementById("authSectionML").style.display = "none";
                document.getElementById("dataSectionML").style.display = "block";
            }
        
            document.getElementById("fetchOrdersButtonML").addEventListener("click", async function(event) {
                mostrarCustomLoader("Cargando ventas Mercado Libre");

                event.preventDefault();
            
                const dateFromML = document.getElementById("date_from_ml").value;
                const dateToML = document.getElementById("date_to_ml").value;
            
                let fetchUrlML = '';
                let payloadML = {};
            
                if (dateFromML || dateToML) {
                    fetchUrlML = '{{ url("/obtener-ordenes-fecha") }}';
                    payloadML = { date_from: dateFromML, date_to: dateToML };
                } else {
                    fetchUrlML = '{{ url("/obtener-ordenes") }}';
                }
            
                try {
                    // Renovar token
                    const renewTokenResponse = await fetch('{{ url("/renovar-token") }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
            
                    const tokenData = await renewTokenResponse.json();
                    if (!tokenData.success) throw new Error("Error al renovar token");
            
                    // Obtener órdenes
                    const ordersResponse = await fetch(fetchUrlML, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: Object.keys(payloadML).length ? JSON.stringify(payloadML) : null
                    });
            
                    const orderData = await ordersResponse.json();
                    if (!orderData.success || !orderData.orders || !orderData.orders.results) {
                        throw new Error("Formato inesperado en la respuesta de órdenes");
                    }
            
                    const ordersWithBillingInfo = await Promise.all(orderData.orders.results.map(async (order) => {
                        try {
                            const billingInfoResponse = await fetch(`{{ route('mercadolibre.billing_info') }}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ order_id: order.id })
                            });
            
                            if (!billingInfoResponse.ok) throw new Error(`No se pudo obtener billing_info de orden ${order.id}`);
                            const billingInfo = await billingInfoResponse.json();
            
                            order.identification = billingInfo?.buyer?.billing_info?.identification || {};
                            order.taxpayer_type = billingInfo?.buyer?.billing_info?.taxes?.taxpayer_type?.description || null;
                            return order;
                        } catch (err) {
                            console.error(`Error con billing_info para orden ${order.id}:`, err);
                            order.identification = {};
                            order.taxpayer_type = null;
                            ocultarCustomLoader();
                            return order;
                        }
                    }));
                    
                    ocultarCustomLoader();
                    // Mostrar datos enriquecidos en consola
                    console.log("Órdenes con datos fiscales:", ordersWithBillingInfo);
            
                    // Cargar tabla visual
                    populateOrdersTableML({ results: ordersWithBillingInfo });
            
                } catch (error) {
                    ocultarCustomLoader();
                    console.error("Error general al obtener órdenes con billing_info:", error);
                    alert("Hubo un error al traer las órdenes. Ver consola.");
                }
            });

            
            function populateOrdersTableML(ordersData) {
                if (!ordersData.results || !Array.isArray(ordersData.results)) {
                    console.error('La respuesta de órdenes no tiene el formato esperado.');
                    return;
                }
            
                const tbodyML = document.querySelector("#ordersTableML tbody");
                tbodyML.innerHTML = "";
            
                ordersData.results.forEach(order => {
                    if (order.order_items && Array.isArray(order.order_items)) {
                        order.order_items.forEach(itemData => {
                            const producto = itemData.item.title;
                            const cantidad = itemData.quantity;
                            const precioUnitario = itemData.unit_price;
                            const sku = itemData.item.seller_sku || itemData.item.id || "-";
            
                            const tr = document.createElement("tr");
                            tr.dataset.order = JSON.stringify(order); // guardamos la orden completa para uso posterior
                            
                            tr.innerHTML = `
                                <td class="text-center">
                                    <input type="checkbox" class="order_checkbox_ml">
                                </td>
                                <td>${producto}</td>
                                <td>${sku}</td>
                                <td>${cantidad}</td>
                                <td>${precioUnitario}</td>
                            `;

                            tbodyML.appendChild(tr);
                        });
                    }
                });
            
                document.getElementById("ordersContainerML").style.display = "block";
            }
        
            document.getElementById("select_all_orders_ml").addEventListener("change", function() {
                const isChecked = this.checked;
                document.querySelectorAll(".order_checkbox_ml").forEach(function(checkbox) {
                    checkbox.checked = isChecked;
                });
            });
        
            document.getElementById("loadMercadolibreArca").addEventListener("click", function(e) {
                mostrarCustomLoader("Esperando respuesta del servidor ARCA");
                e.preventDefault();
                let ordersDataML = [];
                const tbodyML = document.querySelector("#ordersTableML tbody");
                const rowsML = tbodyML.querySelectorAll("tr");
                
                rowsML.forEach(function(row) {
                    const checkbox = row.querySelector(".order_checkbox_ml");
                    if (checkbox && checkbox.checked) {
                        const cells = row.querySelectorAll("td");
                        const product = cells[1].innerText.trim();
                        const sku = cells[2].innerText.trim(); 
                        const quantity = parseFloat(cells[3].innerText.trim());
                        const unit_price = parseFloat(cells[4].innerText.trim());

            
                        ordersDataML.push({
                            invoice_no: "ML-" + new Date().getTime(),
                            customer_name: "MercadoLibre",
                            date: new Date().toISOString().slice(0, 10),
                            product: product,
                            sku: sku,
                            quantity: quantity,
                            unit_price: unit_price,
                            item_tax: 0,
                            item_discount: 0,
                            order_total: quantity * unit_price,
                        });
                    }
                });
                
                if (ordersDataML.length === 0) {
                    alert("Por favor, selecciona al menos una orden para cargar.");
                    return;
                }
                
                fetch("{{ url('/mercadolibre/import-sales') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ orders: ordersDataML })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Ventas de MercadoLibre creadas correctamente");
                    } else {
                        alert("Ocurrió un error: " + data.msg);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Ocurrió un error en la solicitud.");
                });
            });
        });
    </script>
    
    <!-- Scripts para MercadoPago -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let refreshTokenMP = @json(auth()->user()->business->mercadopago_refresh_token ?? null);
        
            if (!refreshTokenMP) {
                document.getElementById("authSectionMP").style.display = "block";
                document.getElementById("dataSectionMP").style.display = "none";
            } else {
                document.getElementById("authSectionMP").style.display = "none";
                document.getElementById("dataSectionMP").style.display = "block";
            }
        
            const paramsMP = new URLSearchParams(window.location.search);
            if (paramsMP.has("code")) {
                document.getElementById("authSectionMP").style.display = "none";
                document.getElementById("dataSectionMP").style.display = "block";
            }
        
            document.getElementById("fetchOrdersButtonMP").addEventListener("click", async function(event) {
                event.preventDefault();
            
                const dateFromMP = document.getElementById("date_from_mp").value;
                const dateToMP = document.getElementById("date_to_mp").value;
            
                let fetchUrlMP = '';
                let payloadMP = {};
            
                if (dateFromMP || dateToMP) {
                    fetchUrlMP = '{{ url("/obtener-ordenes-fecha-mp") }}';
                    payloadMP = { date_from: dateFromMP, date_to: dateToMP };
                } else {
                    fetchUrlMP = '{{ url("/obtener-ordenes-mp") }}';
                }
            
                try {
                    // 1. Renovar token
                    const tokenRes = await fetch('{{ url("/renovar-token-mp") }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
            
                    const tokenData = await tokenRes.json();
                    if (!tokenData.success) throw new Error("Error al renovar token de MercadoPago");
            
                    // 2. Obtener órdenes
                    const ordersRes = await fetch(fetchUrlMP, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: Object.keys(payloadMP).length ? JSON.stringify(payloadMP) : null
                    });
            
                    const ordersJson = await ordersRes.json();
                    if (!ordersJson.success || !ordersJson.orders || !ordersJson.orders.elements) {
                        throw new Error("Formato inesperado en la respuesta de órdenes de MercadoPago");
                    }
            
                    const enrichedOrders = await Promise.all(
                        ordersJson.orders.elements.map(async (order) => {
                            const paymentId = order.main_payment_id || null;
            
                            order.identification = {};
                            order.taxpayer_type = null;
            
                            if (paymentId) {
                                try {
                                    const fiscalRes = await fetch('{{ route("mercadopago.datos_fiscales") }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({ payment_id: paymentId })
                                    });
            
                                    if (fiscalRes.ok) {
                                        const fiscalData = await fiscalRes.json();
                                        order.identification = fiscalData.identification || {};
                                        order.taxpayer_type = fiscalData.taxpayer_type || null;
                                    } else {
                                        console.warn(`No se pudo obtener datos fiscales para payment_id: ${paymentId}`);
                                    }
                                } catch (err) {
                                    console.error("Error al obtener datos fiscales:", err);
                                }
                            }
            
                            return order;
                        })
                    );
            
                    // 3. Mostrar tabla con órdenes enriquecidas
                    populateOrdersTableMP({ elements: enrichedOrders });
            
                } catch (error) {
                    console.error("Error al obtener y enriquecer órdenes:", error);
                    alert("Ocurrió un error al traer las órdenes de MercadoPago. Ver consola.");
                }
            });

        
            function populateOrdersTableMP(ordersData) {
                // Corregir acceso a elements en lugar de results
                if (!ordersData.elements || !Array.isArray(ordersData.elements)) {
                    console.error('La respuesta de órdenes no tiene el formato esperado.');
                    return;
                }
            
                const tbodyMP = document.querySelector("#ordersTableMP tbody");
                tbodyMP.innerHTML = "";
            
                ordersData.elements.forEach(order => {
                    // Los items vienen en order.items (no order.order_items)
                    if (order.items && Array.isArray(order.items)) {
                        order.items.forEach(itemData => {
                            const producto = itemData.title; // Usar title en lugar de item.title
                            const cantidad = itemData.quantity;
                            const precioUnitario = itemData.unit_price;
            
                            const tr = document.createElement("tr");
                            tr.dataset.order = JSON.stringify(order); // Guardamos la orden completa (opcional)
                            tr.dataset.paymentId = order.main_payment_id || ''; // <-- Guardamos el payment_id
                
                            tr.innerHTML = `
                                <td class="text-center">
                                    <input type="checkbox" class="order_checkbox_mp">
                                </td>
                                <td>${producto}</td>
                                <td>${cantidad}</td>
                                <td>${precioUnitario}</td>
                            `;
                            tbodyMP.appendChild(tr);
                        });
                    }
                });
            
                document.getElementById("ordersContainerMP").style.display = "block";
            }
        
            document.getElementById("select_all_orders_mp").addEventListener("change", function() {
                const isChecked = this.checked;
                document.querySelectorAll(".order_checkbox_mp").forEach(function(checkbox) {
                    checkbox.checked = isChecked;
                });
            });
        
            document.getElementById("loadMercadopagoSales").addEventListener("click", function(e) {
                e.preventDefault();
                let ordersDataMP = [];
                const tbodyMP = document.querySelector("#ordersTableMP tbody");
                const rowsMP = tbodyMP.querySelectorAll("tr");
                
                rowsMP.forEach(function(row) {
                    const checkbox = row.querySelector(".order_checkbox_mp");
                    if (checkbox && checkbox.checked) {
                        const cells = row.querySelectorAll("td");
                        const product = cells[1].innerText.trim();
                        const sku = cells[2].innerText.trim(); 
                        const quantity = parseFloat(cells[3].innerText.trim());
                        const unit_price = parseFloat(cells[4].innerText.trim());
            
                        ordersDataMP.push({
                            invoice_no: "MP-" + new Date().getTime(),
                            customer_name: "MercadoPago",
                            date: new Date().toISOString().slice(0, 10),
                            product: product,
                            sku: "",
                            quantity: quantity,
                            unit_price: unit_price,
                            item_tax: 0,
                            item_discount: 0,
                            order_total: quantity * unit_price,
                        });
                    }
                });
                
                if (ordersDataMP.length === 0) {
                    alert("Por favor, selecciona al menos una orden para cargar.");
                    return;
                }
                
                fetch("{{ url('/mercadopago/import-sales') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ orders: ordersDataMP })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Ventas de MercadoPago creadas correctamente");
                    } else {
                        alert("Ocurrió un error: " + data.msg);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Ocurrió un error en la solicitud.");
                });
            });
        });
    </script>
    
    <script>
        document.getElementById("loadMercadolibreArca").addEventListener("click", function () {
            mostrarCustomLoader("Esperando respuesta del servidor ARCA");
            const selectedOrders = [];
            const rows = document.querySelectorAll("#ordersTableML tbody tr");
    
            rows.forEach(function (row) {
                const checkbox = row.querySelector(".order_checkbox_ml");
                if (checkbox && checkbox.checked) {
                    const orderData = JSON.parse(row.dataset.order || '{}'); // ✅ CORRECTO: dentro del forEach
    
                    const cells = row.querySelectorAll("td");
                    const product = cells[1].innerText.trim();
                    const sku = cells[2].innerText.trim(); 
                    const quantity = parseFloat(cells[3].innerText.trim());
                    const unit_price = parseFloat(cells[4].innerText.trim());;
    
                    const selectedIvaRate = document.getElementById("iva_rate_selector").value || "iva_21";
                    const selectedPtoVta = parseInt(document.getElementById("pto_vta_selector").value) || 1;
                    
                    selectedOrders.push({
                        product: product,
                        quantity: quantity,
                        unit_price: unit_price,
                        transaction_date: new Date().toISOString().slice(0, 10),
                        identification: orderData.identification || {},
                        taxpayer_type: orderData.taxpayer_type || null,
                        invoice_iva: selectedIvaRate,
                        pto_vta: selectedPtoVta,
                        order_id: orderData.id
                    });
                }
            });
    
            if (selectedOrders.length === 0) {
                alert("Seleccioná al menos una orden para generar facturas.");
                return;
            }
    
            const cuitEmpresa = document.getElementById("cuitEmpresa").innerText.trim();

            fetch('{{ route("mercadolibre.procesar_facturas_arca") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    orders: selectedOrders,
                    cuit_empresa: cuitEmpresa // ✅ se incluye aquí
                })
            })

            .then(response => {
                if (!response.ok) throw new Error("Error al generar las facturas.");
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = "facturas_mercadolibre.zip";
                document.body.appendChild(a);
                a.click();
                a.remove();
                ocultarCustomLoader();
            })
            .catch(error => {
                ocultarCustomLoader();
                console.error("Error:", error);
                alert("Ocurrió un error al generar las facturas.");
            });
        });
    </script>
    
    <script>
        document.getElementById("loadMercadopagoArca").addEventListener("click", async function () {
            const selectedOrders = [];
            const rows = document.querySelectorAll("#ordersTableMP tbody tr");
        
            const selectedPtoVta = parseInt(document.getElementById("pto_vta_selector").value) || 1;
        
            for (const row of rows) {
                const checkbox = row.querySelector(".order_checkbox_mp");
                if (checkbox && checkbox.checked) {
                    const cells = row.querySelectorAll("td");
                    const product = cells[1].innerText.trim();
                    const sku = cells[2].innerText.trim(); 
                    const quantity = parseFloat(cells[3].innerText.trim());
                    const unit_price = parseFloat(cells[4].innerText.trim());;
        
                    const paymentId = row.dataset.paymentId || null;
        
                    // Llamar al backend para obtener identificación
                    let identification = {};
                    let taxpayer_type = null;
        
                    try {
                        const fiscalRes = await fetch('{{ route("mercadopago.datos_fiscales") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ payment_id: paymentId })
                        });
        
                        if (fiscalRes.ok) {
                            const fiscalData = await fiscalRes.json();
                            identification = fiscalData.identification || {};
                            taxpayer_type = fiscalData.taxpayer_type || null;
                        } else {
                            console.warn(`No se pudo obtener datos fiscales para payment_id: ${paymentId}`);
                        }
                    } catch (err) {
                        console.error("Error al obtener datos fiscales:", err);
                    }
        
                    selectedOrders.push({
                        product: product,
                        quantity: quantity,
                        unit_price: unit_price,
                        transaction_date: new Date().toISOString().slice(0, 10),
                        pto_vta: selectedPtoVta,
                        invoice_iva: "iva_21",
                        payment_id: paymentId,
                        identification: identification,
                        taxpayer_type: taxpayer_type
                    });
                }
            }
        
            if (selectedOrders.length === 0) {
                alert("Seleccioná al menos una orden para generar facturas.");
                return;
            }
        
            const cuitEmpresa = document.getElementById("cuitEmpresa").innerText.trim();

            fetch('{{ route("mercadolibre.procesar_facturas_arca") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    orders: selectedOrders,
                    cuit_empresa: cuitEmpresa // ✅ se incluye aquí
                })
            })

            .then(response => {
                if (!response.ok) throw new Error("Error al generar las facturas.");
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = "facturas_mercadopago.zip";
                document.body.appendChild(a);
                a.click();
                a.remove();
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Ocurrió un error al generar las facturas.");
            });
        });


    </script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let tnAccessToken = @json(auth()->user()->business->tiendanube_access_token ?? null);
        
            // Mostrar sección correcta según si está autorizado
            if (!tnAccessToken) {
                document.getElementById("authSectionTN").style.display = "block";
                document.getElementById("dataSectionTN").style.display = "none";
            } else {
                document.getElementById("authSectionTN").style.display = "none";
                document.getElementById("dataSectionTN").style.display = "block";
            }
        
            // Detectar si llega "code" en la URL (luego de autorizar)
            const paramsTN = new URLSearchParams(window.location.search);
            if (paramsTN.has("code")) {
                document.getElementById("authSectionTN").style.display = "none";
                document.getElementById("dataSectionTN").style.display = "block";
            }
        
            // Traer órdenes Tiendanube
            document.getElementById("fetchOrdersButtonTN").addEventListener("click", async function (event) {
                mostrarCustomLoader("Cargando órdenes Tiendanube...");
                event.preventDefault();
        
                const dateFrom = document.getElementById("date_from_tn").value;
                const dateTo = document.getElementById("date_to_tn").value;
        
                let fetchUrl = '';
                let payload = {};
        
                if (dateFrom || dateTo) {
                    fetchUrl = '{{ url("/obtener-ordenes-tiendanube-fecha") }}';
                    payload = { date_from: dateFrom, date_to: dateTo };
                } else {
                    fetchUrl = '{{ url("/obtener-ordenes-tiendanube") }}';
                }
        
                try {
                    const response = await fetch(fetchUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: Object.keys(payload).length ? JSON.stringify(payload) : null
                    });
        
                    const data = await response.json();
        
                    if (!data.success || !Array.isArray(data.orders)) {
                        throw new Error("Respuesta inválida al obtener órdenes.");
                    }
        
                    populateOrdersTableTN(data.orders);
                    document.getElementById("ordersContainerTN").style.display = "block";
                    ocultarCustomLoader();
        
                } catch (error) {
                    ocultarCustomLoader();
                    console.error("Error al obtener órdenes Tiendanube:", error);
                    alert("Ocurrió un error al traer las órdenes.");
                }
            });
        
            // Marcar/desmarcar todas las órdenes
            document.getElementById("select_all_orders_tn").addEventListener("change", function () {
                const isChecked = this.checked;
                document.querySelectorAll(".order_checkbox_tn").forEach(cb => cb.checked = isChecked);
            });
        
            // Renderizar la tabla con órdenes
            function populateOrdersTableTN(orders) {
                const tbody = document.querySelector("#ordersTableTN tbody");
                tbody.innerHTML = "";
            
                orders.forEach(order => {
                    if (!order.products || !Array.isArray(order.products)) return;
            
                    order.products.forEach(product => {
                        const name = product.name || "(Sin nombre)";
                        const quantity = parseFloat(product.quantity) || 0;
                        const price = parseFloat(product.price) || 0;
            
                        const tr = document.createElement("tr");
                        tr.dataset.order = JSON.stringify(order); // Guarda la orden completa para uso posterior
            
                        tr.innerHTML = `
                            <td class="text-center">
                                <input type="checkbox" class="order_checkbox_tn">
                            </td>
                            <td>${name}</td>
                            <td>${quantity}</td>
                            <td>${price.toFixed(2)}</td>
                        `;
            
                        tbody.appendChild(tr);
                    });
                });
            
                document.getElementById("ordersContainerTN").style.display = "block";
            }

            
        
            // Facturar ARCA
            document.getElementById("loadTiendanubeArca").addEventListener("click", function () {
                procesarFacturasTiendanube('{{ route("tiendanube.procesar_facturas_arca") }}', 'facturas_tiendanube_arca.zip');
            });
        
            // Facturar Proforma
            document.getElementById("loadTiendanubeProforma").addEventListener("click", function () {
                procesarFacturasTiendanube('{{ route("store.proformas.orders") }}', 'facturas_tiendanube_proforma.zip');
            });

        
            async function procesarFacturasTiendanube(endpoint, filename) {
                mostrarCustomLoader("Generando facturas...");
                const selectedOrders = [];
                const rows = document.querySelectorAll("#ordersTableTN tbody tr");
        
                const selectedIvaRate = document.getElementById("iva_rate_selector_tn").value || "iva_21";
                const selectedPtoVta = parseInt(document.getElementById("pto_vta_selector_tn").value) || 1;
        
                rows.forEach(row => {
                    const checkbox = row.querySelector(".order_checkbox_tn");
                    if (checkbox && checkbox.checked) {
                        const orderData = JSON.parse(row.dataset.order || '{}');
                        const cells = row.querySelectorAll("td");
                        const product = cells[1].innerText.trim();
                        const sku = cells[2].innerText.trim(); 
                        const quantity = parseFloat(cells[3].innerText.trim());
                        const unit_price = parseFloat(cells[4].innerText.trim());
        
                        selectedOrders.push({
                            product,
                            quantity,
                            unit_price,
                            transaction_date: new Date().toISOString().slice(0, 10),
                            identification: orderData.identification || {},
                            taxpayer_type: orderData.taxpayer_type || null,
                            invoice_iva: selectedIvaRate,
                            pto_vta: selectedPtoVta
                        });
                    }
                });
        
                if (selectedOrders.length === 0) {
                    ocultarCustomLoader();
                    alert("Seleccioná al menos una orden.");
                    return;
                }
        
                try {
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ orders: selectedOrders })
                    });
        
                    if (!res.ok) throw new Error("Error al generar facturas");
                    const blob = await res.blob();
        
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement("a");
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    ocultarCustomLoader();
                } catch (error) {
                    ocultarCustomLoader();
                    console.error("Error al procesar facturas:", error);
                    alert("Ocurrió un error al generar las facturas.");
                }
            }
        });
    </script>

    <script>
        function mostrarCustomLoader(mensaje) {
            document.getElementById('customLoaderMessage').innerText = mensaje;
            document.getElementById('customLoaderOverlay').style.display = 'block';
        }
    
        function ocultarCustomLoader() {
            document.getElementById('customLoaderOverlay').style.display = 'none';
        }
    </script>


    </script>

    <div id="customLoaderOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(255,255,255,0.85); z-index:9999; text-align:center; padding-top:20%;">
            <div style="font-size:20px; font-weight:bold;" id="customLoaderMessage">Cargando...</div>
    </div>
@endsection

@cannot('view_purchase_price')
    <style>
        .show_price_with_permission {
            display: none !important;
        }
    </style>
@endcannot
