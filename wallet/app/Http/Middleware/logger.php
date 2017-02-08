<?php

namespace App\Http\Middleware;

use App\Model\log;
use Closure;

class logger
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
//        $client = new \GuzzleHttp\Client();
//        $res = $client->request('GET', 'https://api.github.com/repos/guzzle/guzzle');
//        dd($res);

        return $next($request);
    }
    public function terminate($request, $response)
    {

//        dd($request->ip());
        $action_log=new log();
        $action_log->action=$request->path();
        $action_log->request_body=serialize($request->all());
        $action_log->request_method=$request->method();
        $action_log->response_content=serialize($response->getContent());
        $action_log->response_status=$response->getStatusCode();
        $action_log->save();
    }
}
