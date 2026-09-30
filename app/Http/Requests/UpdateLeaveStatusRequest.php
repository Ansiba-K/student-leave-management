<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class UpdateLeaveStatusRequest extends FormRequest
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

    protected function prepareForValidation()
    {
        $this->merge([
            'leave_id' => $this->route('id'),
            'status' => $this->query('status'),
        ]);
    }

    public function rules()
    {
        return [
            'leave_id' => 'required|exists:leaves,id',
            'status' => 'required|integer|in:2,3',
            'staff_id' => 'required|exists:staff,id',
            'rejection_reason' => 'required_if:status,3|string',
        ];
    }
}
