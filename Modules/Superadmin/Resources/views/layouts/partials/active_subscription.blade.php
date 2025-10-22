@if(!empty($__subscription) && env('APP_ENV') != 'demo')
<a href="https://app.trevitsoft.com/subscription"
   target="_blank"
   title="@lang('superadmin::lang.active_package_description')"
   class="btn btn-sm mt-2 d-flex align-items-center"
   style="background-color: #46af3f; color: #fff; gap: 6px; border: none;"
   data-toggle="popover"
   data-html="true"
   data-placement="right"
   data-trigger="hover"
   data-content="
        <table class='table table-condensed'>
            <tr class='text-center'> 
                <td colspan='2'>
                    {{$__subscription->package_details['name'] }}
                </td>
            </tr>
            <tr class='text-center'>
                <td colspan='2'>
                    {{ @format_date($__subscription->start_date) }} - {{ @format_date($__subscription->end_date) }}
                </td>
            </tr>
            <tr> 
                <td colspan='2'>
                    <i class='fa fa-check text-success'></i>
                    @if($__subscription->package_details['location_count'] == 0)
                        @lang('superadmin::lang.unlimited')
                    @else
                        {{$__subscription->package_details['location_count']}}
                    @endif
                    @lang('business.business_locations')
                </td>
            </tr>
            <tr>
                <td colspan='2'>
                    <i class='fa fa-check text-success'></i>
                    @if($__subscription->package_details['user_count'] == 0)
                        @lang('superadmin::lang.unlimited')
                    @else
                        {{$__subscription->package_details['user_count']}}
                    @endif
                    @lang('superadmin::lang.users')
                </td>
            </tr>
            <tr>
                <td colspan='2'>
                    <i class='fa fa-check text-success'></i>
                    @if($__subscription->package_details['product_count'] == 0)
                        @lang('superadmin::lang.unlimited')
                    @else
                        {{$__subscription->package_details['product_count']}}
                    @endif
                    @lang('superadmin::lang.products')
                </td>
            </tr>
            <tr>
                <td colspan='2'>
                    <i class='fa fa-check text-success'></i>
                    @if($__subscription->package_details['invoice_count'] == 0)
                        @lang('superadmin::lang.unlimited')
                    @else
                        {{$__subscription->package_details['invoice_count']}}
                    @endif
                    @lang('superadmin::lang.invoices')
                </td>
            </tr>
        </table>
   ">
   <svg xmlns="http://www.w3.org/2000/svg" fill="#ffffff" height="20" width="20" viewBox="0 0 24 24" style="display:inline-block; vertical-align: middle;">
     <path d="M5 16v2h14v-2h-2.586l.293-.293A1 1 0 0016 14h-1.586l.293-.293A1 1 0 0014 12h-4a1 1 0 00-.707.293L9.586 14H8a1 1 0 00-.707 1.707l.293.293H5zm4.414-6.414L12 6l2.586 3.586a1 1 0 001.414 0L20 5v10H4V5l3.586 3.586a1 1 0 001.414 0z"/>
   </svg> Mejorar mi plan
</a>
@endif








