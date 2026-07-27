<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, CreatesNewUsers $creator)
    {
        $user = $creator->create($request->validated());

        return redirect()->route('login')->with('success', '会員登録が完了しましたので、こちらからログインしてください');
    }
}
