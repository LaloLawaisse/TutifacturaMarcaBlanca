<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Enviar Factura AFIP</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
                <div class="form-group">
                    <label>Enlace de la factura:</label>
                    <input type="text" class="form-control" id="afip_invoice_url" 
                        value="{{ $enlace }}" readonly>
                </div>
                <div class="form-group">
                    <label>Número de WhatsApp:</label>
                    <input type="tel" class="form-control" id="whatsapp_number" 
                       value="549{{ $numero_whatsapp ?? '' }}" 
                       placeholder="5491112345678">
                    <small class="form-text text-muted">Formato internacional sin prefijo</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary btn-enviar-whatsapp">
                    <i class="fab fa-whatsapp"></i> Enviar
                </button>
            </div>
        </div>
    </div>
</div>