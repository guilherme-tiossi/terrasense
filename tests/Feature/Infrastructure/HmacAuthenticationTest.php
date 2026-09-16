<?php

namespace Tests\Feature\Infrastructure;

use App\Infrastructure\Auth\HmacSignatureVerifier;
use App\Infrastructure\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HmacAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['hmac_secret' => 'hmac-test-secret']);
    }

    public function test_aceita_assinatura_valida(): void
    {
        $body = json_encode([
            'user_plant_id' => 1,
            'humidity' => 35.5,
            'measured_at' => '2026-09-15T18:00:00Z',
        ], JSON_THROW_ON_ERROR);

        $response = $this->call(
            'POST',
            '/api/readings',
            [],
            [],
            [],
            $this->transformHeadersToServerVars($this->hmacHeaders($this->user, 'POST', '/api/readings', $body)),
            $body,
        );

        $this->assertNotSame(401, $response->getStatusCode());
    }

    public function test_rejeita_assinatura_invalida(): void
    {
        $body = json_encode([
            'user_plant_id' => 1,
            'humidity' => 35.5,
            'measured_at' => '2026-09-15T18:00:00Z',
        ], JSON_THROW_ON_ERROR);

        $headers = $this->hmacHeaders($this->user, 'POST', '/api/readings', $body);
        $headers['X-Terrasense-Signature'] = str_repeat('a', 64);

        $response = $this->postJson('/api/readings', json_decode($body, true), $headers);

        $response->assertUnauthorized();
    }

    public function test_rejeita_usuario_inexistente(): void
    {
        $body = json_encode([
            'user_plant_id' => 1,
            'humidity' => 35.5,
            'measured_at' => '2026-09-15T18:00:00Z',
        ], JSON_THROW_ON_ERROR);

        $headers = $this->hmacHeaders($this->user, 'POST', '/api/readings', $body);
        $headers['X-Terrasense-User-Id'] = '99999';

        $response = $this->postJson('/api/readings', json_decode($body, true), $headers);

        $response->assertUnauthorized();
    }

    public function test_rejeita_timestamp_expirado(): void
    {
        $body = json_encode([
            'user_plant_id' => 1,
            'humidity' => 35.5,
            'measured_at' => '2026-09-15T18:00:00Z',
        ], JSON_THROW_ON_ERROR);

        $expiredTimestamp = time() - config('terrasense.hmac_tolerance_seconds') - 10;

        $response = $this->call(
            'POST',
            '/api/readings',
            [],
            [],
            [],
            $this->transformHeadersToServerVars(
                $this->hmacHeaders($this->user, 'POST', '/api/readings', $body, $expiredTimestamp)
            ),
            $body,
        );

        $response->assertUnauthorized();
    }

    public function test_rejeita_headers_ausentes(): void
    {
        $response = $this->postJson('/api/readings', [
            'user_plant_id' => 1,
            'humidity' => 35.5,
            'measured_at' => '2026-09-15T18:00:00Z',
        ]);

        $response->assertUnauthorized();
    }

    public function test_comparacao_de_assinatura_e_segura(): void
    {
        $verifier = new HmacSignatureVerifier;
        $timestamp = time();
        $body = '{"humidity":35.5}';

        $valid = $verifier->compute($this->user, 'POST', '/api/readings', $timestamp, $body);
        $almostValid = substr($valid, 0, -1).($valid[-1] === 'a' ? 'b' : 'a');

        $this->assertFalse($verifier->verify(
            $this->user,
            'POST',
            '/api/readings',
            $timestamp,
            $body,
            $almostValid,
        ));
    }
}
