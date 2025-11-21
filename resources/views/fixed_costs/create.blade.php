@extends('layouts.app')
@section('title', 'Agregar costo fijo')

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Agregar costo fijo</h1>
</section>
<section class="content">
    {!! Form::open(['url' => action([\App\Http\Controllers\FixedCostController::class, 'store']), 'method' => 'post']) !!}
    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('name', 'Nombre:*') !!}
                    {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('amount', 'Monto:*') !!}
                    {!! Form::text('amount', null, ['class' => 'form-control input_number', 'required']) !!}
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('day_of_month', 'Día del mes:*') !!}
                    {!! Form::number('day_of_month', 1, ['class' => 'form-control', 'min' => 1, 'max' => 31, 'required']) !!}
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('active', 1, true, ['class' => 'input-icheck']) !!} Activo
                    </label>
                </div>
            </div>
        </div>
    @endcomponent
    <div class="tw-flex tw-gap-2">
        <button type="submit" class="tw-dw-btn tw-dw-btn-success tw-text-white">Guardar</button>
        <a href="{{ action([\App\Http\Controllers\FixedCostController::class, 'index']) }}" class="tw-dw-btn">Cancelar</a>
    </div>
    {!! Form::close() !!}
</section>
@endsection
