<?php

namespace App\Services;

use App\Models\Leave;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveAppliedMail;
use App\Services\LeaveBalanceService;

class LeaveService
{
    private $leaveBalanceService;

    public function __construct(LeaveBalanceService $leaveBalanceService)
    {
        $this->leaveBalanceService = $leaveBalanceService;
    }

    public function applyStudentLeave(array $data)
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

        // Get the student details
        $student = DB::table('students')
            ->where('id', $data['student_id'])
            ->first();

        // Find authority staff and HOD from the student's department
        $approvers = DB::table('staff')
            ->where('department_id', $student->department_id)
            ->where('is_authority', 1)
            ->whereIn('role', [1, 2])
            ->pluck('email')
            ->toArray();

        // Calculate total leave days
        $fromDate = \Carbon\Carbon::parse($data['from_date']);
        $toDate = \Carbon\Carbon::parse($data['to_date']);

        $totalLeaveDays = $fromDate->diffInDays($toDate) + 1;


        // student apply leave
        $data['applicant_type'] = 1;
        $data['staff_id'] = null;
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

        $response = [
            'leave_id' => $leave->id,
            'student_id' => $leave->student_id,
            'from_date' => $leave->from_date,
            'to_date' => $leave->to_date,
            'total_leave_days' => $totalLeaveDays,
            'reason' => $leave->reason,
            'status' => 'Pending',
        ];

        // Send email to authority staff and HOD
        if (!empty($approvers)) {
            Mail::to($approvers)
                ->send(new LeaveAppliedMail($leave, $student));
        }
        return $response;
    }

    // Staff leave application
    public function applyStaffLeave($data)
    {
        // Get the staff member who is applying for leave
        $staff = DB::table('staff')
            ->where('id', $data['staff_id'])
            ->first();

        // Half-day leave check
        if ($data['leave_session'] != 1) {

            $existingLeaves = DB::table('leaves')
                ->where('staff_id', $data['staff_id'])
                ->where('applicant_type', 2)
                ->whereIn('status', [1, 2])
                ->where('from_date', '<=', $data['from_date'])
                ->where('to_date', '>=', $data['from_date'])
                ->get();

            foreach ($existingLeaves as $leave) {

                // Existing Full Day
                if ($leave->leave_session == 1) {
                    return false;
                }

                // Same session already exists
                if ($leave->leave_session == $data['leave_session']) {
                    return false;
                }
            }
        } else {

            $existingLeave = DB::table('leaves')
                ->where('staff_id', $data['staff_id'])
                ->where('applicant_type', 2)
                ->whereIn('status', [1, 2])
                ->where('from_date', '<=', $data['to_date'])
                ->where('to_date', '>=', $data['from_date'])
                ->first();

            if ($existingLeave) {
                return false;
            }
        }

        // Check leave balance
        $year = \Carbon\Carbon::parse($data['from_date'])->year;
        $remainingDays = null;

        if ($data['leave_type_id'] != 3) {
            $remainingDays = $this->leaveBalanceService->getRemainingDays(
                $data['staff_id'],
                $data['leave_type_id'],
                $year
            );

            if ($remainingDays === null) {
                return 'balance_not_found';
            }
        }

        // Calculate total leave days
        $fromDate = \Carbon\Carbon::parse($data['from_date']);
        $toDate = \Carbon\Carbon::parse($data['to_date']);

        $totalLeaveDays = $fromDate->diffInDays($toDate) + 1;

        if ($data['leave_session'] == 2 || $data['leave_session'] == 3) {
            $totalLeaveDays = $totalLeaveDays * 0.5;
        }

        if ($data['leave_type_id'] != 3 && $totalLeaveDays > $remainingDays) {
            return 'insufficient_balance';
        }

        $data['student_id'] = null;
        $data['applicant_type'] = 2;
        $data['status'] = 1;
        $data['approved_by'] = null;
        $data['rejected_by'] = null;
        $data['rejection_reason'] = null;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // Insert the staff leave
        $id = DB::table('leaves')->insertGetId($data);

        // Get the staff leave with staff name
        $leave = DB::table('leaves')
            ->join('staff', 'leaves.staff_id', '=', 'staff.id')
            ->where('leaves.id', $id)
            ->select(
                'leaves.id as leave_id',
                'leaves.staff_id',
                'staff.name as staff_name',
                'leaves.from_date',
                'leaves.to_date',
                'leaves.leave_type_id',
                'leaves.leave_session',
                'leaves.reason',
                'leaves.status',
                'leaves.approved_by',
                'leaves.rejected_by',
                'leaves.rejection_reason'
            )
            ->first();

        $leave->total_leave_days = $totalLeaveDays;


        if ($leave->status == 1) {
            $leave->status = 'Pending';
        } elseif ($leave->status == 2) {
            $leave->status = 'Approved';
        } elseif ($leave->status == 3) {
            $leave->status = 'Rejected';
        } elseif ($leave->status == 4) {
            $leave->status = 'Cancelled';
        }

        return $leave;
    }

    public function getAllStudentLeaves($status = null)
    {
        // Get all leaves submitted by students
        $leaves = DB::table('leaves')
            ->join('students', 'leaves.student_id', '=', 'students.id')
            ->where('leaves.applicant_type', 1)
            ->select(
                'leaves.id as leave_id',
                'leaves.student_id',
                'students.name as student_name',
                'leaves.from_date',
                'leaves.to_date',
                'leaves.reason',
                'leaves.status',
                'leaves.approved_by',
                'leaves.rejected_by',
                'leaves.rejection_reason'
            )


            ->orderBy('leaves.id', 'desc');

        if ($status !== null) {
            $leaves->where('leaves.status', $status);
        }
        $leaves = $leaves->get();

        // Calculate total leave days
        foreach ($leaves as $leave) {
            $fromDate = \Carbon\Carbon::parse($leave->from_date);
            $toDate = \Carbon\Carbon::parse($leave->to_date);

            $leave->total_leave_days = $fromDate->diffInDays($toDate) + 1;

            // Convert numeric status to readable status
            if ($leave->status == 1) {
                $leave->status = 'Pending';
            } elseif ($leave->status == 2) {
                $leave->status = 'Approved';
            } elseif ($leave->status == 3) {
                $leave->status = 'Rejected';
            } elseif ($leave->status == 4) {
                $leave->status = 'Cancelled';
            }
        }

        return $leaves;
    }


    // Get all leaves submitted by staff
    public function getAllStaffLeaves($status = null)
    {

        $leaves = DB::table('leaves')
            ->join('staff', 'leaves.staff_id', '=', 'staff.id')
            ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
            ->where('leaves.applicant_type', 2)
            ->select(
                'leaves.id as leave_id',
                'leaves.staff_id',
                'staff.name as staff_name',
                'leaves.from_date',
                'leaves.to_date',
                'leaves.leave_session',
                'leave_types.name as leave_type',
                'leaves.reason',
                'leaves.status',
                'leaves.approved_by',
                'leaves.rejected_by',
                'leaves.rejection_reason'
            )
            ->orderBy('leaves.id', 'desc');

        if ($status !== null) {
            $leaves->where('leaves.status', $status);
        }
        $leaves = $leaves->get();

        // Calculate total leave days
        foreach ($leaves as $leave) {
            $fromDate = \Carbon\Carbon::parse($leave->from_date);
            $toDate = \Carbon\Carbon::parse($leave->to_date);

            $totalLeaveDays = $fromDate->diffInDays($toDate) + 1;
            if ($leave->leave_session == 2 || $leave->leave_session == 3) {
                $totalLeaveDays = $totalLeaveDays * 0.5;
            }

            $leave->total_leave_days = $totalLeaveDays;

            // Convert numeric status to readable status
            if ($leave->status == 1) {
                $leave->status = 'Pending';
            } elseif ($leave->status == 2) {
                $leave->status = 'Approved';
            } elseif ($leave->status == 3) {
                $leave->status = 'Rejected';
            } elseif ($leave->status == 4) {
                $leave->status = 'Cancelled';
            }
        }

        return $leaves;
    }



    public function getStudentLeaves($studentId, $status = null)
    {
        // Get leaves for the selected student
        $query = DB::table('leaves')
            ->join('students', 'leaves.student_id', '=', 'students.id')
            ->where('leaves.student_id', $studentId)
            ->where('leaves.applicant_type', 1)
            ->select(
                'leaves.id',
                'leaves.student_id',
                'leaves.from_date',
                'leaves.to_date',
                'leaves.reason',
                'leaves.status',
                'leaves.rejection_reason'
            )
            ->orderBy('leaves.id', 'desc');

        // Apply status filter
        if ($status !== null) {
            $query->where('leaves.status', $status);
        }

        $leaves = $query->get();

        // Calculate days and convert status
        foreach ($leaves as $leave) {

            $fromDate = \Carbon\Carbon::parse($leave->from_date);
            $toDate = \Carbon\Carbon::parse($leave->to_date);

            $leave->total_leave_days = $fromDate->diffInDays($toDate) + 1;


            if ($leave->status == 1) {
                $leave->status = 'Pending';
            } elseif ($leave->status == 2) {
                $leave->status = 'Approved';
            } elseif ($leave->status == 3) {
                $leave->status = 'Rejected';
            } elseif ($leave->status == 4) {
                $leave->status = 'Cancelled';
            }
        }

        return $leaves;
    }

    public function getStaffLeaves(int $staffId, $status = null)
    {
        // Get leaves for the selected staff
        $query = DB::table('leaves')
            ->join('staff', 'leaves.staff_id', '=', 'staff.id')
            ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
            ->where('leaves.staff_id', $staffId)
            ->where('leaves.applicant_type', 2)
            ->select(
                'leaves.id',
                'leaves.staff_id',
                'leaves.from_date',
                'leaves.to_date',
                'leaves.leave_session',
                'leaves.leave_type_id',
                'leave_types.name as leave_type',
                'leaves.reason',
                'leaves.status',
                'leaves.rejection_reason',
            )
            ->orderBy('leaves.id', 'desc');

        // Apply status filter
        if ($status !== null) {
            $query->where('leaves.status', $status);
        }

        $leaves = $query->get();

        // Calculate days and convert status
        foreach ($leaves as $leave) {

            $fromDate = \Carbon\Carbon::parse($leave->from_date);
            $toDate = \Carbon\Carbon::parse($leave->to_date);

            $totalLeaveDays = $fromDate->diffInDays($toDate) + 1;

            if ($leave->leave_session == 2 || $leave->leave_session == 3) {
                $totalLeaveDays = $totalLeaveDays * 0.5;
            }

            $leave->total_leave_days = $totalLeaveDays;

            if ($leave->status == 1) {
                $leave->status = 'Pending';
            } elseif ($leave->status == 2) {
                $leave->status = 'Approved';
            } elseif ($leave->status == 3) {
                $leave->status = 'Rejected';
            } elseif ($leave->status == 4) {
                $leave->status = 'Cancelled';
            }
        }

        return $leaves;
    }


    public function cancelStudentLeave(int $studentId, int $leaveId)
    {
        $leave = DB::table('leaves')
            ->where('id', $leaveId)
            ->where('student_id', $studentId)
            ->where('applicant_type', 1)
            ->first();

        if (!$leave) {
            return null;
        }

        if ($leave->status != 1) {
            return 'not_pending';
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

    // Cancel a staff leave
    public function cancelStaffLeave($staffId, $leaveId)
    {
        // Find the staff leave
        $leave = DB::table('leaves')
            ->where('id', $leaveId)
            ->where('staff_id', $staffId)
            ->where('applicant_type', 2)
            ->first();


        if (!$leave) {
            return null;
        }

        // Already rejected
        if ($leave->status == 3) {
            return 'rejected';
        }

        // Already cancelled
        if ($leave->status == 4) {
            return 'cancelled';
        }

        // If approved leave is cancelled, reduce used days
        if ($leave->status == 2 && $leave->leave_type_id != 3) {

            $fromDate = \Carbon\Carbon::parse($leave->from_date);
            $toDate = \Carbon\Carbon::parse($leave->to_date);

            $leaveDays = $fromDate->diffInDays($toDate) + 1;

            // Half-day leave
            if ($leave->leave_session == 2 || $leave->leave_session == 3) {
                $leaveDays = $leaveDays * 0.5;
            }

            DB::table('leave_balances')
                ->where('staff_id', $leave->staff_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', $fromDate->year)
                ->decrement('used_days', $leaveDays);
        }

        // Cancel the leave
        DB::table('leaves')
            ->where('id', $leaveId)
            ->update([
                'status' => 4,
                'updated_at' => now(),
            ]);

        return DB::table('leaves')
            ->where('id', $leaveId)
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


        if ($staff->is_authority != 1) {
            return 'not_authorized';
        }

        // Get the applicant's department
        if ($leave->applicant_type == 1) {


            $applicant = DB::table('students')
                ->where('id', $leave->student_id)
                ->first();

            if (!$applicant) {
                return 'student_not_found';
            }

            $applicantDepartmentId = $applicant->department_id;
        } else {

            // Staff leave
            $applicant = DB::table('staff')
                ->where('id', $leave->staff_id)
                ->first();

            if (!$applicant) {
                return 'staff_not_found';
            }

            // Staff cannot approve their own leave
            if ($leave->staff_id == $staffId) {
                return 'self_approval_not_allowed';
            }

            $applicantDepartmentId = $applicant->department_id;
        }


        if ($staff->role != 3) {
            if ($staff->department_id != $applicantDepartmentId) {
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

        if ($status == 2 && $leave->applicant_type == 2 && $leave->leave_type_id != 3) {

            $fromDate = \Carbon\Carbon::parse($leave->from_date);
            $toDate = \Carbon\Carbon::parse($leave->to_date);

            $leaveDays = $fromDate->diffInDays($toDate) + 1;

            if ($leave->leave_session == 2 || $leave->leave_session == 3) {
                $leaveDays = $leaveDays * 0.5;
            }

            DB::table('leave_balances')
                ->where('staff_id', $leave->staff_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', $fromDate->year)
                ->increment('used_days', $leaveDays);
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
