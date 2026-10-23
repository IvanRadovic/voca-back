<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Walks every GET endpoint of the API against the seeded demo data, as a guest
 * and as each role, and fails on any 5xx.
 *
 * This is a safety net for framework and dependency upgrades, not a behaviour
 * spec: it proves that every route still boots, resolves its bindings, runs
 * its queries and serialises a response.
 */
class ApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_health_endpoint_is_up(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_seeded_accounts_can_log_in(): void
    {
        foreach (['admin@voca.test', 'nvo@voca.test'] as $email) {
            $this->postJson('/api/login', ['email' => $email, 'password' => 'password'])
                ->assertOk()
                ->assertJsonStructure(['token']);
        }

        $this->postJson('/api/login', ['email' => 'admin@voca.test', 'password' => 'wrong'])
            ->assertStatus(422);
    }

    public function test_youth_can_register(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Smoke Test',
            'email' => 'smoke@voca.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'smoke@voca.test', 'role' => User::ROLE_YOUTH]);
    }

    public function test_email_rule_rejects_header_injection(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Smoke Test',
            'email' => "smoke@voca.test\r\nBcc: victim@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_protected_routes_reject_guests(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        $this->getJson('/api/admin/mentors')->assertUnauthorized();
        $this->getJson('/api/nvo/stats')->assertUnauthorized();
    }

    public function test_role_middleware_blocks_other_roles(): void
    {
        $youth = User::where('role', User::ROLE_YOUTH)->firstOrFail();

        $this->actingAs($youth)->getJson('/api/admin/mentors')->assertForbidden();
        $this->actingAs($youth)->getJson('/api/nvo/stats')->assertForbidden();
    }

    public function test_certificate_pdf_renders(): void
    {
        $certificate = Certificate::first();

        if (! $certificate) {
            $this->markTestSkipped('The seeder created no certificates.');
        }

        $this->get("/api/certificates/{$certificate->code}/pdf")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_every_get_route_responds_without_server_error_for_every_role(): void
    {
        $actors = [
            'guest' => null,
            'youth' => User::where('role', User::ROLE_YOUTH)->firstOrFail(),
            'nvo' => User::where('role', User::ROLE_NVO)->firstOrFail(),
            'admin' => User::where('role', User::ROLE_ADMIN)->firstOrFail(),
        ];

        $failures = [];
        $checked = 0;

        foreach ($this->getRoutes() as $route) {
            $uri = $this->fillParameters($route);

            if ($uri === null) {
                $failures[] = "{$route->uri()}: no seeded record to fill its parameters";

                continue;
            }

            foreach ($actors as $name => $user) {
                if ($user) {
                    $this->actingAs($user, 'sanctum');
                } else {
                    $this->app['auth']->forgetGuards();
                }

                $status = $this->getJson($uri)->getStatusCode();
                $checked++;

                if ($status >= 500) {
                    $failures[] = "GET {$uri} as {$name}: {$status}";
                }
            }
        }

        $this->assertGreaterThan(20, $checked, 'The route walk checked suspiciously few routes.');
        $this->assertSame([], $failures, "Routes failing:\n".implode("\n", $failures));
    }

    /** @return array<int, Route> */
    private function getRoutes(): array
    {
        return array_values(array_filter(
            RouteFacade::getRoutes()->getRoutes(),
            fn (Route $route) => in_array('GET', $route->methods())
                && Str::startsWith($route->uri(), 'api/'),
        ));
    }

    /** Replace every {param} with a key that exists in the seeded data. */
    private function fillParameters(Route $route): ?string
    {
        $values = [
            'call' => fn () => \App\Models\Call::value('id'),
            'nvo' => fn () => \App\Models\Nvo::value('id'),
            'post' => fn () => \App\Models\Post::value('slug'),
            'mentor' => fn () => \App\Models\Mentor::value('id'),
            'code' => fn () => Certificate::value('code'),
            'application' => fn () => \App\Models\Application::value('id'),
        ];

        $uri = '/'.$route->uri();

        foreach ($route->parameterNames() as $name) {
            $value = isset($values[$name]) ? $values[$name]() : null;

            if ($value === null) {
                return null;
            }

            $uri = preg_replace('/\{'.$name.'(:[^}]+)?\??\}/', (string) $value, $uri);
        }

        return $uri;
    }
}
