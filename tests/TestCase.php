<?php

namespace Tests;

use App\Infrastructure\Auth\HmacSignatureVerifier;
use App\Infrastructure\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function hmacHeaders(
        User $user,
        string $method,
        string $path,
        string $body,
        ?int $timestamp = null,
    ): array {
        $timestamp ??= time();
        $verifier = new HmacSignatureVerifier;
        $signature = $verifier->compute($user, $method, $path, $timestamp, $body);

        return [
            'X-Terrasense-User-Id' => (string) $user->id,
            'X-Terrasense-Timestamp' => (string) $timestamp,
            'X-Terrasense-Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
