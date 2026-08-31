<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\LuckService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LuckController extends Controller
{
    public function __construct(private LuckService $service) {}

    public function show(Link $link): View
    {
        return view('luck', [
            'link' => $link,
            'attempt' => session('attempt'),
        ]);
    }

    public function generate(Link $link): RedirectResponse
    {
        $attempt = $this->service->generate($link->user);

        return redirect()
            ->route('luck', $link)
            ->with('attempt', [
                'number' => $attempt->number,
                'result' => $attempt->result->label(),
                'prize' => $attempt->prize,
            ]);
    }

    public function history(Link $link): View
    {
        return view('luck', [
            'link' => $link,
            'attempt' => null,
            'history' => $this->service->getLatestHistory($link->user),
        ]);
    }
}
