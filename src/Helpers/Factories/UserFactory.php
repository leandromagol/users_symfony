<?php

namespace App\Helpers\Factories;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory
{
    private $hasher;
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function BuildUser( $array): User
    {
        $user = new User();
        $user->setEmail($array['email']);
        $user->setPassword($this->hasher->hashPassword($user,$array['password']));
        $user->setRoles($array['roles'] ?? []);
        return $user;
    }
}