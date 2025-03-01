<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DatabaseService;

class SwitchToMasterDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //dd('Middleware working here in Master');
        // Switch to the master database
        DatabaseService::switchToMaster();

        // Continue with the request
        return $next($request);
    }
}
