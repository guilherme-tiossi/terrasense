<?php

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Auth\HmacSignatureVerifier;
use App\Infrastructure\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateHmacSignature
{
    public const USER_ATTRIBUTE = 'terrasense_user';

    public function __construct(
        private readonly HmacSignatureVerifier $verifier,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->header('X-Terrasense-User-Id');
        $timestampHeader = $request->header('X-Terrasense-Timestamp');
        $signature = $request->header('X-Terrasense-Signature');

        if ($userId === null || $timestampHeader === null || $signature === null) {
            return response()->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        if (! ctype_digit((string) $timestampHeader)) {
            return response()->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::query()->find($userId);

        if ($user === null || $user->hmac_secret === null) {
            return response()->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        $timestamp = (int) $timestampHeader;
        $rawBody = $request->getContent();

        if (! $this->verifier->verify(
            $user,
            $request->method(),
            $request->getPathInfo(),
            $timestamp,
            $rawBody,
            $signature,
        )) {
            return response()->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        $request->attributes->set(self::USER_ATTRIBUTE, $user);

        return $next($request);
    }
}
