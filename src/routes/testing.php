<?php

use Hyvor\Internal\InternalApi\Middleware\InternalApiFromMiddleware;
use Hyvor\Internal\InternalApi\Middleware\InternalApiMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// used for InternalApiTesting testing
Route::middleware(InternalApiMiddleware::class)->group(function () {
    Route::post(
        '/api/internal/internal-api-testing-test-route-post',
        fn(Request $request) => response()->json($request->all())
    );
});


Route::middleware([InternalApiFromMiddleware::class . ':core'])
    ->get('/api/internal/internal-api-testing-test-route-from-middleware', fn() => 'ok');