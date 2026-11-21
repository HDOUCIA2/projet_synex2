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
  ->withCommands([
    \App\Console\Commands\ImportConflictData::class,
    \App\Console\Commands\CreateAdminUser::class,
  ])
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
    $middleware->redirectGuestsTo('/auth/login-basic');
    $middleware->alias([
      'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();