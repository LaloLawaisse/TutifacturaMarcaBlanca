@extends('layouts.app')
@section('title', 'Insumos')

@section('content')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Insumos
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">Gestión de insumos</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['title' => 'Listado'])
                    @can('product.create')
                        <a href="{{ route('materials.create') }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-mb-3">
                            <i class="fa fa-plus"></i> Agregar insumo
                        </a>
                    @endcan

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="materials_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Unidades en stock</th>
                                    <th>Vinculados a productos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
    </section>
@endsection

@section('javascript')
<script>
$(function(){
    var table = $('#materials_table').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: {
            url: '{{ route('materials.index') }}',
            data: function(d){}
        },
        columns: [
            { data: 'id', name: 'materiales.ID' },
            { data: 'nombre', name: 'nombre' },
            { data: 'precio', name: 'precio', searchable: false, orderable: false },
            { data: 'unidades_en_stock', name: 'unidades_en_stock' },
            { data: 'productos', name: 'productos', searchable: false, orderable: false },
            { data: 'acciones', name: 'acciones', searchable: false, orderable: false },
        ],
        fnDrawCallback: function(oSettings){
            __currency_convert_recursively($('#materials_table'));
        }
    });

    $(document).on('click', '.adjust_stock_btn', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var qty = prompt('Cantidad a ajustar (entero positivo):', '0');
        if(qty === null) return;
        qty = parseInt(qty, 10);
        if(isNaN(qty) || qty < 0){ return; }
        var action = prompt('Acción (increase, decrease, set):', 'increase');
        if(!action) return;
        $.ajax({
            url: '/materials/adjust-stock/' + id,
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content'), quantity: qty, action: action },
            success: function(){ table.ajax.reload(null,false); }
        })
    });

    $(document).on('click', '.delete_material_btn', function(e){
        e.preventDefault();
        var href = $(this).data('href');
        console.log('delete_material_btn clicked', href);
        if(confirm('{{ __('messages.are_you_sure') }}')){
            console.log('Sending DELETE for material to', href);
            $.ajax({
                url: href,
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                },
                success: function(result){
                    console.log('Delete material response', result);
                    if (result && result.success) {
                        table.ajax.reload();
                    } else {
                        alert('No se pudo borrar el insumo.');
                    }
                },
                error: function(){
                    console.error('Error AJAX deleting material');
                    alert('Error al borrar el insumo.');
                }
            })
        }
    });
});
</script>
@endsection



