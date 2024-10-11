<?php

use App\Models\Square\SquareLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateActionColumnInSquareLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $options = [
            SquareLog::ACTION_CREATE_CUSTOMER,
            SquareLog::ACTION_UPDATE_CUSTOMER,
            SquareLog::ACTION_CREATE_CUSTOMER_CARD,
            SquareLog::ACTION_CREATE_ORDER,
            SquareLog::ACTION_GET_ORDER,
            SquareLog::ACTION_CANCEL_ORDER,
            SquareLog::ACTION_CREATE_INVOICE,
            SquareLog::ACTION_PUBLISH_INVOICE,
            SquareLog::ACTION_CREATE_PAYMENT,
        ];
        $optionsStr = '';
        for ($i = 0; $i < count($options) - 1; $i++) {
            $optionsStr .= "'" . $options[$i] . "', ";
        }
        $optionsStr .= "'" . $options[count($options) - 1] . "'";

        \DB::statement("ALTER TABLE square_logs MODIFY COLUMN `action` ENUM({$optionsStr})");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $options = [
            SquareLog::ACTION_CREATE_CUSTOMER,
            SquareLog::ACTION_UPDATE_CUSTOMER,
            SquareLog::ACTION_CREATE_CUSTOMER_CARD,
            SquareLog::ACTION_CREATE_ORDER,
            SquareLog::ACTION_CREATE_INVOICE,
            SquareLog::ACTION_PUBLISH_INVOICE,
            SquareLog::ACTION_CREATE_PAYMENT,
        ];
        $optionsStr = '';
        for ($i = 0; $i < count($options) - 1; $i++) {
            $optionsStr .= "'" . $options[$i] . "', ";
        }
        $optionsStr .= "'" . $options[count($options) - 1] . "'";
        
        \DB::statement("ALTER TABLE square_logs MODIFY COLUMN `action` ENUM({$optionsStr})");
    }
}
