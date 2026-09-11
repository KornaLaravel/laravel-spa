<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use jeremykenedy\LaravelRoles\App\Exceptions\LevelDeniedException;
use jeremykenedy\LaravelRoles\App\Exceptions\PermissionDeniedException;
use jeremykenedy\LaravelRoles\App\Exceptions\RoleDeniedException;
use jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyLevel;
use jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyPermission;
use jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyRole;
use Rakutentech\LaravelRequestDocs\LaravelRequestDocsMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // The route loading callback is held in a *static* property on
        // Illuminate\Foundation\Support\Providers\RouteServiceProvider, so every
        // subclass of it runs this callback, not just the framework's own
        // instance. silviolleite/laravelpwa ships such a subclass and it boots
        // among the discovered package providers, which is earlier than the
        // packages that register route macros (Route::personalDataExports).
        // Registering once, deferred to app boot, keeps the routes single and
        // guarantees every package macro exists by the time they load.
        using: function () {
            static $registered = false;

            if ($registered) {
                return;
            }

            $registered = true;

            app()->booted(function () {
                Route::middleware('api')
                    ->prefix('api')
                    ->group(base_path('routes/api.php'));

                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            });
        },
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
    )
    ->withEvents(discover: false)
    ->withMiddleware(function (Middleware $middleware) {
        // This app has no server-rendered login route, so the modern skeleton's
        // default of redirectGuestsTo(route('login')) throws RouteNotFoundException
        // and turns every unauthenticated non-JSON request into a 500. Login is a
        // client-side route served by the SPA catch-all instead.
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->preventRequestForgery(except: [
            'api/*',
            'login',
            'register',
        ]);

        $middleware->statefulApi();
        $middleware->throttleApi();

        $middleware->api(append: [
            LaravelRequestDocsMiddleware::class,
        ]);

        $middleware->alias([
            'role' => VerifyRole::class,
            'permission' => VerifyPermission::class,
            'level' => VerifyLevel::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            $userLevelCheck = $e instanceof RoleDeniedException ||
                $e instanceof PermissionDeniedException ||
                $e instanceof LevelDeniedException;

            if (! $userLevelCheck) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 403,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            abort(403);
        });
    })
    ->create();
