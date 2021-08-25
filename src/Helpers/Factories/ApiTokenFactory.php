<?php

namespace App\Helpers\Factories;

use App\Document\ApiToken;

class ApiTokenFactory
{
    public function buildApiToken(string $token,\DateTimeImmutable $expires_at): ApiToken
    {
        $api_token = new ApiToken();
        $api_token->setToken($token);
        $api_token->setExpiresAt($expires_at);
        return $api_token;
    }
}