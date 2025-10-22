@extends('layouts.app')

@php
	if (!empty($status) && $status == 'quotation') {
		$title = __('lang_v1.add_quotation');
	} else if (!empty($status) && $status == 'draft') {
		$title = __('lang_v1.add_draft');
	} else {
		$title = __('sale.add_sale');
	}

	if($sale_type == 'sales_order') {
		$title = __('lang_v1.sales_order');
	}
@endphp

@section('title', $title)

@section('content')
<style>
    /* Loader container */
    .loader-container {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        justify-content: center;
        align-items: center;
        flex-direction: column;
        z-index: 9999;
    }

    /* Spinner moderno */
    .loader {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }

    /* Texto del loader */
    .loader-text {
        color: #2c3e50;
        font-size: 1.2em;
        letter-spacing: 1px;
    }
    
    .simple_text {
        font-weight: 450 !important;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .contact_due_text {
        display: block;
        text-align: center;  /* centra el texto */
        margin-top: 5px;
    }

</style>

<!-- Resto de tu contenido actual -->
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{$title}}</h1>
    <div class="tw-text-center tw-mt-2">
        <p>
            ¿Deseas importar ventas de tu 
            <a href="https://app.Trevitsoft.com/stock-adjustments/create" target="_blank" style="color: #007bff; text-decoration: underline;">
                Ecommerce
            </a>?
        </p>
    </div>
</section>
<!-- Main content -->
<section class="content no-print">

<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@if(!empty($pos_settings['allow_overselling']))
	<input type="hidden" id="is_overselling_allowed">
@endif
@if(session('business.enable_rp') == 1)
    <input type="hidden" id="reward_point_enabled">
@endif
@if(count($business_locations) > 0)
<div class="row">
	<div class="col-sm-3">
		<div class="form-group">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-map-marker"></i>
				</span>
			{!! Form::select('select_location_id', $business_locations, $default_location->id ?? null, ['class' => 'form-control input-sm',
			'id' => 'select_location_id', 
			'required', 'autofocus'], $bl_attributes) !!}
			<span class="input-group-addon">
					@show_tooltip(__('tooltip.sale_location'))
				</span> 
			</div>
		</div>
	</div>
</div>
@endif
@php
	$custom_labels = json_decode(session('business.custom_labels'), true);
	$common_settings = session()->get('business.common_settings');
@endphp
<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
	{!! Form::open(['url' => action([\App\Http\Controllers\SellPosController::class, 'store']), 'method' => 'post', 'id' => 'add_sell_form', 'files' => true ]) !!}
	 @if(!empty($sale_type))
	 	<input type="hidden" id="sale_type" name="type" value="{{$sale_type}}">
	 @endif
	<div class="row">
		<div class="col-md-12 col-sm-12">
			@component('components.widget', ['class' => 'box-solid'])
				{!! Form::hidden('location_id', !empty($default_location) ? $default_location->id : null , ['id' => 'location_id', 'data-receipt_printer_type' => !empty($default_location->receipt_printer_type) ? $default_location->receipt_printer_type : 'browser', 'data-default_payment_accounts' => !empty($default_location) ? $default_location->default_payment_accounts : '']); !!}

				@if(!empty($price_groups))
					@if(count($price_groups) > 1)
						<div class="col-sm-4">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-money-bill-alt"></i>
									</span>
									@php
										reset($price_groups);
										$selected_price_group = !empty($default_price_group_id) && array_key_exists($default_price_group_id, $price_groups) ? $default_price_group_id : null;
									@endphp
									{!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
									{!! Form::select('price_group', $price_groups, $selected_price_group, ['class' => 'form-control select2', 'id' => 'price_group']); !!}
									<span class="input-group-addon">
										@show_tooltip(__('lang_v1.price_group_help_text'))
									</span>
								</div>
							</div>
						</div>
						
					@else
						@php
							reset($price_groups);
						@endphp
						{!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
					@endif
				@endif

				{!! Form::hidden('default_price_group', null, ['id' => 'default_price_group']) !!}

				@if(in_array('types_of_service', $enabled_modules) && !empty($types_of_service))
					<div class="col-md-4 col-sm-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fa fa-external-link-square-alt text-primary service_modal_btn"></i>
								</span>
								{!! Form::select('types_of_service_id', $types_of_service, null, ['class' => 'form-control', 'id' => 'types_of_service_id', 'style' => 'width: 100%;', 'placeholder' => __('lang_v1.select_types_of_service')]); !!}

								{!! Form::hidden('types_of_service_price_group', null, ['id' => 'types_of_service_price_group']) !!}

								<span class="input-group-addon">
									@show_tooltip(__('lang_v1.types_of_service_help'))
								</span>
							</div>
							<small><p class="help-block hide" id="price_group_text">@lang('lang_v1.price_group'): <span></span></p></small>
						</div>
					</div>
				@endif
				
				@if(in_array('subscription', $enabled_modules))
					<div class="col-md-4 pull-right col-sm-6">
						<div class="checkbox">
							<label>
				              {!! Form::checkbox('is_recurring', 1, false, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.subscribe')?
				            </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link"><i class="fa fa-external-link"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
						</div>
					</div>
				@endif
				<div class="clearfix"></div>
				<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
					<div class="form-group">
						{!! Form::label('contact_id', __('contact.customer') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-user"></i>
							</span>
							<input type="hidden" id="default_customer_id" 
							value="{{ $walk_in_customer['id']}}" >
							<input type="hidden" id="default_customer_name" 
							value="{{ $walk_in_customer['name']}}" >
							<input type="hidden" id="default_customer_balance" value="{{ $walk_in_customer['balance'] ?? ''}}" >
							<input type="hidden" id="default_customer_address" value="{{ $walk_in_customer['shipping_address'] ?? ''}}" >
							@if(!empty($walk_in_customer['price_calculation_type']) && $walk_in_customer['price_calculation_type'] == 'selling_price_group')
								<input type="hidden" id="default_selling_price_group" 
							value="{{ $walk_in_customer['selling_price_group_id'] ?? ''}}" >
							@endif
							{!! Form::select('contact_id', 
								[], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required']); !!}
							<span class="input-group-btn">
								<button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
							</span>
						</div>
						<small class="text-danger hide contact_due_text"><strong>@lang('account.customer_due'):</strong> <span></span></small>
					</div>
					<small>
					<div class="form-group">
						<label class="simple_text" for="business_name_show">Business Name:</label>
						<div class="form-control">
							{{ $business_details->name ?? 'No business name available' }}
						</div>
					</div>
					
					</small>
				</div>

				<div class="col-md-3">
		          <div class="form-group">
		            <div class="multi-input">
		            @php
						$is_pay_term_required = !empty($pos_settings['is_pay_term_required']);
					@endphp
		              {!! Form::label('pay_term_number', __('contact.pay_term') . ':', ['class' => 'simple_text'])!!} @show_tooltip(__('tooltip.pay_term'))
		              <br/>
		              {!! Form::number('pay_term_number', 30, [
                            'class' => 'form-control width-40 pull-left', 
                            'placeholder' => __('contact.pay_term'), 
                            'required' => $is_pay_term_required
                        ]); !!}
                        
                        {!! Form::select('pay_term_type', 
                            ['months' => __('lang_v1.months'), 'days' => __('lang_v1.days')], 
                            'days', [
                            'class' => 'form-control width-60 pull-left',
                            'placeholder' => __('messages.please_select'), 
                            'required' => $is_pay_term_required
                        ]); !!}

		            </div>
		          </div>
		        </div>

				@if(!empty($commission_agent))
				@php
					$is_commission_agent_required = !empty($pos_settings['is_commission_agent_required']);
				@endphp
				<div class="col-sm-3">
					<div class="form-group">
					{!! Form::label('commission_agent', __('lang_v1.commission_agent') . ':', ['class' => 'simple_text']) !!}
					{!! Form::select('commission_agent', 
								$commission_agent, null, ['class' => 'form-control select2', 'id' => 'commission_agent', 'required' => $is_commission_agent_required]); !!}
					</div>
				</div>
				@endif
				<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
					<div class="form-group">
						{!! Form::label('transaction_date', __('sale.sale_date') . ':*', ['class' => 'simple_text']) !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
							{!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']); !!}
						</div>
					</div>
				</div>
				<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
                <div class="form-group">
                    {!! Form::label('payment_due_date_general', 'Fecha de Vencimiento de Pago:', ['class' => 'simple_text']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        <input type="datetime-local" class="form-control" name="payment_due_date_general" id="payment_due_date_general">
                    </div>
                </div>
            </div>



				
				@if(!empty($status))
					<input type="hidden" name="status" id="status" value="{{$status}}">

					@if(in_array($status, ['draft', 'quotation']))
						<input type="hidden" id="disable_qty_alert">
					@endif
				@else
					<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
						<div class="form-group">
							{!! Form::label('status', __('sale.status') . ':*', ['class' => 'simple_text']) !!}
							{!! Form::select('status', $statuses, 'final', ['class' => 'form-control select2', 'required']); !!}

						</div>
					</div>
				@endif
				@if($sale_type != 'sales_order')
					<div class="col-sm-3" style="display:none;">
						<div class="form-group">
							{!! Form::label('invoice_scheme_id', __('invoice.invoice_scheme') . ':', ['class' => 'simple_text']) !!}
							{!! Form::select('invoice_scheme_id', $invoice_schemes, $default_invoice_schemes->id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
						</div>
					</div>
				@endif
					@can('edit_invoice_number')
					<div class="col-sm-3" style="display:none;">
						<div class="form-group">
							{!! Form::label('invoice_no', $sale_type == 'sales_order' ? __('restaurant.order_no') : __('sale.invoice_no') . ':', ['class' => 'simple_text']) !!}
							{!! Form::text('invoice_no', null, ['class' => 'form-control', 'placeholder' => $sale_type == 'sales_order' ? __('restaurant.order_no') : __('sale.invoice_no')]); !!}
							<p class="help-block">@lang('lang_v1.keep_blank_to_autogenerate')</p>
						</div>
					</div>
					@endcan
					
                <div class="col-sm-3">
	                <div class="form-group" style="display:none;">
	                    {!! Form::label('upload_document', __('purchase.attach_document') . ':', ['class' => 'simple_text']) !!}
	                    {!! Form::file('sell_document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
	                    <p class="help-block">
	                    	@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
	                    	@includeIf('components.document_help_text')
	                    </p>
	                </div>
	            </div>
		
		
        <div class="row justify-content-center block-center">
          <div class="col-md-6">
            <div class="form-group text-center">
              <label class="simple_text">@lang('¿Desea generar factura ARCA?')</label>
              <select class="form-control" id="generate_invoice_select">
                <option value="No">@lang('No')</option>
                <option value="Si" selected>@lang('Sí')</option>
              </select>
            </div>
          </div>
        </div>



		<div id="additional_invoice_fields" class="col-md-12">
			<div id="invoiceForm">
				<div class="row">
					<!-- Tipo de Factura -->
					<div class="col-sm-4">
						<div class="form-group">
							<label class="simple_text" for="invoice_type">Tipo de Factura:*</label>
							<select id="invoice_type" name="invoice_type" class="form-control select2">
								<option value="">Seleccione</option>
								<option value="Factura A">Factura A</option>
								<option value="Factura B">Factura B</option>
								<option value="Factura C">Factura C</option>
								<option value="Nota de Credito A">Nota de Credito A</option>
								<option value="Nota de Credito B">Nota de Credito B</option>
								<option value="Nota de Credito C">Nota de Credito C</option>
								<option value="Recibo A">Recibo A</option>
								<option value="Recibo B">Recibo B</option>
								<option value="Recibo C">Recibo C</option>
							</select>
						</div>
					</div>

					<!-- Tipo de Documento del Comprador -->
					<div class="col-sm-4">
						<div class="form-group">
							<label class="simple_text" for="buyer_document_type">Tipo de Documento del Comprador:*</label>
							<select id="buyer_document_type" name="buyer_document_type" class="form-control select2">
								<option value="CUIT">CUIT</option>
								<option value="CUIL">CUIL</option>
								<option value="DNI">DNI</option>
								<option value="ConsumidorFinal">Consumidor final</option>
							</select>
						</div>
					</div>

					<!-- Número de Documento -->
					<div class="col-sm-4">
                        <div class="form-group">
                            <label class="simple_text" for="buyer_document_number">Número de Documento (CUIT/CUIL):*</label>
                            <input
                                type="text"
                                id="buyer_document_number"
                                name="buyer_document_number"
                                class="form-control"
                                placeholder="Ingrese el número de documento"
                                pattern="\d*"
                                value="{{ old('buyer_document_number', $contact->tax_number ?? '') }}"
                            />
                        </div>
                    </div>

					<!-- Concepto de la Factura -->
					<div class="col-sm-4">
						<div class="form-group">
							<label class="simple_text" for="invoice_concept">Concepto de la Factura:*</label>
							<select id="invoice_concept" name="invoice_concept" class="form-control select2">
								<option value="productos">Productos</option>
								<option value="servicios">Servicios</option>
								<option value="productos_y_servicios">Productos y Servicios</option>
							</select>
						</div>
					</div>
					
					<div class="col-sm-4">
						<div class="form-group" id="iva_receptor_text" >
							<label class="simple_text" for="iva_receptor">Condicion IVA receptor:*</label>
							<select id="iva_receptor" name="iva_receptor" class="form-control select2" style:"display: none">
								<option value="">Seleccione</option>
								<option value="1">IVA Responsable Inscripto</option>
								<option value="6">Responsable Monotributo</option>
								<option value="13">Monotributista Social</option>
								<option value="16">Monotributo Trabajador Independiente Promovido</option>
								<option value="4">IVA Sujeto Exento</option>
								<option value="5">Consumidor Final</option>
								<option value="7">Sujeto No Categorizado</option>
								<option value="8">Proveedor del exterior</option>
								<option value="9">Cliente del exterior</option>
								<option value="10">IVA Liberado – Ley N° 19.640</option>
								<option value="15">IVA No Alcanzado</option>
							</select>
						</div>
					</div>
					
					<div class="col-sm-4">
						<div class="form-group" id="iva_receptor_text" >
							<label class="simple_text" for="pto_vta">Punto de venta:*</label>
							<select id="pto_vta" name="iva_receptor" class="form-control select2" style:"display: none">
								<option value="">Seleccione</option>
								<option value="1">Punto de venta 1</option>
								<option value="2">Punto de venta 2</option>
								<option value="3">Punto de venta 3</option>
								<option value="4">Punto de venta 4</option>
								<option value="5">Punto de venta 5</option>
								<option value="6">Punto de venta 6</option>
								<option value="7">Punto de venta 7</option>
								<option value="8">Punto de venta 8</option>
								<option value="9">Punto de venta 9</option>
								<option value="10">Punto de venta 10</option>
								<option value="11">Punto de venta 11</option>
								<option value="12">Punto de venta 12</option>
								<option value="13">Punto de venta 13</option>
								<option value="14">Punto de venta 14</option>
								<option value="15">Punto de venta 15</option>
							</select>
						</div>
					</div>
					
					<!-- Cantidades IVA -->
					<div class="col-sm-4">
						<div class="form-group" id="invoice_iva_text" >
							<label class="simple_text" for="invoice_iva">Tipo impositivo de IVA:*</label>
							<select id="invoice_iva" name="invoice_iva" class="form-control select2" style:"display: none">
								<option value="">Seleccione</option>
								<option value="iva_21">IVA 21%</option>
								<option value="iva_27">IVA 27%</option>
								<option value="iva_10">IVA 10.5%</option>
								<option value="excento_iva">Excento IVA%</option>
							</select>
						</div>
					</div>
					
					<div class="col-sm-4">
						<div class="form-group" id="num_factura">
							<label class="simple_text" for="num_factura">Numero de la Factura a asociar: </label>
							<input type="text" id="num_factura_val" name="num_factura_val" class="form-control" />
						</div>
					</div>
					
					<div class="col-sm-4">
						<div class="form-group" id="cbu_cliente">
							<label class="simple_text" for="cbu_cliente">CBU del Cliente: </label>
							<input type="text" id="cbu_cliente_val" name="cbu_cliente_val" class="form-control" />
						</div>
					</div>
					
					<div class="col-sm-4">
						<div class="form-group" id="tipo_transferencia">
							<label class="simple_text" for="tipo_transferencia">Tipo de Transferencia:*</label>
							<select id="tipo_transferencia" name="tipo_transferencia" class="form-control select2" style:"display: none">
								<option value="">Seleccione</option>
								<option value="SCA">Transferencia al Sistema de Circulacion Abierta</option>
								<option value="ADC">Agente de Deposito Colectivo</option>
							</select>
						</div>
					</div>
					
					<!-- Fechas -->
					<div class="col-sm-4" id="start_date">
						<div class="form-group">
							<label class="simple_text" for="start_date">Fecha de Inicio:</label>
							<input type="date" id="start_date_val" name="start_date_val" class="form-control" />
						</div>
					</div>

					<div class="col-sm-4" id="end_date">
						<div class="form-group">
							<label class="simple_text" for="end_date">Fecha de Fin de Servicio:</label>
							<input type="date" id="end_date_val" name="end_date_val" class="form-control" />
						</div>
					</div>

					<div class="col-sm-4" id="payment_due_date">
						<div class="form-group">
							<label class="simple_text" for="payment_due_date">Fecha de Vencimiento del Pago:</label>
							<input type="date" id="payment_due_date_val" name="payment_due_date_val" class="form-control" />
						</div>
					</div>
					
					
				</div>
			</div>
		</div>

		
				
				@php
			        $custom_field_1_label = !empty($custom_labels['sell']['custom_field_1']) ? $custom_labels['sell']['custom_field_1'] : '';

			        $is_custom_field_1_required = !empty($custom_labels['sell']['is_custom_field_1_required']) && $custom_labels['sell']['is_custom_field_1_required'] == 1 ? true : false;

			        $custom_field_2_label = !empty($custom_labels['sell']['custom_field_2']) ? $custom_labels['sell']['custom_field_2'] : '';

			        $is_custom_field_2_required = !empty($custom_labels['sell']['is_custom_field_2_required']) && $custom_labels['sell']['is_custom_field_2_required'] == 1 ? true : false;

			        $custom_field_3_label = !empty($custom_labels['sell']['custom_field_3']) ? $custom_labels['sell']['custom_field_3'] : '';

			        $is_custom_field_3_required = !empty($custom_labels['sell']['is_custom_field_3_required']) && $custom_labels['sell']['is_custom_field_3_required'] == 1 ? true : false;

			        $custom_field_4_label = !empty($custom_labels['sell']['custom_field_4']) ? $custom_labels['sell']['custom_field_4'] : '';

			        $is_custom_field_4_required = !empty($custom_labels['sell']['is_custom_field_4_required']) && $custom_labels['sell']['is_custom_field_4_required'] == 1 ? true : false;
		        @endphp
		        @if(!empty($custom_field_1_label))
		        	@php
		        		$label_1 = $custom_field_1_label . ':';
		        		if($is_custom_field_1_required) {
		        			$label_1 .= '*';
		        		}
		        	@endphp

		        	<div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('custom_field_1', $label_1 ) !!}
				            {!! Form::text('custom_field_1', null, ['class' => 'form-control','placeholder' => $custom_field_1_label, 'required' => $is_custom_field_1_required]); !!}
				        </div>
				    </div>
		        @endif
		        @if(!empty($custom_field_2_label))
		        	@php
		        		$label_2 = $custom_field_2_label . ':';
		        		if($is_custom_field_2_required) {
		        			$label_2 .= '*';
		        		}
		        	@endphp

		        	<div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('custom_field_2', $label_2 ) !!}
				            {!! Form::text('custom_field_2', null, ['class' => 'form-control','placeholder' => $custom_field_2_label, 'required' => $is_custom_field_2_required]); !!}
				        </div>
				    </div>
		        @endif
		        @if(!empty($custom_field_3_label))
		        	@php
		        		$label_3 = $custom_field_3_label . ':';
		        		if($is_custom_field_3_required) {
		        			$label_3 .= '*';
		        		}
		        	@endphp

		        	<div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('custom_field_3', $label_3 ) !!}
				            {!! Form::text('custom_field_3', null, ['class' => 'form-control','placeholder' => $custom_field_3_label, 'required' => $is_custom_field_3_required]); !!}
				        </div>
				    </div>
		        @endif
		        @if(!empty($custom_field_4_label))
		        	@php
		        		$label_4 = $custom_field_4_label . ':';
		        		if($is_custom_field_4_required) {
		        			$label_4 .= '*';
		        		}
		        	@endphp

		        	<div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('custom_field_4', $label_4 ) !!}
				            {!! Form::text('custom_field_4', null, ['class' => 'form-control','placeholder' => $custom_field_4_label, 'required' => $is_custom_field_4_required]); !!}
				        </div>
				    </div>
		        @endif
		        <div class="clearfix"></div>

		        @if((!empty($pos_settings['enable_sales_order']) && $sale_type != 'sales_order') || $is_order_request_enabled)
					<div class="col-sm-3">
						<div class="form-group">
							{!! Form::label('sales_order_ids', __('lang_v1.sales_order').':') !!}
							{!! Form::select('sales_order_ids[]', [], null, ['class' => 'form-control select2', 'multiple', 'id' => 'sales_order_ids']); !!}
						</div>
					</div>
					<div class="clearfix"></div>
				@endif
				<!-- Call restaurant module if defined -->
		        @if(in_array('tables' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
		        	<span id="restaurant_module_span">
		        	</span>
		        @endif
			@endcomponent

            @component('components.widget', ['class' => 'box-solid'])
                <div class="col-sm-10 col-sm-offset-1">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fas fa-search-plus"></i></button>
                            </div>
                            {!! Form::text('search_product', null, [
                                'class' => 'form-control mousetrap',
                                'id' => 'search_product',
                                'placeholder' => __('lang_v1.search_product_placeholder'),
                                'disabled' => is_null($default_location)? true : false,
                                'autofocus' => is_null($default_location)? false : true,
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action([\App\Http\Controllers\ProductController::class, 'quickAdd'])}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
            
                <div class="row col-sm-12 pos_product_div" style="min-height: 0">
                    <input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">
            
                    <!-- Keeps count of product rows -->
                    <input type="hidden" id="product_row_count" value="0">
            
                    @php
                        $hide_tax = '';
                        if( session()->get('business.enable_inline_tax') == 0){
                            $hide_tax = 'hide';
                        }
                    @endphp
            
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
                            <thead>
                                <tr>
                                    <th class="text-center" id="product_header">	
                                        @lang('sale.product')
                                    </th>
                                    <th class="text-center" id="qty_header">
                                        @lang('sale.qty')
                                    </th>
                                    @if(!empty($pos_settings['inline_service_staff']))
                                        <th class="text-center" id="service_staff_header">
                                            @lang('restaurant.service_staff')
                                        </th>
                                    @endif
                                    <th class="@if(!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif" id="unit_price_header">
                                        @lang('sale.unit_price')
                                    </th>
                                    <th class="@if(!auth()->user()->can('edit_product_discount_from_sale_screen')) hide @endif" id="discount_header">
                                        @lang('receipt.discount')
                                    </th>
                                    <th class="text-center {{$hide_tax}}" id="tax_header">
                                        @lang('sale.tax')
                                    </th>
                                    <th class="text-center {{$hide_tax}}" id="price_inc_tax_header">
                                        @lang('sale.price_inc_tax')
                                    </th>
                                    @if(!empty($common_settings['enable_product_warranty']))
                                        <th id="warranty_header">@lang('lang_v1.warranty')</th>
                                    @endif
                                    <th class="text-center" id="subtotal_header">
                                        @lang('sale.subtotal')
                                    </th>
                                    <th class="text-center" id="remove_header"><i class="fas fa-times" aria-hidden="true"></i></th>
                                </tr>
                            </thead>
                            <tbody id="products_tbody"></tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered table-striped">
                            <tr>
                                <td>
                                    <div class="pull-right">
                                        <b>@lang('sale.item'):</b> 
                                        <span class="total_quantity" id="total_quantity">0</span>
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <b class="importe" id="importe">0</b>
                                        <span class="price_total" id="price_total">0</span>
                                    </div>
                            <tr id="iva_row" style="display: none;">
                                <td>
                                    <div class="pull-right">
                                        <b>IVA:</b>
                                        <span id="iva_result">0</span>
                                    </div>
                                </td>
                            </tr>
                            <tr id="total_row" style="display: none;">
                                <td>                            
                                    <div class="pull-right">
                                        <b>Importe Total:</b>
                                        <span id="total_amount">0</span>
                                    </div>
                                </td>                            
                                </td>
                            </tr>
                            </tr>
                        </table>
                    </div>
                </div>
            @endcomponent

			@component('components.widget', ['class' => 'box-solid'])
            
            	<button type="button" id="toggle_discount_section" class="btn btn-primary btn-block" style="margin-bottom: 15px;">
            		Descuentos
            	</button>
            
            	<div id="discount_section" style="display: none;">
            		<div class="col-md-4  @if($sale_type == 'sales_order') hide @endif">
            	        <div class="form-group">
            	            {!! Form::label('discount_type', __('sale.discount_type') . ':*' ) !!}
            	            <div class="input-group">
            	                <span class="input-group-addon">
            	                    <i class="fa fa-info"></i>
            	                </span>
            	                {!! Form::select('discount_type', ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')], 'percentage' , ['class' => 'form-control','placeholder' => __('messages.please_select'), 'required', 'data-default' => 'percentage']); !!}
            	            </div>
            	        </div>
            	    </div>
            
            	    @php
            	    	$max_discount = !is_null(auth()->user()->max_sales_discount_percent) ? auth()->user()->max_sales_discount_percent : '';
            	    	$sales_discount = $business_details->default_sales_discount;
            	    	if($max_discount != '' && $sales_discount > $max_discount) $sales_discount = $max_discount;
            	    	$default_sales_tax = $business_details->default_sales_tax;
            	    	if($sale_type == 'sales_order') {
            	    		$sales_discount = 0;
            	    		$default_sales_tax = null;
            	    	}
            	    @endphp
            
            	    <div class="col-md-4 @if($sale_type == 'sales_order') hide @endif">
            	        <div class="form-group">
            	            {!! Form::label('discount_amount', __('sale.discount_amount') . ':*' ) !!}
            	            <div class="input-group">
            	                <span class="input-group-addon">
            	                    <i class="fa fa-info"></i>
            	                </span>
            	                {!! Form::text('discount_amount', @num_format($sales_discount), ['class' => 'form-control input_number', 'data-default' => $sales_discount, 'data-max-discount' => $max_discount, 'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', ['discount' => $max_discount != '' ? @num_format($max_discount) : '']) ]); !!}
            	            </div>
            	        </div>
            	    </div>
            
            	    <div class="col-md-4 @if($sale_type == 'sales_order') hide @endif"><br>
            	    	<b>@lang( 'sale.discount_amount' ):</b>(-) 
            			<span class="display_currency" id="total_discount">0</span>
            	    </div>
            
            	    <div class="clearfix"></div>
            
            	    <div class="col-md-12 well well-sm bg-light-gray @if(session('business.enable_rp') != 1 || $sale_type == 'sales_order') hide @endif">
            	    	<input type="hidden" name="rp_redeemed" id="rp_redeemed" value="0">
            	    	<input type="hidden" name="rp_redeemed_amount" id="rp_redeemed_amount" value="0">
            	    	<div class="col-md-12"><h4>{{session('business.rp_name')}}</h4></div>
            	    	<div class="col-md-4">
            		        <div class="form-group">
            		            {!! Form::label('rp_redeemed_modal', __('lang_v1.redeemed') . ':' ) !!}
            		            <div class="input-group">
            		                <span class="input-group-addon">
            		                    <i class="fa fa-gift"></i>
            		                </span>
            		                {!! Form::number('rp_redeemed_modal', 0, ['class' => 'form-control direct_sell_rp_input', 'data-amount_per_unit_point' => session('business.redeem_amount_per_unit_rp'), 'min' => 0, 'data-max_points' => 0, 'data-min_order_total' => session('business.min_order_total_for_redeem') ]); !!}
            		                <input type="hidden" id="rp_name" value="{{session('business.rp_name')}}">
            		            </div>
            		        </div>
            		    </div>
            		    <div class="col-md-4">
            		    	<p><strong>@lang('lang_v1.available'):</strong> <span id="available_rp">0</span></p>
            		    </div>
            		    <div class="col-md-4">
            		    	<p><strong>@lang('lang_v1.redeemed_amount'):</strong> (-)<span id="rp_redeemed_amount_text">0</span></p>
            		    </div>
            	    </div>
            
            	    <div class="clearfix"></div>
            
            	    <div class="col-md-4">
            	    	<div class="form-group">
            	            {!! Form::label('tax_rate_id', __('sale.order_tax') . ':*' ) !!}
            	            <div class="input-group">
            	                <span class="input-group-addon">
            	                    <i class="fa fa-info"></i>
            	                </span>
            	                {!! Form::select('tax_rate_id', $taxes['tax_rates'], $default_sales_tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control', 'data-default'=> $default_sales_tax], $taxes['attributes']); !!}
            
            					<input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" 
            					value="@if(empty($edit)) {{@num_format($business_details->tax_calculation_amount)}} @else {{@num_format($transaction->tax?->amount)}} @endif" data-default="{{$business_details->tax_calculation_amount}}">
            	            </div>
            	        </div>
            	    </div>
            
            	    <div class="col-md-4 col-md-offset-4">
            	    	<b>@lang( 'sale.order_tax' ):</b>(+) 
            			<span class="display_currency" id="order_tax">0</span>
            	    </div>				
            
            	    <div class="col-md-12">
            	    	<div class="form-group">
            				{!! Form::label('sell_note',__('sale.sell_note')) !!}
            				{!! Form::textarea('sale_note', null, ['class' => 'form-control', 'rows' => 3]); !!}
            			</div>
            	    </div>
            
            		<input type="hidden" name="is_direct_sale" value="1">
            	</div> {{-- cierre de #discount_section --}}
            
            @endcomponent
            
    		@component('components.widget', ['class' => 'box-solid'])
    			<button type="button" id="toggle_shipping_section" class="btn btn-primary btn-block" style="margin-bottom: 15px;">
                	Envío
                </button>
    
                <div id="shipping_section" style="display: none;">
    			<div class="col-md-4">
    				<div class="form-group">
    		            {!! Form::label('shipping_details', __('sale.shipping_details')) !!}
    		            {!! Form::textarea('shipping_details',null, ['class' => 'form-control','placeholder' => __('sale.shipping_details') ,'rows' => '3', 'cols'=>'30']); !!}
    		        </div>
    			</div>
    			<div class="col-md-4">
    				<div class="form-group">
    		            {!! Form::label('shipping_address', __('lang_v1.shipping_address')) !!}
    		            {!! Form::textarea('shipping_address',null, ['class' => 'form-control','placeholder' => __('lang_v1.shipping_address') ,'rows' => '3', 'cols'=>'30']); !!}
    		        </div>
    			</div>
    			<div class="col-md-4">
    				<div class="form-group">
    					{!!Form::label('shipping_charges', __('sale.shipping_charges'))!!}
    					<div class="input-group">
    					<span class="input-group-addon">
    					<i class="fa fa-info"></i>
    					</span>
    					{!!Form::text('shipping_charges',@num_format(0.00),['class'=>'form-control input_number','placeholder'=> __('sale.shipping_charges')]);!!}
    					</div>
    				</div>
    			</div>
    			<div class="clearfix"></div>
    			<div class="col-md-4">
    				<div class="form-group">
    		            {!! Form::label('shipping_status', __('lang_v1.shipping_status')) !!}
    		            {!! Form::select('shipping_status',$shipping_statuses, null, ['class' => 'form-control','placeholder' => __('messages.please_select')]); !!}
    		        </div>
    			</div>
    			<div class="col-md-4">
    		        <div class="form-group">
    		            {!! Form::label('delivered_to', __('lang_v1.delivered_to') . ':' ) !!}
    		            {!! Form::text('delivered_to', null, ['class' => 'form-control','placeholder' => __('lang_v1.delivered_to')]); !!}
    		        </div>
    		    </div>
    			<div class="col-md-4">
    				<div class="form-group">
    					{!! Form::label('delivery_person', __('lang_v1.delivery_person') . ':' ) !!}
    					{!! Form::select('delivery_person', $users, null, ['class' => 'form-control select2','placeholder' => __('messages.please_select')]); !!}
    				</div>
    			</div>
    		    @php
    		        $shipping_custom_label_1 = !empty($custom_labels['shipping']['custom_field_1']) ? $custom_labels['shipping']['custom_field_1'] : '';
    
    		        $is_shipping_custom_field_1_required = !empty($custom_labels['shipping']['is_custom_field_1_required']) && $custom_labels['shipping']['is_custom_field_1_required'] == 1 ? true : false;
    
    		        $shipping_custom_label_2 = !empty($custom_labels['shipping']['custom_field_2']) ? $custom_labels['shipping']['custom_field_2'] : '';
    
    		        $is_shipping_custom_field_2_required = !empty($custom_labels['shipping']['is_custom_field_2_required']) && $custom_labels['shipping']['is_custom_field_2_required'] == 1 ? true : false;
    
    		        $shipping_custom_label_3 = !empty($custom_labels['shipping']['custom_field_3']) ? $custom_labels['shipping']['custom_field_3'] : '';
    		        
    		        $is_shipping_custom_field_3_required = !empty($custom_labels['shipping']['is_custom_field_3_required']) && $custom_labels['shipping']['is_custom_field_3_required'] == 1 ? true : false;
    
    		        $shipping_custom_label_4 = !empty($custom_labels['shipping']['custom_field_4']) ? $custom_labels['shipping']['custom_field_4'] : '';
    		        
    		        $is_shipping_custom_field_4_required = !empty($custom_labels['shipping']['is_custom_field_4_required']) && $custom_labels['shipping']['is_custom_field_4_required'] == 1 ? true : false;
    
    		        $shipping_custom_label_5 = !empty($custom_labels['shipping']['custom_field_5']) ? $custom_labels['shipping']['custom_field_5'] : '';
    		        
    		        $is_shipping_custom_field_5_required = !empty($custom_labels['shipping']['is_custom_field_5_required']) && $custom_labels['shipping']['is_custom_field_5_required'] == 1 ? true : false;
    	        @endphp
    
    	        @if(!empty($shipping_custom_label_1))
    	        	@php
    	        		$label_1 = $shipping_custom_label_1 . ':';
    	        		if($is_shipping_custom_field_1_required) {
    	        			$label_1 .= '*';
    	        		}
    	        	@endphp
    
    	        	<div class="col-md-4">
    			        <div class="form-group">
    			            {!! Form::label('shipping_custom_field_1', $label_1 ) !!}
    			            {!! Form::text('shipping_custom_field_1', !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_1']) ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_1'] : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_1, 'required' => $is_shipping_custom_field_1_required]); !!}
    			        </div>
    			    </div>
    	        @endif
    	        @if(!empty($shipping_custom_label_2))
    	        	@php
    	        		$label_2 = $shipping_custom_label_2 . ':';
    	        		if($is_shipping_custom_field_2_required) {
    	        			$label_2 .= '*';
    	        		}
    	        	@endphp
    
    	        	<div class="col-md-4">
    			        <div class="form-group">
    			            {!! Form::label('shipping_custom_field_2', $label_2 ) !!}
    			            {!! Form::text('shipping_custom_field_2', !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_2']) ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_2'] : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_2, 'required' => $is_shipping_custom_field_2_required]); !!}
    			        </div>
    			    </div>
    	        @endif
    	        @if(!empty($shipping_custom_label_3))
    	        	@php
    	        		$label_3 = $shipping_custom_label_3 . ':';
    	        		if($is_shipping_custom_field_3_required) {
    	        			$label_3 .= '*';
    	        		}
    	        	@endphp
    
    	        	<div class="col-md-4">
    			        <div class="form-group">
    			            {!! Form::label('shipping_custom_field_3', $label_3 ) !!}
    			            {!! Form::text('shipping_custom_field_3', !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_3']) ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_3'] : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_3, 'required' => $is_shipping_custom_field_3_required]); !!}
    			        </div>
    			    </div>
    	        @endif
    	        @if(!empty($shipping_custom_label_4))
    	        	@php
    	        		$label_4 = $shipping_custom_label_4 . ':';
    	        		if($is_shipping_custom_field_4_required) {
    	        			$label_4 .= '*';
    	        		}
    	        	@endphp
    
    	        	<div class="col-md-4">
    			        <div class="form-group">
    			            {!! Form::label('shipping_custom_field_4', $label_4 ) !!}
    			            {!! Form::text('shipping_custom_field_4', !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_4']) ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_4'] : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_4, 'required' => $is_shipping_custom_field_4_required]); !!}
    			        </div>
    			    </div>
    	        @endif
    	        @if(!empty($shipping_custom_label_5))
    	        	@php
    	        		$label_5 = $shipping_custom_label_5 . ':';
    	        		if($is_shipping_custom_field_5_required) {
    	        			$label_5 .= '*';
    	        		}
    	        	@endphp
    
    	        	<div class="col-md-4">
    			        <div class="form-group">
    			            {!! Form::label('shipping_custom_field_5', $label_5 ) !!}
    			            {!! Form::text('shipping_custom_field_5', !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_5']) ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_5'] : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_5, 'required' => $is_shipping_custom_field_5_required]); !!}
    			        </div>
    			    </div>
    	        @endif
    	        <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('shipping_documents', __('lang_v1.shipping_documents') . ':') !!}
                        {!! Form::file('shipping_documents[]', ['id' => 'shipping_documents', 'multiple', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                        <p class="help-block">
                        	@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                        	@includeIf('components.document_help_text')
                        </p>
                    </div>
                </div>
    	        <div class="clearfix"></div>
    	        <div class="col-md-12 text-center">
    				<button type="button" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white" id="toggle_additional_expense"> <i class="fas fa-plus"></i> @lang('lang_v1.add_additional_expenses') <i class="fas fa-chevron-down"></i></button>
    			</div>
    			<div class="col-md-8 col-md-offset-4" id="additional_expenses_div" style="display: none;">
    				<table class="table table-condensed">
    					<thead>
    						<tr>
    							<th>@lang('lang_v1.additional_expense_name')</th>
    							<th>@lang('sale.amount')</th>
    						</tr>
    					</thead>
    					<tbody>
    						<tr>
    							<td>
    								{!! Form::text('additional_expense_key_1', null, ['class' => 'form-control', 'id' => 'additional_expense_key_1']); !!}
    							</td>
    							<td>
    								{!! Form::text('additional_expense_value_1', 0, ['class' => 'form-control input_number', 'id' => 'additional_expense_value_1']); !!}
    							</td>
    						</tr>
    						<tr>
    							<td>
    								{!! Form::text('additional_expense_key_2', null, ['class' => 'form-control', 'id' => 'additional_expense_key_2']); !!}
    							</td>
    							<td>
    								{!! Form::text('additional_expense_value_2', 0, ['class' => 'form-control input_number', 'id' => 'additional_expense_value_2']); !!}
    							</td>
    						</tr>
    						<tr>
    							<td>
    								{!! Form::text('additional_expense_key_3', null, ['class' => 'form-control', 'id' => 'additional_expense_key_3']); !!}
    							</td>
    							<td>
    								{!! Form::text('additional_expense_value_3', 0, ['class' => 'form-control input_number', 'id' => 'additional_expense_value_3']); !!}
    							</td>
    						</tr>
    						<tr>
    							<td>
    								{!! Form::text('additional_expense_key_4', null, ['class' => 'form-control', 'id' => 'additional_expense_key_4']); !!}
    							</td>
    							<td>
    								{!! Form::text('additional_expense_value_4', 0, ['class' => 'form-control input_number', 'id' => 'additional_expense_value_4']); !!}
    							</td>
    						</tr>
    					</tbody>
    				</table>
    			</div>
    		    <div class="col-md-4 col-md-offset-8">
    		    	@if(!empty($pos_settings['amount_rounding_method']) && $pos_settings['amount_rounding_method'] > 0)
    		    	<small id="round_off"><br>(@lang('lang_v1.round_off'): <span id="round_off_text">0</span>)</small>
    				<br/>
    				<input type="hidden" name="round_off_amount" 
    					id="round_off_amount" value=0>
    				@endif
    		    	<div style:"display:none"><b>@lang('sale.total_payable'): </b>
    					<input type="hidden" name="final_total" id="final_total_input">
    					<span id="total_payable">0</span>
    				</div>
    		    </div>
    			@endcomponent
    		</div>	
		</div>
	</div>
	@if(!empty($common_settings['is_enabled_export']) && $sale_type != 'sales_order')
		@component('components.widget', ['class' => 'box-solid', 'title' => __('lang_v1.export')])
			<div class="col-md-12 mb-12">
                <div class="form-check">
                    <input type="checkbox" name="is_export" class="form-check-input" id="is_export" @if(!empty($walk_in_customer['is_export'])) checked @endif>
                    <label class="form-check-label" for="is_export">@lang('lang_v1.is_export')</label>
                </div>
            </div>
	        @php
	            $i = 1;
	        @endphp
	        @for($i; $i <= 6 ; $i++)
	            <div class="col-md-4 export_div" @if(empty($walk_in_customer['is_export'])) style="display: none;" @endif>
	                <div class="form-group">
	                    {!! Form::label('export_custom_field_'.$i, __('lang_v1.export_custom_field'.$i).':') !!}
	                    {!! Form::text('export_custom_fields_info['.'export_custom_field_'.$i.']', !empty($walk_in_customer['export_custom_field_'.$i]) ? $walk_in_customer['export_custom_field_'.$i] : null, ['class' => 'form-control','placeholder' => __('lang_v1.export_custom_field'.$i), 'id' => 'export_custom_field_'.$i]); !!}
	                </div>
	            </div>
	        @endfor
		@endcomponent
	@endif
	@php
		$is_enabled_download_pdf = config('constants.enable_download_pdf');
	@endphp
	@if((empty($status) || (!in_array($status, ['quotation', 'draft'])) || $is_enabled_download_pdf) && $sale_type != 'sales_order')
		@can('sell.payments')
			@component('components.widget', ['class' => 'box-solid'])
    			<button type="button" id="toggle_payment_section" class="btn btn-primary btn-block" style="margin-bottom: 15px;">
                    Agregar Pago
                </button>
            
            	<div id="payment_section" style="display: none;">
			@if($is_enabled_download_pdf)
				<div class="well row">
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label("prefer_payment_method" , __('lang_v1.prefer_payment_method') . ':') !!}
							@show_tooltip(__('lang_v1.this_will_be_shown_in_pdf'))
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fas fa-money-bill-alt"></i>
								</span>
								{!! Form::select("prefer_payment_method", $payment_types, 'cash', ['class' => 'form-control','style' => 'width:100%;']); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label("prefer_payment_account" , __('lang_v1.prefer_payment_account') . ':') !!}
							@show_tooltip(__('lang_v1.this_will_be_shown_in_pdf'))
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fas fa-money-bill-alt"></i>
								</span>
								{!! Form::select("prefer_payment_account", $accounts, null, ['class' => 'form-control','style' => 'width:100%;']); !!}
							</div>
						</div>
					</div>
				</div>
			@endif
			@if(empty($status) || !in_array($status, ['quotation', 'draft']))
				<div class="payment_row" id="payment_rows_div">
					<div class="row">
						<div class="col-md-12 mb-12">
							<strong>@lang('lang_v1.advance_balance'):</strong> <span id="advance_balance_text"></span>
							{!! Form::hidden('advance_balance', null, ['id' => 'advance_balance', 'data-error-msg' => __('lang_v1.required_advance_balance_not_available')]); !!}
						</div>
					</div>
					@include('sale_pos.partials.payment_row_form', ['row_index' => 0, 'show_date' => true, 'show_denomination' => true])
                </div>
                <div class="payment_row">
					<div class="row">
						<div class="col-md-12">
			        		<hr>
			        		<strong>
			        			@lang('lang_v1.change_return'):
			        		</strong>
			        		<br/>
			        		<span class="lead text-bold change_return_span">0</span>
			        		{!! Form::hidden("change_return", $change_return['amount'], ['class' => 'form-control change_return input_number', 'required', 'id' => "change_return"]); !!}
			        		<!-- <span class="lead text-bold total_quantity">0</span> -->
			        		@if(!empty($change_return['id']))
			            		<input type="hidden" name="change_return_id" 
			            		value="{{$change_return['id']}}">
			            	@endif
						</div>
					</div>
					<div class="row hide payment_row" id="change_return_payment_data">
						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label("change_return_method" , __('lang_v1.change_return_payment_method') . ':*') !!}
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-money-bill-alt"></i>
									</span>
									@php
										$_payment_method = empty($change_return['method']) && array_key_exists('cash', $payment_types) ? 'cash' : $change_return['method'];

										$_payment_types = $payment_types;
										if(isset($_payment_types['advance'])) {
											unset($_payment_types['advance']);
										}
									@endphp
									{!! Form::select("payment[change_return][method]", $_payment_types, $_payment_method, ['class' => 'form-control col-md-12 payment_types_dropdown', 'id' => 'change_return_method', 'style' => 'width:100%;']); !!}
								</div>
							</div>
						</div>
						@if(!empty($accounts))
						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label("change_return_account" , __('lang_v1.change_return_payment_account') . ':') !!}
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-money-bill-alt"></i>
									</span>
									{!! Form::select("payment[change_return][account_id]", $accounts, !empty($change_return['account_id']) ? $change_return['account_id'] : '' , ['class' => 'form-control select2', 'id' => 'change_return_account', 'style' => 'width:100%;']); !!}
								</div>
							</div>
						</div>
						@endif
						@include('sale_pos.partials.payment_type_details', ['payment_line' => $change_return, 'row_index' => 'change_return'])
					</div>
					<hr>
					<div class="row">
						<div class="col-sm-12">
							<div class="pull-right"><strong>@lang('lang_v1.balance'):</strong> <span class="balance_due">0.00</span></div>
						</div>
					</div>
				</div>
			@endif
			</div>
			@endcomponent
		@endcan
	@endif
	
	<div class="row">
		{!! Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']); !!}
		<div class="col-sm-12 text-center tw-mt-4">
			<button type="button" id="submit-sell" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">@lang('messages.save')</button>
			<button type="button" id="save-and-print" class="tw-dw-btn tw-dw-btn-success tw-dw-btn-lg tw-text-white">@lang('lang_v1.save_and_print')</button>
			<button type="button" id="generar-factura" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">@lang('Guardar')</button>
		</div>
	</div>
	
	<label id="direccion" style="display:none;">{{ session('business.tax_number_2') }}</label>
    <label id="cuitEmpresa" style="display:none;">{{ session('business.tax_number_1') }}</label>
    <label id="fechaInicio" style="display:none;">{{ session('business.start_date') }}</label>
    <label id="logoEmpresa" style="display:none;">{{ session('business.logo') }}</label>
    <label id="business_name" style="display:none;">{{ session('business.name') }}</label>

	@if(empty($pos_settings['disable_recurring_invoice']))
		@include('sale_pos.partials.recurring_invoice_modal')
	@endif
	
	{!! Form::close() !!}
</section>

<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>

<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

<div class="modal fade types_of_service_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>




@include('sale_pos.partials.configure_search_modal')

@stop

<!-- Al final del body, antes de los scripts -->
<div class="loader-container" id="loader">
    <div class="loader"></div>
    <span class="loader-text">Esperando respuesta servidores ARCA...</span>
</div>

@section('javascript')
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <script>
        $(document).ready(function () {
            // Mostrar u ocultar los campos adicionales según la opción seleccionada en el desplegable
            $('#generate_invoice_select').change(function () {
                const value = $(this).val();  // Obtener el valor seleccionado (yes o no)
                if (value === 'Si') {
                    $('#additional_invoice_fields').show();  // Mostrar si se selecciona "Sí"
                    $('#generar-factura').show();
                    $('#save-and-print').hide();
                    $('#submit-sell').hide();
                } else {
                    $('#additional_invoice_fields').hide();  // Ocultar si se selecciona "No"
                    $('#generar-factura').hide();
                    $('#save-and-print').show();
                    $('#submit-sell').show();
                }
            }).trigger('change');  // Ejecuta al cargar la página para aplicar el estado inicial
    
            // Mostrar u ocultar el campo de fecha según el valor seleccionado en el campo de concepto de factura
            $('#invoice_concept').on('change', function () {
                const value = $(this).val();
                const startDate = $('#start_date');
				const endDate = $('#end_date');
				const paymentDueDate = $('#payment_due_date');
                if (value === 'servicios' || value === 'productos_y_servicios') {
                    startDate.show();  // Muestra el campo de fecha
					endDate.show();
					paymentDueDate.show();
                } else {
                    startDate.hide();  
					endDate.hide();
					paymentDueDate.hide();  
                }
            }).trigger('change');  // Ejecuta al cargar la página
        });
    </script>
    
    

    <script type="text/javascript">
    	$(document).ready( function() {
    		function syncPaymentRowsVisibility() {
                var $st = $('#status');
                var val = $st.val();
            
                // Si no existe #status, por defecto mostramos pagos
                if (!$st.length) { 
                    $('#payment_rows_div').removeClass('hide');
                    return;
                }
            
                // Si existe pero es hidden, igual leemos su valor
                if (val === 'final' || val === '' || val === undefined || val === null) {
                    // Por defecto, mostrar si es final o no está seteado
                    $('#payment_rows_div').removeClass('hide');
                } else {
                    $('#payment_rows_div').addClass('hide');
                }
            }
            
            $('#status').on('change', syncPaymentRowsVisibility);
            
            // Sincroniza al cargar
            syncPaymentRowsVisibility();

    		$('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });

            $('#shipping_documents').fileinput({
		        showUpload: false,
		        showPreview: false,
		        browseLabel: LANG.file_browse_label,
		        removeLabel: LANG.remove,
		    });

		    $(document).on('change', '#prefer_payment_method', function(e) {
			    var default_accounts = $('select#select_location_id').length ? 
			                $('select#select_location_id')
			                .find(':selected')
			                .data('default_payment_accounts') : $('#location_id').data('default_payment_accounts');
			    var payment_type = $(this).val();
			    if (payment_type) {
			        var default_account = default_accounts && default_accounts[payment_type]['account'] ? 
			            default_accounts[payment_type]['account'] : '';
			        var account_dropdown = $('select#prefer_payment_account');
			        if (account_dropdown.length && default_accounts) {
			            account_dropdown.val(default_account);
			            account_dropdown.change();
			        }
			    }
			});

		    function setPreferredPaymentMethodDropdown() {
			    var payment_settings = $('#location_id').data('default_payment_accounts');
			    payment_settings = payment_settings ? payment_settings : [];
			    enabled_payment_types = [];
			    for (var key in payment_settings) {
			        if (payment_settings[key] && payment_settings[key]['is_enabled']) {
			            enabled_payment_types.push(key);
			        }
			    }
			    if (enabled_payment_types.length) {
			        $("#prefer_payment_method > option").each(function() {
		                if (enabled_payment_types.indexOf($(this).val()) != -1) {
		                    $(this).removeClass('hide');
		                } else {
		                    $(this).addClass('hide');
		                }
			        });
			    }
			}
			
			setPreferredPaymentMethodDropdown();

			$('#is_export').on('change', function () {
	            if ($(this).is(':checked')) {
	                $('div.export_div').show();
	            } else {
	                $('div.export_div').hide();
	            }
	        });

			if($('.payment_types_dropdown').length){
				$('.payment_types_dropdown').change();
			}

    	});
    </script>
    

    <script>
        document.getElementById('generar-factura').addEventListener('click', async function () {
            document.getElementById('loader').style.display = 'flex';
    
            // Elimina todo lo que no sea número y divide por 100 para quitar los "2 ceros extra"
            function sanitizeInt(value) {
                const limpio = (value || '').replace(/[^\d]/g, '');
                return Math.floor(parseInt(limpio, 10) / 100) || 0;
            }
    
            var invoice_type = document.getElementById('invoice_type').value;
            var invoice_concept = document.getElementById('invoice_concept').value;
            var buyer_document_type = document.getElementById('buyer_document_type').value;
            var buyer_document_number = document.getElementById('buyer_document_number').value;
            if (buyer_document_number === '0') {
                buyer_document_type = 'ConsumidorFinal';
            }
    
            var transaction_date = document.getElementById('transaction_date')?.value || null;
    
            var denomination_total_amount = document.getElementById('price_total');
            denomination_total_amount = sanitizeInt(denomination_total_amount.textContent);
    
            var start_date = document.getElementById('start_date_val').value;
            var end_date = document.getElementById('end_date_val').value;
            var payment_due_date = document.getElementById('payment_due_date_val').value;
            var business_name = document.getElementById('business_name').textContent;
            var fechaInicio = document.getElementById('fechaInicio').textContent;
            var invoice_iva = document.getElementById('invoice_iva').value;
    
            var iva_receptor = document.getElementById('iva_receptor').value;
            var direccion = document.getElementById('direccion').textContent;
            var cuitEmpresa = document.getElementById('cuitEmpresa').textContent;
            var logoEmpresa = document.getElementById('logoEmpresa').textContent;
    
            var invoiceTypeMap = {
                'Factura A': 1, 'Factura B': 6, 'Factura C': 11,
                'Nota de Credito A': 3, 'Nota de Credito B': 8, 'Nota de Credito C': 13,
                'Recibo A': 4, 'Recibo B': 9, 'Recibo C': 15
            };
            var invoiceConceptMap = {
                'productos': 1, 'servicios': 2, 'productos y servicios': 3
            };
            var buyerDocumentTypeMap = {
                'CUIT': 80, 'CUIL': 86, 'DNI': 96, 'ConsumidorFinal': 99
            };
    
            var tipoTransferencia = document.getElementById('tipo_transferencia').value;
            var cbu_cliente_val = document.getElementById('cbu_cliente_val').value;
            var num_factura_val = document.getElementById('num_factura_val').value;
            var pto_vta = document.getElementById('pto_vta').value;
    
            var selectElement = document.getElementById('select_location_id');
            var locacion_comercial = selectElement.options[selectElement.selectedIndex].text;
            
            var customer_name = document.getElementById('select2-customer_id-container')?.textContent?.trim() || '';
            const business_name_fallback = document.getElementById('business_name')?.textContent?.trim() || '';
            
            if (!customer_name || customer_name.toLowerCase().includes('walk-in')) {
                customer_name = business_name_fallback;
            }


            var customer_cuit = document.getElementById('buyer_document_number')?.value || null;
            var customer_address = document.getElementById('customer_address')?.value || null;
            var payment_condition = document.getElementById('payment_condition')?.value || null;
    
            invoice_type = invoiceTypeMap[invoice_type] || null;
            invoice_concept = invoiceConceptMap[invoice_concept] || null;
            buyer_document_type = buyerDocumentTypeMap[buyer_document_type] || null;
    
            function convertToDateFormat(date) {
                var [month, day, year] = date.split(' ')[0].split('/');
                return `${year}${month}${day}`;
            }
    
            if (transaction_date) {
                transaction_date = convertToDateFormat(transaction_date);
            }
    
            const tbody = document.getElementById('products_tbody');
            const rows = tbody.querySelectorAll('tr');
            const products = Array.from(rows).map(row => {
                const columns = row.querySelectorAll('td');
    
                const raw_text = columns[0]?.textContent || '';
                const cleaned_text = raw_text
                    .replace(/\s+/g, ' ')                     // Normaliza espacios
                    .trim()
                    .split(/Agregue|product-img/i)[0]         // Corta antes de textos irrelevantes
                    .replace(/MLA\d+/gi, '')                  // Elimina códigos tipo MLAxxxxxxx
                    .replace(/\d{4,}/g, '')                   // Elimina cualquier número largo (>= 4 dígitos)
                    .replace(/\b(GENERICO|GENÉRICO)\b/gi, '') // Elimina la palabra Generico
                    .replace(/\d+\.\d{2}/g, '')               // Elimina precios tipo 995.00
                    .replace(/\bPc\(s\) en stock\b/gi, '')    // Elimina "Pc(s) en stock"
                    .trim();
                
                const product_name = cleaned_text || null;



    
                const quantityInput = columns[1]?.querySelector('input[name*="[quantity]"]');
                const quantity = quantityInput.value;
    
                const unitPriceInput = columns[2]?.querySelector('input[name*="[unit_price]"]');
                const unit_price = unitPriceInput ? sanitizeInt(unitPriceInput.value) : 0;
    
                const discountInput = columns[3]?.querySelector('input[name*="[line_discount_amount]"]');
                const discount = discountInput ? sanitizeInt(discountInput.value) : 0;
    
                const lineTotalInput = columns[6]?.querySelector('input.pos_line_total');
                const subtotal = lineTotalInput ? sanitizeInt(lineTotalInput.value) : null;
    
                return {
                    product_name,
                    quantity,
                    unit_price,
                    discount,
                    subtotal,
                };
            });
    
            try {
                const response = await fetch('/crear-factura', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        invoice_type,
                        invoice_concept,
                        buyer_document_type,
                        buyer_document_number,
                        transaction_date,
                        denomination_total_amount,
                        start_date,
                        end_date,
                        payment_due_date,
                        business_name,
                        products,
                        direccion,
                        cuitEmpresa,
                        fechaInicio,
                        invoice_iva,
                        tipoTransferencia,
                        cbu_cliente_val,
                        num_factura_val,
                        logoEmpresa,
                        iva_receptor,
                        locacion_comercial,
                        pto_vta,
                        customer_name,
                        customer_cuit,
                        customer_address,
                        payment_condition,
                    })
                });
    
                if (response.ok) {
                    const blob = await response.blob();
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'factura.pdf';
                    link.click();
                    document.getElementById('submit-sell').click();
                    alert('Factura generada exitosamente.');
                    document.getElementById('loader').style.display = 'none';
                } else {
                    const errorResult = await response.json();
                    alert(`Error: ${errorResult.error || 'Ocurrió un problema inesperado.'}`);
                    document.getElementById('loader').style.display = 'none';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Hubo un error inesperado al intentar generar la factura.');
                document.getElementById('loader').style.display = 'none';
            }
        });
    </script>





    <!-- Script para mostrar/ocultar filas y calcular valores -->
<!-- Script para cálculos -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const priceTotalElement = document.getElementById('price_total');
            const iva21Element = document.getElementById('iva_result');
            const totalAmountElement = document.getElementById('total_amount');
            const invoiceIvaSelect = document.getElementById('invoice_iva');
    
            function getIvaPercentage() {
                const selectedValue = invoiceIvaSelect.value;
                switch (selectedValue) {
                    case 'iva_21': return 0.21;
                    case 'iva_27': return 0.27;
                    case 'iva_10': return 0.105;
                    case 'excento_iva': return 0;
                    default: return 0;
                }
            }
    
            function updateValues() {
                const rawAmount = priceTotalElement.textContent.replace(/,/g, '');
                const netAmount = parseFloat(rawAmount) || 0;
                const ivaPercentage = getIvaPercentage();
                const iva = netAmount * ivaPercentage;
                const totalAmount = netAmount + iva;
    
                iva21Element.textContent = iva.toFixed(2);
                totalAmountElement.textContent = totalAmount.toFixed(2);
            }
    
            const observer = new MutationObserver(updateValues);
            observer.observe(priceTotalElement, { childList: true, characterData: true });
            invoiceIvaSelect.addEventListener('change', updateValues);
    
            updateValues();
        });
    </script>

    <script>
        $(document).ready(function () {
            // Mostrar u ocultar las filas de IVA y Total según la opción seleccionada en el desplegable
            $('#invoice_type').change(function () {
                const value = $(this).val();  // Obtener el valor seleccionado en el desplegable
                if (value === 'Factura A' || value === 'Nota de Credito A' || value === 'Factura B' || value === 'Nota de Credito B' || value === 'Recibo A' || value === 'Recibo B') {
                    $('#iva_row').show();  // Mostrar si se selecciona "Factura A"
                    $('#total_row').show();
                    $('#invoice_iva').show().prop('disabled', false);
                    $('#invoice_iva_text').show().prop('disabled', false);
                    $('#importe').text('Importe Neto Gravado:');// Mostrar fila de total
                    document.dispatchEvent(new Event('invoiceTypeUpdated'));
                } else {
                    $('#iva_row').hide();  // Ocultar si no se selecciona "Factura A"
                    $('#total_row').hide();
                    $('#invoice_iva').hide().prop('disabled', true).val('');
                    $('#invoice_iva_text').hide().prop('disabled', true).val('');
                    $('#importe').text('Importe Total:');// Ocultar fila de total
                    document.dispatchEvent(new Event('invoiceTypeUpdated'));
                }
            }).trigger('change');  // Ejecuta al cargar la página para aplicar el estado inicial
        });
    </script>
    
    <script>
        $(document).ready(function () {
            // Mostrar u ocultar las filas de IVA y Total según la opción seleccionada en el desplegable
            $('#invoice_type').change(function () {
                const value = $(this).val();  // Obtener el valor seleccionado en el desplegable
                if (value === 'Nota de Credito A' || value === 'Nota de Credito B' || value === 'Nota de Credito C') {
                    $('#num_factura').show();  // Mostrar si se selecciona "Nota de credito"
                    $('#tipo_transferencia').show();
                    $('#cbu_cliente').show();
                    document.dispatchEvent(new Event('invoiceTypeUpdated'));
                } else {
                    $('#num_factura').hide();  // Ocultar si no se selecciona "Nota de credito"
                    $('#tipo_transferencia').hide();
                    $('#cbu_cliente').hide();
                    document.dispatchEvent(new Event('invoiceTypeUpdated'));
                }
            }).trigger('change');  // Ejecuta al cargar la página para aplicar el estado inicial
        });
    </script>

    <script>
        $(document).ready(function() {
            // Escuchar cambios en el campo invoice_iva
            $('#invoice_iva').on('change', function() {
                // Obtener el valor seleccionado
                var selectedIva = $(this).val();
        
                // Mapear los valores de invoice_iva a tax_rate_id
                var taxRateId = '';
                switch (selectedIva) {
                    case 'iva_21':
                        taxRateId = 1; // Asegúrate de que este valor coincida con el de $taxes['tax_rates']
                        break;
                    case 'iva_27':
                        taxRateId = 2; // Asegúrate de que este valor coincida con el de $taxes['tax_rates']
                        break;
                    case 'iva_10':
                        taxRateId = 3; // Asegúrate de que este valor coincida con el de $taxes['tax_rates']
                        break;
                    case 'excento_iva':
                    default:
                        taxRateId = 4; // Asegúrate de que este valor coincida con el de $taxes['tax_rates']
                        break;
                }
        
                // Actualizar el campo tax_rate_id
                $('#tax_rate_id').val(taxRateId).trigger('change');
            });
        });
    </script>
    
    
    
    <script>
        $(document).ready(function(){
            $('#buyer_document_number').on('blur', function() {
                var taxId = $(this).val().trim();
    
                if (/^\d{11}$/.test(taxId)) {
                    $.ajax({
                        url: '/get-taxpayer-details',
                        type: 'POST',
                        dataType: 'json',
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: JSON.stringify({ tax_id: taxId }),
                        success: function(data) {
                            console.log('✅ Respuesta recibida del servidor:', data);
    
                            $('#found_taxpayer_name').remove();
    
                            // Mapeo de tipo impositivo
                            var descripcion = data.descripcionImpuesto ? data.descripcionImpuesto.toUpperCase() : "";
                            var selectedValue;
                            switch (descripcion) {
                                case 'IVA':
                                    selectedValue = '1';
                                    break;
                                case 'MONOTRIBUTO':
                                    selectedValue = '6';
                                    break;
                                case 'IVA NO ALCANZADO':
                                    selectedValue = '15';
                                    break;
                                default:
                                    selectedValue = '5';
                            }
    
                            // Validar que la opción exista en el select, o agregarla
                            if ($('#iva_receptor option[value="' + selectedValue + '"]').length === 0) {
                                $('#iva_receptor').append(`<option value="${selectedValue}">${descripcion}</option>`);
                            }
    
                            $('#iva_receptor').val(selectedValue).trigger('change');
    
                            // Buscar nombre o razón social
                            var fullName = '';
                            var general = data?.data?.datosGenerales;
    
                            if (general) {
                                if (general.razonSocial) {
                                    fullName = general.razonSocial;
                                } else if (general.apellido && general.nombre) {
                                    fullName = `${general.apellido}, ${general.nombre}`;
                                }
                            }
    
                            console.log('👤 Nombre o Razón Social detectado:', fullName);
    
                            if (fullName) {
                                $('#buyer_document_number').closest('.form-group').append(`
                                    <div id="found_taxpayer_name">
                                        <p style="color: green; font-weight: bold; margin-top: 5px;">Razón Social: ${fullName}</p>
                                    </div>
                                `);
                            }
                        },
                        error: function(error) {
                            console.error('❌ Error en la llamada AJAX:', error);
                            $('#found_taxpayer_name').remove();
                        }
                    });
                } else {
                    console.log('⚠️ CUIT inválido o incompleto:', taxId);
                    $('#found_taxpayer_name').remove();
                }
            });
        });
    </script>





    
    <script>
    	document.getElementById('toggle_discount_section').addEventListener('click', function () {
    		const section = document.getElementById('discount_section');
    		section.style.display = section.style.display === 'none' ? 'block' : 'none';
    	});
    </script>
    
    <script>
        document.getElementById('toggle_shipping_section').addEventListener('click', function () {
        	const section = document.getElementById('shipping_section');
        	section.style.display = section.style.display === 'none' ? 'block' : 'none';
        });
    </script>
    
    <script>
    	document.getElementById('toggle_payment_section').addEventListener('click', function () {
    		const section = document.getElementById('payment_section');
    		section.style.display = section.style.display === 'none' ? 'block' : 'none';
    	});
    </script>
    
    <script>
    $(document).on('click', '#consultar_cuit', function () {
        var cuit = $('#cuit_number').val();
        if (!cuit) {
            alert('Por favor, ingrese un número de CUIT/CUIL.');
            return;
        }
    
        $.ajax({
            method: 'POST',
            url: '{{ route('get.taxpayer.details') }}',
            data: {
                tax_id: cuit,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                $('#consultar_cuit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Consultando...');
            },
            success: function (response) {
                console.log('Respuesta ARCA:', response);
    
                if (response.success) {
                    const datos = response.data;
                    const generales = datos.datosGenerales || {};
                    const domicilio = generales.domicilioFiscal || {};
                    const impuesto = response.descripcionImpuesto;
                    
    
                    // Llenar campos
                    $('#tax_number').val(cuit);
                    $('#first_name').val(generales.nombre || '');
                    $('#last_name').val(generales.apellido || '');
                    const tipoClave = (generales.tipoClave || '').toUpperCase();
                    const tipoDoc = (tipoClave === 'DNI') ? 'DNI' : 'CUIT/CUIL';
                    $('#document_type').val(tipoDoc);
    
                    if (impuesto) {
                        const mapeoValores = {
                            'IVA RESPONSABLE INSCRIPTO': 'IVA Responsable Inscripto',
                            'MONOTRIBUTO': 'Monotributo',
                            'MONOTRIBUTISTA SOCIAL': 'Monotributista Social',
                            'MONOTRIBUTO TRABAJADOR INDEPENDIENTE PROMOVIDO': 'Monotributo Trabajador Independiente Promovido',
                            'IVA EXENTO': 'IVA Exento',
                            'CONSUMIDOR FINAL': 'Consumidor Final',
                            'SUJETO NO CATEGORIZADO': 'Sujeto No Categorizado',
                            'PROVEEDOR DEL EXTERIOR': 'Proveedor del exterior',
                            'CLIENTE DEL EXTERIOR': 'Cliente del exterior',
                            'IVA LIBERADO – LEY N° 19.640': 'IVA Liberado – Ley N° 19.640',
                            'IVA NO ALCANZADO': 'IVA No Alcanzado'
                        };
    
                        const impuestoNormalizado = (impuesto || '').trim().toUpperCase();
                        const descripcion = mapeoValores[impuestoNormalizado];
    
                        if (descripcion !== undefined) {
                            let matched = false;
                            $('#tax_category option').each(function () {
                                if ($(this).text().trim().toUpperCase() === impuestoNormalizado) {
                                    $('#tax_category').val($(this).val()).trigger('change');
                                    matched = true;
                                    return false; // break loop
                                }
                            });
                            if (!matched) {
                                console.warn('No se encontró descripción para:', impuesto);
                                $('#tax_category').val(null).trigger('change');
                            }
    
                        } else {
                            console.warn('No se encontró descripción para:', impuesto);
                            $('#tax_category').val('');
                        }
                    }
    
                    // Dirección combinada (opcional)
                    const direccionCompleta = [domicilio.direccion, domicilio.localidad, domicilio.descripcionProvincia]
                        .filter(Boolean).join(', ');
                    $('#address_line_1').val(direccionCompleta);
    
                    alert('Datos cargados correctamente.');
                } else {
                    alert('La consulta no fue exitosa.');
                }
            },
            error: function(xhr) {
                console.error('Error al consultar ARCA:', xhr.responseText);
                alert('Ocurrió un error al consultar los datos.');
            },
            complete: function () {
                $('#consultar_cuit').prop('disabled', false).html('Consultar en ARCA');
            }
        });
    });
    </script>
    
    <script>
    $(document).ready(function () {
        // Mostrar/ocultar secciones según tipo seleccionado
        $('input[type=radio][name=contact_type_radio]').change(function () {
            var tipo = $(this).val();
            if (tipo === 'individual') {
                $('.individual').show();
                $('.business').hide();
            } else {
                $('.individual').hide();
                $('.business').show();
            }
        });
    
        // Al cargar el modal, simular que se seleccionó el valor por defecto
        $('input[name="contact_type_radio"]:checked').trigger('change');
    });
    </script>
    
    <script>
        // Asegurarse de que el campo hidden se rellene con el total con IVA
        function actualizarFinalTotalConIVA() {
            const totalConIva = document.getElementById('total_amount')?.textContent || '0';
            const totalLimpio = totalConIva.replace(/,/g, '').trim();
            const totalNumerico = parseFloat(totalLimpio) || 0;
            document.getElementById('final_total_input').value = totalNumerico.toFixed(2);
        }
    
        // Actualizar antes de enviar el formulario
        document.getElementById('submit-sell')?.addEventListener('click', actualizarFinalTotalConIVA);
        document.getElementById('save-and-print')?.addEventListener('click', actualizarFinalTotalConIVA);
    </script>

@endsection
