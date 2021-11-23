<?php

use App\Models\User;
use App\Models\Freelancer\Freelancer;
use App\Models\Freelancer\FreelancerLanguage;
use App\Models\Freelancer\CategoryFreelancer;
use Illuminate\Database\Seeder;

class FreelancersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Freelancer::class)->create(['user_id' => 1]);

        $users=User::where([['id', '!=', 1], ['country_id', '!=', NULL]])->get();
        foreach ($users as $user) {
            factory(Freelancer::class)->create(['user_id' => $user->id]);
        }

        $freelancers=Freelancer::get();
        foreach($freelancers as $freelancer) {
            factory(FreelancerLanguage::class)->create(['freelancer_id' => $freelancer->id]);
        }

        foreach($freelancers as $freelancer) {
            factory(CategoryFreelancer::class)->create(['freelancer_id' => $freelancer->id]);
        }
    }
}
