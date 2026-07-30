<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Cache;

class CheckToken
{
    public function handle($request, \Closure $next)
    {
        $token = $request->param('token');
        if(!empty($token)){
            $key = 'token:'.$token;
            $exists = Cache::store('redis_token')->get($key);
            if(empty($exists)){
                return ajax_return(1, 'token异常或过期');
            }else{
                $res = json_decode($exists, true);
                $request->token_member_id = $res['id'];
            }
        }else{
            return ajax_return(1, 'token不能为空');
        }
        return $next($request);
    }
}