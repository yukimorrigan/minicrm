<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
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
            'first_name_en' => $this->faker->unique()->firstName(),
            'first_name_ua' => $uaFaker->unique()->firstName(),
            'last_name_en' => $this->faker->unique()->lastName(),
            'last_name_ua' => $uaFaker->unique()->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $uaFaker->unique()->phoneNumber(),
            'company_id' => Company::all()->pluck('id')->random()
        ];
    }
}
