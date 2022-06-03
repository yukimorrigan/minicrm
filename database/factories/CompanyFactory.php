<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $uaFaker = Faker::create('uk_UA');
        return [
            'name_en' => $this->faker->unique()->company(),
            'name_ua' => $uaFaker->unique()->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $uaFaker->unique()->phoneNumber(),
            'website' => $this->faker->url()
        ];
    }
}
