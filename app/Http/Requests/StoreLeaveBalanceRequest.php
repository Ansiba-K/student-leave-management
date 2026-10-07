<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveBalanceRequest extends FormRequest
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
            'staff_id' => 'required|exists:staff,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer',
            'allocated_days' => 'required|numeric|min:0',
        ];
    }
}
