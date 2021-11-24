<?php

use App\Models\Proposal;
use Illuminate\Database\Seeder;

class ProposalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Proposal::class, 10)->create(['state' => '2']);
    }
}
