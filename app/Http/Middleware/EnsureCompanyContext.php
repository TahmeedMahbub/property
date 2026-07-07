<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->bound('currentCompany')) {
            return response()->json([
                'message' => 'Company context required. You must be an active member of this company.',
            ], 403);
        }

        return $next($request);
    }
}
