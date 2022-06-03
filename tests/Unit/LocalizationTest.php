<?php

namespace Tests\Unit;

use App\Facades\LocalizationService;
use App\Http\Middleware\SetLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Tests\CreatesApplication;

class LocalizationTest extends TestCase
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
     * @dataProvider url_provider
     */
    public function test_set_language_middleware_changes_config_language($prefix, $url, $expected)
    {
        $route = Mockery::mock(\Illuminate\Routing\Route::class)
            ->makePartial()
            ->shouldReceive('getPrefix')
            ->andReturn($prefix)
            ->getMock();
        $request = Mockery::mock(Request::class, function (MockInterface $mock) use ($route, $url) {
            $mock->shouldReceive([
                'route' => $route,
                'path'  => $url
            ]);
        });
        $middleware = new SetLanguage();
        $middleware->handle($request, function () {});
        $this->assertEquals($expected, App::getLocale());
    }

    public function url_provider()
    {
        return [
            ['/', '/', 'ua'],
            ['/', '/companies', 'ua'],
            ['/en', '/en', 'en'],
            ['/en', '/en/companies', 'en']
        ];
    }

    /**
     * Custom getLangPrefix method returns lang code or empty string.
     * @dataProvider custom_prefix_provider
     */
    public function test_can_mock_lang_prefix($expected)
    {
        $this->mock_prefix($expected);
        $prefix = LocalizationService::getLangPrefix();
        $this->assertEquals($expected, $prefix);
    }

    public function mock_prefix($prefix)
    {
        $requestMock = Mockery::mock(Request::class)
            ->makePartial()
            ->shouldReceive('segment')
            ->with(1, '')
            ->andReturn($prefix);

        $this->app->instance('request', $requestMock->getMock());
    }

    public function custom_prefix_provider()
    {
        return [
            [''],
            ['en']
        ];
    }

    /**
     * @dataProvider language_provider
     */
    public function test_get_lang_text_returns_uppercase_language($prefix, $expected)
    {
        $this->mock_prefix($prefix);
        $lang = LocalizationService::getLangText($prefix);
        $this->assertEquals($expected, $lang);
    }

    public function language_provider()
    {
        return [
            ['', 'UA'],
            ['ua', 'UA'],
            ['en', 'EN']
        ];
    }

    /**
     * @dataProvider other_languages_provider
     */
    public function test_get_other_languages_returns_languages_except_current($prefix, $uri, $expected)
    {
        $this->mock_prefix($prefix);
        $route = Mockery::mock(\Illuminate\Routing\Route::class, function (MockInterface $mock) use ($prefix, $uri) {
            $mock->shouldReceive([
                'getPrefix' => '/'.$prefix,
                'uri'       => $uri
            ]);
        });
        Route::shouldReceive('getCurrentRoute')
            ->andReturn($route);
        $languages = LocalizationService::getOtherLanguages();
        $this->assertEquals($expected, $languages);
    }

    public function other_languages_provider()
    {
        return [
            ['', '/', [
                [
                    'route' => '/en',
                    'text' => 'EN'
                ]
            ]],
            ['', 'companies', [
                [
                    'route' => '/en/companies',
                    'text' => 'EN'
                ]
            ]],
            ['en', 'en', [
                [
                    'route' => '/',
                    'text' => 'UA'
                ]
            ]],
            ['en', 'en/companies', [
                [
                    'route' => '/companies',
                    'text' => 'UA'
                ]
            ]]
        ];
    }
}
