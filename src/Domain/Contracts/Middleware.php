<?php

declare(strict_types=1);

namespace SimpleRouter\Domain\Contracts;

use SimpleRouter\Application\Http\Request;
use SimpleRouter\Application\Http\Response;
use Closure;

/**
 * Contract for HTTP middleware
 */
interface Middleware
{
    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response;
}
