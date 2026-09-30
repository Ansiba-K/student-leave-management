<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDepartmentRequest;
use App\Services\DepartmentService;
use App\Http\Requests\UpdateDepartmentRequest;

class DepartmentController extends Controller
{
    protected $departmentService;
    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    // create department
    public function store(StoreDepartmentRequest $request)
    {
        $department = $this->departmentService->addDepartment(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Department Created Successfully',
            'data' => $department
        ], 201);
    }

    // get all departments
    public function index()
    {

        $departments = $this->departmentService->getAll();

        return response()->json([
            'success' => true,
            'message' => 'Departments retrieved successfully',
            'data' => $departments
        ], 200);
    }

    // get department by id
    public function show($id)
    {

        $department = $this->departmentService->getById($id);

        if ($department === null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Department not found'
                ],
                404
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Department retrieved successfully',
            'data' => $department
        ], 200);
    }

    // update
    public function update($id, UpdateDepartmentRequest $request)
    {
        $department = $this->departmentService->update(
            $id,
            $request->validated()
        );

        if ($department === null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Department not found'
                ],
                404
            );
        }


        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'data' => $department
        ], 200);
    }

    // delete department
    public function destroy($id)
    {
        $department = $this->departmentService->deleteDepartment(
            $id
        );

        if ($department === null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Department not found'
                ],
                404
            );
        }

        if ($department === 'student_exists') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Department cannot be deleted because students enrolled to it'
                ],
                422
            );
        }

        if ($department === 'staff_exists') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Department cannot be deleted because staffs enrolled to it'
                ],
                422
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully',
            'data' => $department
        ], 200);
    }
}
