@extends('layouts.app')
@section('title', 'COSTOS FIJOS')

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">COSTOS FIJOS
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">Gestiona tus costos fijos mensuales</small>
    </h1>
</section>
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        <div class="tw-mb-2">
            <a href="{{ action([\App\Http\Controllers\FixedCostController::class, 'create']) }}" class="tw-dw-btn tw-dw-btn-success tw-text-white">Agregar costo fijo</a>
        </div>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Monto</th>
                    <th>Día del mes</th>
                    <th>Próxima ejecución</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costs as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td><span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $c->amount }}">{{ $c->amount }}</span></td>
                        <td>{{ $c->day_of_month }}</td>
                        <td>{{ $c->next_run_date }}</td>
                        <td>{{ $c->active ? 'Sí' : 'No' }}</td>
                        <td>
                            <a class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary" href="{{ action([\App\Http\Controllers\FixedCostController::class, 'edit'], [$c->id]) }}">Editar</a>
                            <button class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-danger" onclick="if(confirm('¿Eliminar costo fijo?')){ fetch('{{ action([\App\Http\Controllers\FixedCostController::class, 'destroy'], [$c->id]) }}', {method:'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(()=>location.reload()); }">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No hay costos fijos aún.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endcomponent
</section>
@endsection
