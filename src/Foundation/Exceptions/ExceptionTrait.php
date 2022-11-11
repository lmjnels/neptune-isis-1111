<?php


namespace Foundation\Exceptions;


use Illuminate\Http\Response;

trait ExceptionTrait
{
    public function apiException($request, $e)
    {
        if ($this->isModel($e)) {
            return $this->modelResponse($e);
        }

        if ($this->isHttp($e)) {
            return $this->httpResponse($e);
        }

        return parent::render($request, $e);
    }


    protected function isModel($e)
    {
        return $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException;
    }

    protected function isHttp($e)
    {
        return $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    }


    protected function modelResponse($e)
    {
        $modelClass = explode('\\', $e->getModel());

        return response([
            'error' => 'Model '.end($modelClass).' not found',
        ], Response::HTTP_NOT_FOUND);
    }

    protected function httpResponse($e)
    {
        return response([
            'error' => 'Incorrect route name',
        ], Response::HTTP_NOT_FOUND);
    }
}
