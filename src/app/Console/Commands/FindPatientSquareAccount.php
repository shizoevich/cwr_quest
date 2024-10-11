<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use App\Jobs\Square\FindCustomer;
use App\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\Dispatchable;

class FindPatientSquareAccount extends Command
{
    use Dispatchable, Queueable;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient:find-square';
    protected $BASE_SQUARE_URL = 'https://connect.squareup.com/v2/';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $squareCustomers = $this->getSquareCustomers();

        $patients = Patient::with(['squareAccount','squareAccount.cards'])->get();
        foreach ($patients as $patient) {

            if(!is_null($patient->squareAccount)) {

            } else {
                $findSquareAccount = null;
                foreach ($squareCustomers as $squareCustomer) {
                    if(array_key_exists('reference_id', $squareCustomer) && $squareCustomer['reference_id'] == $patient->patient_id) {
                        $findSquareAccount = $squareCustomer;
                        break;
                    }

                    if(array_key_exists('family_name', $squareCustomer) && array_key_exists('given_name', $squareCustomer) && $squareCustomer['family_name'] == $patient->last_name && $squareCustomer['given_name'] == $patient->first_name) {
                        $findSquareAccount = $squareCustomer;
                        break;
                    }

                }

                if(!is_null($findSquareAccount)) {
                    $squareAccountData = ['external_id' => $findSquareAccount['id']];
                    $patient->squareAccount()->create($squareAccountData);
                    $patient = Patient::find($patient->id);

                    if(array_key_exists('cards', $findSquareAccount)) {
                        foreach ($findSquareAccount['cards'] as $card) {
                            $patient->squareAccount->cards()->create([
                                'card_id' => $card['id'],
                                'card_brand' => $card['card_brand'],
                                'last_four' => $card['last_4'],
                                'exp_month' => $card['exp_month'],
                                'exp_year' => $card['exp_year'],
                            ]);
                        }
                    }

                }
            }

        }
    }


    private function getSquareCustomers() {
        $squareCustomers = [];
        $squareCursor = null;
        while($squareCursor !== '') {
            $result = json_decode($this->getCustomers($squareCursor), true);
            if(array_key_exists('cursor', $result)) {
                $squareCursor = $result['cursor'];

            } else {
                $squareCursor = '';
            }
            if(is_null($result['customers'])) {
                break;
            }
            $squareCustomers = array_merge($squareCustomers, $result['customers']);
        }

        return $squareCustomers;
    }

    /**
     * @param $url
     * @param string $method
     *
     * @return mixed
     */
    private function sendRequest($url, $method = 'GET') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . config('square.access_token'),
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $response = curl_exec($ch);

        curl_close($ch);
        return $response;
    }

    /**
     * @return mixed
     */

    public function getCustomers($cursor = null) {
        $url = $this->BASE_SQUARE_URL . "customers";
        if(!is_null($cursor)) {
            $url .= '?cursor=' . $cursor;
        }
        return $this->sendRequest($url);
    }

}
