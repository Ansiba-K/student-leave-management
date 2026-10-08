<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class LeaveBalanceService
{
    public function createBalance($data)
    {
        try {

            return DB::table('leave_balances')->insertGetId([
                'staff_id' => $data['staff_id'],
                'leave_type_id' => $data['leave_type_id'],
                'year' => $data['year'],
                'allocated_days' => $data['allocated_days'],
                'used_days' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException $e) {

            if ($e->errorInfo[1] == 1062) {
                return 'duplicate_balance';
            }

            throw $e;
        }
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

    public function getStaffBalances($staffId, $leaveTypeId = null)
    {

        $query = DB::table('leave_balances')
            ->join('leave_types', 'leave_balances.leave_type_id', '=', 'leave_types.id')
            ->where('leave_balances.staff_id', $staffId);

        if ($leaveTypeId) {
            $query->where('leave_balances.leave_type_id', $leaveTypeId);
        }
        $balances = $query->select(
            'leave_balances.staff_id',
            'leave_types.name as leave_type',
            'leave_balances.year',
            'leave_balances.allocated_days',
            'leave_balances.used_days'
        )
            ->get()
            ->map(function ($balance) {

                $balance->remaining_days =
                    $balance->allocated_days - $balance->used_days;

                return $balance;
            });
        return $balances;
    }


    // get the total number of unpaid leave days for a staff member in a given year
    public function getUnpaidLeaveDays($staffId, $year)
    {
        $leaves = DB::table('leaves')
            ->where('staff_id', $staffId)
            ->where('applicant_type', 2)
            ->where('leave_type_id', 3)
            ->where('status', 2)
            ->whereYear('from_date', $year)
            ->get();

        $totalDays = 0;

        foreach ($leaves as $leave) {

            $fromDate = \Carbon\Carbon::parse($leave->from_date);
            $toDate = \Carbon\Carbon::parse($leave->to_date);

            $totalDays += $fromDate->diffInDays($toDate) + 1;
        }

        return $totalDays;
    }
}
