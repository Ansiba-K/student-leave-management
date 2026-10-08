<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraintToLeaveBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->unique(
                ['staff_id', 'leave_type_id', 'year'],
                'unique_staff_leave_type_year'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            Schema::table('leave_balances', function (Blueprint $table) {
                $table->dropUnique('unique_staff_leave_type_year');
            });
        });
    }
}
