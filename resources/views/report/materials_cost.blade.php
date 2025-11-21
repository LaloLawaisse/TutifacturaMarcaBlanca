@extends('layouts.app')

@section('title', __('lang_v1.materials_cost_report'))

@section('content')
<section class="content-header">
    <h1>@lang('lang_v1.materials_cost_report')</h1>
</section>

<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('materials_cost_start_date', __('report.start_date') . ':') !!}
                    {!! Form::text('materials_cost_start_date', null, ['class' => 'form-control', 'id' => 'materials_cost_start_date', 'placeholder' => __('report.start_date')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('materials_cost_end_date', __('report.end_date') . ':') !!}
                    {!! Form::text('materials_cost_end_date', null, ['class' => 'form-control', 'id' => 'materials_cost_end_date', 'placeholder' => __('report.end_date')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('materials_cost_location', __('purchase.business_location') . ':') !!}
                    {!! Form::select('materials_cost_location', $business_locations, null, ['class' => 'form-control select2', 'id' => 'materials_cost_location', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group" style="margin-top: 25px;">
                    <button class="btn btn-primary" id="btn_refresh_materials_cost">@lang('lang_v1.refresh')</button>
                </div>
            </div>
        </div>
        <p class="text-muted" style="margin-top:-10px;">@lang('lang_v1.materials_cost_help')</p>
    @endcomponent

    <div class="row">
        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fas fa-dolly-flatbed"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('lang_v1.materials_cost_purchased')</span>
                    <span class="info-box-number" id="materials_cost_purchased" data-currency_conversion="true">0.00</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fas fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('lang_v1.materials_cost_sold')</span>
                    <span class="info-box-number" id="materials_cost_sold" data-currency_conversion="true">0.00</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
@parent
<script>
$(function () {
    $('#materials_cost_start_date, #materials_cost_end_date').datetimepicker({
        format: moment_date_format,
        ignoreReadonly: true
    });

    // Valores iniciales: inicio del mes y hoy
    if (!$('#materials_cost_start_date').val()) {
        $('#materials_cost_start_date').val(moment().startOf('month').format(moment_date_format));
    }
    if (!$('#materials_cost_end_date').val()) {
        $('#materials_cost_end_date').val(moment().format(moment_date_format));
    }

    function fetchMaterialCosts() {
        const startRaw = $('#materials_cost_start_date').val();
        const endRaw = $('#materials_cost_end_date').val();

        const data = {
            start_date: startRaw ? moment(startRaw, moment_date_format).format('YYYY-MM-DD') : '',
            end_date: endRaw ? moment(endRaw, moment_date_format).format('YYYY-MM-DD') : '',
            location_id: $('#materials_cost_location').val(),
        };

        $.ajax({
            method: 'GET',
            url: '{{ action([\App\Http\Controllers\ReportController::class, "materialsCostReport"]) }}',
            data,
            success: function (res) {
                const purchased = res.purchased_cost || 0;
                const sold = res.sold_cost || 0;

                $('#materials_cost_purchased').text(__currency_trans_from_en(purchased, true, false));
                $('#materials_cost_sold').text(__currency_trans_from_en(sold, true, false));
            },
        });
    }

    $('#btn_refresh_materials_cost').on('click', function (e) {
        e.preventDefault();
        fetchMaterialCosts();
    });

    // Initial load
    fetchMaterialCosts();
});
</script>
@endsection
