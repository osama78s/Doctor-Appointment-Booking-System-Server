<?php

namespace App\Http\Middleware;

use App\Traits\ApiTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Get_doctor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    use ApiTrait;
    
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->role != 'doctor') 
        {
            return $this->errorsMessage(['error' => 'You Are Not Allowed To Make Request On This Route']);
        }
        return $next($request);
    }
}
