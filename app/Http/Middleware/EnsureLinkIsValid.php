<?php

namespace App\Http\Middleware;

use App\Models\Link;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLinkIsValid
{
    /**
     * The token is the only credential for page A
     */
    public function handle(Request $request, Closure $next): Response
    {
        $link = $request->route('link');

        abort_unless($link instanceof Link && $link->isValid(), 404);

        return $next($request);
    }
}
