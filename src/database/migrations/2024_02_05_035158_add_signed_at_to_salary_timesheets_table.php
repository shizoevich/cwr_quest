<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSignedAtToSalaryTimesheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_timesheets', function (Blueprint $table) {
            $table->timestamp('signed_at')->after('is_resolve_complaint')->nullable();
        });

        DB::table('salary_timesheets')
            ->whereNull('signed_at')
            ->update(['signed_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_timesheets', function (Blueprint $table) {
            $table->dropColumn('signed_at');
        });
    }
}
