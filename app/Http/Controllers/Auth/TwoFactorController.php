<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function showChallenge(): RedirectResponse|View
    {
        if (! session()->has('two_factor_login_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function challenge(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $id = session('two_factor_login_id');
        if (! $id) {
            return redirect()->route('login');
        }

        /** @var User|null $user */
        $user = User::query()->find($id);
        if (! $user || ! $user->two_factor_secret) {
            session()->forget('two_factor_login_id');

            return redirect()->route('login')->with('errormsg', 'Session expired. Sign in again.');
        }

        $google2fa = new Google2FA;
        $secret = decrypt($user->two_factor_secret);
        if (! $google2fa->verifyKey($secret, $request->string('code'))) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        session()->forget('two_factor_login_id');
        Auth::login($user);

        return redirect()->intended($this->postLoginPath($user));
    }

    public function showSetup(Request $request, QrCodeService $qr): View
    {
        $user = $request->user();
        $pendingSecret = session('2fa_setup_secret');
        $qrSvg = null;
        $otpauth = null;
        if ($pendingSecret) {
            $google2fa = new Google2FA;
            $otpauth = $google2fa->getQRCodeUrl(config('app.name'), (string) $user->email, $pendingSecret);
            $qrSvg = $qr->svg($otpauth, 200);
        }

        return view('auth.two-factor-setup', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'pendingSecret' => $pendingSecret,
            'qrSvg' => $qrSvg,
        ]);
    }

    public function generateSecret(Request $request): RedirectResponse
    {
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();
        session(['2fa_setup_secret' => $secret]);

        return redirect()->route('two-factor.setup')->with('success', 'Scan the QR code with your authenticator app, then enter the code below.');
    }

    public function confirmSetup(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $pending = session('2fa_setup_secret');
        if (! $pending) {
            return redirect()->route('two-factor.setup')->with('error', 'Generate a new secret first.');
        }

        $google2fa = new Google2FA;
        if (! $google2fa->verifyKey($pending, $request->string('code'))) {
            return back()->withErrors(['code' => 'Invalid code. Try again.']);
        }

        $user = $request->user();
        $user->two_factor_secret = encrypt($pending);
        $user->two_factor_confirmed_at = now();
        $user->save();

        session()->forget('2fa_setup_secret');

        return redirect()->route('two-factor.setup')->with('success', 'Two-factor authentication is enabled.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()->route('two-factor.setup')->with('success', 'Two-factor authentication has been turned off.');
    }

    private function postLoginPath(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin-home');
        }
        if ($user->canAccessErp()) {
            return route('erp.dashboard');
        }

        return route('home');
    }
}
