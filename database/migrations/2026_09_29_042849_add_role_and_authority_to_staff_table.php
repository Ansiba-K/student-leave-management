<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleAndAuthorityToStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            // 1 = Staff, 2 = HOD, 3 = Principal
            $table->tinyInteger('role')
                ->default(1)
                ->after('department_id');

            // Can approve/reject leaves
            $table->boolean('is_authority')
                ->default(false)
                ->after('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'is_authority'
            ]);
        });
    }
}
