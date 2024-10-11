<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_providers', function (Blueprint $table) {
            $table->string('id', 191)->unique();
            $table->string('name', 191)->unique();
            $table->string('address', 255);
            $table->string('city', 255);
            $table->string('state', 255);
            $table->string('zip', 255);
            $table->string('group_no', 255);
            $table->string('tax_id', 255);
            $table->string('npi', 255);
            $table->string('phone', 255);
            $table->timestamps();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_providers');
    }
}
