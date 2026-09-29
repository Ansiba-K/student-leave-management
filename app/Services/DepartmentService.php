<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    // create departments
    public function addDepartment($data)
    {
        $id = DB::table('departments')->insertGetId([

            'name' => $data['name'],
            'created_at' => now(),
            'updated_at' => now(),

        ]);

        return DB::table('departments')
            ->where('id', $id)
            ->first();
    }

    // get all departments
    public function getAll()
    {

        return  DB::table('departments')
            ->select(
                'id',
                'name'
            )
            ->get();
    }


    // get departments by id
    public function getById($id)
    {

        return DB::table('departments')
            ->where('id', $id)
            ->first();
    }

    // update
    public function update($id, $data)
    {
        $department = DB::table('departments')
            ->where('id', $id)
            ->first();

        if (!$department) {
            return null;
        }

        DB::table('departments')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'updated_at' => now(),
            ]);

        return DB::table('departments')
            ->where('id', $id)
            ->first();
    }


    // delete
    public function deleteDepartment($id)
    {
        $department = DB::table('departments')
            ->where('id', $id)
            ->first();

        if (!$department) {
            return null;
        }

        $studentExists = DB::table('students')
            ->where('department_id', $id)
            ->exists();

        if ($studentExists) {
            return 'student_exists';
        }

        $staffExists = DB::table('staff')
            ->where('department_id', $id)
            ->exists();

        if ($staffExists) {
            return 'staff_exists';
        }

        DB::table('departments')
            ->where('id', $id)
            ->delete();

        return true;
    }
}
