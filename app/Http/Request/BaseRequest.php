<?php

namespace App\Http\Requests;

use App\Exceptions\Api\ApiRequestException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class BaseRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * @param Validator $validator
     *
     * @return JsonResponse|void
     * @throws ApiRequestException
     */
    protected function failedValidation(Validator $validator) {
        $error = $validator->errors()->all();
        throw new ApiRequestException($error[0]);
    }
}
