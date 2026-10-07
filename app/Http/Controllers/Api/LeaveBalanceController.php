<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveBalanceRequest;
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;

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



        return response()->json([
            'success' => true,
            'message' => 'Leave balance created successfully',
            'data' => ['id' => $id]
        ], 201);
    }
}
