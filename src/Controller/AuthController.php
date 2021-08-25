<?php

namespace App\Controller;

use App\Helpers\Factories\ApiTokenFactory;
use App\Helpers\JwtHelper;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class AuthController extends AbstractController
{
    private $userRepository;
    private $em;
    private $dm;
    private $apiTokenFactory;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $em,DocumentManager $documentManager,ApiTokenFactory $apiTokenFactory)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->dm = $documentManager;
        $this->apiTokenFactory = $apiTokenFactory;
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request, UserPasswordHasherInterface $hasher)
    {

        $input = $request->toArray();

        if (!isset($input['username']) || !isset($input['password'])){
            return new JsonResponse('user or password is incorrect', Response::HTTP_NOT_FOUND);
        }
        $user = $this->userRepository->findOneBy(['email' => $input['username']]);
        if (empty($user)) {
            return new JsonResponse('user or password is incorrect', Response::HTTP_NOT_FOUND);
        }
        if (!$hasher->isPasswordValid($user, $input['password'])) {
            return new JsonResponse('user or password is incorrect', Response::HTTP_NOT_FOUND);
        }

        $now = new \DateTime('NOW');
        $expires_at = new \DateTimeImmutable($now->modify('+60 minutes')->format('Y-m-d H:i:s'));
        $token = JwtHelper::generateJwt($user);
        $api_token =$this->apiTokenFactory->buildApiToken($token,$expires_at);
        $this->dm->persist($api_token);
        $this->dm->flush();
        return $this->json([
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'access_token' => $token,
        ]);
    }
}