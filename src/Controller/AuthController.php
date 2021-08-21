<?php

namespace App\Controller;

use App\Document\ApiToken;
use App\Repository\UserRepository;
use App\Validators\LoginRequestValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

/**
 * @Route("/api/v1")
 */
class AuthController extends AbstractController
{
    private $userRepository;
    private $em;
    private $dm;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $em,DocumentManager $documentManager)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->dm = $documentManager;
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
        $uuid = Uuid::v4();
        $expires_at = new \DateTimeImmutable($now->modify('+60 minutes')->format('Y-m-d H:i:s'));
        $token = JWT::encode(['username' => $user->getEmail(), '','api_token'=>$uuid->jsonSerialize()], $_ENV['APP_SECRET']);
        $apitoken = new ApiToken();
        $apitoken->setToken($token);
        $apitoken->setExpiresAt($expires_at);
        $this->dm->persist($apitoken);
        $this->dm->flush();
        return $this->json([
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'access_token' => $token,
            'apitoken'=>$apitoken->getId()
        ]);
    }
}