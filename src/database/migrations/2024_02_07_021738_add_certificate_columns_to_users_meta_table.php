<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCertificateColumnsToUsersMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_meta', function (Blueprint $table) {
            $table->string('harassment_certificate_original_name')->nullable()->after('signature');
            $table->string('harassment_certificate_aws_name')->nullable()->after('harassment_certificate_original_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_meta', function (Blueprint $table) {
            $table->dropColumn(['harassment_certificate_original_name', 'harassment_certificate_aws_name']);
        });
    }
}
