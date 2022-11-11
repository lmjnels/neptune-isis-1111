<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Breeze/Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param \App\Http\Requests\Auth\RegisterUserRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     */
    public function store(RegisterUserRequest $request)
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new UserRegistered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
