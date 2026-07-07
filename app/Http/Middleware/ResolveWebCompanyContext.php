<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\CompanyMembership;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveWebCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect('/login');
        }

        // If user is super admin, give them the first company or a specific one from session
        if ($user->is_super_admin) {
            $companyId = session('current_company_id');
            $company = $companyId
                ? Company::find($companyId)
                : Company::first();

            if ($company) {
                app()->instance('currentCompany', $company);
                return $next($request);
            }
        }

        // Get the company from session or default to user's first active membership
        $companyId = session('current_company_id');

        if ($companyId) {
            $membership = CompanyMembership::where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->with(['company', 'role.permissions'])
                ->first();
        }

        if (empty($membership)) {
            $membership = CompanyMembership::where('user_id', $user->id)
                ->where('status', 'active')
                ->with(['company', 'role.permissions'])
                ->first();
        }

        if (! $membership) {
            // User has no company — redirect to a page that creates one
            if (! $request->is('dashboard')) {
                return redirect('/dashboard');
            }
            app()->instance('currentCompany', null);
            return $next($request);
        }

        session(['current_company_id' => $membership->company_id]);
        app()->instance('currentCompany', $membership->company);
        app()->instance('currentMembership', $membership);

        return $next($request);
    }
}
