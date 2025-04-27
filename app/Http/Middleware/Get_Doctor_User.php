<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Get_Doctor_User
{
    use ApiTrait;

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->role != 'admin') {
            return $next($request);
        }
        
        return $this->errorsMessage(['error' => 'You Are Not Allowed To Make Request On This Route']);
    }
}
