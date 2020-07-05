<?php

namespace App\Exceptions;

use App\Exceptions\Api\ApiRequestException;
use App\Traits\JsonResponseTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler {
//    use JsonResponseTrait;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        SwooleExitException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $exception
     *
     * @return void
     */
    public function report(Exception $exception) {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request   $request
     * @param Exception $exception
     *
     * @return Response
     */
    public function render($request, Exception $exception) {
        if (($exception instanceof ModelNotFoundException) && ($request->is('agent/*'))) {
            return redirect(route('agent.activity'));
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            throw new ApiRequestException('请求方式错误');
        } else if (!config('app.debug') && $exception instanceof QueryException) {
            throw (new ApiException())->dbError($exception->getMessage());
        } else if ($exception instanceof UnauthorizedHttpException) {
            throw new ApiRequestException('缺少 Token');
        } else if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            if ($request->expectsJson()) {
                throw (new ApiException())->noFound();
            } else {
                $this->prepareResponse($request, $exception);
            }
        } else if ($exception instanceof SwooleExitException) {
            //直接调用perpare
            return $exception->getResponse()->prepare($request);
        }
        return parent::render($request, $exception);
    }
}
