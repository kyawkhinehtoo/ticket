<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends \Filament\Http\Responses\Auth\LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {


        // You can use the Filament facade to get the current panel and check the ID
        if (Filament::getCurrentPanel()->getId() === 'admin') {

            if (Auth::check()) {
                $user = Auth::user();
                if ($user->isClient()) {
                    return redirect('/client');
                }
            }
        }

        if (Filament::getCurrentPanel()->getId() === 'client') {

            if (Auth::check()) {
                $user = Auth::user();
                if ($user->isAdmin()) {
                    return redirect('/admin');
                }
            }
        }

        return parent::toResponse($request);
    }
}
