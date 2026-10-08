<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveBalanceRequest;
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveBalanceController extends Controller
{
    private $leaveBalanceService;
    public function __construct(LeaveBalanceService $leaveBalanceService)
    {
        $this->leaveBalanceService = $leaveBalanceService;
    }

    public function store(storeLeaveBalanceRequest $request)
    {
        $id = $this->leaveBalanceService->createBalance($request->all());

        if ($id === 'duplicate_balance') {
            return response()->json([
                'success' => false,
                'message' => 'Leave balance already exists for this staff, leave type and year',
                'data' => null
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave balance created successfully',
            'data' => ['id' => $id]
        ], 201);
    }

    public function index(Request $request, $staffId)
    {
        $leaveTypeId = $request->leave_type_id;

        $leaveTypeId = $request->leave_type_id;
        $year = $request->year ?? date('Y');

        // Unpaid Leave
        if ((int) $leaveTypeId === 3) {

            $totalDays = $this->leaveBalanceService
                ->getUnpaidLeaveDays($staffId, $year);

            return response()->json([
                'success' => true,
                'message' => 'Unpaid leave summary retrieved successfully',
                'data' => [
                    'staff_id' => $staffId,
                    'year' => $year,
                    'unpaid_leave_days' => $totalDays
                ]
            ], 200);
        }

        // casual/sick
        $balances = $this->leaveBalanceService
            ->getStaffBalances($staffId, $leaveTypeId);

        return response()->json([
            'success' => true,
            'message' => 'Leave balances retrieved successfully',
            'data' => $balances
        ]);
    }
}
