@extends('layouts.app')
@section('title', 'Editar insumo')

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Editar insumo</h1>
    @include('layouts.partials.error')
</section>

<section class="content">
    {!! Form::model($material, ['url' => route('materials.update', request()->route('material')), 'method' => 'PUT', 'id' => 'material_edit_form']) !!}
    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('nombre', 'Nombre:*') !!}
                    {!! Form::text('nombre', null, ['class' => 'form-control', 'required', 'placeholder' => 'Nombre del insumo']) !!}
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('precio', 'Precio:') !!}
                    {!! Form::text('precio', null, ['class' => 'form-control input_number', 'placeholder' => 'Precio']) !!}
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('unidades_en_stock', 'Unidades en stock:') !!}
                    {!! Form::number('unidades_en_stock', null, ['class' => 'form-control', 'min' => 0]) !!}
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-8">
                <div class="form-group">
                    {!! Form::label('productos_linkeados', 'Vincular a productos:') !!}
                    {!! Form::select('productos_linkeados[]', [], null, ['class' => 'form-control select2', 'multiple', 'id' => 'productos_linkeados']) !!}
                    <p class="help-block">Seleccione productos que consumen este insumo.</p>
                    <div id="productos_rows" class="table-responsive" style="margin-top:10px;">
                        <table class="table table-bordered" id="productos_table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th style="width:120px;">Cantidad de insumo</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endcomponent

    <div class="tw-flex tw-gap-2">
        <button type="submit" class="tw-dw-btn tw-dw-btn-success tw-text-white">Actualizar</button>
        <a href="{{ route('materials.index') }}" class="tw-dw-btn">Cancelar</a>
    </div>
    {!! Form::close() !!}
</section>
@endsection

@section('javascript')
<script>
$(function(){
    var selected = @json($material->productos_linkeados ?? []);
    var productQuantities = @json($product_quantities ?? []);

    var $productosSelect = $('#productos_linkeados');
    var $productosTableBody = $('#productos_table tbody');

    function rowId(id) {
        return 'producto-row-' + id;
    }

    function addProductoRow(data, qty) {
        var id = data.id;
        var text = data.text;
        qty = qty || 1;
        if (!id || !text) return;
        if ($('#' + rowId(id)).length) return;

        var safeText = $('<div>').text(text).html();
        var html = '<tr id="' + rowId(id) + '" data-product-id="' + id + '">'
            + '<td>' + safeText + '</td>'
            + '<td><input type="number" class="form-control input_number" '
            + 'name="productos_qty[' + id + ']" min="0" step="0.01" value="' + qty + '"></td>'
            + '</tr>';
        $productosTableBody.append(html);
    }

    function removeProductoRow(id) {
        $('#' + rowId(id)).remove();
    }

    $productosSelect.select2({
        ajax: {
            url: '{{ url('/materials/products-options') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term } },
            processResults: function (data) { return data; },
            cache: true
        },
        placeholder: 'Buscar productos...',
        minimumInputLength: 1,
        width: '100%'
    }).on('select2:select', function (e) {
        var data = e.params.data || {};
        addProductoRow(data, 1);
    }).on('select2:unselect', function (e) {
        var data = e.params.data || {};
        if (data.id) {
            removeProductoRow(data.id);
        }
    });

    if (selected && selected.length) {
        $.ajax({
            url: '{{ url('/materials/products-options') }}',
            data: { q: '' },
            success: function(data){
                var $sel = $productosSelect;
                selected.forEach(function(id){
                    var match = (data.results || []).find(function(p){ return p.id == id });
                    var text = match ? match.text : ('#' + id);
                    var option = new Option(text, id, true, true);
                    $sel.append(option).trigger('change');

                    var qty = productQuantities[id] !== undefined ? productQuantities[id] : 1;
                    addProductoRow({id: id, text: text}, qty);
                });
            }
        });
    }
});
</script>
@endsection








