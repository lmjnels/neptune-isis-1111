<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestLogsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $payload = $this->getPayload($request);

        Log::channel('requests')->info(json_encode($payload));

        return $next($request);
    }

    private function getPayload($request): array
    {
        return [
            'host' => $request->host(),
            'request_uri' => $request->getRequestUri(),
            'request' => $request->all(),
            'query' => $request->query(),
            'post' => $request->post(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user' => $request->user(),
        ];
    }
}
