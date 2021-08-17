<?php

namespace App\Controller;

use App\Entity\User;
use App\Helpers\Factories\UserFactory;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use function Symfony\Component\String\u;

/**
 * @Route("/api/v1")
 */
class UserController extends AbstractFOSRestController
{
    private $userRepository;
    private $em;
    private $userFactory;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $em, UserFactory $userFactory)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->userFactory = $userFactory;
    }

    /**
     * @Route("/user", name="user",methods={"GET"})
     */
    public function index(): Response
    {
        $user = $this->userRepository->findAll();
        $view = $this->view(['success' => true, 'data' => $user], Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @Route("/user", name="user_post",methods={"POST"})
     */
    public function store(Request $request): Response
    {
        $body = $request->toArray();
        $user = $this->userFactory->BuildUser($body);
        $this->em->persist($user);
        $this->em->flush();
        $view = $this->view(['success' => true, 'data' => ['message' => 'User saved successfully']], Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @Route("/user/{id}", name="user_show",methods={"GET"})
     */
    public function show($id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new ResourceNotFoundException("User not found");
        }
        $view = $this->view($user, Response::HTTP_OK, []);
        return $this->handleView($view);
    }

    /**
     * @Route("/user/{id}", name="user_update",methods={"PUT"})
     */
    public function update($id, Request $request): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new ResourceNotFoundException("User not found");
        }
        $body = $request->toArray();

        $userUpdated = $this->userFactory->BuildUser($body);
        $user->setRoles($userUpdated->getRoles());
        $user->setEmail($userUpdated->getEmail());
        $user->setPassword($userUpdated->getPassword());
        $this->em->flush();
        $view = $this->view($user, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
}
