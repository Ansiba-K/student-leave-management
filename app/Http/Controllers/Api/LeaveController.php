<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreStudentLeaveRequest;
use App\Http\Requests\StoreStaffLeaveRequest;
use App\Services\LeaveService;
use App\Services\StaffService;
use App\Services\StudentService;
use App\Http\Requests\CancelLeaveRequest;
use App\Http\Requests\UpdateLeaveStatusRequest;
use App\Http\Requests\LeaveFilterRequest;
use App\Http\Requests\CancelStaffLeaveRequest;

class LeaveController extends Controller
{
    protected $leaveService;
    protected $staffService;
    protected $studentService;

    public function __construct(LeaveService $leaveService, StaffService $staffService, StudentService $studentService)
    {
        $this->leaveService = $leaveService;
        $this->staffService = $staffService;
        $this->studentService = $studentService;
    }

    // create leave for student
    public function storeStudentLeave(StoreStudentLeaveRequest $request)
    {
        $leave = $this->leaveService->applyStudentLeave(
            $request->validated()
        );

        if ($leave === false) {
            return response()->json([
                'success' => false,
                'message' => 'Student already has a leave for these dates'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave applied successfully',
            'data' => $leave
        ], 201);
    }

    // create leave for staff
    public function storeStaffLeave(StoreStaffLeaveRequest $request)
    {
        $leave = $this->leaveService->applyStaffLeave(
            $request->validated()
        );

        if ($leave === 'balance_not_found') {
            return response()->json([
                'success' => false,
                'message' => 'Leave balance not found for this staff and leave type',
                'data' => null
            ], 422);
        }

        if ($leave === 'insufficient_balance') {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient leave balance',
                'data' => null
            ], 422);
        }

        if ($leave === false) {
            return response()->json([
                'success' => false,
                'message' => 'Staff already has a leave for these dates'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff leave applied successfully',
            'data' => $leave
        ], 201);
    }

    // get all student leaves
    public function allStudentLeaves(LeaveFilterRequest $request)
    {

        $status = $request->status;
        // Get all student leaves
        $leaves = $this->leaveService->getAllStudentLeaves($status);

        if ($leaves->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $leaves
        ]);
    }

    public function allStaffLeaves(LeaveFilterRequest $request)
    {

        $status = $request->status;
        // Get all staff leaves
        $leaves = $this->leaveService->getAllStaffLeaves($status);

        if ($leaves->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $leaves
        ]);
    }


    // get student with leave
    public function studentLeaves($student_id, LeaveFilterRequest $request)
    {
        $status = $request->status;
        $student = $this->studentService->getStudentById($student_id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        $leaves = $this->leaveService->getStudentLeaves($student_id, $status);

        if ($leaves->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No leave records found',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student leaves retrieved successfully',
            'data' => $leaves
        ]);
    }



    // get staff with leave
    public function staffLeaves($staff_id, LeaveFilterRequest $request)
    {
        $status = $request->status;

        $staff = $this->staffService->getStaffById($staff_id);

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found',
                'data' => null
            ]);
        }

        $leaves = $this->leaveService->getStaffLeaves($staff_id, $status);


        if ($leaves->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No leave records found',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff leaves retrieved successfully',
            'data' => $leaves
        ]);
    }

    // cancel student leave
    public function cancelStudentLeave($student_id, CancelLeaveRequest $request)
    {
        $leave = $this->leaveService->cancelStudentLeave(
            $student_id,
            $request->leave_id
        );

        if ($leave === null) {
            return response()->json([
                'success' => false,
                'message' => 'Leave not found for this student'
            ], 404);
        }

        if ($leave === 'not_pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending leave can be cancelled'
            ], 422);
        }
        return response()->json([
            'success' => true,
            'message' => 'Leave cancelled successfully',
            'data' => [
                'leave_id' => $leave->id,
                'student_id' => $leave->student_id,
                'from_date' => $leave->from_date,
                'to_date' => $leave->to_date,
                'reason' => $leave->reason,
                'status' => 'Cancelled'
            ]
        ]);
    }

    public function cancelStaffLeave(
        $staff_id,
        CancelStaffLeaveRequest $request
    ) {
        // Cancel staff leave
        $leave = $this->leaveService->cancelStaffLeave(
            $staff_id,
            $request->leave_id
        );

        // Leave not found for this staff
        if ($leave === null) {
            return response()->json([
                'success' => false,
                'message' => 'Leave not found for this staff',
                'data' => null
            ], 404);
        }

        if ($leave === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Rejected leave cannot be cancelled',
                'data' => null
            ], 422);
        }

        if ($leave === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Leave is already cancelled',
                'data' => null
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave cancelled successfully',
            'data' => [
                'leave_id' => $leave->id,
                'staff_id' => $leave->staff_id,
                'from_date' => $leave->from_date,
                'to_date' => $leave->to_date,
                'reason' => $leave->reason,
                'status' => 'Cancelled'
            ]
        ]);
    }


    // student leave approve or reject
    public function updateStatus($id, UpdateLeaveStatusRequest $request)
    {
        $leave = $this->leaveService->updateLeaveStatus(
            $id,
            $request->status,
            $request->staff_id,
            $request->rejection_reason
        );


        if ($leave === null) {
            return response()->json([
                'success' => false,
                'message' => 'Leave not found'
            ], 404);
        }


        if ($leave === 'staff_not_found') {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }


        if ($leave === 'not_authorized') {
            return response()->json([
                'success' => false,
                'message' => 'Staff is not authorized to approve or reject leaves'
            ], 403);
        }


        if ($leave === 'different_department') {
            return response()->json([
                'success' => false,
                'message' => 'You can only manage leaves within your department'
            ], 403);
        }

        if ($leave === 'self_approval_not_allowed') {
            return response()->json([
                'success' => false,
                'message' => 'Staff cannot approve or reject their own leave'
            ], 403);
        }


        if ($leave === 'student_not_found') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }


        if ($leave === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Leave is already approved'
            ], 422);
        }


        if ($leave === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Leave is already rejected'
            ], 422);
        }


        if ($leave === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cancelled leave cannot be updated'
            ], 422);
        }

        $data = [
            'leave_id' => $leave->id,
            'from_date' => $leave->from_date,
            'to_date' => $leave->to_date,
            'reason' => $leave->reason,
            'status' => $request->status == 2 ? 'Approved' : 'Rejected',
        ];

        // Add only the relevant applicant ID
        if ($leave->applicant_type == 1) {
            $data['student_id'] = $leave->student_id;
        } else {
            $data['staff_id'] = $leave->staff_id;
        }


        if ($request->status == 2) {
            $data['approved_by'] = $leave->approved_by;
        } else {
            $data['rejected_by'] = $leave->rejected_by;
            $data['rejection_reason'] = $leave->rejection_reason;
        }

        return response()->json([
            'success' => true,
            'message' => $request->status == 2
                ? 'Leave approved successfully'
                : 'Leave rejected successfully',
            'data' => $data
        ], 200);
    }

}
