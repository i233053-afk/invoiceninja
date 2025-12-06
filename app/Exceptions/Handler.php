<?php

namespace App\Exceptions;

use Throwable;
use App\Utils\NinjaWrapper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Sentry\State\Scope;
use Sentry\Laravel\Integration;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        MaxAttemptsExceededException::class,
        ValidationException::class,
        NotFoundHttpException::class,
        RelationNotFoundException::class,
        // add other exceptions as needed
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  Throwable  $exception
     * @return void
     * @throws Throwable
     */
    public function report(Throwable $exception)
    {
        $ninja = app(NinjaWrapper::class); // Get NinjaWrapper from container

        if ($ninja->isHosted()) {
            Integration::configureScope(function (Scope $scope): void {
                $name = 'hosted@invoiceninja.com';
                $key = 'Anonymous';

                if (auth()->guard('contact') && auth()->guard('contact')->user()) {
                    $name = 'Contact = ' . auth()->guard('contact')->user()->email;
                    $key = auth()->guard('contact')->user()->company->account->key;
                } elseif (auth()->guard('user') && auth()->guard('user')->user()) {
                    $name = 'Admin = ' . auth()->guard('user')->user()->email;
                    $key = auth()->user()->account->key;
                }

                $scope->setUser([
                    'id'    => $key,
                    'email' => 'hosted@invoiceninja.com',
                    'name'  => $name,
                ]);
            });

            if ($this->validException($exception) && $this->sentryShouldReport($exception, $ninja)) {
                Integration::captureUnhandledException($exception);
            }
        }

        parent::report($exception);

        if ($ninja->isSelfHost() && $exception instanceof \Illuminate\Encryption\MissingAppKeyException) {
            info('To setup the app run: cp .env.example .env');
        }
    }

    private function validException($exception)
    {
        $messagesToIgnore = [
            'file_put_contents',
            'Permission denied',
            'flock',
            'expects parameter 1 to be resource',
            'fwrite()',
            'LockableFile',
        ];

        foreach ($messagesToIgnore as $msg) {
            if (strpos($exception->getMessage(), $msg) !== false) {
                return false;
            }
        }

        return true;
    }

    protected function sentryShouldReport(Throwable $e, NinjaWrapper $ninja)
    {
        $internalDontReport = []; // Add internal exceptions if needed

        if ($ninja->isHosted()) {
            $dontReport = array_merge($this->hostedDontReport ?? [], $internalDontReport);
        } else {
            $dontReport = array_merge($this->selfHostDontReport ?? [], $internalDontReport);
        }

        return is_null(Arr::first($dontReport, fn ($type) => $e instanceof $type));
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException && $request->expectsJson()) {
            return response()->json(['message' => $exception->getMessage()], 400);
        } elseif ($exception instanceof ValidationException && $request->expectsJson()) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $exception->validator->getMessageBag()], 422);
        } elseif ($exception instanceof NotFoundHttpException && $request->expectsJson()) {
            return response()->json(['message' => 'Route does not exist'], 404);
        } elseif ($exception instanceof MethodNotAllowedHttpException && $request->expectsJson()) {
            return response()->json(['message' => 'Method not supported for this route'], 404);
        } elseif ($exception instanceof AuthorizationException && $request->expectsJson()) {
            return response()->json(['message' => $exception->getMessage()], 401);
        } elseif ($exception instanceof TokenMismatchException) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with(['message' => 'Token expired', 'message-type' => 'danger']);
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $guard = Arr::get($exception->guards(), 0);

        $login = match ($guard) {
            'contact' => 'client.login',
            'user' => 'login',
            'vendor' => 'vendor.catchall',
            'ronin' => 'ronin.login',
            default => 'default',
        };

        return redirect()->guest(route($login));
    }
}

