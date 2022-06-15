<?php

namespace Database\Factories;

use App\Models\User;
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
            'name' => [
                'en' => $this->faker->unique()->company(),
                'ua' => $uaFaker->unique()->company(),
            ],
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $uaFaker->unique()->phoneNumber(),
            'website' => $this->faker->url(),
            'created_by' => User::all()->pluck('id')->random()
        ];
    }
}
