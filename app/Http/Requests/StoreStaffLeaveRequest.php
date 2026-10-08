<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffLeaveRequest extends FormRequest
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
            'leave_session' => 'required|integer|in:1,2,3',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if (
                in_array((int) $this->leave_session, [2, 3], true) &&
                $this->from_date !== $this->to_date
            ) {
                $validator->errors()->add(
                    'to_date',
                    'Forenoon or Afternoon leave can only be applied for a single day'
                );
            }
        });
    }
}


