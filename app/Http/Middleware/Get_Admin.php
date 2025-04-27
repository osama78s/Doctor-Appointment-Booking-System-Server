<?php

namespace App\Http\Middleware;

use App\Traits\ApiTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Get_Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

     use ApiTrait;

    public function handle(Request $request, Closure $next): Response
    {
        if($request->user()->role != 'admin'){
            return $this->errorsMessage(['error' => 'You Are Not Allowed To Access This Route']);
        }
        return $next($request);
    }
}
