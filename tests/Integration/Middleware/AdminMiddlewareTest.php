<?php
namespace Tests\Integration\Middleware;

use App\Http\Middleware\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    private Admin $adminMiddleware;

    public function setUp(): void
    {
        parent::setUp();

        $this->adminMiddleware = app()->make(Admin::class);
    }

    public function testRedirectsToAdminLoginIfNotAuthenticated(): void
    {
        $request = Request::create('/admin/dashboard');

        $response = $this->adminMiddleware->handle($request, function () {
            return 'passes';
        });

        assert($response instanceof Response);

        $this->assertTrue($response->isRedirection());
        $this->assertEquals(route('admin.login'), $response->headers->get('Location'));
    }

    public function testSucceedsIfAuthenticated(): void
    {
        $request = Request::create('/admin/dashboard');
        $this->actingAs(User::factory()->createQuietly());

        $response = $this->adminMiddleware->handle($request, function () {
            return 'passes';
        });

        $this->assertEquals('passes', $response);
    }
}
