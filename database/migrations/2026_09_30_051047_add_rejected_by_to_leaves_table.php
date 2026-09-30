<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRejectedByToLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->unsignedBigInteger('rejected_by')
                ->nullable()
                ->after('approved_by');

            $table->foreign('rejected_by')
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
        Schema::table('leaves', function (Blueprint $table) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->dropForeign(['rejected_by']);
                $table->dropColumn('rejected_by');
            });
        });
    }
}
