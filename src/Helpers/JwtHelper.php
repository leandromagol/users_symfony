<?php

namespace App\Helpers;

use App\Document\ApiToken;
use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Firebase\JWT\JWT;

 class JwtHelper
{

     public static function validJWT(string $token,DocumentManager $documentManager)
    {
        $apiToken = $documentManager->getRepository(ApiToken::class)->findOneBy(['token'=>$token]);
        if (!$apiToken){
            return ['success'=>false,'status' => 'invalid'];
        }
        try {
            $decode = JWT::decode($apiToken->getToken(),$_ENV['APP_SECRET'],['HS256']);
            $now = new \DateTime('NOW');
            $now = $now->format('Y-m-d H:i:s');
           if (strtotime($now) > strtotime($apiToken->getExpiresAt())){
                return ['success'=>false,'status' => 'expired'];
            }
            return ['success'=>true,'status' => 'ok','username'=>$decode->username];
        }catch (\Exception $exception){
            return ['success'=>false,'status' => 'invalid'];
        }

    }
    public static function generateJwt(User $user): string
    {

        $token = JWT::encode(['username' => $user->getEmail()], $_ENV['APP_SECRET']);
        return $token;
    }
}