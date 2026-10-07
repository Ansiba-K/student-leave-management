<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $table = 'leave_balances';

    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'year',
        'allocated_days',
        'used_days',
    ];
}
