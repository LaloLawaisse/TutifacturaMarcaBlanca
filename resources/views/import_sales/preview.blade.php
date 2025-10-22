@extends('layouts.app')
@section('title', __('lang_v1.preview_imported_sales'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('lang_v1.preview_imported_sales')</h1>
</section>

<!-- Main content -->
<section class="content">
    {!! Form::open(['url' => action([\App\Http\Controllers\ImportSalesController::class, 'import']), 'method' => 'post', 'id' => 'import_sale_form']) !!}
    {!! Form::hidden('file_name', $file_name); !!}
    @component('components.widget')
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('group_by', __('lang_v1.group_sale_line_by') . ':*') !!} @show_tooltip(__('lang_v1.group_by_tooltip'))
                {!! Form::select('group_by', $parsed_array[0], null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]); !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('location_id', __('business.business_location') . ':*') !!}
                {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control', 'required', 'placeholder' => __('messages.please_select')]); !!}
            </div>
        </div>
    </div>
    @endcomponent

    @component('components.widget')
    <div class="row">
        <div class="col-md-12">
            <div class="scroll-top-bottom" style="max-height: 400px;">
                <table class="table table-condensed table-striped">
                    @foreach(array_slice($parsed_array, 0, 101) as $row)
                        <tr>
                            <td>@if($loop->index > 0 ){{$loop->index}} @else # @endif</td>
                            @foreach($row as $k => $v)
                                @if($loop->parent->index == 0)
                                    <th>{{$v}}</th>
                                @else
                                    <td>{{$v}}</td>
                                @endif
                            @endforeach
                        </tr>
                        @if($loop->index == 0)
                            <tr>
                                <td>@if($loop->index > 0){{$loop->index}}@endif</td>
                                @foreach($row as $k => $v)
                                    <td>
                                        {!! Form::select(
                                            'import_fields[' . $k . ']', 
                                            $import_fields, 
                                            null, // No se define un valor preseleccionado
                                            ['class' => 'form-control import_fields select2', 'placeholder' => __('lang_v1.skip'), 'style' => 'width: 100%;']
                                        ) !!}
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    @endcomponent

    <div class="row">
        <div class="col-md-12">
            <!-- Botón original -->
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right">@lang('messages.submit')</button>
        </div>
    </div>
    {!! Form::close() !!}

    <!-- Botón adicional para Facturas ARCA en un formulario separado -->
        <!-- Botón adicional para Facturas ARCA en un formulario separado -->
    <!-- Botón adicional para Facturas ARCA -->
    <!-- Botón para Facturas ARCA -->
    <div class="row">
        <div class="col-md-12">
            <form id="form_facturas_arca" method="POST" action="{{ route('carga-masiva') }}" target="_blank">
                @csrf
                <input type="hidden" name="preview_data" id="preview_data_input">
                <input type="hidden" name="current_date" value="{{ \Carbon\Carbon::now() }}">
                <input type="hidden" name="business_name" value="{{ session('business.name') }}">
                <input type="hidden" name="business_cuit" value="{{ session('business.tax_number_1') }}">
                <input type="hidden" name="business_address" value="{{ session('business.tax_number_2') }}">
                <input type="hidden" name="business_start_date" value="{{ session('business.start_date') }}">
                <input type="hidden" name="business_logo" value="{{ session('business.logo') }}">
                <button type="button" id="btn_facturas_arca" class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right" style="margin-top: 20px;">
                    Facturas ARCA
                </button>
            </form>
        </div>
    </div>

</section>
@stop

@section('javascript')
<script type="text/javascript">
    $(document).on('submit', 'form#import_sale_form, form#carga_masiva_form', function() {
        var import_fields = [];

        $('.import_fields').each(function() {
            if ($(this).val()) {
                import_fields.push($(this).val());
            }
        });

        if (import_fields.indexOf('customer_phone_number') == -1 && import_fields.indexOf('customer_email') == -1) {
            alert("{{__('lang_v1.email_or_phone_required')}}");
            return false;
        }
        if (import_fields.indexOf('product') == -1 && import_fields.indexOf('sku') == -1) {
            alert("{{__('lang_v1.product_name_or_sku_is_required')}}");
            return false;
        }
        if (import_fields.indexOf('quantity') == -1) {
            alert("{{__('lang_v1.quantity_is_required')}}");
            return false;
        }
        if (import_fields.indexOf('unit_price') == -1) {
            alert("{{__('lang_v1.unit_price_is_required')}}");
            return false;
        }

        if (hasDuplicates(import_fields)) {
            alert("{{__('lang_v1.cannot_select_a_field_twice')}}");
            return false;
        }
    });

    function hasDuplicates(array) {
        return (new Set(array)).size !== array.length;
    }
</script>

<script type="text/javascript">
    // Función para capturar los datos del preview como JSON
    function getPreviewData() {
        let table = document.querySelector('.table.table-condensed'); // Seleccionamos la tabla del preview
        let rows = table.querySelectorAll('tr'); // Todas las filas
        let previewData = [];

        rows.forEach((row, rowIndex) => {
            let cells = row.querySelectorAll('td, th'); // Celdas en la fila
            let rowData = {};

            cells.forEach((cell, cellIndex) => {
                rowData[`column_${cellIndex}`] = cell.innerText.trim();
            });

            previewData.push(rowData);
        });

        return previewData;
    }

    // Manejar el evento de clic en el botón Facturas ARCA
    document.querySelector('#btn_facturas_arca').addEventListener('click', function (e) {
        e.preventDefault(); // Prevenir el comportamiento predeterminado
    
        let previewData = getPreviewData(); // Obtener los datos del preview
    
        // Obtener los valores de los campos ocultos
        let currentDate = document.querySelector('input[name="current_date"]').value;
        let businessName = document.querySelector('input[name="business_name"]').value;
        let businessCuit = document.querySelector('input[name="business_cuit"]').value;
        let businessAddress = document.querySelector('input[name="business_address"]').value;
        let businessStartDate = document.querySelector('input[name="business_start_date"]').value;
        let businessLogo = document.querySelector('input[name="business_logo"]').value;
    
        // Hacer una solicitud POST usando fetch
        fetch("{{ route('carga-masiva') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                preview_data: previewData,
                current_date: currentDate,
                business_name: businessName,
                business_cuit: businessCuit,
                business_address: businessAddress,
                business_start_date: businessStartDate,
                business_logo: businessLogo
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Error en la respuesta del servidor.");
            }
            return response.blob(); // Convertir la respuesta a un blob
        })
        .then(blob => {
            // Crear un enlace de descarga temporal
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = "facturas_arca.zip"; // Nombre sugerido para el archivo
            document.body.appendChild(a);
            a.click(); // Simular el clic para iniciar la descarga
            a.remove();
            window.URL.revokeObjectURL(url); // Liberar memoria
        })
        .catch(error => {
            console.error("Error al enviar los datos:", error);
            alert("Ocurrió un error al enviar los datos.");
        });
    });


</script>
@endsection
