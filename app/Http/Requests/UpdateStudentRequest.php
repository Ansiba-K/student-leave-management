<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Override;

class UpdateStudentRequest extends FormRequest
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
            'name' => 'required|string',

            'email' => 'required|email|unique:students,email,' . $this->route('id'),

            'phone' => 'required|string',

            'course' => 'required|string',

            'department_id' => 'required|exists:departments,id',
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'name.required' => 'Student name is required',
            'name.string' => 'Student name must be a string',

            'email.required' => 'Student email is required',
            'email.email' => 'Student email is not valid',
            'email.unique' => 'Student email already exists',

            'phone.required' => 'Student phone is required',
            'phone.string' => 'Student phone must be a string',

            'course.required' => 'Course is required',
            'course.string' => 'Course must be a string',

            'department_id.required' => 'Department is required',
            'department_id.exists' => 'Department does not exist',
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
