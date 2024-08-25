<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->user()) {
            return $next($request);
        }

        return to_route('admin.login');
    }
}
