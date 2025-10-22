<?php

use Illuminate\Support\Facades\Route;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Carga Masiva de Facturas</h1>
    
    <?php if (session('success')): ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php elseif (session('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <form action="<?= route('carga-masiva') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="archivo_csv" class="form-label">Seleccione el archivo CSV:</label>
            <input type="file" class="form-control" id="archivo_csv" name="archivo_csv" accept=".csv" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Procesar Archivo</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>