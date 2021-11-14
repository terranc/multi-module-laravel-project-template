<?php

namespace App\Exceptions;

use App\Traits\JsonResponseTrait;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Exception;

class ApiException extends Exception
{
    use JsonResponseTrait;

    const DB_ERROR = -FoundationResponse::HTTP_NOT_IMPLEMENTED;
    const AUTH_ERROR = -FoundationResponse::HTTP_UNAUTHORIZED;
    const PERMISSION_ERROR = -FoundationResponse::HTTP_FORBIDDEN;
    const NO_FOUND_ERROR = -FoundationResponse::HTTP_NOT_FOUND;
    const SYS_ERROR = -FoundationResponse::HTTP_INTERNAL_SERVER_ERROR;
    const REQUEST_ERROR = -FoundationResponse::HTTP_BAD_REQUEST;
    public $errorData = null;

    public function __construct($message = "", $code = -1, $data = null, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorData = $data;
    }

    public function noFound($message = '') {
        $this->message = $message ?: 'No found';
        $this->code = self::NO_FOUND_ERROR;
        return $this;
    }

    public function unauthorized($message = '') {
        $this->message = $message ?: 'Authorize fail, login first please';
        $this->code = self::AUTH_ERROR;
        return $this;
    }

    public function forbidden($message = '') {
        $this->message = $message ?: 'No permission';
        $this->code = self::PERMISSION_ERROR;
        return $this;
    }

    public function dbError($message = '') {
        $this->message = $message ?: 'Database errors';
        $this->code = self::DB_ERROR;
        return $this;
    }

    public function sysError($message = '') {
        $this->message = $message ?: 'System errors';
        $this->code = self::SYS_ERROR;
        return $this;
    }

    public function requestError($message = '') {
        $this->message = $message ?: 'Request errors';
        $this->code = self::REQUEST_ERROR;
        return $this;
    }
    public function render() {
        if (request()->pjax()) {
            $error = new MessageBag([
                'title'   => '错误',
                'message' => $this->getMessage(),
            ]);
            return back()->withInput()->with(compact('error'));
        } else {
            if (request()->input('_editable')) {
                return $this->apiResponse($this->getCode(), $this->getMessage(), $this->errorData, [
                    'status' => false,
                    'errors' => [$this->getMessage()],
                ])->setStatusCode(FoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
            } else {
                return $this->apiResponse($this->getCode(), $this->getMessage(), $this->errorData);
            }
        }
    }
}
