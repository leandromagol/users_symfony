<?php

namespace App\Helpers;

use Firebase\JWT\JWT;

 class JwtVerify
{
    public static function ValidJWT(string $token)
    {
        try {
            $decode = JWT::decode($token,$_ENV['APP_SECRET'],['HS256']);
            $now = new \DateTime('NOW');
            $now = $now->format('Y-m-d H:i:s');
            if (strtotime($now) > strtotime($decode->expires_at)){
                return ['success'=>false,'status' => 'expired'];
            }
            return ['success'=>true,'status' => 'ok'];
        }catch (\Exception $exception){
            return ['success'=>false,'status' => 'invalid'];
        }

    }
}