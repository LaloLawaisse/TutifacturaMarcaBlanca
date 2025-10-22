@php
    $is_mobile = isMobile();
@endphp
<div class="row">
    <div
        class="pos-form-actions tw-rounded-tr-xl tw-rounded-tl-xl tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white tw-cursor-pointer">
        <div
            class="tw-flex tw-items-center tw-justify-between tw-flex-col sm:tw-flex-row md:tw-flex-row lg:tw-flex-row xl:tw-flex-row tw-gap-2 tw-px-4 tw-py-0 tw-overflow-x-auto tw-w-full">

            <div class="md:!tw-w-none !tw-flex md:!tw-hidden !tw-flex-row !tw-items-center !tw-gap-3">
                <div class="tw-pos-total tw-flex tw-items-center tw-gap-3">
                    <div class="tw-text-black tw-font-bold tw-text-sm tw-flex tw-items-center tw-flex-col tw-leading-1">
                        <div>@lang('sale.total_payable'):</div>
                        {{-- <div>Payable:</div> --}}
                    </div>
                    <input type="hidden" name="final_total" id="final_total_input" value="0.00">
                    <span id="total_payable" class="tw-text-green-900 tw-font-bold tw-text-sm number">0.00</span>
                </div>
            </div>

            <div class="!tw-w-full md:!tw-w-none !tw-flex md:!tw-hidden !tw-flex-row !tw-items-center !tw-gap-3">
               
                    <button type="button"
                        class=" tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-[#001F3E] tw-rounded-md tw-p-2 tw-w-[8.5rem] @if (!$is_mobile)  @endif no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                        id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-money-check-alt"
                            aria-hidden="true"></i> @lang('lang_v1.checkout_multi_pay') </button>


              
                    <button type="button"
                        class="tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-[rgb(40,183,123)] tw-p-2 tw-rounded-md tw-w-[5.5rem] tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1 @if (!$is_mobile)  @endif no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize @if ($is_mobile) col-xs-6 @endif"
                        data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <i class="fas fa-money-bill-alt"
                            aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')</button>


                @if (empty($edit))
                    <button type="button" class="tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-red-600 tw-p-2 tw-rounded-md tw-w-[5.5rem] tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1" id="pos-cancel"> <i
                            class="fas fa-window-close"></i> @lang('sale.cancel')</button>
                @else
                    <button type="button" class="btn-danger tw-dw-btn hide tw-dw-btn-xs" id="pos-delete"
                        @if (!empty($only_payment)) disabled @endif> <i class="fas fa-trash-alt"></i>
                        @lang('messages.delete')</button>
                @endif
            </div>
            <div class="tw-flex tw-items-center tw-gap-4 tw-flex-row tw-overflow-x-auto">

                <!--@if (!Gate::check('disable_draft') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))-->
                <!--    <button type="button"-->
                <!--        class="tw-font-bold tw-text-gray-700 tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 @if ($pos_settings['disable_draft'] != 0) hide @endif"-->
                <!--        id="pos-draft" @if (!empty($only_payment)) disabled @endif><i-->
                <!--            class="fas fa-edit tw-text-[#009ce4]"></i> @lang('sale.draft')</button>-->
                <!--@endif-->

                    <button type="button"
                        class="tw-hidden md:tw-flex md:tw-flex-row md:tw-items-center md:tw-justify-center md:tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-xs tw-bg-[#001F3E] tw-rounded-md tw-p-2 tw-w-[8.5rem] @if ($is_mobile) col-xs-6 @endif"
                        id="pos-quotation" @if (!empty($only_payment)) disabled @endif><i
                            class="fas fa-edit tw-text-[#FFFFFF]"></i> @lang('lang_v1.quotation')</button>
                            
                    


     
                        <button type="button"
                            class="tw-hidden md:tw-flex md:tw-flex-row md:tw-items-center md:tw-justify-center md:tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-xs tw-bg-[#001F3E] tw-rounded-md tw-p-2 tw-w-[8.5rem]  no-print pos-express-finalize"
                            data-pay_method="suspend" title="@lang('lang_v1.tooltip_suspend')"
                            @if (!empty($only_payment)) disabled @endif>
                            <i class="fas fa-pause tw-text-[#EF4B51]" aria-hidden="true"></i>
                            @lang('lang_v1.suspend')
                        </button>
     


                        <input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
                        <button type="button"
                            class=" tw-hidden md:tw-flex md:tw-flex-row md:tw-items-center md:tw-justify-center md:tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-xs tw-bg-[#001F3E] tw-rounded-md tw-p-2 tw-w-[8.5rem] pos-express-finalize @if ($is_mobile) col-xs-6 @endif"
                            data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')"
                            @if (!empty($only_payment)) disabled @endif>
                            <i class="fas fa-check tw-text-[#FFFFFF]" aria-hidden="true"></i> @lang('lang_v1.credit_sale')
                        </button>
      
                <!--@if (!Gate::check('disable_card') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))-->
                <!--    <button type="button"-->
                <!--        class="tw-font-bold tw-text-gray-700 tw-cursor-pointer tw-text-xs md:tw-text-xs tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1  no-print @if (!empty($pos_settings['disable_suspend']))  @endif pos-express-finalize @if (!array_key_exists('card', $payment_types)) hide @endif @if ($is_mobile) col-xs-6 @endif"-->
                <!--        data-pay_method="card" title="@lang('lang_v1.tooltip_express_checkout_card')">-->
                <!--        <i class="fas fa-credit-card tw-text-[#D61B60]" aria-hidden="true"></i> @lang('lang_v1.express_checkout_card')-->
                <!--    </button>-->
                <!--@endif-->
                
                    <button type="button"
                        class="tw-hidden md:tw-flex md:tw-flex-row md:tw-items-center md:tw-justify-center md:tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-[#001F3E] tw-rounded-md tw-p-2 tw-w-[8.5rem] @if (!$is_mobile)  @endif no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                        id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-money-check-alt"
                            aria-hidden="true"></i> Tarjeta </button>


                    

         
                    <button type="button"
                        class="tw-hidden md:tw-flex md:tw-flex-row md:tw-items-center md:tw-justify-center md:tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-xs tw-bg-[#001F3E] tw-rounded-md tw-p-2 tw-w-[8.5rem] tw-hidden md:tw-flex lg:tw-flex lg:tw-flex-row lg:tw-items-center lg:tw-justify-center lg:tw-gap-1 @if (!$is_mobile)  @endif no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize"
                        data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <i class="fas fa-money-bill-alt"
                            aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')</button>
                            
                            
                    <button type="button"
                        class="tw-hidden md:tw-flex md:tw-flex-row md:tw-items-center md:tw-justify-center md:tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-xs tw-bg-[#001F3E] tw-rounded-md tw-p-2 tw-w-[9rem] @if (!$is_mobile)  @endif no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                        id="generar-factura-pos" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-money-check-alt"
                            aria-hidden="true"></i> Generar Factura </button>
                    
                    <label id="logoEmpresa" style="display:none;">{{ session('business.logo') }}</label>
                    <label id="business_name" style="display:none">{{ session('business.name') }}</label>
                    <script>
                        document.getElementById('generar-factura-pos').addEventListener('click', async function () {
                            try {
                                const price_total = parseFloat(document.getElementById('total_payable').textContent.trim().replace(/,/g, '')) || 0;
                                // Extraer productos
                                const tbody = document.querySelector('#pos_table tbody');
                                const rows = tbody.querySelectorAll('tr');
                                const products = Array.from(rows).map(row => {
                                    const columns = row.querySelectorAll('td');
                                    const product_name = columns[0]?.textContent.trim()
                                        .replace(/\s+/g, ' ') // Limpia saltos de línea y múltiples espacios
                                        .trim() // Elimina espacios iniciales y finales
                                        .split('/')[0] // Toma solo la parte antes del primer "/"
                                        .replace(/[^a-zA-Z\s]/g, '') // Elimina números y caracteres no alfabéticos
                                        .split(' ')[0] // Toma la primera palabra antes del primer espacio
                                        || null;
                                        
                                    const quantityInput = columns[1]?.querySelector('input[name*="[quantity]"]');
                                    const quantity = quantityInput ? parseFloat(quantityInput.value.replace(/,/g, '')) : null; // Convertir a número si es válido
                                    
                                    //const unitPriceInput = columns[2]?.querySelector('input[name*="[unit_price]"]');
                                    //const unit_price = unitPriceInput ? parseFloat(unitPriceInput.value) : 0;
                                    
                                    const lineTotalInput = columns[3]?.querySelector('input.pos_line_total');
                                    const subtotal = lineTotalInput ? parseFloat(lineTotalInput.value.replace(/,/g, '')) : null;
                                    
                                    const unit_price = (quantity && quantity !== 0) ? subtotal / quantity : 0; // Evitar división por cero
                                    
                                    return {
                                        product_name,
                                        quantity,
                                        unit_price,
                                        subtotal
                                    };
                                });
                        
                                // Enviar datos al servidor
                                const response = await fetch('/sale_pos/generar-factura-pos', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify({ 
                                        products,
                                        price_total,
                                        }) // Enviar productos como JSON
                                });
                        
                                const data = await response.text();
                            
                                if (!document.getElementById('modalGenerarFactura')) {
                                    document.body.insertAdjacentHTML('beforeend', data); // Insertar el contenido recibido en el body
                                }
                            
                                const modalBody = document.querySelector('#modalGenerarFactura .modal-body');
                                modalBody.innerHTML = data; // Cargar la respuesta dentro del modal-body
                            
                                // Mostrar el modal
                                $('#modalGenerarFactura').modal('show');
                            
                                // Registrar el evento para el botón 'generar-factura' dentro del modal
                                const registrarEventoBoton = () => {
                                    const button = document.getElementById('generar-factura');
                                    if (button) {
                                        console.log('Botón encontrado:', button);
                                        button.addEventListener('click', async function () {
                                            console.log('Evento activado correctamente');
                                            
                                            // Aquí va tu lógica para obtener valores y hacer el fetch de la factura
                                            
                                            var invoice_type = document.getElementById('invoice_type')?.value || null;
                                            var invoice_concept = document.getElementById('invoice_concept')?.value || null;
                                            var buyer_document_type = document.getElementById('buyer_document_type')?.value || null;
                                            var buyer_document_number = document.getElementById('buyer_document_number')?.value || null;
                                            var transaction_date = document.getElementById('transaction_date')?.value || null;
                                            
                                            var denomination_total_amount = document.getElementById('final_total_input')?.value || null;
                                            if (denomination_total_amount) {
                                                denomination_total_amount = parseInt(denomination_total_amount.replace(/,/g, ''), 10);
                                            }
                                            var start_date = document.getElementById('start_date_val')?.value || null;
                                            var end_date = document.getElementById('end_date_val')?.value || null;
                                            var payment_due_date = document.getElementById('payment_due_date_val')?.value || null;
                                            var business_name = document.getElementById('business_name')?.textContent || null;
                                            var fechaInicio = document.getElementById('fechaInicio')?.textContent || null;
                                            var invoice_iva = document.getElementById('invoice_iva')?.value || null;
                                            var direccion = document.getElementById('direccion')?.textContent || null;
                                            var cuitEmpresa = document.getElementById('cuitEmpresa')?.textContent || null;
                                            var tipoTransferencia = document.getElementById('tipo_transferencia')?.value || null;
                                            var cbu_cliente_val = document.getElementById('cbu_cliente_val')?.value || null;
                                            var num_factura_val = document.getElementById('num_factura_val')?.value || null;
                                            var logoEmpresa = document.getElementById('logoEmpresa').textContent || null;
                                            var iva_receptor = document.getElementById('iva_receptor').value;
                                            var pto_vta = document.getElementById('pto_vta').value;
                                            
                                            var selectElement = document.getElementById('select_location_id');
                                            var locacion_comercial = selectElement.options[selectElement.selectedIndex].text;
                                            
                                            var invoiceTypeMap = { 'Factura A': 1, 'Factura B': 6, 'Factura C': 11, 'Nota de Credito A': 3, 'Nota de Credito B': 8, 'Nota de Credito C': 13 };
                                            var invoiceConceptMap = { 'productos': 1, 'servicios': 2, 'productos y servicios': 3 };
                                            var buyerDocumentTypeMap = { 'CUIT': 80, 'CUIL': 86, 'DNI': 96, 'Consumidor Final': 99 };
                                            
                                            invoice_type = invoiceTypeMap[invoice_type] || null;
                                            console.log('Mapped invoice_type:', invoice_type);
                                            
                                            invoice_concept = invoiceConceptMap[invoice_concept] || null;
                                            console.log('Mapped invoice_concept:', invoice_concept);
                                            
                                            buyer_document_type = buyerDocumentTypeMap[buyer_document_type] || null;
                                            console.log('Mapped buyer_document_type:', buyer_document_type);
                                            
                                            // Función para convertir la fecha al formato YYYYMMDD
                                            function convertToDateFormat(date) {
                                              if (!date) return null;
                                            
                                              // Normaliza: quita hora si viene ("YYYY-MM-DDTHH:mm" o con espacio)
                                              let v = String(date).trim();
                                              v = v.split('T')[0].split(' ')[0];
                                            
                                              // YYYY-MM-DD -> YYYYMMDD
                                              let m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(v);
                                              if (m) return m[1] + m[2] + m[3];
                                            
                                              // MM/DD/YYYY -> YYYYMMDD
                                              m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(v);
                                              if (m) return m[3] + m[1] + m[2];
                                            
                                              // Ya viene como YYYYMMDD
                                              if (/^\d{8}$/.test(v)) return v;
                                            
                                              // Fallback: solo dígitos
                                              const digits = v.replace(/\D/g, '');
                                              if (digits.length >= 8) {
                                                // Si empieza con año (YYYY...)
                                                if (/^\d{4}/.test(digits)) return digits.slice(0, 8);      // YYYYMMDD...
                                                // Si parece MMDDYYYY...
                                                return digits.slice(-4) + digits.slice(0, 2) + digits.slice(2, 4);
                                              }
                                            
                                              return null; // formato inesperado
                                            }

                                        
                                            // Convertir la fecha si está presente
                                            if (transaction_date) {
                                                transaction_date = convertToDateFormat(transaction_date);  // Sobrescribe directamente
                                            }
                    
                                            // Realizar el fetch para generar la factura
                                            try {
                                                const response = await fetch('/crear-factura-pos', {
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
                                                        direccion,
                                                        cuitEmpresa,
                                                        fechaInicio,
                                                        invoice_iva,
                                                        tipoTransferencia,
                                                        cbu_cliente_val,
                                                        num_factura_val,
                                                        products,
                                                        logoEmpresa,
                                                        iva_receptor,
                                                        locacion_comercial,
                                                        pto_vta,
                                                    })
                                                });
                    
                                                if (response.ok) {
                                                    // Descargar PDF
                                                    const blob = await response.blob();
                                                    const link = document.createElement('a');
                                                    link.href = URL.createObjectURL(blob);
                                                    link.download = 'factura.pdf';
                                                    link.click();
                                                    alert('Factura generada exitosamente.');
                                                } else {
                                                    const error = await response.json();
                                                    alert(`Error: ${error.message || 'Ocurrió un error inesperado.'}`);
                                                }
                                            } catch (error) {
                                                console.error('Error al generar la factura:', error);
                                                alert('Error al generar la factura.');
                                            }
                                        });
                                    } else {
                                        console.error('El botón no está en el DOM o no se encuentra con este ID.');
                                    }
                                };
                                

                                    $(document).ready(function () {
                                        // Mostrar u ocultar las filas de IVA y Total según la opción seleccionada en el desplegable
                                        $('#invoice_type').change(function () {
                                            const value = $(this).val();  // Obtener el valor seleccionado en el desplegable
                                            if (value === 'Factura A' || value === 'Nota de Credito A' || value === 'Factura B' || value === 'Nota de Credito B') {
                                                $('#invoice_iva').show().prop('disabled', false);
                                                $('#invoice_iva_text').show().prop('disabled', false);
                                                document.dispatchEvent(new Event('invoiceTypeUpdated'));
                                            } else {
                                                $('#invoice_iva').hide().prop('disabled', true).val('');
                                                $('#invoice_iva_text').hide().prop('disabled', true).val('');
                                                document.dispatchEvent(new Event('invoiceTypeUpdated'));
                                            }
                                        }).trigger('change');  // Ejecuta al cargar la página para aplicar el estado inicial
                                    });
                                
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


                                    $(document).ready(function () {
                                        
                                        $('#invoice_concept').change(function () {
                                            const value = $(this).val();  
                                            if (value === 'servicios' || value === 'productos_y_servicios') {
                                                $('#fechaIni').show();
                                                $('#fechaFin').show();
                                                $('#fechaVen').show();
                                                document.dispatchEvent(new Event('invoiceConceptUpdated'));
                                            } else {
                                                $('#fechaIni').hide();  
                                                $('#fechaFin').hide();
                                                $('#fechaVen').hide();
                                                document.dispatchEvent(new Event('invoiceConceptUpdated'));
                                            }
                                        }).trigger('change');  
                                    });
                                    
                                    
                                    $(document).ready(function ()  {
                                        const invoiceIvaSelect = document.getElementById('invoice_iva'); // Select del IVA
                                        const denominationTotalInput = document.getElementById('denomination_total_amount'); // Input del total
                                    
                                        // Guardar el valor inicial del input al cargar la página
                                        const initialBaseAmount = parseFloat(denominationTotalInput.value.replace(/,/g, '')) || 0;
                                    
                                        // Evento cuando cambia el select de IVA
                                        invoiceIvaSelect.addEventListener('change', function () {
                                            const ivaType = this.value;
                                    
                                            let ivaPercentage = 0;
                                    
                                            // Mapear el tipo de IVA a su porcentaje
                                            switch (ivaType) {
                                                case 'iva_21':
                                                    ivaPercentage = 21;
                                                    break;
                                                case 'iva_27':
                                                    ivaPercentage = 27;
                                                    break;
                                                case 'iva_10':
                                                    ivaPercentage = 10.5;
                                                    break;
                                                case 'excento_iva':
                                                    ivaPercentage = 0;
                                                    break;
                                                default:
                                                    ivaPercentage = 0;
                                            }
                                    
                                            // Calcular el total a partir del valor inicial
                                            const totalAmount = initialBaseAmount * (1 + ivaPercentage / 100);
                                    
                                            // Actualizar el valor del input (redondeado a 2 decimales)
                                            denominationTotalInput.value = totalAmount.toFixed(2);
                                        });
                                    
                                        // Ejecuta el cambio al cargar la página para aplicar el estado inicial
                                        invoiceIvaSelect.dispatchEvent(new Event('change'));
                                    });

                                // Registrar evento al botón dinámico después de mostrar el modal
                                registrarEventoBoton();
                            } catch (error) {
                                console.error('Error al cargar la vista:', error);
                            }
                        });
                    </script>

                    <button type="button"
                        class="tw-hidden md:tw-flex md:tw-flex-row md:tw-items-center md:tw-justify-center md:tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-xs tw-bg-[#001F3E] tw-rounded-md tw-p-2 tw-w-[8.5rem] no-print"
                        id="carga-masiva-pos" 
                        title="Cargar Facturas Masivas">
                        <i class="fas fa-file-upload" aria-hidden="true"></i> Cargar Masivo
                    </button>
                    <script>
                        document.getElementById('carga-masiva-pos').addEventListener('click', async function () {
                            try {
                                // Realizar fetch a la ruta correspondiente
                                const response = await fetch('/sale_pos/carga-masiva-pos', {
                                    method: 'GET',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                });
                    
                                // Verificar que la respuesta sea exitosa
                                if (!response.ok) {
                                    throw new Error(`Error ${response.status}: ${response.statusText}`);
                                }
                    
                                // Obtener el contenido HTML de la respuesta
                                const data = await response.text();
                    
                                // Insertar el contenido dinámico en el modal
                                const modalBody = document.querySelector('#modalCargaMasiva .modal-body');
                                modalBody.innerHTML = data;
                    
                                // Mostrar el modal
                                $('#modalCargaMasiva').modal('show');
                    
                            } catch (error) {
                                console.error('Error al cargar el contenido del modal:', error);
                                alert('Ocurrió un error inesperado al cargar el modal.');
                            }
                        });
                    </script>


                
                @if (empty($edit))
                    <button type="button"
                        class="tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-xs tw-bg-red-600 tw-p-2 tw-rounded-md tw-w-[8.5rem] tw-hidden md:tw-flex lg:tw-flex lg:tw-flex-row lg:tw-items-center lg:tw-justify-center lg:tw-gap-1"
                        id="pos-cancel"> <i class="fas fa-window-close"></i> @lang('sale.cancel')</button>
                @else
                    <button type="button"
                        class="tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-xs tw-bg-red-600 tw-p-2 tw-rounded-md tw-w-[8.5rem] tw-hidden md:tw-flex lg:tw-flex lg:tw-flex-row lg:tw-items-center lg:tw-justify-center lg:tw-gap-1 hide"
                        id="pos-delete" @if (!empty($only_payment)) disabled @endif> <i
                            class="fas fa-trash-alt"></i> @lang('messages.delete')</button>
                @endif

                @if (!$is_mobile)
                    {{-- <div class="bg-navy pos-total text-white ">
					<span class="text">Total Venta</span>
					<input type="hidden" name="final_total" 
												id="final_total_input" value=0>
					<span id="total_payable" class="number">0</span>
					</div> --}}
                    <div class="pos-total md:tw-flex md:tw-items-center md:tw-gap-3 tw-hidden">
                        <div
                            class="tw-text-black tw-font-bold tw-text-sm md:tw-text-lg tw-flex tw-items-center tw-flex-col">
                            <div>Total Venta:</div>
                        </div>

                        <input type="hidden" name="final_total" id="final_total_input" value="0.00">
                        <span id="total_payable"
                            class="tw-text-green-900 tw-font-bold tw-text-base md:tw-text-lg number">0.00</span>
                    </div>
                @endif
            </div>

            <div class="tw-w-full md:tw-w-fit tw-flex tw-flex-col tw-items-end tw-gap-3 tw-hidden md:tw-block">
                @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                    <button type="button"
                        class="tw-hidden md:tw-flex md:tw-flex-row md:tw-items-center md:tw-justify-center md:tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-[#001F3E] tw-rounded-md tw-p-2 "
                        data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions"> <i
                            class="fas fa-clock"></i> @lang('lang_v1.recent_transactions')</button>
                @endif
            </div>
        </div>
    </div>
</div>
@if (isset($transaction))
    @include('sale_pos.partials.edit_discount_modal', [
        'sales_discount' => $transaction->discount_amount,
        'discount_type' => $transaction->discount_type,
        'rp_redeemed' => $transaction->rp_redeemed,
        'rp_redeemed_amount' => $transaction->rp_redeemed_amount,
        'max_available' => !empty($redeem_details['points']) ? $redeem_details['points'] : 0,
    ])
@else
    @include('sale_pos.partials.edit_discount_modal', [
        'sales_discount' => $business_details->default_sales_discount,
        'discount_type' => 'percentage',
        'rp_redeemed' => 0,
        'rp_redeemed_amount' => 0,
        'max_available' => 0,
    ])
@endif

@if (isset($transaction))
    @include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $transaction->tax_id])
@else
    @include('sale_pos.partials.edit_order_tax_modal', [
        'selected_tax' => $business_details->default_sales_tax,
    ])
@endif

@include('sale_pos.partials.edit_shipping_modal')







