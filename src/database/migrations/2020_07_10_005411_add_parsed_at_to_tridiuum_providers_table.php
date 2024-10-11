<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParsedAtToTridiuumProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tridiuum_providers', function(Blueprint $table) {
            $table->timestamp('parsed_at')->nullable()->after('custom_reassigned_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tridiuum_providers', function(Blueprint $table) {
            $table->dropColumn('parsed_at');
        });
    }
}
