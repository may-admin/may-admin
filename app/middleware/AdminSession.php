<?php
namespace app\middleware;

class AdminSession
{
    public function handle($request, \Closure $next)
    {
        define('ISPJAX', request()->isPjax());
        define('CONTROLLER_NAME', request()->controller());
        define('ACTION_NAME', request()->action());
        
        define('ADMINID', session('admin_id'));
        if (empty(ADMINID)){
            return redirect((string) url('login/index'));
        }
        return $next($request);
    }
}
