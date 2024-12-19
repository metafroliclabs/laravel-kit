<?php

namespace App\Http\Middleware;

use App\Helpers\Constant;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->role == Constant::ADMIN) {
            return $next($request);
        }
        throw new AuthorizationException("You do not have the required permissions to access this resource.");
    }
}
