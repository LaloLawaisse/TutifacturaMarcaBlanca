<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Rules\ReCaptcha;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Importar la clase Log
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $moduleUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->middleware('guest')->except('logout');
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Change authentication from email to username
     *
     * @return void
     */
    public function username()
    {
        return 'username';
    }

    public function logout()
    {
        $this->businessUtil->activityLog(auth()->user(), 'logout');

        request()->session()->flush();
        \Auth::logout();

        return redirect('/login');
    }

    /**
     * The user has been authenticated.
     * Check if the business is active or not.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $business_active = $user->business ? $user->business->is_active : null;
        Log::info('Post-login check start', [
            'user_id' => $user->id,
            'username' => $user->username,
            'user_type' => $user->user_type,
            'status' => $user->status ?? null,
            'allow_login' => $user->allow_login,
            'business_id' => $user->business_id,
            'business_is_active' => $business_active,
            'ip' => $request->ip(),
        ]);

        $this->businessUtil->activityLog($user, 'login', null, [], false, $user->business_id);
        
        // Registrar login exitoso
        Log::info('Login exitoso', [
            'user_id' => $user->id,
            'username' => $user->username
        ]);
        
        if (! $user->business->is_active) {
            \Auth::logout();
            Log::warning('Login fallido: negocio inactivo', ['user_id' => $user->id]);
            return redirect('/login')
              ->with(
                  'status',
                  ['success' => 0, 'msg' => __('lang_v1.business_inactive')]
              );
        } elseif ($user->status != 'active') {
            \Auth::logout();
            Log::warning('Login fallido: usuario inactivo', ['user_id' => $user->id]);
            return redirect('/login')
              ->with(
                  'status',
                  ['success' => 0, 'msg' => __('lang_v1.user_inactive')]
              );
        } elseif (! $user->allow_login) {
            \Auth::logout();
            Log::warning('Login fallido: login no permitido', ['user_id' => $user->id]);
            return redirect('/login')
                ->with(
                    'status',
                    ['success' => 0, 'msg' => __('lang_v1.login_not_allowed')]
                );
        } elseif (($user->user_type == 'user_customer') && ! $this->moduleUtil->hasThePermissionInSubscription($user->business_id, 'crm_module')) {
            \Auth::logout();
            Log::warning('Login fallido: sin suscripción CRM', ['user_id' => $user->id]);
            return redirect('/login')
                ->with(
                    'status',
                    ['success' => 0, 'msg' => __('lang_v1.business_dont_have_crm_subscription')]
                );
        }
        Log::info('Post-login check passed', [
            'user_id' => $user->id,
            'username' => $user->username,
        ]);
    }

    protected function redirectTo()
    {
        $user = \Auth::user();
        $can_dashboard = $user->can('dashboard.data');
        $can_sell_create = $user->can('sell.create');
        $target = '/home';

        if (! $can_dashboard && $can_sell_create) {
            $target = '/pos/create';
        }

        if ($user->user_type == 'user_customer') {
            $target = 'contact/contact-dashboard';
        }

        Log::info('Post-login redirect', [
            'user_id' => $user->id,
            'username' => $user->username,
            'user_type' => $user->user_type,
            'can_dashboard' => $can_dashboard,
            'can_sell_create' => $can_sell_create,
            'target' => $target,
        ]);

        return $target;
    }

    public function validateLogin(Request $request)
    {
        // Registrar el intento de login
        Log::info('Intento de login', [
            'username' => $request->get($this->username()),
            'ip' => $request->ip()
        ]);
    
        if (config('constants.enable_recaptcha')) {
            $this->validate($request, [
                $this->username() => 'required|string',
                'password' => 'required|string',
                'g-recaptcha-response' => ['required', new ReCaptcha]
            ]);
        } else {
            $this->validate($request, [
                $this->username() => 'required|string',
                'password' => 'required|string',
            ]);
        }
    }

    public function credentials(Request $request)
    {
        $login = $request->input($this->username());
        
        // Determinar si el input es un email válido
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        return [
            $field => $login,
            'password' => $request->input('password')
        ];
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $login = (string) $request->input($this->username());
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($field, $login)->first();

        if ($user && ! Hash::check((string) $request->input('password'), $user->password)) {
            Log::warning('Login fallido: contraseña incorrecta', [
                'user_id' => $user->id,
                $field => $login,
                'ip' => $request->ip(),
            ]);
        } else {
            Log::warning('Login fallido: usuario no encontrado', [
                $field => $login,
                'ip' => $request->ip(),
            ]);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
}
