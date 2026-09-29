<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CancelLeaveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'student_id' => $this->route('student_id'),
        ]);
    }

    public function rules()
    {
        return [
            'student_id' => 'required|exists:students,id',
            'leave_id' => 'required|exists:leaves,id',
        ];
    }

    public function messages()
    {
        return [
            'student_id.required' => 'Student ID is required',
            'student_id.exists'   => 'Student does not exist',
            
            'leave_id.required' => 'Leave ID is required',
            'leave_id.exists' => 'Leave does not exist',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
