<?php

use App\Models\Square\SquarePaymentMethod;
use Illuminate\Database\Seeder;

class SquarePaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->getPaymentMethods()->each(function($item, $index) {
            $item['order'] = $index + 1;
            SquarePaymentMethod::updateOrCreate(['slug' => $item['slug']], $item);
        });
    }
    
    private function getPaymentMethods()
    {
        return collect([
            [
                'slug' => 'cash',
                'title' => 'Cash',
            ],
            [
                'slug' => 'check',
                'title' => 'Check',
            ],
            [
                'slug' => 'credit_card',
                'title' => 'Credit Card',
            ],
            [
                'slug' => 'invoice',
                'title' => 'Invoice',
            ],
        ]);
    }
}
