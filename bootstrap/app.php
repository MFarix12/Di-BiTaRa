<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
        $middleware->appendToGroup('web',\App\Http\Middleware\ResolveLearnerSession::class);
        $middleware->redirectGuestsTo('/log-masuk-admin');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $r) {
            if(!$r->routeIs('student.*','access.*'))return null;
            if($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Illuminate\Auth\AuthenticationException)return null;
            $code=$e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $e->getStatusCode() : 500;
            if(!in_array($code,[419,500,503]))return null;
            if($r->expectsJson())return response()->json(['message'=>'Misi berhenti sekejap. Semak sambungan dan cuba lagi.'],$code);
            return response()->view('errors.'.$code,[],$code);
        });
    })->create();
