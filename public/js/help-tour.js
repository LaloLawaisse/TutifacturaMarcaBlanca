/*$(document).ready(function() {
    console.log('[Tour] Iniciando configuración');

    // 1. Configurar tags primero
    window.usetifulTags = { /* ... */ };

    // 2. Cargar script con verificación dual
    /*if (!document.getElementById('usetifulScript')) {
        console.log('[Tour] Inyectando script');
        const script = document.createElement('script');
        script.async = true;
        script.src = "https://www.usetiful.com/dist/usetiful.js";
        script.id = 'usetifulScript';
        script.dataset.token = "24d3b231f01b1aa12537464103bf39f9";
        script.dataset.autostart = "false";

        // Verificación en 2 pasos
        script.onload = () => {
            console.log('[Tour] Script cargado - Usetiful disponible?', 
                      typeof Usetiful !== 'undefined', 
                      'Window.Usetiful:', window.Usetiful);
            
            // Forzar disponibilidad en ámbito global
            if (!window.Usetiful && typeof Usetiful !== 'undefined') {
                window.Usetiful = Usetiful;
                console.log('[Tour] Usetiful reasignado a window');
            }
        };

        document.head.appendChild(script);
    }

    // 3. Función segura para iniciar tour
    const startUsetifulTour = () => {
        const maxAttempts = 5;
        let attempts = 0;
        
        const tryStart = () => {
            if (window.Usetiful && typeof window.Usetiful.start === 'function') {
                console.log('[Tour] Iniciando tour (intento #'+attempts+')');
                window.Usetiful.start();
                return true;
            }
            
            if (attempts < maxAttempts) {
                attempts++;
                console.log('[Tour] Esperando Usetiful... (intento #'+attempts+')');
                setTimeout(tryStart, 300);
                return false;
            }
            
            console.error('[Tour] Usetiful no disponible después de '+maxAttempts+' intentos');
            return false;
        };
        
        tryStart();
    };

    // 4. Manejador de clic mejorado
    $('#start_tour').click(function() {
        console.log('[Tour] Click detectado - Estado Usetiful:', typeof Usetiful);
        $("#side-bar").removeClass("tw-overflow-y-auto");
        startUsetifulTour();
    });

    // 5. Inicio automático con verificación reforzada
    if (localStorage.getItem('upos_app_tour_shown') !== 'true') {
        console.log('[Tour] Intento inicio automático');
        startUsetifulTour();
        localStorage.setItem('upos_app_tour_shown', 'true');
    }
});*/