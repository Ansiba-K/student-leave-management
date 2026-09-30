<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Override;

class StoreStudentRequest extends FormRequest
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
            'email' => 'required|email|unique:students,email',
            'phone' => 'required|string',
            'course' => 'required|string',
            'department_id' => 'required|exists:departments,id',
        ];
    }
}
