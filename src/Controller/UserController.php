<?php

namespace App\Controller;

use App\Form\Type\UserType;
use App\Helpers\Factories\UserFactory;
use App\Repository\UserRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @Route("/api/v1")
 */
class UserController extends AbstractFOSRestController
{
    private $userRepository;
    private $userFactory;

    public function __construct(UserRepository $userRepository, UserFactory $userFactory)
    {
        $this->userRepository = $userRepository;

        $this->userFactory = $userFactory;
    }

    /**
     * @Route("/user", name="user",methods={"GET"})
     */
    public function index(): Response
    {
        $user = $this->userRepository->findAll();
        $view = $this->view([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => $user
        ], Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @Route("/user", name="user_post",methods={"POST"})
     */
    public function store(Request $request): Response
    {
        $form = $this->createForm(UserType::class);
        $body = json_decode($request->getContent(), true);

        $form->submit($body);
        if (!$form->isSubmitted() || !$form->isValid()) {
            $view = $this->view(['success' => false, 'data' => [
                'message' => 'Error on save user',
                'data' => $form]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }

        $user = $this->userFactory->BuildUser($body);
        $this->userRepository->store($user);
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
        $view = $this->view([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => $user
        ], Response::HTTP_OK, []);
        return $this->handleView($view);
    }

    /**
     * @Route("/user/{id}", name="user_update",methods={"PUT"})
     * @throws \Exception
     */
    public function update($id, Request $request): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new ResourceNotFoundException("User not found");
        }
        $form = $this->createForm(UserType::class, $user);
        $body = json_decode($request->getContent(), true);

        $form->submit($body);
        if (!$form->isSubmitted() || !$form->isValid()) {
            $view = $this->view(['success' => false, 'data' => [
                'message' => 'Error on save user',
                'data' => $form]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
        $userUpdated = $this->userFactory->BuildUser($body);
        if ($userUpdated->getRoles()) $user->setRoles($userUpdated->getRoles());
        $user->setEmail($userUpdated->getEmail());
        $user->setPassword($userUpdated->getPassword());
        $this->userRepository->store($user);
        $view = $this->view(
            [
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ], Response::HTTP_OK, []);
        return $this->handleView($view);
    }

    /**
     * @Route("/user/{id}", name="user_delete",methods={"DELETE"})
     */
    public function delete($id)
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new ResourceNotFoundException("User not found");
        }
        $this->userRepository->delete($user);
        $view = $this->view(['success' => true, 'message' => 'User removed successfully'], Response::HTTP_OK, []);
        return $this->handleView($view);
    }
}
