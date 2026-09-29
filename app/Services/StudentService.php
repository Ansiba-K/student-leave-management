<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StudentService
{

    // Create student
    public function addStudent($data)
    {
        $id = DB::table('students')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'course' => $data['course'],
            'department_id' => $data['department_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->getStudentById($id);
    }
    // Get all students
    public function getAllStudents()
    {
        return DB::table('students')
            ->join(
                'departments',
                'students.department_id',
                '=',
                'departments.id'
            )
            ->select(
                'students.id',
                'students.name',
                'students.email',
                'students.phone',
                'students.course',
                'students.department_id',
                'departments.name as department_name'
            )
            ->get();
    }

    // Get student by ID
    public function getStudentById($id)
    {
        return DB::table('students')
            ->join(
                'departments',
                'students.department_id',
                '=',
                'departments.id'
            )
            ->where('students.id', $id)
            ->select(
                'students.id',
                'students.name',
                'students.email',
                'students.phone',
                'students.course',
                'students.department_id',
                'departments.name as department_name'
            )
            ->first();
    }
    
    // Update student
    public function updateStudent($id, $data)
    {
        $student = DB::table('students')
            ->where('id', $id)
            ->first();

        if (!$student) {
            return null;
        }

        DB::table('students')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'course' => $data['course'],
                'department_id' => $data['department_id'],
                'updated_at' => now(),
            ]);

        return $this->getStudentById($id);
    }

    // Delete student
    public function deleteStudent($id)
    {
        $student = DB::table('students')
            ->where('id', $id)
            ->first();

        if (!$student) {
            return null;
        }

        $hasLeaves = DB::table('leaves')
            ->where('student_id', $id)
            ->exists();

        if ($hasLeaves) {
            return 'leave_exists';
        }

        DB::table('students')
            ->where('id', $id)
            ->delete();

        return true;
    }
}