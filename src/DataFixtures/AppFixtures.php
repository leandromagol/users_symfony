<?php

namespace App\DataFixtures;

use App\Helpers\Factories\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private $userFactory;
    public function __construct(UserFactory $userFactory)
    {
     $this->userFactory = $userFactory;
    }

    public function load(ObjectManager $manager)
    {
        $user = $this->userFactory->BuildUser([
            'email'=>'admin@admin.com',
                'password'=>12345678
        ]);
        $manager->persist($user);
        $manager->flush();
    }
}
