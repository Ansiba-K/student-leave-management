<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('approved_by')->nullable();

        $table->date('from_date');
        $table->date('to_date');
        $table->text('reason');

        $table->string('status')->default('pending');
        $table->text('rejection_reason')->nullable();
            $table->timestamps();

        $table->foreign('student_id')
          ->references('id')
          ->on('students')
          ->onDelete('cascade');

        $table->foreign('approved_by')
          ->references('id')
          ->on('staff')
          ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaves');
    }
}
