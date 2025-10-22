<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    <div class="modal-header">
      <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
      <h3 class="modal-title">Generar Factura</h3>
    </div>

    <div class="modal-body">
      <form id="facturaForm">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="invoice_type">Tipo de Factura</label>
              <select id="invoice_type" class="form-control">
				<option value="Factura A">Factura A</option>
				<option value="Factura B">Factura B</option>
				<option value="Factura C">Factura C</option>
				<option value="Nota de Credito A">Nota de Credito A</option>
				<option value="Nota de Credito B">Nota de Credito B</option>
				<option value="Nota de Credito C">Nota de Credito C</option>
              </select>
            </div>
            <div class="form-group">
              <label for="invoice_concept">Concepto de la Factura</label>
              <select id="invoice_concept" class="form-control">
                <option value="productos">Productos</option>
                <option value="servicios">Servicios</option>
                <option value="productos_y_servicios">Productos y Servicios</option>
              </select>
            </div>
            <div class="form-group">
              <label for="buyer_document_type">Tipo de Documento</label>
              <select id="buyer_document_type" class="form-control">
                <option>CUIT</option>
                <option>CUIL</option>
                <option>DNI</option>
                <option>Consumidor Final</option>
              </select>
            </div>
            <div class="form-group">
              <label for="buyer_document_number">Número de Documento</label>
              <input type="number" id="buyer_document_number" class="form-control" placeholder="Ingrese número de documento">
            </div>
            <div class="form-group">
              <label for="transaction_date">Fecha Actual</label>
              <input type="date" id="transaction_date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="denomination_total_amount">Precio Total</label>
              <input type="text" id="denomination_total_amount" class="form-control" value="{{ $price_total }}">
            </div>
            <div class="form-group" id="fechaIni">
              <label for="start_date">Fecha de Inicio</label>
              <input type="date" id="start_date" class="form-control">
            </div>
            <div class="form-group" id="fechaFin">
              <label for="end_date">Fecha de Finalización</label>
              <input type="date" id="end_date" class="form-control">
            </div>
            <div class="form-group" id="fechaVen">
              <label for="payment_due_date">Fecha de Vencimiento</label>
              <input type="date" id="payment_due_date" class="form-control">
            </div>
            <div class="form-group" id="invoice_iva_text">
              <label for="invoice_iva">Tipo de IVA</label>
              <select id="invoice_iva" class="form-control">
                <option value="iva_21">IVA 21%</option>
                <option value="iva_27">IVA 27%</option>
                <option value="iva_10">IVA 10.5%</option>
                <option value="excento_iva">Exento IVA</option>
              </select>
            </div>
            <div class="form-group" id="tipo_transferencia">
              <label for="tipoTransferencia">Tipo de Transferencia</label>
              <select id="tipoTransferencia" class="form-control">
                <option>Transferencia al Sistema de Circulación Abierta</option>
                <option>Agente de Depósito Colectivo</option>
              </select>
            </div>
            <div class="form-group" id="cbu_cliente">
              <label for="cbuCliente">CBU del Cliente</label>
              <input type="number" id="cbu_cliente_val" class="form-control" placeholder="Ingrese CBU">
            </div>
            <div class="form-group" id="num_factura">
              <label for="numeroFactura">Número de la Factura</label>
              <input type="number" id="num_factura_val" class="form-control" placeholder="Ingrese número de la factura">
            </div>
            <div class="form-group" id="iva_receptor_text">
                <label class="simple_text" for="iva_receptor">Condición IVA receptor:*</label>
                <select id="iva_receptor" name="iva_receptor" class="form-control select2">
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
            
            <div class="form-group">
              <label for="select_location_id">Locación Comercial</label>
              <select class="form-control" id="select_location_id" name="select_location_id">
                @foreach($business_locations as $id => $name)
                  <option value="{{ $id }}" @if($default_location && $default_location->id == $id) selected @endif>
                    {{ $name }}
                  </option>
                @endforeach
              </select>
            </div>
            

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
        </div>
      </form>
    </div>
    
    <label id="direccion" style="display:none;">{{ session('business.tax_number_2') }}</label>
    <label id="cuitEmpresa" style="display:none;">{{ session('business.tax_number_1') }}</label>
    <label id="fechaInicio" style="display:none;">{{ session('business.start_date') }}</label>
    <label id="logoEmpresa" style="display:none;">{{ session('business.logo') }}</label>
    <label id="business_name" style="display:none;">{{ session('business.name') }}</label>
    
    <div class="modal-footer">
      <button type="button" class="btn btn-primary" id="generar-factura">Generar Factura</button>
      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
    </div>


  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<style type="text/css">
  .modal {
    overflow: auto;
  }
</style>


