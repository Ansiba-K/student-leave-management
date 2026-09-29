<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStaffRequest extends FormRequest
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
            'email' => 'required|email|unique:staff,email,' . $this->route('id'),
            'phone' => 'required|string',
            'department_id' => 'required_unless:role,3|nullable|exists:departments,id',
            'role' => 'required|integer|in:1,2,3',
            'is_authority' => 'required|boolean',
        ];
    }

    // Custom validation messages
    public function messages()
    {
        return [
            'name.required' => 'Staff name is required',
            'name.string' => 'Staff name must be a string',

            'email.required' => 'Staff email is required',
            'email.email' => 'Staff email is not valid',
            'email.unique' => 'Staff email already exists',

            'phone.required' => 'Staff phone is required',
            'phone.string' => 'Staff phone must be a string',

            'department_id.exists' => 'Department does not exist',
            'department_id.required_unless' =>'Department is required for Staff and HOD',

            'role.required' => 'Role is required',
            'role.integer' => 'Role must be an integer',
            'role.in' => 'Role must be 1 (Staff), 2 (HOD), or 3 (Principal)',

            'is_authority.required' => 'Authority status is required',
            'is_authority.boolean' => 'Authority status must be 0 or 1',
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
