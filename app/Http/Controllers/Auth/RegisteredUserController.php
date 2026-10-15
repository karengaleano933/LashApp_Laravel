<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'apellido' => ['required', 'string', 'max:255'],
            'documento' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:255'],
        ]);

        $user = User::create([
            'role_id' => 3,
            'name' => $request->name,
            'apellido' => $request->apellido,
            'documento' => $request->documento,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        \App\Models\Cliente::create([
            'user_id' => $user->id,
        ]);

        event(new Registered($user));

    

        return redirect()->route('login')->with('success', '¡Registro exitoso! Por favor, inicia sesión con tus datos.');

    }
}
