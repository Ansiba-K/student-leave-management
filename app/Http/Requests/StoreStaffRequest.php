<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Override;

class StoreStaffRequest extends FormRequest
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
            'email' => 'required|email|unique:staff,email',
            'phone' => 'required|string',
            'department_id' => 'required_unless:role,3|nullable|exists:departments,id',
            'role' => 'required|integer|in:1,2,3',
            'is_authority' => 'required|boolean',

        ];
    }
}
