<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LeaveBalanceService
{
    public function createBalance($data)
    {
        return DB::table('leave_balances')->insertGetId([
            'staff_id' => $data['staff_id'],
            'leave_type_id' => $data['leave_type_id'],
            'year' => $data['year'],
            'allocated_days' => $data['allocated_days'],
            'used_days' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getRemainingDays($staffId, $leaveTypeId, $year)
    {
        $leaveBalance = DB::table('leave_balances')
            ->where('staff_id', $staffId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (!$leaveBalance) {
            return null;
        }

        return $leaveBalance->allocated_days - $leaveBalance->used_days;
    }
}
