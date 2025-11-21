<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">


<div class="col-md-4">
    <div class="box box-success hvr-grow-shadow with-border">
        <div class="box-header with-border text-center">
            <h2 class="box-title" style="font-size: 35px;" >{{ $package->name }}</h2>
            @if($package->mark_package_as_popular == 1)
                <div class="pull-right">
                    <span class="badge bg-green">@lang('superadmin::lang.popular')</span>
                </div>
            @endif
        </div>
        
        <!-- Cuerpo del contenido -->
        <div class="box-body text-center">
            <i class="fa fa-check text-success"></i>
            @if($package->location_count == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{ $package->location_count }}
            @endif
            @lang('business.business_locations')
            <br/><br/>

            <i class="fa fa-check text-success"></i>
            @if($package->user_count == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{ $package->user_count }}
            @endif
            @lang('superadmin::lang.users')
            <br/><br/>

            <i class="fa fa-check text-success"></i>
            @if($package->product_count == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{ $package->product_count }}
            @endif
            @lang('superadmin::lang.products')
            <br/><br/>

            <i class="fa fa-check text-success"></i>
            @if($package->invoice_count == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{ $package->invoice_count }}
            @endif
            @lang('superadmin::lang.invoices')
            <br/><br/>

            @if(!empty($package->custom_permissions))
                @foreach($package->custom_permissions as $permission => $value)
                    @isset($permission_formatted[$permission])
                        <i class="fa fa-check text-success"></i>
                        {{ $permission_formatted[$permission] }}
                        <br/><br/>
                    @endisset
                @endforeach
            @endif

            @if($package->trial_days != 0)
                <i class="fa fa-check text-success"></i>
                {{ $package->trial_days }} @lang('superadmin::lang.trial_days')
                <br/><br/>
            @endif
            
            <h3 class="text-center">
                @php
                    $interval_type = !empty($intervals[$package->interval]) ? $intervals[$package->interval] : __('lang_v1.' . $package->interval);
                @endphp
                @if($package->price != 0)
                    <span class="display_currency" data-currency_symbol="true">
                        {{ $package->price }}
                    </span>
                    <small>
                        / {{ $package->interval_count }} {{ $interval_type }}
                    </small>
                @else
                    @lang('superadmin::lang.free_for_duration', ['duration' => $package->interval_count . ' ' . $interval_type])
                @endif
            </h3>
        </div>
        <!-- /.box-body -->

        <!-- Pie con lógica específica para botones -->
        <div class="box-footer bg-gray disabled text-center">
            @if(isset($action_type) && $action_type == 'register')
                <a href="#"
                   class="btn btn-block btn-success mp-checkout-btn" 
                   data-plan-id="{{ $package->id }}"
                   data-preapproval-plan="
                       @if($package->id == 1)
                           2c93808493dee0d001941ea48af21d5a
                       @elseif($package->id == 2)
                           2c93808493dee0d001941ea517821d5b
                       @elseif($package->id == 3)
                           2c93808493dee09001941ea5b5131d6c
                       @elseif($package->id == 5)
                           2c93808495b859210195d4c1bde10fab
                       @endif">
                    @if($package->price != 0)
                        @lang('superadmin::lang.register_subscribe')
                    @else
                        @lang('superadmin::lang.register_free')
                    @endif
                </a>
            @else
                <a href="#"
                   class="btn btn-block btn-success mp-checkout-btn"
                   data-plan-id="{{ $package->id }}"
                   data-preapproval-plan="
                       @if($package->id == 1)
                           2c93808493dee0d001941ea48af21d5a
                       @elseif($package->id == 2)
                           2c93808493dee0d001941ea517821d5b
                       @elseif($package->id == 3)
                           2c93808493dee09001941ea5b5131d6c
                       @elseif($package->id == 5)
                           2c93808495b859210195d4c1bde10fab
                       @endif"
                   data-action-type="nonregister">
                    @if($package->price != 0)
                        @lang('superadmin::lang.pay_and_subscribe')
                    @else
                        @lang('superadmin::lang.subscribe')
                    @endif
                </a>
            @endif


            {{ $package->description }}
        </div>
    </div>
    
    <!-- Modal de Pago -->

    <!-- /.box -->
</div>


@section('javascript')
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        $(document).ready(function() {
            $('.mp-checkout-btn').on('click', function(e) {
                e.preventDefault();
                
                // Extraer datos del botón
                var preapprovalPlan = $(this).data('preapproval-plan').trim();
                var planId = $(this).data('plan-id');
                // Definir la variable actionType a partir del atributo data-action-type, 
                // y en caso de no existir se asigna "register" como valor por defecto.
                var actionType = $(this).data('action-type') || "register";
                
                // Redireccionar a la view de Mercado Pago, marcando que es modo nonregister
                if (actionType === "nonregister") {
                    window.location.href = "{{ route('pago') }}" + "?planId=" + planId + "&preapprovalPlan=" + preapprovalPlan + "&action=nonregister";
                    return;
                }

                
                // Si no hay preapproval plan, redirige directamente
                if (preapprovalPlan === "") {
                    window.location.href = "{{ route('business.getRegister') }}" + "?package=" + planId;
                    return;
                }
                
                // Redireccionar a la nueva view para el Checkout Brick de Mercado Pago,
                // pasando los parámetros necesarios en la URL
                window.location.href = "{{ route('pago') }}" + 
                    "?planId=" + planId + "&preapprovalPlan=" + preapprovalPlan;
            });
        });
    </script>
@endsection

