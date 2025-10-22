<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    <div class="modal-header">
      <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
      <h3 class="modal-title">Carga Masiva de Facturas</h3>
    </div>

    <div class="modal-body">
      <form action="<?= route('carga-masiva') ?>" method="POST" enctype="multipart/form-data" id="uploadCSVForm">
        <?= csrf_field() ?>
        
        <div class="form-group">
          <label for="archivo_csv">Seleccione el archivo CSV:</label>
          <input type="file" class="form-control" id="archivo_csv" name="archivo_csv" accept=".csv" required>
        </div>
        
      </form>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary" form="uploadCSVForm">Procesar Archivo</button>
      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
    </div>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<style type=
