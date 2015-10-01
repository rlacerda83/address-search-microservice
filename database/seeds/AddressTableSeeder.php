<?php

use App\Models\ServiceSearch;
use Illuminate\Database\Seeder;

class AddressTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Start AddressSearch seeder!');

        ServiceSearch::create([
            'name'            => 'PostOficce Brazil search',
            'model_reference' => 'App\Services\AddressSearch\PostOfficeBrazil',
            'country_code'      => 'BR',
            'status' => 1
        ]);

        $this->command->info('AddressSearch table seeded!');
    }
}
