<?php

namespace Tests\Unit\Crm;

use App\Http\Requests\Crm\Employee\StoreEmployeeRequest;
use App\Http\Requests\Crm\Employee\UpdateEmployeeRequest;
use App\Mail\NewEmployee;
use App\Models\Company;
use App\Models\Employee;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ServerBag;
use Tests\CreatesApplication;

class EmployeeTest extends TestCase
{
    use CreatesApplication;

    /**
     * The Illuminate application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    protected function setUp(): void
    {
        parent::setUp();
        // set facade root
        $this->app = $this->createApplication();
        Facade::setFacadeApplication($this->app);
    }

    /**
     * @dataProvider build_email_provider
     */
    public function test_build_new_employee_mail_contains_employee_info($lang, $employee)
    {
        Config::set('app.locale', $lang);
        $mailable = new NewEmployee($employee);
        $this->assertTrue($mailable->hasSubject(__('emails.new_employee_subject')));
        $mailable->assertSeeInHtml($employee["id"]);
        $mailable->assertSeeInHtml($employee["first_name_$lang"]);
        $mailable->assertSeeInHtml($employee["last_name_$lang"]);
        $mailable->assertSeeInHtml($employee["email"]);
        $mailable->assertSeeInHtml($employee["phone"]);
        $mailable->assertSeeInHtml($employee->company["name_$lang"]);
    }

    public function build_email_provider() : array
    {
        $faker = Faker::create('en_EN');
        $uaFaker = Faker::create('uk_UA');

        $company = new Company();
        $company->id = 1;
        $company->name_en = $faker->unique()->company();
        $company->name_ua = $uaFaker->unique()->company();
        $company->email = $faker->unique()->companyEmail();
        $company->phone = $uaFaker->unique()->phoneNumber();
        $company->website = $faker->url();

        $employee = new Employee();
        $employee->id = 1;
        $employee->first_name_en = $faker->unique()->firstName();
        $employee->first_name_ua = $uaFaker->unique()->firstName();
        $employee->last_name_en = $faker->unique()->lastName();
        $employee->last_name_ua = $uaFaker->unique()->lastName();
        $employee->email = $faker->unique()->safeEmail();
        $employee->phone = $uaFaker->unique()->phoneNumber();
        $employee->company = $company;

        return [
            ['ua', $employee],
            ['en', $employee]
        ];
    }

    /**
     * @dataProvider invalid_store_data_provider
     */
    public function test_store_employee_request_with_invalid_data_fails($form, $expected)
    {
        $request = StoreEmployeeRequest::create(route('employees.store', [], false), 'POST', $form);
        $validator = Validator::make($form, $request->rules());
        $contains = array_intersect($validator->errors()->keys(), $expected);
        $this->assertFalse($validator->passes());
        $this->assertEqualsCanonicalizing($expected, $contains);
    }

    /**
     * @dataProvider invalid_store_data_provider
     */
    public function test_update_employee_request_with_invalid_data_fails($form, $expected)
    {
        $this->mock_put_request(['employee' => 1]);
        $request = UpdateEmployeeRequest::create(
            route('employees.update', ['employee' => 1], false),
            'PUT',
            $form
        );
        $validator = Validator::make($form, $request->rules());
        $contains = array_intersect($validator->errors()->keys(), $expected);
        $this->assertFalse($validator->passes());
        $this->assertEqualsCanonicalizing($expected, $contains);
    }

    public function mock_put_request($parameters)
    {
        $route = Mockery::mock(\Illuminate\Routing\Route::class, function (MockInterface $mock) {
            $mock->makePartial();
        });
        $route->parameters = $parameters;
        $server = Mockery::mock(ServerBag::class, function (MockInterface $mock) {
            $mock->makePartial()
                ->shouldReceive('isSecure')
                ->andReturn(true);
        });
        $server->set('parameters', $parameters);
        $requestMock = Mockery::mock(Request::class)
            ->makePartial()
            ->shouldReceive(['route' => $route, 'getHost' => 'localhost', 'getPort' => 80])
            ->getMock();
        $requestMock->server = $server;
        $this->app->instance('request', $requestMock);
    }

    public function invalid_store_data_provider()
    {
        return [
            [
                [], // all fields are empty
                ['first_name_en','first_name_ua','last_name_en','last_name_ua','email','phone','company_id'] // required fields
            ],
            [
                [
                    'first_name_en' => 'Українською',
                    'last_name_en' => 'Українською',
                    'first_name_ua' => 'In Ukrainian',
                    'last_name_ua' => 'In Ukrainian'
                ],
                ['first_name_en','first_name_ua','last_name_en','last_name_ua'] // mixed up languages
            ],
            [
                ['email' => '1'],
                ['email']
            ],
            [
                ['phone' => '1'],
                ['phone']
            ],
            [
                ['company_id' => 'not integer'],
                ['company_id']
            ]
        ];
    }

    /**
     * @dataProvider valid_store_data_provider
     */
    public function test_store_employee_request_with_valid_data_passes($form)
    {
        $request = StoreEmployeeRequest::create(route('employees.store', [], false), 'POST', $form);
        $validator = Validator::make($form, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /**
     * @dataProvider valid_store_data_provider
     */
    public function test_update_store_employee_request_with_valid_data_passes($form)
    {
        $this->mock_put_request(['employee' => 1]);
        $request = UpdateEmployeeRequest::create(
            route('employees.update', ['employee' => 1], false),
            'PUT',
            $form
        );
        $validator = Validator::make($form, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function valid_store_data_provider()
    {
        return [
            [
                [
                    'first_name_en' => 'Test first name',
                    'first_name_ua' => 'Тестове ім\'я',
                    'last_name_en'  => 'Test last name',
                    'last_name_ua'  => 'Тестова фамілія',
                    'email'         => 'test@employee.com',
                    'phone'         => '0987654321',
                    'company_id'    => '1'
                ]
            ],
        ];
    }
}
