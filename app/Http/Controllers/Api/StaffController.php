<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Services\StaffService;

class StaffController extends Controller
{
    protected $staffService;
    public function __construct(StaffService $staffService)
    {
         $this->staffService = $staffService;
    }

    // Get all staff
    public function index(){
        $staff = $this->staffService->getAllStaff();

        return response()->Json([
            'success'=> true,
            'meassage'=>'Staff retrieved successfully',
            'data' => $staff
        ],200);
    }

    // get by id
    public function show($id){
        $staff = $this->staffService->getStaffById($id);

        if ($staff === null) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff retrieved successfully',
            'data' => $staff
        ], 200);
    }

    // create a new staff
    public function store(StoreStaffRequest $request){
        $staff = $this->staffService->addStaff(
            $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' => 'Staff created successfully',
            'data' => $staff
        ], 201);

    }

    // Update an existing staff member
    public function update($id, UpdateStaffRequest $request)
    {
        $staff = $this->staffService->updateStaff(
            $id,
            $request->validated()
        );

        if ($staff === null) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff updated successfully',
            'data' => $staff
        ], 200);
    }

    // delete existing staff

    public function destroy($id){
        $staff = $this->staffService->deleteStaff($id);

        if ($staff === null) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        if ($staff === 'leave_exists') {
            return response()->json([
                'success' => false,
                'message' => 'Staff cannot be deleted because leave records exist'
            ], 422);
        }


        return response()->json([
            'success' => true,
            'message' => 'Staff deleted successfully',
        ], 200);
    }

}

