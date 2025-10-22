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
                <option>Productos</option>
                <option>Servicios</option>
                <option>Productos y Servicios</option>
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
            <div class="form-group">
              <label for="end_date">Fecha de Finalización</label>
              <input type="date" id="end_date" class="form-control">
            </div>
            <div class="form-group">
              <label for="payment_due_date">Fecha de Vencimiento</label>
              <input type="date" id="payment_due_date" class="form-control">
            </div>
            <div class="form-group">
              <label for="invoice_iva">Tipo de IVA</label>
              <select id="invoice_iva" class="form-control">
                <option>IVA 21%</option>
                <option>IVA 27%</option>
                <option>IVA 10.5%</option>
                <option>Exento IVA</option>
              </select>
            </div>
            <div class="form-group">
              <label for="tipoTransferencia">Tipo de Transferencia</label>
              <select id="tipoTransferencia" class="form-control">
                <option>Transferencia al Sistema de Circulación Abierta</option>
                <option>Agente de Depósito Colectivo</option>
              </select>
            </div>
            <div class="form-group">
              <label for="cbuCliente">CBU del Cliente</label>
              <input type="number" id="cbu_cliente_val" class="form-control" placeholder="Ingrese CBU">
            </div>
            <div class="form-group">
              <label for="numeroFactura">Número de la Factura</label>
              <input type="number" id="num_factura_val" class="form-control" placeholder="Ingrese número de la factura">
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


