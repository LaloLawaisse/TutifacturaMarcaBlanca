<!DOCTYPE html>
<html>
<head>
    <title>Vista previa de Factura</title>
    <style>
        .container { margin: 20px; }
        iframe { width: 100%; height: 80vh; border: none; }
        .download-btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <iframe src="{{ $preview_url }}"></iframe>
        <br>
        <a href="{{ $file_url }}" class="download-btn">Descargar Factura</a>
    </div>
</body>
</html>