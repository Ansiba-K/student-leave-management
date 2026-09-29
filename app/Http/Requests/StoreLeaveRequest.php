<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLeaveRequest extends FormRequest
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
        'student_id' => 'required|exists:students,id',
        'from_date' => 'required|date|after_or_equal:today',
        'to_date' => 'required|date|after_or_equal:from_date',
        'reason' => 'required|string',
    ];
    
    }

    public function messages()
{
    return [
        'student_id.required' => 'Student ID is required',
        'student_id.exists' => 'Student does not exist',
        'from_date.required' => 'From date is required',
        'from_date.date' => 'From date is not a valid date',
        'from_date.after_or_equal' => 'Leave cannot start before today',
        'to_date.required' => 'To date is required',
        'to_date.date' => 'To date is not a valid date',
        'to_date.after_or_equal' => 'To date must be on or after from date',
        'reason.required' => 'Leave reason is required',
        'reason.string' => 'Leave reason must be a string',
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

