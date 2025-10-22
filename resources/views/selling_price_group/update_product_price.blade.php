@extends('layouts.app')
@section('title', __('lang_v1.update_product_price'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'Actualizacion de precios' )
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @if (session('notification') || !empty($notification))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    @if(!empty($notification['msg']))
                        {{$notification['msg']}}
                    @elseif(session('notification.msg'))
                        {{ session('notification.msg') }}
                    @endif
                </div>
            </div>  
        </div>     
    @endif
    @component('components.widget', ['class' => 'box-primary', 'title' => __('Importar/Exportar precios de productos')])
            <div class="row">
                <div class="col-sm-6">
                    <a href="{{action([\App\Http\Controllers\SellingPriceGroupController::class, 'export'])}}" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('Exportar precio productos')</a>
                </div>
                <div class="col-sm-6">
                    {!! Form::open(['url' => action([\App\Http\Controllers\SellingPriceGroupController::class, 'import']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="form-group">
                        {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                        {!! Form::file('product_group_prices', ['required' => 'required']); !!}
                    </div>
                    <div class="form-group">
                        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.submit')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="col-sm-12">
                    <h4>@lang('lang_v1.instructions'):</h4>
                    <ol>
                        <li>@lang('1. Exportar precios de productos al crear el boton de arriba')</li>
                        <li>@lang('2. Cambia en el precio de los productos, incluyendo impuestos y grupos de precios de ventas')</li>
                        <li>@lang('3. *IMPORTANTE* No cambies el precio, SKU o headers de producto')</li>
                        <li>@lang('4. Luego de los cambios, importa el archivo editado')</li>
                    </ol>
                    
                </div>
            </div>
    @endcomponent
    

</section>
<!-- /.content -->
@stop
