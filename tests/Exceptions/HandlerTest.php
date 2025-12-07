<?php

namespace Tests\Exceptions;

use Tests\TestCase;
use Mockery;
use ReflectionClass;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use App\Exceptions\Handler;
use App\Exceptions\PaymentRefundFailed;
use App\Exceptions\PaymentFailed;
use App\Exceptions\YodleeApiException;
use App\Exceptions\GenericPaymentDriverFailure;
use App\Exceptions\StripeConnectFailure;
use App\Exceptions\InternalPDFFailure;
use App\Exceptions\PhantomPDFFailure;
use App\Exceptions\FilePermissionsFailure;
use App\Exceptions\MissingAppKeyException;
use App\Utils\Ninja;
use App\Services\Integration;
use Sentry\State\Scope;
use Illuminate\Support\Facades\Auth;
/**
 * Exposes protected unauthenticated() for testing
 */
class TestableHandler extends Handler
{
    public function publicUnauthenticated($request, $exception)
    {
        return $this->unauthenticated($request, $exception);
    }

    public function publicReport($exception)
    {
        return $this->report($exception);
    }
}

/**
 * Class HandlerTest
 *
 * Tests core branches of App\Exceptions\Handler
 */
class HandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function getHandlerInstance(): Handler
    {
        return new Handler(app());
    }

    private function getTestableHandler(): TestableHandler
    {
        return new TestableHandler(app());
    }

    private function callPrivate(Handler $handler, string $method, array $args = [])
    {
        $ref = new ReflectionClass($handler);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);
        return $m->invokeArgs($handler, $args);
    }

    /** @test */
    public function valid_exception_detects_blocked_messages()
    {
        $handler = $this->getHandlerInstance();

        $blocked_messages = [
            'file_put_contents(): failed to open stream',
            'Permission denied while accessing file',
            'flock() failed to acquire lock',
            'expects parameter 1 to be resource, boolean given',
            'fwrite(): unable to write',
            'LockableFile encountered an issue',
        ];

        foreach ($blocked_messages as $msg) {
            $result = $this->callPrivate($handler, 'validException', [new \Exception($msg)]);
            $this->assertFalse($result);
        }

        $ok = $this->callPrivate($handler, 'validException', [new \Exception('A harmless message')]);
        $this->assertTrue($ok);
    }

    /** @test */
    public function unauthenticated_returns_json_or_redirect()
    {
        $handler = $this->getTestableHandler();

        $reqJson = Mockery::mock(Request::class);
        $reqJson->shouldReceive('expectsJson')->andReturn(true);
        $exJson = new AuthenticationException('unauth', ['user']);
        $resJson = $handler->publicUnauthenticated($reqJson, $exJson);
        $this->assertEquals(401, $resJson->getStatusCode());

        $reqRedirect = Mockery::mock(Request::class);
        $reqRedirect->shouldReceive('expectsJson')->andReturn(false);
        $exRedirect = new AuthenticationException('unauth', ['contact']);
        $resRedirect = $handler->publicUnauthenticated($reqRedirect, $exRedirect);
        $this->assertInstanceOf(RedirectResponse::class, $resRedirect);
    }

/** @test */
public function report_handles_all_branches()
{
    // Create a mock NinjaWrapper
    $ninjaMock = Mockery::mock(\App\Utils\NinjaWrapper::class);
    
    // Simulate hosted environment
    $ninjaMock->shouldReceive('isHosted')->andReturn(true);
    $ninjaMock->shouldReceive('isSelfHost')->andReturn(false);

    // Inject mock into handler
    $handler = new TestableHandler(app(), $ninjaMock);

    // Mock Integration (Sentry) as before
    $integration = Mockery::mock('overload:App\Services\Integration');
    $integration->shouldReceive('configureScope')->once()->andReturnUsing(function ($callback) {
        $scope = Mockery::mock(Scope::class);
        $scope->shouldReceive('setUser')->once();
        $callback($scope);
    });
    $integration->shouldReceive('captureUnhandledException')->once();

    // Mock a user with company/account
    $mockUser = Mockery::mock();
    $mockUser->shouldReceive('email')->andReturn('contact@example.com');
    $mockCompany = Mockery::mock();
    $mockAccount = Mockery::mock();
    $mockAccount->shouldReceive('key')->andReturn('contact-key');
    $mockCompany->shouldReceive('account')->andReturn($mockAccount);
    $mockUser->shouldReceive('company')->andReturn($mockCompany);

    // Mock auth guards using Facade swapping
    $guardMock = Mockery::mock();
    $guardMock->shouldReceive('user')->andReturn($mockUser);

    \Illuminate\Support\Facades\Auth::shouldReceive('guard')
        ->with('contact')->andReturn($guardMock);
    \Illuminate\Support\Facades\Auth::shouldReceive('guard')
        ->with('user')->andReturn(null);

    // Throw a regular exception to cover hosted branch
    $exception = new \Exception('Test');
    $handler->publicReport($exception);

    // Self-host branch with MissingAppKeyException
   if (class_exists(MissingAppKeyException::class)) {
    $ninjaMock->shouldReceive('isSelfHost')->andReturn(true);
    $missingKeyEx = new MissingAppKeyException();

    $this->expectOutputRegex('/To setup the app run/');
    $handler->publicReport($missingKeyEx);
}

    // Also cover non-hosted branch with app()->bound('sentry')
    $ninjaMock->shouldReceive('isHosted')->andReturn(false);
    $ninjaMock->shouldReceive('isSelfHost')->andReturn(false);

    $handler->publicReport(new \Exception('Another test'));
    
    $this->assertTrue(true); // ensures PHPUnit sees at least one assertion

}



   
    /** @test */
    public function render_returns_400_for_model_not_found_when_json_expected()
    {
        $handler = $this->getHandlerInstance();

        $req = Mockery::mock(Request::class);
        $req->shouldReceive('expectsJson')->andReturn(true);
$req->shouldReceive('getRequestFormat')->andReturn('html');
$req->shouldReceive('getMimeType')->andReturn('html'); 
        $ex = new ModelNotFoundException('Model not found message');

        $response = $handler->render($req, $ex);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Model not found message', $response->getContent());
    }

    /** @test */
    public function render_internal_pdf_and_phantom_pdf_return_500_when_json_expected()
    {
        $handler = $this->getHandlerInstance();
        $req = Mockery::mock(Request::class);
        $req->shouldReceive('expectsJson')->andReturn(true);
$req->shouldReceive('getRequestFormat')->andReturn('html');
        if (class_exists(InternalPDFFailure::class)) {
            $ex = new InternalPDFFailure('pdf internal');
            $response = $handler->render($req, $ex);
            $this->assertEquals(500, $response->getStatusCode());
            $this->assertStringContainsString('pdf internal', $response->getContent());
        }

        if (class_exists(PhantomPDFFailure::class)) {
            $ex2 = new PhantomPDFFailure('phantom pdf');
            $response2 = $handler->render($req, $ex2);
            $this->assertEquals(500, $response2->getStatusCode());
            $this->assertStringContainsString('phantom pdf', $response2->getContent());
        }
    }

    /** @test */
    public function render_file_permissions_failure_returns_500()
    {
        if (! class_exists(FilePermissionsFailure::class)) {
            $this->markTestSkipped('FilePermissionsFailure class missing in this codebase.');
            return;
        }

        $handler = $this->getHandlerInstance();
        $req = Mockery::mock(Request::class);
        // handler checks FilePermissionsFailure with no expectsJson requirement
        $req->shouldReceive('expectsJson')->andReturn(false);
$req->shouldReceive('getRequestFormat')->andReturn('html');
        $ex = new FilePermissionsFailure('perm fail');
        $response = $handler->render($req, $ex);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('perm fail', $response->getContent());
    }

    /** @test */
    public function render_throttle_requests_returns_429_when_json_expected()
    {
        $handler = $this->getHandlerInstance();
        $req = Mockery::mock(Request::class);
        $req->shouldReceive('expectsJson')->andReturn(true);

        $ex = new ThrottleRequestsException('Too many');
        $response = $handler->render($req, $ex);

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertStringContainsString('Too many', $response->getContent());
    }

    /** @test */
    public function render_authorization_exception_returns_401_when_json_expected()
    {
        $handler = $this->getHandlerInstance();
        $req = Mockery::mock(Request::class);
        $req->shouldReceive('expectsJson')->andReturn(true);

        $ex = new AuthorizationException('Not allowed');
        $response = $handler->render($req, $ex);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString('Not allowed', $response->getContent());
    }

    /** @test */
    public function render_token_mismatch_redirects_back()
    {
        $handler = $this->getHandlerInstance();

        $req = Mockery::mock(Request::class);
        $req->shouldReceive('expectsJson')->andReturn(false);
        $req->shouldReceive('except')->andReturn([]);

        // Use token mismatch exception from framework
        $ex = new \Illuminate\Session\TokenMismatchException();

        $response = $handler->render($req, $ex);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function render_not_found_and_method_not_allowed_return_404_when_json_expected()
    {
        $handler = $this->getHandlerInstance();
        $req = Mockery::mock(Request::class);
        $req->shouldReceive('expectsJson')->andReturn(true);

        $nf = new NotFoundHttpException('route not found');
        $res1 = $handler->render($req, $nf);
        $this->assertEquals(404, $res1->getStatusCode());
        $this->assertStringContainsString('Route does not exist', $res1->getContent());

        $mn = new MethodNotAllowedHttpException([], 'method not allowed');
        $res2 = $handler->render($req, $mn);
        $this->assertEquals(404, $res2->getStatusCode());
        $this->assertStringContainsString('Method not supported for this route', $res2->getContent());
    }

    /** @test */
    public function render_validation_exception_returns_422_with_errors()
    {
        $handler = $this->getHandlerInstance();
        $req = Mockery::mock(Request::class);
        $req->shouldReceive('expectsJson')->andReturn(true);

        // Build a ValidationException with a Validator that has a MessageBag
        $validator = \Illuminate\Support\Facades\Validator::make([], [
            'name' => 'required',
        ]);
        try {
            $validator->validate();
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $ex = $ve;
        }

        $response = $handler->render($req, $ex);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('The given data was invalid', $response->getContent());
    }

    /** @test */
    public function render_relation_not_found_returns_400_when_json_expected()
    {
        if (! class_exists(RelationNotFoundException::class)) {
            $this->markTestSkipped('RelationNotFoundException not present');
            return;
        }
elseif ($exception instanceof RelationNotFoundException && $request->expectsJson()) {
    return response()->json(['message' => 'Relation not found: ' . $exception->getMessage()], 400);
}

        $handler = $this->getHandlerInstance();
        $req = Mockery::mock(Request::class);
        $req->shouldReceive('expectsJson')->andReturn(true);

        $ex = new RelationNotFoundException('myrelation');
        $res = $handler->render($req, $ex);

        $this->assertEquals(400, $res->getStatusCode());
        $this->assertStringContainsString('Relation', $res->getContent());
    }

    /** @test */
   /** @test */
public function render_generic_payment_driver_failure_and_stripe_connect_return_400()
{
    $handler = $this->getHandlerInstance();
    $req = Mockery::mock(Request::class);
    $req->shouldReceive('expectsJson')->andReturn(true);

    if (class_exists(GenericPaymentDriverFailure::class)) {
        $ex = new GenericPaymentDriverFailure('driver fail');
        $res = $handler->render($req, $ex);
        $this->assertEquals(400, $res->getStatusCode());
        $this->assertStringContainsString('driver fail', $res->getContent());
    }elseif ($exception instanceof GenericPaymentDriverFailure && $request->expectsJson()) {
    return response()->json(['message' => $exception->getMessage()], 400);
} elseif ($exception instanceof StripeConnectFailure && $request->expectsJson()) {
    return response()->json(['message' => $exception->getMessage()], 400);
}


    if (class_exists(StripeConnectFailure::class)) {
        $company = app()->make(\App\Models\Company::class);

        // FIX HERE — StripeConnectFailure accepts only ONE parameter
        $ex2 = new StripeConnectFailure($company);

        $res2 = $handler->render($req, $ex2);
        $this->assertEquals(400, $res2->getStatusCode());
    }
}

    /** @test */
    public function unauthenticated_returns_json_when_requested_and_redirect_when_not()
    {
        $handler = $this->getHandlerInstance();

        $reqJson = Mockery::mock(Request::class);
        $reqJson->shouldReceive('expectsJson')->andReturn(true);

        $ae = new AuthenticationException('unauth', ['user']);

        $resJson = $this->callPrivate($handler, 'unauthenticated', [$reqJson, $ae]);
        $this->assertEquals(401, $resJson->getStatusCode());
        $this->assertStringContainsString('Unauthenticated', $resJson->getContent());

        // redirect case
        $reqRedirect = Mockery::mock(Request::class);
        $reqRedirect->shouldReceive('expectsJson')->andReturn(false);

        $ae2 = new AuthenticationException('no', ['contact']);
        $resRedirect = $this->callPrivate($handler, 'unauthenticated', [$reqRedirect, $ae2]);

        $this->assertInstanceOf(RedirectResponse::class, $resRedirect);
    }
}

