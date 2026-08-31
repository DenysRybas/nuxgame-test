<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('register');
    }

    public function store(RegisterUserRequest $request, RegistrationService $service): RedirectResponse
    {
        /** @var array{username: string, phone_number: string} $data */
        $data = $request->validated();

        return redirect()->route(
            'luck',
            $service->register($data['username'], $data['phone_number'])
        );
    }
}
