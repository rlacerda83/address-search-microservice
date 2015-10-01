<?php

use App\Models\AddressSearch;
use App\Models\Country;
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

        $country = Country::find(30);
        AddressSearch::create([
            'name'            => 'PostOficce Brazil search',
            'model_reference' => '\App\Services\AddressSearch\PostOfficeBrazil',
            'country_id'      => $country->id,
        ]);

        $this->command->info('AddressSearch table seeded!');
    }
}
