<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
    
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        $data = array();
        foreach ($errors as $key => $error) {
            $temp = new \stdClass();
            $temp->name = $key;
            $temp->messages = $error;
            $data[] = $temp;
        }
        throw new HttpResponseException(response()->json([
            'data' => [
                'errors' => $data,
            ],
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
