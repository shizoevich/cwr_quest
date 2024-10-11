<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientInformationFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_information_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id');
            $table->string('name');
            $table->date('date_of_birth');
            $table->string('home_address');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('email');
            $table->boolean('email_sent')->nullable();
            $table->string('home_phone');
            $table->string('mobile_phone');
            $table->string('work_phone')->nullable();
            $table->string('emergency_contact');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relationship');
            $table->string('co_pay')->nullable();
            $table->string('self_pay')->nullable();
            $table->string('charge_for_cancellation')->nullable();
            $table->string('other_charges')->nullable();
            $table->string('other_charges_price')->nullable();
            $table->string('introduction')->nullable();
            $table->string('health_insurance')->nullable();
            $table->string('request_payment_of_authorized')->nullable();
            $table->string('hear_about_us_other_specify')->nullable();
            $table->string('referred_by_other_insurance_specify')->nullable();
            $table->string('payment_for_session_not_converted')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('relationship')->nullable();
            $table->boolean('allow_home_phone_call')->nullable();
            $table->boolean('allow_mobile_phone_call')->nullable();
            $table->boolean('allow_mobile_send_messages')->nullable();
            $table->boolean('allow_work_phone_call')->nullable();
            $table->boolean('access_credit_card')->nullable();
            $table->boolean('notify_agree')->nullable();
            $table->boolean('receive_electronic_v_of_pp')->nullable();
            $table->boolean('receive_paper_v_of_pp')->nullable();
            $table->boolean('unencrypted_communications')->nullable();
            $table->boolean('allow_request_payment_of_authorized')->nullable();
            $table->boolean('yelp')->nullable();
            $table->boolean('google')->nullable();
            $table->boolean('yellow_pages')->nullable();
            $table->boolean('event_i_attended')->nullable();
            $table->boolean('hear_about_us_other')->nullable();
            $table->boolean('friend_or_relative')->nullable();
            $table->boolean('another_professional')->nullable();
            $table->boolean('kaiser')->nullable();
            $table->boolean('referred_by_other_insurance')->nullable();
            $table->boolean('store_credit_card')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_information_forms');
    }
}
