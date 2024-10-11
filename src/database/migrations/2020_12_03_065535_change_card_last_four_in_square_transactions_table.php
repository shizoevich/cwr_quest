<?php

use App\Models\Square\SquareTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCardLastFourInSquareTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('square_transactions', function(Blueprint $table) {
            $table->string('card_last_four', 4)->nullable()->change();
        });
        SquareTransaction::query()->update(['card_last_four' => \DB::raw("LPAD(`card_last_four`, 4, '0')")]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('square_transactions', function(Blueprint $table) {
            $table->integer('card_last_four')->nullable()->change();
        });
    }
}
