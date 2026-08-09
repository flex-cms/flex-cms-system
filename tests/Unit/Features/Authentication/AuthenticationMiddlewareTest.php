<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Authentication;

use Flex\Core\Http\Contracts\RequestHandlerInterface;
use Flex\Core\Http\Request;
use Flex\Core\Http\Response;
use Flex\Features\Authentication\Contracts\AuthenticatorInterface;
use Flex\Features\Authentication\Contracts\LoginUrlResolverInterface;
use Flex\Features\Authentication\Middleware\Authenticate;
use Flex\Features\Authentication\Middleware\RequireAdmin;
use PHPUnit\Framework\TestCase;

final class AuthenticationMiddlewareTest extends TestCase
{
    protected function setUp(): void { $_SESSION = []; }

    public function testGuestIsRedirectedAndOriginalUrlIsStored(): void
    {
        $middleware = new Authenticate(new FakeAuth(false, false), new FakeLoginUrl());
        $response = $middleware->process(new Request('GET', '/admin/settings?tab=general'), new SuccessHandler());

        self::assertSame(302, $response->status());
        self::assertSame('/login', $response->header('Location'));
        self::assertSame('/admin/settings?tab=general', $_SESSION['redirect_url']);
    }

    public function testApiGuestReceives401Json(): void
    {
        $middleware = new Authenticate(new FakeAuth(false, false), new FakeLoginUrl());
        $response = $middleware->process(new Request('GET', '/api/settings'), new SuccessHandler());

        self::assertSame(401, $response->status());
        self::assertStringContainsString('Unauthenticated', $response->content());
    }

    public function testAuthenticatedUserContinues(): void
    {
        $middleware = new Authenticate(new FakeAuth(true, false), new FakeLoginUrl());
        self::assertSame(200, $middleware->process(new Request('GET', '/profile'), new SuccessHandler())->status());
    }

    public function testNonAdminReceives403(): void
    {
        $middleware = new RequireAdmin(new FakeAuth(true, false), new FakeLoginUrl());
        self::assertSame(403, $middleware->process(new Request('GET', '/admin'), new SuccessHandler())->status());
    }

    public function testAdminContinues(): void
    {
        $middleware = new RequireAdmin(new FakeAuth(true, true), new FakeLoginUrl());
        self::assertSame('success', $middleware->process(new Request('GET', '/admin'), new SuccessHandler())->content());
    }
}

final readonly class FakeAuth implements AuthenticatorInterface
{
    public function __construct(private bool $authenticated, private bool $admin) {}
    public function check(): bool { return $this->authenticated; }
    public function isAdmin(): bool { return $this->admin; }
}

final class FakeLoginUrl implements LoginUrlResolverInterface
{
    public function loginUrl(): string { return '/login'; }
}

final class SuccessHandler implements RequestHandlerInterface
{
    public function handle(Request $request): Response { return Response::make('success'); }
}
