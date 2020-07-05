<?php

namespace App\Exceptions;

class DontReportApiException extends ApiException {
    public function report(\Exception $exception) {
    }
}
