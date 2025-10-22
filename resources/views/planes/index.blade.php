<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Planes</title>
    
    <!-- Preconexión para mejorar el rendimiento -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Cargar las fuentes Poppins y Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif; /* Aplica la fuente Poppins */
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        /* Content Header */
        .content-header {
            padding: 20px;
            background-color: #005a5a;
            color: white;
            text-align: center;
        }

        .content-header h1 {
            margin: 0;
            font-size: 2rem;
        }

        /* Main Content */
        .content {
            padding: 20px;
        }

        .box {
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .box-header {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 10px 10px 0 0;
            text-align: center; /* Asegúrate de que esté aquí */
        }
        
        .box-title {
            font-size: 1.5rem;
            color: #333;
            text-align: center; /* También asegura que este estilo esté aplicado */
        }


        .box-body {
            padding: 20px;
            background-color: white;
            border-radius: 0 0 10px 10px;
        }

        .box-body p {
            font-size: 1.2rem;
            color: #666;
        }

        .box-body h4 {
            font-size: 1.5rem;
            color: #333;
            margin-top: 10px;
        }

        .box-footer {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 0 0 10px 10px;
            text-align: center;
        }

        .btn {
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        /* Plan Box Colors */
        .box-primary {
            background-color: #007bff;
            color: white;
        }

        .box-success {
            background-color: #28a745;
            color: white;
        }

        .box-warning {
            background-color: #ffc107;
            color: white;
        }

        .box-footer .btn {
            background-color: white;
            color: #333;
            border: 2px solid #333;
        }

        .box-footer .btn:hover {
            background-color: #333;
            color: white;
        }

        /* Grid Layout for the Boxes */
        .row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .col-sm-4 {
            flex: 1;
            min-width: 300px;
        }
        
        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Elegi tu plan</h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        <div class="row">

            <!-- Plan $9000 -->
            <div class="col-sm-4">
                <div class="box box-success">
                    <div class="box-header text-center">
                        <h3 class="box-title">Plan Basico</h3>
                    </div>
                    <div class="box-body text-center">
                        <p>Descripcion</p>
                        <p>Duracion: 30 Dias</p>
                        <p>Acceso a todas las funcionalidades durante un periodo de 30 dias, plan basico para tu negocio con el cual podras facturar ventas y gestionar tu negocio.</p>
                        <h4>Precio: $9000</h4>
                    </div>
                    <div class="box-footer text-center">
                        <button class="btn btn-success" onclick="choosePlan('9000')">Solicitar</button>
                    </div>
                </div>
            </div>

            <!-- Plan $25000 -->
            <div class="col-sm-4">
                <div class="box box-warning">
                    <div class="box-header text-center">
                        <h3 class="box-title">Plan Completo</h3>
                    </div>
                    <div class="box-body text-center">
                        <p>Descripcion</p>
                        <p>Duracion: 30 Dias</p>
                        <p>Acceso a todas las funcionalidades durante un periodo de 30 dias, plan basico para tu negocio con el cual podras facturar ventas y gestionar tu negocio. 
                        Ademas, tendras acceso a solicitudes de desarrollo y atencion personalizada para tu negocio.</p>
                        <h4>Precio: $25000</h4>
                    </div>
                    <div class="box-footer text-center">
                        <button class="btn btn-success" onclick="choosePlan('25000')">Solicitar</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->

    <!-- Modal HTML -->
    <div class="modal" id="planModal" tabindex="-1" role="dialog" aria-labelledby="planModalLabel" aria-hidden="true">
        <div class="modal-dialog relative w-auto max-w-6xl mx-auto mt-12">
            <div class="modal-content bg-white rounded-lg shadow-lg p-6">
                <div class="modal-header flex justify-between items-center pb-4 border-b">
                    <h5 class="modal-title text-2xl font-semibold" id="planModalLabel">Confirmar Solicitud de Plan</h5>
                    <button type="button" class="text-gray-600 hover:text-gray-800" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="planForm" method="POST" action="{{ route('business.store') }}" enctype="multipart/form-data">
    
                        <!-- Language -->
                        <input type="hidden" name="language" value="es">
    
                        <!-- Plan Details -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-4">
                            <div class="form-group">
                                <label for="name" class="text-gray-700 font-medium">Nombre del Paquete</label>
                                <input type="text" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="name" name="name" required>
                            </div>
    
                            <div class="form-group">
                                <label for="start_date" class="text-gray-700 font-medium">Fecha de Inicio</label>
                                <input type="date" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="start_date" name="start_date" required>
                            </div>
    
                            <div class="form-group">
                                <label for="currency_id" class="text-gray-700 font-medium">ID de Moneda</label>
                                <select class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="currency_id" name="currency_id">
                                    <option value="4">ARS - Pesos Argentinos</option>
                                    <!-- Añadir más opciones si es necesario -->
                                </select>
                            </div>
    
                            <div class="form-group">
                                <label for="website" class="text-gray-700 font-medium">Sitio Web</label>
                                <input type="url" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="website" name="website">
                            </div>
                        </div>
    
                        <!-- Contact Information -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-4">
                            <div class="form-group">
                                <label for="mobile" class="text-gray-700 font-medium">Número de Teléfono</label>
                                <input type="tel" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="mobile" name="mobile">
                            </div>
    
                            <div class="form-group">
                                <label for="alternate_number" class="text-gray-700 font-medium">Número Alternativo</label>
                                <input type="tel" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="alternate_number" name="alternate_number">
                            </div>
    
                            <div class="form-group">
                                <label for="country" class="text-gray-700 font-medium">País</label>
                                <input type="text" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="country" name="country" value="Argentina" required>
                            </div>
    
                            <div class="form-group">
                                <label for="state" class="text-gray-700 font-medium">Provincia/Estado</label>
                                <input type="text" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="state" name="state" value="ProbarEstado" required>
                            </div>
                        </div>
    
                        <!-- Address Information -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-4">
                            <div class="form-group">
                                <label for="city" class="text-gray-700 font-medium">Ciudad</label>
                                <input type="text" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="city" name="city" value="Buenos Aires" required>
                            </div>
    
                            <div class="form-group">
                                <label for="zip_code" class="text-gray-700 font-medium">Código Postal</label>
                                <input type="text" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="zip_code" name="zip_code" value="1706" required>
                            </div>
    
                            <div class="form-group">
                                <label for="landmark" class="text-gray-700 font-medium">Hito</label>
                                <input type="text" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="landmark" name="landmark" value="Haedo">
                            </div>
    
                            <div class="form-group">
                                <label for="time_zone" class="text-gray-700 font-medium">Zona Horaria</label>
                                <input type="text" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="time_zone" name="time_zone" value="America/Argentina/Buenos_Aires" required>
                            </div>
                        </div>
    
                        <!-- Personal Information -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-4">
                            <div class="form-group">
                                <label for="first_name" class="text-gray-700 font-medium">Primer Nombre</label>
                                <input type="text" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="first_name" name="first_name" value="Prueba Nombre" required>
                            </div>
    
                            <div class="form-group">
                                <label for="last_name" class="text-gray-700 font-medium">Apellido</label>
                                <input type="text" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="last_name" name="last_name">
                            </div>
    
                            <div class="form-group">
                                <label for="username" class="text-gray-700 font-medium">Nombre de Usuario</label>
                                <input type="text" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="username" name="username" value="PruebaPaquete" required>
                            </div>
    
                            <div class="form-group">
                                <label for="email" class="text-gray-700 font-medium">Correo Electrónico</label>
                                <input type="email" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="email" name="email" value="pruebaemail@gmail.com" required>
                            </div>
                        </div>
    
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-4">
                            <div class="form-group">
                                <label for="password" class="text-gray-700 font-medium">Contraseña</label>
                                <input type="password" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="password" name="password" value="12345678" required>
                            </div>
    
                            <div class="form-group">
                                <label for="confirm_password" class="text-gray-700 font-medium">Confirmar Contraseña</label>
                                <input type="password" class="form-control w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="confirm_password" name="confirm_password" value="12345678" required>
                            </div>
    
                            <div class="form-group">
                                <label for="package_id" class="text-gray-700 font-medium">ID del Paquete</label>
                                <input type="hidden" name="package_id" value="1">
                            </div>
    
                            <div class="form-group">
                                <label for="paid_via" class="text-gray-700 font-medium">Método de Pago</label>
                                <input type="hidden" name="paid_via" value="offline">
                                <input type="hidden" name="payment_transaction_id" value="">
                            </div>
                        </div>
    
                        <!-- Submit Button -->
                        <button type="submit" class="btn bg-blue-600 text-white w-full py-3 rounded-lg hover:bg-blue-700 transition duration-300">Enviar Solicitud</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Mostrar el modal
        function choosePlan(price) {
            document.getElementById('planModal').style.display = 'block';
        }

        // Cerrar el modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('planModal').style.display = 'none';
        }

        // Cerrar el modal si el usuario hace clic fuera del modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('planModal')) {
                document.getElementById('planModal').style.display = 'none';
            }
        }
    </script>
    <script>
        function submitFormData() {
            const data = new FormData();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            // Agregar los campos al FormData
            data.append('_token', csrfToken);
            data.append('language', 'es');
            data.append('name');
            data.append('start_date');
            data.append('currency_id', '4');
            data.append('business_logo', null); // Para enviar un archivo, agrega un Blob o File aquí si es necesario
            data.append('website');
            data.append('mobile');
            data.append('alternate_number', '');
            data.append('country', 'Argentina');
            data.append('state');
            data.append('city');
            data.append('zip_code');
            data.append('landmark');
            data.append('time_zone', 'America/Argentina/Buenos_Aires');
            data.append('surname', '');
            data.append('first_name');
            data.append('last_name', '');
            data.append('username');
            data.append('email');
            data.append('password');
            data.append('confirm_password');
            data.append('package_id', '1');
            data.append('paid_via', 'offline');
            data.append('payment_transaction_id', '');
        
            // Enviar la solicitud con fetch usando multipart/form-data
            fetch('https://app.trevitsoft.com/superadmin/business', {
                method: 'POST',
                body: data, // Los datos se envían usando FormData, automáticamente con Content-Type multipart/form-data
            })
            .then(response => response.json())
            .then(jsonResponse => {
                console.log('Respuesta del servidor:', jsonResponse);
                alert('¡Solicitud enviada correctamente!');
            })
            .catch(error => {
                console.error('Error al enviar la solicitud:', error);
                alert('Error de conexión o al procesar la solicitud.');
            });
        }
    </script>


</body>
</html>
