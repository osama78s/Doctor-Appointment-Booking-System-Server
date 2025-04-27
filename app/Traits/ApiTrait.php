<?php

namespace App\Traits;

trait ApiTrait
{
    public function data(array $data,string $message='' ,int $code=200)
    {
        return response()->json(
            [
                'message'       => $message,
                'data'      => $data,
                'errors'    => (object)[],
            ],$code);
    }


    public function successMessage(string $message = '', int $code = 200)
    {
        return response()->json([
            'message' => $message,
            'errors' => (object)[],
            'data' => (object)[]
        ], $code);
    }



    public function errorsMessage(array $errors, string $message = '', int $code = 404)
    {
        return response()->json(
    [
            'message' => $message,
            'errors' => $errors,
            'data' => (object)[]
        ], $code);
    }
}