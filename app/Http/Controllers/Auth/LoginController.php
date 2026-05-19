<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function login(Request $req)
    {
        $input = $req->all();
        $this->validate(
            $req,
            [
                'email' => 'required|email',
                'password' => 'required',
            ]
        );
        if (auth()->attempt(['email' => $input['email'], 'password' => $input['password']])) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            if ($user->hasTwoFactorEnabled()) {
                auth()->logout();
                $req->session()->put('two_factor_login_id', $user->id);

                return redirect()->route('two-factor.challenge');
            }

            if ($user->isAdmin()) {
                return redirect()->route('admin-home');
            }

            if ($user->canAccessErp()) {
                return redirect()->route('erp.dashboard');
            }

            return redirect()->route('home');
        }
        return redirect()->route('login')->with('errormsg', 'Invalid credentials');
    }
}
