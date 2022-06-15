<?php

namespace Tests\Unit\Crm;

use App\Http\Requests\Crm\Company\StoreCompanyRequest;
use App\Http\Requests\Crm\Company\UpdateCompanyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ServerBag;
use Tests\CreatesApplication;

class CompanyTest extends TestCase
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

    public function test_form_request_validates_user_authorized()
    {
        $request = new StoreCompanyRequest();
        $this->assertEquals(true, $request->authorize());
    }

    /**
     * @dataProvider invalid_store_data_provider
     */
    public function test_store_company_request_with_invalid_data_fails($form, $expected)
    {
        $request = StoreCompanyRequest::create(route('companies.store', [], false), 'POST', $form);
        $validator = Validator::make($form, $request->rules());
        $contains = array_intersect($validator->errors()->keys(), $expected);
        $this->assertFalse($validator->passes());
        $this->assertEqualsCanonicalizing($expected, $contains);
    }

    /**
     * @dataProvider invalid_store_data_provider
     */
    public function test_update_company_request_with_invalid_data_fails($form, $expected)
    {
        $this->mock_put_request(['company' => 1]);
        $request = UpdateCompanyRequest::create(
            route('companies.update', ['company' => 1], false),
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
                ['name_en','name_ua','email','phone','website'] // required fields
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
                ['website' => 'not url'],
                ['website']
            ],
            [
                ['logo' => 'not image'],
                ['logo']
            ]
        ];
    }

    /**
     * @dataProvider valid_store_data_provider
     */
    public function test_store_company_request_with_valid_data_passes($form)
    {
        $request = StoreCompanyRequest::create(route('companies.store', [], false), 'POST', $form);
        $validator = Validator::make($form, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /**
     * @dataProvider valid_store_data_provider
     */
    public function test_update_store_company_request_with_valid_data_passes($form)
    {
        $this->mock_put_request(['company' => 1]);
        $request = UpdateCompanyRequest::create(
            route('companies.update', ['company' => 1], false),
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
                [   // without logo
                    'name_en' => 'Test company',
                    'name_ua' => 'Тестова компанія',
                    'email'   => 'test@company.com',
                    'phone'   => '0987654321',
                    'website' => 'https://laravel.com/'
                ]
            ],
        ];
    }
}
