<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLeaveRequest;
use App\Services\LeaveService;
use App\Http\Requests\ApproveLeaveRequest;
use App\Http\Requests\RejectLeaveRequest;
use App\Http\Requests\CancelLeaveRequest;
use App\Http\Requests\StaffLeaveRequest;
use App\Http\Requests\UpdateLeaveStatusRequest;

class LeaveController extends Controller
{
    protected $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function store(StoreLeaveRequest $request)
    {
        $leave = $this->leaveService->applyLeave(
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

    public function index(StaffLeaveRequest $request)
    {
        $leaves = $this->leaveService->getAllLeaves(
            $request->status
        );

        return response()->json([
            'success' => true,
            'message' => 'Leaves retrieved successfully',
            'data' => $leaves
        ], 200);
    }



    public function studentLeaves($student_id)
    {
        $leaves = $this->leaveService->getStudentLeaves($student_id);

        if ($leaves === null) {
            return response()->json([
                'success' => false,
                'message' => 'Student does not exist'
            ], 404);
        }

        if ($leaves->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Student has not applied for any leave yet',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student leaves retrieved successfully',
            'data' => $leaves
        ]);
    }

    public function cancel($student_id, CancelLeaveRequest $request)
    {
        $leave = $this->leaveService->cancelLeave(
            $student_id,
            $request->leave_id
        );

        if ($leave === null) {
            return response()->json([
                'success' => false,
                'message' => 'Leave not found for this student'
            ], 404);
        }

        if ($leave === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Approved leave cannot be cancelled'
            ], 422);
        }

        if ($leave === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Rejected leave cannot be cancelled'
            ], 422);
        }

        if ($leave === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Leave is already cancelled'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave cancelled successfully',
            'data' => $leave
        ]);
    }


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

        return response()->json([
            'success' => true,
            'message' => $request->status == 2
                ? 'Leave approved successfully'
                : 'Leave rejected successfully',
            'data' => $leave
        ], 200);
    }
}
