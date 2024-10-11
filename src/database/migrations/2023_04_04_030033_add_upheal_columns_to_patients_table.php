<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUphealColumnsToPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('upheal_user_id')->nullable()->after('is_test')->comment('external user id from upheal');
            $table->string('upheal_client_session_url')->nullable()->after('upheal_user_id')->comment('link to join upheal session (for clients)');
            $table->string('upheal_therapist_session_url')->nullable()->after('upheal_client_session_url')->comment('link to join upheal session (for therapists)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('upheal_user_id');
            $table->dropColumn('upheal_client_session_url');
            $table->dropColumn('upheal_therapist_session_url');
        });
    }
}
