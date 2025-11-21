@extends('layouts.auth2')
@section('title', config('app.name', 'ultimatePOS'))
@inject('request', 'Illuminate\Http\Request')
@section('content')
<div class="col-md-12 col-sm-12 col-xs-12 right-col tw-pt-20 tw-pb-10 tw-px-5 tw-flex tw-flex-col tw-items-center tw-justify-center" 
    style="background-color: #FF006B;">
    <div class="tw-text-6xl tw-font-extrabold tw-text-center tw-text-white tw-shadow-lg tw-px-4 tw-py-2 tw-rounded-md"
    style="background-color: #FF3385;">
        {{ config('app.name', 'UltimatePOS') }}
    </div>
    
    <p class="tw-text-lg tw-font-medium tw-text-center tw-text-white tw-mt-2 tw-shadow-md tw-rounded-md tw-px-3 tw-py-1"
    style="background-color: #FF3385;">
        HOME
    </p>
</div>

<div id="whatsapp-button">
            <a href="https://api.whatsapp.com/send/?phone=%2B5491138752594&text=Hola%21%20Te%20escribo%20por%20asistencia%20con%20la%20pagina%20Tutifactura.&type=phone_number&app_absent=0" target="_blank" title="Escríbenos por WhatsApp">
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


@endsection
            