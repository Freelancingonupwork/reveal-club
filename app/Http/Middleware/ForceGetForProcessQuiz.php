<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ForceGetForProcessQuiz
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('process-quiz') && $request->isMethod('post')) {
            Log::warning('POST request detected on process-quiz. Converting to GET.', [
                'params'      => $request->all(),
                'query'       => $request->query(),
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'session_id'  => $request->session()->getId(),
                'time'        => now()->toDateTimeString(),
            ]);

            // Optional: Merge POST params into query so they aren’t lost
            $queryParams = array_merge($request->query(), $request->all());

            return redirect()->route('questions', $queryParams);
        }

        return $next($request);
    }
}
