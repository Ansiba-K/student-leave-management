<?php

namespace App\Services;

use App\Models\Leave;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function applyLeave(array $data)
    {

        $existingLeave = DB::table('leaves')
            ->where('student_id', $data['student_id'])
            ->whereIn('status', [1, 2])
            ->where('from_date', '<=', $data['to_date'])
            ->where('to_date', '>=', $data['from_date'])
            ->first();

        if ($existingLeave) {
            return false;
        }

        // Calculate total leave days
        $fromDate = \Carbon\Carbon::parse($data['from_date']);
        $toDate = \Carbon\Carbon::parse($data['to_date']);

        $totalLeaveDays = $fromDate->diffInDays($toDate) + 1;

        $data['status'] = 1;
        $data['approved_by'] = null;
        $data['rejection_reason'] = null;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $id = DB::table('leaves')->insertGetId($data);

        $leave = DB::table('leaves')
            ->where('id', $id)
            ->first();

        $leave->total_leave_days = $totalLeaveDays;
        return $leave;
    }

    public function getAllLeaves($status = null)
    {
        $query = DB::table('leaves')
            ->join('students', 'leaves.student_id', '=', 'students.id')
            ->select(
                'leaves.id',
                'students.name as student_name',
                'leaves.from_date',
                'leaves.to_date',
                'leaves.reason',
                'leaves.status',
                'leaves.approved_by',
                'leaves.rejection_reason'
            );

        if ($status !== null) {
            $query->where('leaves.status', $status);
        }
        $leaves = $query->get();

        foreach ($leaves as $leave) {
            $fromDate = \Carbon\Carbon::parse($leave->from_date);
            $toDate = \Carbon\Carbon::parse($leave->to_date);
            $leave->total_leave_days = $fromDate->diffInDays($toDate) + 1;
        }
        return $leaves;
    }



    public function getStudentLeaves(int $studentId)

    {

        $student = DB::table('students')
            ->where('id', $studentId)
            ->first();

        if (!$student) {
            return null;
        }
        return DB::table('leaves')
            ->where('leaves.student_id', $studentId)
            ->select(
                'leaves.id',
                'leaves.student_id',
                'leaves.from_date',
                'leaves.to_date',
                'leaves.reason',
                'leaves.status',
                'leaves.rejection_reason'
            )
            ->get();
    }


    public function cancelLeave(int $studentId, int $leaveId)
    {
        $leave = DB::table('leaves')
            ->where('id', $leaveId)
            ->where('student_id', $studentId)
            ->first();

        if (!$leave) {
            return null;
        }

        if ($leave->status == 2) {
            return 'approved';
        }

        if ($leave->status == 3) {
            return 'rejected';
        }

        if ($leave->status == 4) {
            return 'cancelled';
        }

        DB::table('leaves')
            ->where('id', $leaveId)
            ->where('student_id', $studentId)
            ->update([
                'status' => 4,
                'updated_at' => now(),
            ]);

        return DB::table('leaves')
            ->where('id', $leaveId)
            ->where('student_id', $studentId)
            ->first();
    }


    // Approve or reject a leave
    public function updateLeaveStatus(
        $leaveId,
        $status,
        $staffId,
        $rejectionReason = null
    ) {

        $leave = DB::table('leaves')
            ->where('id', $leaveId)
            ->first();

        if (!$leave) {
            return null;
        }

        // Find the staff 
        $staff = DB::table('staff')
            ->where('id', $staffId)
            ->first();

        if (!$staff) {
            return 'staff_not_found';
        }

        // Check this staff member has authority
        if ($staff->is_authority != 1) {
            return 'not_authorized';
        }

        // Find the student who applied for the leave
        $student = DB::table('students')
            ->where('id', $leave->student_id)
            ->first();

        if (!$student) {
            return 'student_not_found';
        }

        // Principal 
        if ($staff->role != 3) {

            // Staff/HOD must belong to the same department
            if ($staff->department_id != $student->department_id) {
                return 'different_department';
            }
        }


        if ($leave->status == 2) {
            return 'approved';
        }


        if ($leave->status == 3) {
            return 'rejected';
        }


        if ($leave->status == 4) {
            return 'cancelled';
        }


        if ($status == 2) {
            $rejectionReason = null;
        }

        // update leave status
        DB::table('leaves')
            ->where('id', $leaveId)
            ->update([
                'status' => $status,
                'approved_by' => $status == 2 ? $staffId : null,
                'rejected_by' => $status == 3 ? $staffId : null,
                'rejection_reason' => $rejectionReason,
                'updated_at' => now(),
            ]);


        return DB::table('leaves')
            ->where('id', $leaveId)
            ->first();
    }
}
