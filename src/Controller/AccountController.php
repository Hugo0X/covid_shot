<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\RegistrationFormType;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;


/**
 * @Route("/account")
*/
class AccountController extends AbstractController
{
    /**
     * @Route("/", name="app_account", methods="GET")
     */
    public function show()
    {
        return $this->render('account/show.html.twig', [      
        ]);
    }

    /**
     * @Route("/edit", name="app_account_edit", methods={"GET", "PATCH"})
     */
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder) : Response
    {
        if($this->isCsrfTokenValid('account_edit', $request->query->get('token'))) {
            $user = $this->getUser();
            $orginalMail = $user->getEmail();

            $userForm = $this->createForm(RegistrationFormType::class, $user, [
                'method' => 'PATCH'
            ]);

            $userForm->handleRequest($request);

            if($userForm->isSubmitted() && $userForm->isValid())
            {
                if($userForm['email']->getData() != $orginalMail)
                {
                    $user->setIsVerified(false);
                }

                $em->flush();

                $this->addFlash('success', 'Votre compte a été mis à jour avec succès');

                return $this->redirectToRoute('app_account');
            }
        }
        else
            return $this->redirectToRoute('app_info_home');


        return $this->render('account/edit.html.twig', [
            'userForm' => $userForm->createView()
        ]);
    }

    /**
     * @Route("/change-password", name="app_account_changePassword", methods={"GET", "PATCH"})
     */
    public function changePassword(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder) : Response
    {
        if($this->isCsrfTokenValid('account_changePassword', $request->query->get('token'))) {
            $user = $this->getUser();

            $form = $this->createForm(ChangePasswordFormType::class, null, [
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

                return $this->redirectToRoute('app_account');
            }
        }
        else
            return $this->redirectToRoute('app_info_home');

        return $this->render('account/changePassword.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete", name="app_account_delete", methods={"DELETE"})
     */
    public function delete(Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete', $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($this->getUser());
            $entityManager->flush();
    
            $this->get('security.token_storage')->setToken(null);
        }
        
        return $this->redirectToRoute('app_info_home');
    }
}
