<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->record($request, 500, 'exception:'.class_basename($exception));
            throw $exception;
        }

        $this->record($request, $response->getStatusCode());

        return $response;
    }

    private function record(Request $request, int $statusCode, string $action = 'request'): void
    {
        try {
            AuditLog::create([
                'user_id' => $request->user()?->id,
                'action' => $action,
                'method' => $request->method(),
                'path' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => $request->except(['password', 'password_confirmation', '_token']),
                'status_code' => $statusCode,
            ]);
        } catch (Throwable) {
            report(new \RuntimeException('Audit log gagal disimpan.'));
        }
    }
}
