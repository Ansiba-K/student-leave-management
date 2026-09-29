<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDepartmentRequest extends FormRequest
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
            'name'  => 'required|string|unique:departments,name',
        ];
    }
    public function messages()
    {
            return [
                'name.required' => 'Department name is required',
                'string.required' => 'Department name must be a string',
                'name.unique' => 'Department name already exist'

            ];
    }

    protected function failedValidation(Validator $validator){
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                 'message' => 'Validation failed',
                  'errors' => $validator->errors() 
                  ],422)
        );
    }
}

