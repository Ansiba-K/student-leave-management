<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StaffService
{
    public function getAllStaff()
    {
        return DB::table('staff')
            ->leftJoin(
                'departments',
                'staff.department_id',
                '=',
                'departments.id'
            )
            ->select(
                'staff.id',
                'staff.name',
                'staff.email',
                'staff.phone',
                'staff.department_id',
                'departments.name as department_name',
                'staff.role',
                'staff.is_authority'
            )
            ->get();
    }

    // Get staff by ID
    public function getStaffById($id)
    {
        return DB::table('staff')
            ->leftJoin(
                'departments',
                'staff.department_id',
                '=',
                'departments.id'
            )
            ->where('staff.id', $id)
            ->select(
                'staff.id',
                'staff.name',
                'staff.email',
                'staff.phone',
                'staff.department_id',
                'departments.name as department_name',
                'staff.role',
                'staff.is_authority'
            )
            ->first();
    }

    // Create a new staff member
    public function addStaff($data)
    {
        $id = DB::table('staff')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'department_id' => $data['department_id'] ?? null,
            'role' => $data['role'],
            'is_authority' => $data['is_authority'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->getStaffById($id);
    }

    // Update an existing staff member
    public function updateStaff($id, $data)
    {

        $staff = DB::table('staff')
            ->where('id', $id)
            ->first();

        if (!$staff) {
            return null;
        }

        // Update staff details
        DB::table('staff')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'department_id' => $data['department_id'] ?? null,
                'role' => $data['role'],
                'is_authority' => $data['is_authority'],
                'updated_at' => now(),
            ]);

        return $this->getStaffById($id);
    }

    public function deleteStaff($id)
    {

        $staff = DB::table('staff')
            ->where('id', $id)
            ->first();

        if (!$staff) {
            return null;
        }

        // Check whether this staff member is used
        // in any leave approval or rejection

        $hasLeaveRecords = DB::table('leaves')
            ->where('approved_by', $id)
            ->exists();

        if ($hasLeaveRecords) {
            return 'leave_exists';
        }

        DB::table('staff')
            ->where('id', $id)
            ->delete();

        return true;
    }
}
