<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Controller\ApiGeoController;
use App\Controller\AccountController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;
    private $session;

    public function __construct(EmailVerifier $emailVerifier, SessionInterface $session)
    {
        $this->emailVerifier = $emailVerifier;
        $this->session = $session;
    }

    /**
     * @Route("/register", name="app_register", methods={"GET", "POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {   
        if ($this->getUser()) {
            return $this->redirectToRoute('app_info_home');
        }

        $user = new User;
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'registration' => true,
        ]);
        $form->handleRequest($request);

        $api = new ApiGeoController;
        $account = new AccountController;

        if ($form->isSubmitted() && $form->isValid() && $api->isPostCodeExist($user->getPostCode()) && $account->verifyUserSecurtiteSociale($user->getSecuriteSociale())) {
            // encode the plain password
            if ($request->get('nextStep') == 'map') {
                $this->session->set('nextStep', 'app_info_map');
            }
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address($this->getParameter('app.mail_from_address'), $this->getParameter('app.mail_from_name')))
                    ->to($user->getEmail())
                    ->subject('TPLV - Veuillez confirmez votre email - TousPourLesVaccins')
                    ->htmlTemplate('mails/confirmation.html.twig')
            );
            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main', // firewall name in security.yaml
                $this->redirectToRoute('app_info_map')
            );
        }
        elseif ($user->getPostCode() && !$api->isPostCodeExist($user->getPostCode()))
            $this->addFlash('error', 'Le code postal renseign?? n\'existe pas');
        elseif ($user->getSecuriteSociale() && !$account->verifyUserSecurtiteSociale($user->getSecuriteSociale()))
            $this->addFlash('error', 'Le format du num??ro de s??curti?? sociale est invalide');

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $exception->getReason());

            return $this->redirectToRoute('app_info_home');
        }

        $this->addFlash('success', 'Votre adresse email a bien ??t?? v??rifi??e.');

        return $this->redirectToRoute('app_info_home');
    }
}
