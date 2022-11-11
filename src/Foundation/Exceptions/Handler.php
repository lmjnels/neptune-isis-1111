<?php

namespace Foundation\Exceptions;

use App\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    use ExceptionTrait;

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param Throwable $e
     * @return Response
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response
    {
        if ($request->expectsJson()) {
            return $this->apiException($request, $e);
        }

        return parent::render($request, $e);
    }


}
