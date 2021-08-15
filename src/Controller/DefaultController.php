<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class DefaultController extends AbstractController
{
    private $userRepository;
    private $em;
    public function __construct(UserRepository $userRepository,EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
    }
    /**
     * @Route("/default", name="default")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DefaultController.php',
        ]);
    }
    /**
     * @Route("/login", name="login", methods={"POST"})
     */
        public function login(Request $request,UserPasswordHasherInterface $hasher){
        $user = $this->getUser();

        $input = $request->toArray();

        $user = $this->userRepository->findOneBy(['email'=>$input['username']]);
        if (empty($user)){
            return new JsonResponse('user or password is incorrect',Response::HTTP_NOT_FOUND);
        }
        if (!$hasher->isPasswordValid($user,$input['password'])){
            return new JsonResponse('user or password is incorrect',Response::HTTP_NOT_FOUND);
        }
        $now = new \DateTime('NOW');
        $expires_at = $now->modify('+1 day')->format('Y-m-d H:i:s');
        $token = JWT::encode(['username'=>$user->getEmail(),'expires_at'=> $expires_at],$_ENV['APP_SECRET']);
        $user->setApiToken($token);
        $this->em->flush();
        return $this->json([
            // The getUserIdentifier() method was introduced in Symfony 5.3.
            // In previous versions it was called getUsername()
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'access_token'=>$token
        ]);
    }
}
