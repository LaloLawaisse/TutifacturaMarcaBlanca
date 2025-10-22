<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscripción Trevitsoft</title>
  <!-- Importamos la fuente Poppins desde Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* Estilos generales */
    body, html {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f5f6fa;
    }
    /* Contenedor principal del formulario */
    .payment-container {
      max-width: 400px;
      margin: 50px auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    /* Cabecera con logo y título */
    .payment-header {
      text-align: center;
      margin-bottom: 20px;
    }
    .payment-header img {
      height: 40px;
      margin-bottom: 10px;
    }
    .payment-header h1 {
      font-size: 24px;
      margin: 0;
      color: #333;
    }
    /* Estilo para el contenedor del Brick de pago */
    #cardPaymentBrick_container {
      margin-bottom: 20px;
    }
    /* Mensaje de error */
    .error-message {
      color: #dc3545;
      padding: 10px;
      border: 1px solid #f5c6cb;
      border-radius: 4px;
      display: none;
      background-color: #f8d7da;
    }
  </style>
</head>
<body>
  <div class="payment-container">
    <div class="payment-header">
      <h1>Trevitsoft</h1>
    </div>
    <div id="cardPaymentBrick_container"></div>
    <div id="paymentErrors" class="error-message"></div>
  </div>

  <!-- SDK de Mercado Pago -->
  <script src="https://sdk.mercadopago.com/js/v2"></script>
  
  @php
    $planId = request('planId') ?? 1;
    switch ($planId) {
        case 1:
            $amount = 25000;
            break;
        case 2:
            $amount = 50000;
            break;
        case 3:
            $amount = 75000;
            break;
        case 5:
            $amount = 144000;
            break;
        default:
            $amount = 100; // Valor por defecto
            break;
    }
  @endphp

  <script>
  // Inicialización de Mercado Pago
  const mp = new MercadoPago('APP_USR-164dd449-93fb-444f-bb5c-6ed119e81774', { 
    locale: 'es-AR',
    advancedFraudPrevention: true 
  });

  const renderCardPaymentBrick = async () => {
    const settings = {
      initialization: {
        amount: {{ $amount }} // Se usa la cantidad obtenida en PHP
      },
      customization: {
        paymentMethods: {
          creditCard: {
            installments: [1] // Solo permite UNA cuota
          },
          debitCard: {
            installments: [1] // Solo permite UNA cuota
          }
        }
      },
      callbacks: {
        onReady: () => {
          document.getElementById('paymentErrors').style.display = 'none';
        },
        onSubmit: async (cardFormData) => {
            
            const planId = {{ $planId }};
            
          try {
            const response = await fetch("/process_payment", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
              },
              body: JSON.stringify({
                ...cardFormData,
                transaction_amount: {{ $amount }}
              })
            });

            const data = await response.json();
            
            if (!response.ok) {
              throw new Error(data.message || 'Error en el pago');
            }

            if (data.status === 'success') {
              let redirectUrl = '';
              // Obtengo el parámetro "action" de la URL enviado desde la redirección del botón
              const actionFromUrl = "{{ request('action', 'register') }}"; // 'register' por defecto

              if (actionFromUrl === 'nonregister') {
                // Cuando es nonregister, se redirige a la acción "pay" del SubscriptionController.
                // Generamos la URL con un placeholder que luego sustituimos por el id del plan.
                redirectUrl = redirectUrl = "{{ action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'pay'], ['package_id' => '__PACKAGE__']) }}".replace('__PACKAGE__', planId);

              } else {
                // Flujo por defecto (register): redirección según el monto (y paquete) obtenido.
                if ({{ $amount }} === 25000) {
                  redirectUrl = 'https://app.trevitsoft.com/business/register?package=1';
                } else if ({{ $amount }} === 50000) {
                  redirectUrl = 'https://app.trevitsoft.com/business/register?package=2';
                } else if ({{ $amount }} === 75000) {
                  redirectUrl = 'https://app.trevitsoft.com/business/register?package=3';
                } else if ({{ $amount }} === 144000) {
                  redirectUrl = 'https://app.trevitsoft.com/business/register?package=5';
                }
              }

              if (redirectUrl) {
                window.location.href = redirectUrl;
              } else {
                showError('No se encontró un paquete correspondiente');
              }
            } else {
              showError(data.status_detail || 'El pago no fue aprobado');
            }
            
            return data;

          } catch (error) {
            showError(error.message);
            throw error;
          }
        },
        onError: (error) => {
          showError(`Error en el formulario: ${error.message}`);
        }
      }
    };

    mp.bricks().create('cardPayment', 'cardPaymentBrick_container', settings);
  };

  // Función para mostrar mensajes de error
  function showError(message) {
    const errorContainer = document.getElementById('paymentErrors');
    errorContainer.textContent = message;
    errorContainer.style.display = 'block';
  }

  renderCardPaymentBrick();
</script>

</body>
</html>
