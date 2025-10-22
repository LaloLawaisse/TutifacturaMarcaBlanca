@extends('layouts.auth')
@section('title', __('superadmin::lang.pricing'))

@section('content')

<div class="container">
    @include('superadmin::layouts.partials.currency')
    @include('layouts.partials.logo')
    <div class="row">
        <div class="box">
            <div class="box-body">
                @include('superadmin::subscription.partials.packages', ['action_type' => 'register'])
            </div>
        </div>
    </div>
</div>
<div id="whatsapp-button">
            <a href="https://api.whatsapp.com/send/?phone=%2B5493573514309&text=Hola%21%20Te%20escribo%20por%20asistencia%20con%20la%20pagina%20trevitsoft.&type=phone_number&app_absent=0" target="_blank" title="Escríbenos por WhatsApp">
                <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" />
            </a>
        </div>

            <div class="overlay tw-hidden"></div>
            

<style>
    @media print {
  #scrollable-container {
    overflow: visible !important;
    height: auto !important;
  }
}
</style>
<style>

    .container {
    max-width: 2600px; /* Aumenta el ancho total del contenedor */
    }
    
    .box {
        font-family: 'Poppins', sans-serif;
        padding: 30px; /* Más espacio interno */
        font-size: 16px; /* Aumenta el tamaño de letra general */
        border-radius: 15px; /* Esquinas más redondeadas */
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); /* Efecto de sombra */
    }
    
    .box-body {
        text-align: left; /* Centra el contenido */
    }
    
    .box h1 {
        font-weight
        font-size: 108px;
    }
    
    .box h2 {
        font-size: 1px; /* Aumenta el tamaño del título */
        font-weight: bold;
    }

    .box h3 {
        font-size: 26px; /* Aumenta el tamaño del precio */
        font-weight: bold;
        color: #28a745; /* Color verde */
    }


    .small-view-side-active {
        display: grid !important;
        z-index: 1000;
        position: absolute;
    }
    .overlay {
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.8);
        position: fixed;
        top: 0;
        left: 0;
        display: none;
        z-index: 20;
    }
    .box-body i {
    font-size: 24px; /* Íconos más grandes */
    margin-right: 5px;
    }
    
    .box-body span,
    .box-body p {
        font-size: 20px; /* Texto más grande */
    }
    

    .tw-dw-btn.tw-dw-btn-xs.tw-dw-btn-outline {
        width: max-content;
        margin: 2px;
    }

    #scrollable-container{
        position:relative;
    }
    
    #whatsapp-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    }
    
    #whatsapp-button a {
        display: block;
        width: 60px;
        height: 60px;
    }
    
    #whatsapp-button img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    #whatsapp-button img:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }




</style>

@stop

@section('javascript')
<script type="text/javascript">
    $(document).ready(function(){
        $('#change_lang').change( function(){
            window.location = "{{ route('pricing') }}?lang=" + $(this).val();
        });
    })
</script>
@endsection


