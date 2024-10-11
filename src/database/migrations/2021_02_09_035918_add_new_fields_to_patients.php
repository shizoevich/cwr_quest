<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldsToPatients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('address_2')->nullable()->after('address');
            $table->string('city', 45)->nullable()->after('address_2');
            $table->string('state', 2)->nullable()->after('city');
            $table->string('zip', 20)->nullable()->after('state');
            $table->tinyInteger('preferred_phone')->nullable()->after('parse_work_phone');
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
            $table->dropColumn([
                'address_2',
                'city',
                'state',
                'zip',
                'preferred_phone',
            ]);
        });
    }
}
