@extends('layouts.app')

@section('title', __('lang_v1.general_report'))

@section('content')
<section class="content-header">
    <h1>@lang('lang_v1.general_report')</h1>
</section>

<section class="content">

    {{-- Filtros --}}
    @component('components.filters', ['title' => __('report.filters')])
        <div class="row">
            {{-- Accesos rápidos de período --}}
            <div class="col-md-12" style="margin-bottom: 10px;">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default btn-sm" id="btn_period_this_month">
                        @lang('lang_v1.general_report_period_this_month')
                    </button>
                    <button type="button" class="btn btn-default btn-sm" id="btn_period_last_month">
                        @lang('lang_v1.general_report_period_last_month')
                    </button>
                    <button type="button" class="btn btn-default btn-sm" id="btn_period_this_year">
                        @lang('lang_v1.general_report_period_this_year')
                    </button>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('gr_start_date', __('report.start_date') . ':') !!}
                    {!! Form::text('gr_start_date', null, ['class' => 'form-control', 'id' => 'gr_start_date', 'placeholder' => __('report.start_date'), 'readonly' => true]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('gr_end_date', __('report.end_date') . ':') !!}
                    {!! Form::text('gr_end_date', null, ['class' => 'form-control', 'id' => 'gr_end_date', 'placeholder' => __('report.end_date'), 'readonly' => true]) !!}
                </div>
            </div>

            {{-- Impuestos editables --}}
            <div class="col-md-12" style="margin-top: 15px;">
                <label><strong>@lang('lang_v1.general_report_taxes')</strong></label>
                <div id="gr_taxes_container">
                    {{-- Las filas de impuestos se agregan dinámicamente --}}
                </div>
                <button type="button" class="btn btn-default btn-sm" id="btn_add_tax" style="margin-top: 8px;">
                    <i class="fa fa-plus"></i> @lang('lang_v1.general_report_add_tax')
                </button>
            </div>

            <div class="col-md-12" style="margin-top: 15px;">
                <button class="btn btn-primary" id="btn_generate_report">
                    <i class="fa fa-play"></i> @lang('lang_v1.general_report_generate')
                </button>
            </div>
        </div>
    @endcomponent

    {{-- Resultado (oculto hasta generar) --}}
    <div id="gr_result_section" style="display: none;">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('lang_v1.general_report_result')</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-default btn-sm" id="btn_print_report">
                        <i class="fa fa-print"></i> @lang('lang_v1.print')
                    </button>
                </div>
            </div>
            <div class="box-body">

                {{-- Período del reporte --}}
                <p class="text-muted" id="gr_period_label" style="margin-bottom: 15px;"></p>

                {{-- Info boxes principales --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fa fa-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">@lang('lang_v1.general_report_total_sales')</span>
                                <span class="info-box-number" id="gr_total_sales">0.00</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-orange"><i class="fa fa-cubes"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">@lang('lang_v1.general_report_materials_cost')</span>
                                <span class="info-box-number" id="gr_materials_cost">0.00</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-red"><i class="fa fa-building"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">@lang('lang_v1.general_report_fixed_costs')</span>
                                <span class="info-box-number" id="gr_fixed_costs">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla de resumen --}}
                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-6 col-md-offset-3">
                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <tr>
                                    <td><strong>@lang('lang_v1.general_report_total_sales')</strong></td>
                                    <td class="text-right" id="gr_sum_sales">0.00</td>
                                </tr>
                                <tr class="danger">
                                    <td>@lang('lang_v1.general_report_materials_cost')</td>
                                    <td class="text-right" id="gr_sum_materials">0.00</td>
                                </tr>
                                <tr class="danger">
                                    <td>@lang('lang_v1.general_report_fixed_costs')</td>
                                    <td class="text-right" id="gr_sum_fixed">0.00</td>
                                </tr>
                                <tr class="warning" id="gr_taxes_summary_rows">
                                    {{-- Filas de impuestos se insertan aquí --}}
                                </tr>
                                <tr class="info">
                                    <td><strong>@lang('lang_v1.general_report_subtotal')</strong></td>
                                    <td class="text-right"><strong id="gr_subtotal">0.00</strong></td>
                                </tr>
                                <tr class="warning">
                                    <td><strong>@lang('lang_v1.general_report_total_taxes')</strong></td>
                                    <td class="text-right"><strong id="gr_total_taxes_sum">0.00</strong></td>
                                </tr>
                                <tr class="success">
                                    <td><strong>@lang('lang_v1.general_report_net')</strong></td>
                                    <td class="text-right"><strong id="gr_net_result">0.00</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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

    // Abrir filtros automáticamente al cargar
    $('#collapseFilter').addClass('in').css('height', '');

    // --- Date pickers ---
    $('#gr_start_date, #gr_end_date').datetimepicker({
        format: moment_date_format,
        ignoreReadonly: true
    });

    // Valor inicial: este mes
    setDateRange(moment().startOf('month'), moment());

    function setDateRange(start, end) {
        $('#gr_start_date').val(start.format(moment_date_format));
        $('#gr_end_date').val(end.format(moment_date_format));
    }

    $('#btn_period_this_month').on('click', function () {
        setDateRange(moment().startOf('month'), moment());
    });
    $('#btn_period_last_month').on('click', function () {
        setDateRange(moment().subtract(1, 'months').startOf('month'),
                     moment().subtract(1, 'months').endOf('month'));
    });
    $('#btn_period_this_year').on('click', function () {
        setDateRange(moment().startOf('year'), moment());
    });

    // --- Tax rows ---
    var taxCount = 0;

    $('#btn_add_tax').on('click', function () {
        taxCount++;
        var row = $('<div class="form-inline gr-tax-row" style="margin-bottom: 6px;" data-tax-id="' + taxCount + '">' +
            '<input type="text" class="form-control input-sm gr-tax-name" placeholder="@lang('lang_v1.general_report_tax_name')" style="width: 220px;" />' +
            '&nbsp;' +
            '<input type="number" class="form-control input-sm gr-tax-value" placeholder="@lang('lang_v1.general_report_tax_value')" step="0.01" min="0" style="width: 140px;" />' +
            '&nbsp;' +
            '<button type="button" class="btn btn-danger btn-sm btn-remove-tax"><i class="fa fa-trash"></i></button>' +
            '</div>');
        $('#gr_taxes_container').append(row);
    });

    $(document).on('click', '.btn-remove-tax', function () {
        $(this).closest('.gr-tax-row').remove();
    });

    // --- Generate report ---
    $('#btn_generate_report').on('click', function () {
        var startRaw = $('#gr_start_date').val();
        var endRaw   = $('#gr_end_date').val();

        if (!startRaw || !endRaw) {
            alert('@lang("report.start_date") / @lang("report.end_date")');
            return;
        }

        var startDate = moment(startRaw, moment_date_format).format('YYYY-MM-DD');
        var endDate   = moment(endRaw,   moment_date_format).format('YYYY-MM-DD');

        $.ajax({
            method: 'GET',
            url: '{{ action([\App\Http\Controllers\ReportController::class, "generalReport"]) }}',
            data: { start_date: startDate, end_date: endDate },
            success: function (res) {
                renderReport(res, startRaw, endRaw);
            }
        });
    });

    function fmt(val) {
        return __currency_trans_from_en(parseFloat(val) || 0, true, false);
    }

    function renderReport(res, startLabel, endLabel) {
        var totalSales    = parseFloat(res.total_sales)    || 0;
        var materialsCost = parseFloat(res.materials_cost) || 0;
        var fixedCosts    = parseFloat(res.fixed_costs)    || 0;

        // Info boxes
        $('#gr_total_sales').text(fmt(totalSales));
        $('#gr_materials_cost').text(fmt(materialsCost));
        $('#gr_fixed_costs').text(fmt(fixedCosts));

        // Summary table — base rows
        $('#gr_sum_sales').text(fmt(totalSales));
        $('#gr_sum_materials').text(fmt(materialsCost));
        $('#gr_sum_fixed').text(fmt(fixedCosts));

        // Collect taxes from user inputs
        var taxRows = '';
        var totalTaxes = 0;
        $('.gr-tax-row').each(function () {
            var name  = $(this).find('.gr-tax-name').val().trim()  || '(impuesto)';
            var value = parseFloat($(this).find('.gr-tax-value').val()) || 0;
            totalTaxes += value;
            taxRows += '<tr class="warning"><td>' + $('<span>').text(name).html() + '</td>' +
                       '<td class="text-right">' + fmt(value) + '</td></tr>';
        });
        $('#gr_taxes_summary_rows').replaceWith(
            '<tbody id="gr_taxes_summary_rows">' + taxRows + '</tbody>'
        );

        var subtotal  = totalSales - materialsCost - fixedCosts;
        var netResult = subtotal - totalTaxes;

        $('#gr_subtotal').text(fmt(subtotal));
        $('#gr_total_taxes_sum').text(fmt(totalTaxes));
        $('#gr_net_result').text(fmt(netResult));

        // Period label
        $('#gr_period_label').text(startLabel + ' — ' + endLabel);

        $('#gr_result_section').show();
        $('html, body').animate({ scrollTop: $('#gr_result_section').offset().top - 60 }, 400);
    }

    // --- Print ---
    $('#btn_print_report').on('click', function () {
        openPrintWindow();
    });

    function openPrintWindow() {
        var period   = $('#gr_period_label').text();
        var sales    = $('#gr_sum_sales').text();
        var materials = $('#gr_sum_materials').text();
        var fixed    = $('#gr_sum_fixed').text();
        var subtotal = $('#gr_subtotal').text();
        var taxTotal = $('#gr_total_taxes_sum').text();
        var net      = $('#gr_net_result').text();

        // Build tax rows HTML
        var taxRowsHtml = '';
        $('#gr_taxes_summary_rows tr').each(function () {
            var cells = $(this).find('td');
            if (cells.length === 2) {
                taxRowsHtml += '<tr><td class="label-col">' + $(cells[0]).text() + '</td>' +
                               '<td class="value-col">' + $(cells[1]).text() + '</td></tr>';
            }
        });

        var html = '<!DOCTYPE html><html lang="es"><head>' +
            '<meta charset="UTF-8">' +
            '<title>@lang("lang_v1.general_report")</title>' +
            '<style>' +
                '* { box-sizing: border-box; margin: 0; padding: 0; }' +
                'body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; padding: 40px; }' +
                '.report-wrapper { max-width: 680px; margin: 0 auto; }' +
                '.report-header { border-bottom: 3px solid #00434a; padding-bottom: 16px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; }' +
                '.report-header .title { font-size: 22px; font-weight: 700; color: #00434a; letter-spacing: 0.5px; }' +
                '.report-header .period { font-size: 12px; color: #555; text-align: right; }' +
                '.report-header .period strong { display: block; font-size: 13px; color: #1a1a1a; }' +
                '.section-title { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #00434a; margin-bottom: 8px; margin-top: 28px; }' +
                'table { width: 100%; border-collapse: collapse; }' +
                'table td { padding: 9px 12px; border-bottom: 1px solid #e8e8e8; }' +
                'table .label-col { color: #444; }' +
                'table .value-col { text-align: right; font-variant-numeric: tabular-nums; font-weight: 500; min-width: 120px; }' +
                'tr.income td { background: #f0faf4; }' +
                'tr.income .value-col { color: #1a7a4a; font-weight: 700; }' +
                'tr.cost td { background: #fff8f0; }' +
                'tr.cost .value-col { color: #c0392b; }' +
                'tr.tax td { background: #fffbf0; }' +
                'tr.tax .value-col { color: #b7800a; }' +
                'tr.subtotal td { background: #f4f8fb; border-top: 2px solid #c5d8e8; border-bottom: 2px solid #c5d8e8; }' +
                'tr.subtotal .label-col { font-weight: 600; }' +
                'tr.subtotal .value-col { font-weight: 700; color: #1a5a8a; font-size: 14px; }' +
                'tr.net td { background: #00434a; border-top: none; border-bottom: none; }' +
                'tr.net .label-col { color: #fff; font-weight: 700; font-size: 14px; }' +
                'tr.net .value-col { color: #7fffd4; font-weight: 700; font-size: 16px; }' +
                '.divider { height: 1px; background: #e0e0e0; margin: 6px 0; }' +
                '.footer { margin-top: 32px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 11px; color: #999; text-align: right; }' +
                '@media print {' +
                    'body { padding: 20px; }' +
                    'tr.net td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
                    'tr.income td, tr.cost td, tr.tax td, tr.subtotal td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
                '}' +
            '</style>' +
            '</head><body>' +
            '<div class="report-wrapper">' +

                '<div class="report-header">' +
                    '<div class="title">@lang("lang_v1.general_report")</div>' +
                    '<div class="period">Período<strong>' + period + '</strong></div>' +
                '</div>' +

                '<div class="section-title">Ingresos</div>' +
                '<table>' +
                    '<tr class="income"><td class="label-col">@lang("lang_v1.general_report_total_sales")</td><td class="value-col">' + sales + '</td></tr>' +
                '</table>' +

                '<div class="section-title">Egresos</div>' +
                '<table>' +
                    '<tr class="cost"><td class="label-col">@lang("lang_v1.general_report_materials_cost")</td><td class="value-col">' + materials + '</td></tr>' +
                    '<tr class="cost"><td class="label-col">@lang("lang_v1.general_report_fixed_costs")</td><td class="value-col">' + fixed + '</td></tr>' +
                '</table>' +

                (taxRowsHtml ? '<div class="section-title">@lang("lang_v1.general_report_taxes")</div><table>' + taxRowsHtml.replace(/<tr>/g, '<tr class="tax">') + '</table>' : '') +

                '<table style="margin-top: 16px;">' +
                    '<tr class="subtotal"><td class="label-col">@lang("lang_v1.general_report_subtotal")</td><td class="value-col">' + subtotal + '</td></tr>' +
                    (taxRowsHtml ? '<tr><td class="label-col" style="color:#b7800a;">@lang("lang_v1.general_report_total_taxes")</td><td class="value-col" style="text-align:right;color:#b7800a;">' + taxTotal + '</td></tr>' : '') +
                    '<tr class="net"><td class="label-col">@lang("lang_v1.general_report_net")</td><td class="value-col">' + net + '</td></tr>' +
                '</table>' +

                '<div class="footer">Generado el ' + moment().format('DD/MM/YYYY HH:mm') + '</div>' +
            '</div>' +
            '<script>window.onload = function(){ window.print(); }<\/script>' +
            '</body></html>';

        var win = window.open('', '_blank', 'width=800,height=700');
        win.document.write(html);
        win.document.close();
    }

});
</script>
@endsection
