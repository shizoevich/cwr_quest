<?php

use App\Models\Square\SquareLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSquareLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('square_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->integer('patient_id')->nullable();
            $table->boolean('is_success');
            $table->enum('action', [
                SquareLog::ACTION_CREATE_CUSTOMER,
                SquareLog::ACTION_UPDATE_CUSTOMER,
                SquareLog::ACTION_CREATE_CUSTOMER_CARD,
                SquareLog::ACTION_CREATE_ORDER,
                SquareLog::ACTION_CREATE_INVOICE,
                SquareLog::ACTION_PUBLISH_INVOICE,
                SquareLog::ACTION_CREATE_PAYMENT,
            ]);
            $table->longText('request')->nullable();
            $table->longText('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('square_logs');
    }
}
