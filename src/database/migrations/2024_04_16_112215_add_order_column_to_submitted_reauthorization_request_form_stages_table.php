<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderColumnToSubmittedReauthorizationRequestFormStagesTable extends Migration
{
    /** transaction_purpose_id_to_officeally_transactions
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submitted_reauthorization_request_form_stages', function (Blueprint $table) {
            $table->unsignedInteger('order')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submitted_reauthorization_request_form_stages', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}
