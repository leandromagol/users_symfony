<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/api/v1")
 */
class ResetPasswordController extends AbstractFOSRestController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("/reset_password", name="app_forgot_password_request",methods={"POST"})
     */
    public function request(Request $request, MailerInterface $mailer)
    {

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        $form->submit($request->toArray());

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer
            );
        }
        $view = $this->view(['success' => false, 'message' => 'Error on request password reset'], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     *
     * @Route("/check-email", name="app_check_email")
     */
    public function checkEmail(): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->json([
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/reset/{token}", name="app_reset_password",methods={"POST"})
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = null): Response
    {

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json(['success' => false,
                'message' => 'error on reset password']);
        }

        // The token is valid; allow the user to change their password.

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        $body = json_decode($request->getContent(), true);
        $form->submit($body);
        if ($request->get('new_password') != $request->get('repeat_new_password')) {
            ;
            return $this->json(
                ['success' => false,
                    'data' => [
                        'message' => 'Password and password confirmation not match'
                    ]]
                , Response::HTTP_BAD_REQUEST);
        }
        if ($form->isSubmitted() && $form->isValid()) {

            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode the plain password, and set it.
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $form->get('new_password')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->getDoctrine()->getManager()->flush();

            return $this->json(['success' => true, 'message' => 'password changed successful']);
        }
        $view = $this->view(['success' => false, 'data' => [
            'message' => 'Error on reset password',
            'data' => $form]
        ], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->checkEmail();
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     'There was a problem handling your password reset request - %s',
            //     $e->getReason()
            // ));

            return $this->checkEmail();
        }

        $email = (new TemplatedEmail())
            ->from(new Address('0b75b45c48-8c20e6@inbox.mailtrap.io', 'mailtrap'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->json(
            [
                'success' => true,
                'message' => 'Password Reset email sent successfully'
            ]
        );
    }
}
