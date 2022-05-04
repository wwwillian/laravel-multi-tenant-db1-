<?php

namespace Wwwillian\MultiTenantDB\Middlewares;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\ConnectionResolver;
use Wwwillian\MultiTenantDB\Models\Company;
use Wwwillian\MultiTenantDB\Events\CompanyAuthenticated;
use Illuminate\Support\Facades\DB;
use Str;

class HandleSubdomains
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $host = $request->getHost();

        if(Str::is($host, config('app.root_domain'))) {
            return $next($request);
        }

        $subdomain  = str_replace('.' . config('app.root_domain'), '', $request->getHost());
        $company = Company::where('subdomain', $subdomain)->first();

        if($this->checkCompanyAvailable($company)) {
            config(['database.connections.clients.database' => $subdomain]);

            $resolver = Model::getConnectionResolver();
            $resolver->setDefaultConnection('clients');
            Model::setConnectionResolver($resolver);

            DB::setDefaultConnection('clients');

            if(isset($company->other_informations) && property_exists($company->other_informations, 'settings')) {
                event(new CompanyAuthenticated($company->other_informations->settings));
            }
        }

        return $next($request);
    }

    /**
     * Check if company subdomain exists and if it is available
     *
     * @param  \Wwwillian\MultiTenantDB\Models\Company $company;
     */
    private function checkCompanyAvailable ($company) {

        //Check if connection exists
        if(!isset($company)) {
            abort(404);
        }

        //Check if connection active
        if(!$company->active()) {
            abort(401, trans('messages.connection_deactivated'));
        }

        app()->instance('Company', $company);

        return true;
    }
}
