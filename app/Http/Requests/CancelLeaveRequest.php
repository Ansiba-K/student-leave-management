<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


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
}
