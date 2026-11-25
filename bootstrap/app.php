<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ticket #011: só redireciona visitantes para o login se essa rota existir.
        $middleware->redirectGuestsTo(fn () => Route::has('login') ? route('login') : null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ticket #011: visitante anônimo tentando votar.
        // Se o projeto tiver rota de login (ex.: Breeze), o Laravel redireciona para ela normalmente.
        // Se não tiver, devolve 401 em vez de estourar erro 500 (Route [login] not defined).
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! Route::has('login')) {
                return $request->expectsJson()
                    ? response()->json(['message' => 'Faça login para votar.'], 401)
                    : response('Faça login para votar.', 401);
            }
        });
    })->create();
