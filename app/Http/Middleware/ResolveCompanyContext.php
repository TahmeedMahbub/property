<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\CompanyMembership;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Resolve company UUID from header or route parameter
        $companyUuid = $request->header('X-Company-Id');

        if (! $companyUuid) {
            $routeCompany = $request->route('company');
            $companyUuid = $routeCompany instanceof Company
                ? $routeCompany->uuid
                : $routeCompany;
        }

        if (! $companyUuid) {
            return $next($request);
        }

        $company = Company::where('uuid', $companyUuid)->first();

        if (! $company) {
            return $next($request);
        }

        // Super admins always have access (no membership required)
        if ($user->isSuperAdmin()) {
            app()->instance('currentCompany', $company);
            app()->instance('currentMembership', null);

            return $next($request);
        }

        // Regular users need an active membership
        $membership = CompanyMembership::with('role.permissions')
            ->where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($membership) {
            app()->instance('currentCompany', $company);
            app()->instance('currentMembership', $membership);
        }

        return $next($request);
    }
}
