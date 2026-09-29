<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StaffLeaveRequest extends FormRequest
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
        return [ 'status' => 'nullable|integer|in:1,2,3,4', ];
    }

    public function messages() {
        return [
             'status.integer' => 'Status must be an integer',
              'status.in' => 'Status must be 1, 2, 3 or 4', 
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
