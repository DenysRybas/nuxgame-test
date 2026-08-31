<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\LinkService;
use Illuminate\Http\RedirectResponse;

class LinkController extends Controller
{
    public function __construct(private LinkService $service) {}

    public function regenerate(Link $link): RedirectResponse
    {
        return redirect()->route(
            'luck',
            $this->service->regenerate($link)
        );
    }

    public function deactivate(Link $link): RedirectResponse
    {
        $this->service->deactivate($link);

        return redirect()->route('home');
    }
}
