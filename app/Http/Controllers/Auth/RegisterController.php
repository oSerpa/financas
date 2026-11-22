<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        $this->ensureRegistrationIsOpen();

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureRegistrationIsOpen();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // A senha é convertida em hash pelo cast 'hashed' do model User.
        $user = User::create($data);
        $user->createDefaultCategories();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /** Com REGISTRATION_ENABLED=false a tela e o envio do cadastro respondem 404. */
    private function ensureRegistrationIsOpen(): void
    {
        abort_unless(config('app.registration_enabled'), 404);
    }
}
