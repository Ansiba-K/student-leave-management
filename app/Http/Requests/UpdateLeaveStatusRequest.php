<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
   
       public function rules() {
         return [
            'leave_id' => 'required|exists:leaves,id',
             'status' => 'required|integer|in:2,3',
              'staff_id' => 'required|exists:staff,id',
               'rejection_reason' => 'required_if:status,3|string',
                ]; }
       
       public function messages() {
         return [
                'leave_id.exists' => 'Leave does not exist',
                
               'status.required' => 'Status is required',
               'status.integer' => 'Status must be an integer',

               'status.in' => 'Status must be 2 (approved) or 3 (rejected)',
               'staff_id.required' => 'Staff ID is required',
               'staff_id.exists' => 'Staff does not exist',
                 
               'rejection_reason.required_if' => 'Rejection reason is required when rejecting a leave',
               'rejection_reason.string' => 'Rejection reason must be a string',
            ]; }

         protected function failedValidation(Validator $validator){
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                 'message' => 'Validation failed',
                  'errors' => $validator->errors() 
                  ],422)
        );
    }

       
    }

