<?php

namespace App\Http\Middleware;

use App\Services\Currency;
use Closure;
use App;

class SalesUnit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $currency = new Currency();
        $sales_unit = $currency->getCoinUnit();
        $request->attributes->add(compact('sales_unit'));
        return $next($request);
    }
}
