<?php

namespace Tests\Helpers;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class ClientPortalTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Route::swap(null); // Reset Route facade after each test
    }

    /** @test */
    public function test_is_active_returns_true_if_page_equals_current_and_boolean_true()
    {
        Route::shouldReceive('currentRouteName')->andReturn('dashboard');
        Route::shouldReceive('currentRouteAction')->andReturn('SomeController@index');

        $this->assertTrue(isActive('dashboard', true));
    }

    /** @test */
    public function test_is_active_returns_bg_gray_if_page_equals_current_and_boolean_false()
    {
        Route::shouldReceive('currentRouteName')->andReturn('dashboard');
        Route::shouldReceive('currentRouteAction')->andReturn('SomeController@index');

        $this->assertEquals('bg-gray-200', isActive('dashboard'));
    }

    /** @test */
    public function test_is_active_returns_true_if_page_equals_show_variant_and_boolean_true()
    {
        Route::shouldReceive('currentRouteName')->andReturn('invoices.show');
        Route::shouldReceive('currentRouteAction')->andReturn('InvoiceController@show');

        // According to helper: '.show' => 's.index'
        $showVariant = 'invoicess.index'; // matches helper transformation
        $this->assertTrue(isActive($showVariant, true));
    }

    /** @test */
    public function test_is_active_returns_false_if_page_does_not_match()
    {
        Route::shouldReceive('currentRouteName')->andReturn('dashboard');
        Route::shouldReceive('currentRouteAction')->andReturn('SomeController@index');

        $this->assertFalse(isActive('settings', true));
    }

/** @test */
public function test_render_returns_view_with_root_option()
{
    $mockedView = 'view-mock';

    $viewMock = \Mockery::mock(\Illuminate\Contracts\View\Factory::class);
    $viewMock->shouldReceive('make')
        ->once()
        ->withArgs(function ($view, $data) {
            return $view === 'admin.custom.welcome'
                && $data['root'] === 'admin'
                && $data['theme'] === 'custom'
                && $data['foo'] === 'bar';
        })
        ->andReturn('view-mock');

    $this->app->instance(\Illuminate\Contracts\View\Factory::class, $viewMock);

    $result = render('welcome', ['root' => 'admin', 'theme' => 'custom', 'foo' => 'bar']);
    $this->assertEquals($mockedView, $result);
}

/** @test */
public function test_render_returns_view_with_default_theme_without_root_option()
{
    $mockedView = 'view-mock';

    $viewMock = \Mockery::mock(\Illuminate\Contracts\View\Factory::class);
    $viewMock->shouldReceive('make')
        ->once()
        ->withArgs(function ($view, $data) {
            return $view === 'portal.ninja2020.dashboard'
                && $data['foo'] === 'bar';
        })
        ->andReturn($mockedView);

    $this->app->instance(\Illuminate\Contracts\View\Factory::class, $viewMock);

    $result = render('dashboard', ['foo' => 'bar']);
    $this->assertEquals($mockedView, $result);
}

/** @test */
public function test_render_returns_view_with_custom_theme_without_root_option()
{
    $mockedView = 'view-mock';

    $viewMock = \Mockery::mock(\Illuminate\Contracts\View\Factory::class);
    $viewMock->shouldReceive('make')
        ->once()
        ->withArgs(function ($view, $data) {
            return $view === 'portal.modern.dashboard'
                && $data['theme'] === 'modern'
                && $data['foo'] === 'bar';
        })
        ->andReturn($mockedView);

    $this->app->instance(\Illuminate\Contracts\View\Factory::class, $viewMock);

    $result = render('dashboard', ['theme' => 'modern', 'foo' => 'bar']);
    $this->assertEquals($mockedView, $result);
}


}

