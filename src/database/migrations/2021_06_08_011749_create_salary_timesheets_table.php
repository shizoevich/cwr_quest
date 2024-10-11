<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryTimesheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_timesheet_visits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('visit_id')->nullable();
            $table->unsignedInteger('billing_period_id');
            $table->integer('patient_id');
            $table->integer('provider_id');
            $table->date('date');
            $table->boolean('is_overtime')->default(false);
            $table->boolean('is_custom_created')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamps();
            
            $table->unique(['visit_id', 'billing_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_timesheet_visits');
    }
}
