<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Language;
use App\Models\Freelancer\Freelancer;
use App\Models\Freelancer\FreelancerLanguage;
use Faker\Generator as Faker;

$factory->define(FreelancerLanguage::class, function (Faker $faker) {
	$languages=Language::all()->pluck('id');
	$freelancers=Freelancer::all()->pluck('id');
    return [
        'language_id' => $faker->randomElement($languages),
        'freelancer_id' => $faker->randomElement($freelancers)
    ];
});
