<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\RegistrationFormType;
use App\Form\NewPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Controller\ApiGeoController;

use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;


/**
 * @Route("/account")
*/
class AccountController extends AbstractController
{
    /**
     * @Route("/", name="app_account_index", methods="GET")
     */
    public function show(SessionInterface $session)
    {
        if (!$this->getUser()) 
            return $this->redirectToRoute('app_info_home');    
        elseif(!$this->getUser()->getIsVerified() && !$session->get('emailSent'))
            $this->addFlash('error', '<span class="h5">Votre compte n\'est pas verifié.<span> <a href=".\email" class="h5 text-light font-weight-bold">Recevoir un email pour le verifier</a>');
        

        return $this->render('account/show.html.twig', [      
        ]);
    }

    /**
     * @Route("/edit", name="app_account_edit", methods={"GET", "PATCH"})
     */
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder) : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if($this->isCsrfTokenValid('account_edit', $request->query->get('token')))
        {
            $user = $this->getUser();
            $orginalMail = $user->getEmail();

            $userForm = $this->createForm(RegistrationFormType::class, $user, [
                'method' => 'PATCH'
            ]);

            $userForm->handleRequest($request);
            
            $api = new ApiGeoController;

            if($userForm->isSubmitted() && $userForm->isValid() && $api->isPostCodeExist($user->getPostCode()) && $this->verifyUserSecurtiteSociale($user->getSecuriteSociale()))
            {
                if($userForm['email']->getData() != $orginalMail)
                {
                    $user->setIsVerified(false);
                }

                $em->flush();

                $this->addFlash('success', 'Votre compte a été mis à jour avec succès');

                return $this->redirectToRoute('app_account_index');
            }
            elseif ($user->getPostCode() && !$api->isPostCodeExist($user->getPostCode()))
                $this->addFlash('error', 'Le code postal renseigné n\'existe pas');
            elseif ($user->getSecuriteSociale() && !$this->verifyUserSecurtiteSociale($user->getSecuriteSociale()))
                $this->addFlash('error', 'Le format du numéro de sécurtié sociale est invalide');
        }    
        else
            return $this->redirectToRoute('app_info_home');


        return $this->render('account/edit.html.twig', [
            'registrationForm' => $userForm->createView()
        ]);
    }

    /**
     * @Route("/change-password", name="app_account_NewPassword", methods={"GET", "PATCH"})
     */
    public function NewPassword(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder) : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($this->isCsrfTokenValid('account_NewPassword', $request->query->get('token'))) {
            $user = $this->getUser();

            $form = $this->createForm(NewPasswordFormType::class, null, [
                'current_password_is_required' => true,
                'method' => 'PATCH'
            ]);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                $user->setPassword(
                    $passwordEncoder->encodePassword($user, $form['plainPassword']->getData())
                );

                $em->flush();

                $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès');

                return $this->redirectToRoute('app_account_index');
            }
        }
        else
            return $this->redirectToRoute('app_info_home');

        return $this->render('account/newPassword.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/email", name="app_account_email", methods={"GET"})
     */
    public function emailValidation(EmailVerifier $emailVerifier, SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if(!$this->getUser()->getIsVerified() && !$session->get('emailSent'))
        {
             $emailVerifier->sendEmailConfirmation('app_verify_email', $this->getUser(),
            (new TemplatedEmail())
                ->from(new Address($this->getParameter('app.mail_from_address'), $this->getParameter('app.mail_from_name')))
                ->to($this->getUser()->getEmail())
                ->subject('TPLV - Veuillez confirmez votre email - TousPourLesVaccins')
                ->htmlTemplate('mails/confirmation.html.twig')
            );

            $session->set('emailSent', true);
            $this->addFlash('success', 'Un email vous a été envoyé !');
        }
        else{
            $this->addFlash('info', 'Un email vous déjà a été envoyé.');
        }   
       
        return $this->redirectToRoute('app_account_index');
    }


    /**
     * @Route("/delete", name="app_account_delete", methods={"DELETE"})
     */
    public function delete(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->isCsrfTokenValid('delete', $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($this->getUser());
            $entityManager->flush();
    
            $this->get('security.token_storage')->setToken(null);
        }
        
        return $this->redirectToRoute('app_info_home');
    }

    public function verifyUserSecurtiteSociale($securiteSociale)
    {
        if(substr($securiteSociale, 0, 1) == ( 1 || 2 )) { // gender
            if((substr($securiteSociale, 3, 2) >=  1 && substr($securiteSociale, 3, 2) <= 12) || // month
                (substr($securiteSociale, 3, 2) >= 20  && substr($securiteSociale, 3, 2) <= 30) || // incomplete civil deed range
                substr($securiteSociale, 3, 2) == 50) { // incomplete civil deed
                    $api = new ApiGeoController;
                    if(($api->isInseeCodeExist(substr($securiteSociale, 5, 5))) || // inseeCode
                        (substr($securiteSociale, 5, 2) >= 91 && substr($securiteSociale, 5, 2) <= 96) || // old colony or protectorate
                        (substr($securiteSociale, 5, 2) == 99)) { // foreigner
                            if(substr($securiteSociale, 5, 2) == '2A') { // Corse-du-Sud to int
                                $securiteSociale = str_replace('2A', 19, $securiteSociale);
                            }
                            elseif (substr($securiteSociale, 5, 2) == '2B' ) { // Haute-Corse to int
                                $securiteSociale = str_replace('2B', 18, $securiteSociale);
                            }
                            if(97 - fmod(substr($securiteSociale, 0, 13), 97) == substr($securiteSociale, 13, 2)){ // security key
                                return true;
                            }
                    }
            }
        }
        return false;
    }
}
