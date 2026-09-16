<?php

namespace App\Infrastructure\Auth;

use App\Infrastructure\Models\User;

/**
 * Mensagem canônica assinada:
 * {HTTP_METHOD}\n{PATH}\n{TIMESTAMP}\n{RAW_BODY}
 *
 * - METHOD: uppercase (ex.: POST)
 * - PATH: request path com barra inicial (ex.: /api/readings)
 * - TIMESTAMP: unix timestamp inteiro
 * - RAW_BODY: corpo bruto da requisição (string vazia se ausente)
 */
class HmacSignatureVerifier
{
    public function isTimestampValid(int $timestamp): bool
    {
        $tolerance = config('terrasense.hmac_tolerance_seconds');

        return abs(time() - $timestamp) <= $tolerance;
    }

    public function compute(User $user, string $method, string $path, int $timestamp, string $rawBody): string
    {
        $message = $this->buildCanonicalMessage($method, $path, $timestamp, $rawBody);

        return hash_hmac('sha256', $message, $user->hmac_secret);
    }

    public function verify(
        User $user,
        string $method,
        string $path,
        int $timestamp,
        string $rawBody,
        string $providedSignature,
    ): bool {
        if (! $this->isTimestampValid($timestamp)) {
            return false;
        }

        $expected = $this->compute($user, $method, $path, $timestamp, $rawBody);

        return hash_equals($expected, strtolower($providedSignature));
    }

    public function buildCanonicalMessage(string $method, string $path, int $timestamp, string $rawBody): string
    {
        return strtoupper($method)."\n".$path."\n".$timestamp."\n".$rawBody;
    }
}
