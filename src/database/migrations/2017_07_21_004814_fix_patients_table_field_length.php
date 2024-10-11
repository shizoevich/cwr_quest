<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixPatientsTableFieldLength extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patients', function ($table) {
            $table->string('primary_insurance', 255)->change();
            $table->string('secondary_insurance', 255)->change();
        });
        if(!Schema::hasColumn('patients', 'watching')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->boolean('watching')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if(Schema::hasColumn('patients', 'watching')) {
            Schema::table('patients', function(Blueprint $table) {
                $table->dropColumn('watching');
            });
        }
    }
}
