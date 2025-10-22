@foreach($featured_products as $variation)
    @php
        // Resolver URL de imagen en una sola variable
        if (!empty($variation->media) && $variation->media->count() > 0) {
            $imgUrl = $variation->media->first()->display_url;
        } elseif (!empty($variation->product->image_url)) {
            $imgUrl = $variation->product->image_url;
        } else {
            $imgUrl = asset('/img/default.png');
        }
    @endphp

    <div class="col-md-3 col-xs-4 product_list no-print">
        <div class="product_box hover:tw-shadow-lg hover:tw-animate-pulse"
             data-toggle="tooltip"
             data-placement="bottom"
             data-variation_id="{{ $variation->id }}"
             title="{{ $variation->full_name }}"
             style="position: relative;">

            <!-- DEBUG: URL de imagen elegida -->
            <!-- DEBUG imgUrl: {{ $imgUrl }} -->

            <div class="image-container"
                 data-img-url="{{ $imgUrl }}"
                 style="
                    /* Forzar visibilidad y dimensiones */
                    display: block;
                    width: 100%;
                    height: 140px !important;
                    min-height: 140px !important;

                    /* Mostrar la imagen como fondo */
                    background-image: url('{{ $imgUrl }}');
                    background-repeat: no-repeat;
                    background-position: center center;
                    background-size: contain;

                    /* Borde de debug para ver el contenedor */
                    border: 1px dashed #e11d48;
                 ">
            </div>

            {{-- Imagen oculta para verificar 200/404/403 y mixed content en consola --}}
            <img src="{{ $imgUrl }}" alt="debug-image"
                 style="display:none"
                 onload="console.info('DEBUG: imagen cargó OK:', this.src)"
                 onerror="console.warn('DEBUG: imagen no cargó (404/403/mixed content/CSP):', this.src)" />

            <div class="text_div" style="position: relative; z-index: 1;">
                <small class="text text-muted">{{ $variation->product->name }}
                    @if($variation->product->type == 'variable')
                        - {{ $variation->name }}
                    @endif
                </small>

                <small class="text-muted">
                    ({{ $variation->sub_sku }})
                </small>
            </div>

        </div>
    </div>
@endforeach
