<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

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
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
    }

    public function logout()
    {
        auth()->logout();
        return redirect('/');
    }

    public function gate(Request $request, $level=null)
    {
        $header = $request->headers;

        $proto = $header->get('X-Forwarded-Proto');
        $host = $header->get('X-Forwarded-Host');
        $port = $header->get('X-Forwarded-Port', 80);
        $uri = $header->get('X-Forwarded-Uri', '/');

        $next = sprintf("%s://%s%s%s",
            $proto,
            $host,
            ($port == 80) ? '' : ':'.$port,
            ($uri == '/') ? '' : $uri
        );

        \Log::debug('auth/gate: '.$next);

        $user = auth()->user();

        if($user) {
            if($level == 'admin' && !$user->admin) {
                abort(401);
            }
            return response('ok')->header('X-Auth-User', $user->email);
        }

        $state = [
            'next' => $next,
            'type' => 'gate',
            'level' => $level,
        ];

        /** @var GoogleProvider $driver */
        $driver = Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with([
                'prompt' => 'select_account',
                'state' => json_encode($state),
            ]);

        return $driver->redirect();
    }

    public function login(Request $request)
    {
        $state = [
            'next' => $request->next,
            'type' => 'login',
        ];

        /** @var GoogleProvider $driver */
        $driver = Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with([
                'prompt' => 'select_account',
                'state' => json_encode($state),
            ]);

        return $driver->redirect();
    }

    public function loginCallback(Request $request)
    {
        $state = json_decode($request->state);
        $next = data_get($state, 'next');
        $type = data_get($state, 'type');
        $level = data_get($state, 'level');
        $callback = data_get($state, 'callback');

        if($type == 'agent') {
            $domain = Str::before(Str::after($callback, '://'), '/');
            if(! Str::endsWith($domain, '.'.config('synchole.main_domain'))) {
                throw new \Exception('invalid domain');
            }
            return redirect($callback.'?'.$request->getQueryString());
        }

        /** @var GoogleProvider $driver */
        $driver = Socialite::driver('google');
        $googleUser = $driver->stateless()->user();
        $email = $googleUser->getEmail();

        $user = User::updateOrCreate([
            'email' => $email,
        ], [
            'name' => $googleUser->getName(),
            'avatar' => $googleUser->getAvatar(),
            'password' => '',
        ]);

        abort_unless($user->email_verified_at, 401);

        auth()->login($user);

        if($type == 'gate') {
            if($level == 'admin' && !$user->admin) {
                abort(401);
            }
            return redirect($next)->header('X-Auth-User', $user->email);
        }

        if($next) {
            return redirect($next);
        }
        return redirect('/');

    }
}
