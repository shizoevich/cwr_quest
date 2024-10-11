<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSquareOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('square_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id')->unique();
            $table->integer('location_id')->unsigned();
            $table->integer('customer_id')->unsigned();
            $table->integer('catalog_item_id')->unsigned();
            $table->integer('amount_money')->unsigned();
            $table->timestamp('order_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('location_id')
                ->references('id')
                ->on('square_locations')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('customer_id')
                ->references('id')
                ->on('patient_square_accounts')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('catalog_item_id')
                ->references('id')
                ->on('square_catalog_items')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('square_orders');
    }
}
