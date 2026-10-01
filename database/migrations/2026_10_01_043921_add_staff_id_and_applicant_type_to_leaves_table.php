<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStaffIdAndApplicantTypeToLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leaves', function (Blueprint $table) {

            // Student ID can be empty when staff applies for leave
            $table->unsignedBigInteger('student_id')
                ->nullable()
                ->change();

            $table->unsignedBigInteger('staff_id')
                ->nullable()
                ->after('student_id');

            // 1 = Student, 2 = Staff
            $table->tinyInteger('applicant_type')
                ->default(1)
                ->after('staff_id');


            $table->foreign('staff_id')
                ->references('id')
                ->on('staff')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leaves', function (Blueprint $table) {
            Schema::table('leaves', function (Blueprint $table) {

                $table->dropForeign(['staff_id']);
                $table->dropColumn([
                    'staff_id',
                    'applicant_type'
                ]);
            });
        });
    }
}
