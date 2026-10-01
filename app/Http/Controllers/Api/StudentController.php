<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Services\StudentService;
use App\Http\Requests\StudentSearchRequest;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    // Get all students and search students by type and value
    public function index(StudentSearchRequest $request)
    {
        $students = $this->studentService->getAllStudents(
        $request->type,
        $request->value
    );

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No students found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Students retrieved successfully',
            'data' => $students
        ], 200);
    }

    // Get student by ID
    public function show($id)
    {
        $student = $this->studentService->getStudentById($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student retrieved successfully',
            'data' => $student
        ], 200);
    }

    // Create student
    public function store(StoreStudentRequest $request)
    {
        $student = $this->studentService->addStudent(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => $student
        ], 201);
    }

    // Update student
    public function update($id, UpdateStudentRequest $request)
    {
        $student = $this->studentService->updateStudent(
            $id,
            $request->validated()
        );

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => $student
        ], 200);
    }

    // Delete student
    public function destroy($id)
    {
        $result = $this->studentService->deleteStudent($id);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        if ($result === 'leave_exists') {
            return response()->json([
                'success' => false,
                'message' => 'Student cannot be deleted because leave records exist'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully'
        ], 200);
    }
}
