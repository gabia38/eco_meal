<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\User;
use App\Form\BusinessRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class BusinessRegistrationController extends AbstractController
{
    #[Route('/businessRegister', name: 'app_business_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $user = new User();
            $business = new Business();
            $user->setBusiness($business);

            $form = $this->createForm(BusinessRegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var string $plainPassword */
                $plainPassword = $form->get('plainPassword')->getData();

                // encode the plain password
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                $user->setRoles(['ROLE_BUSINESS']);
                $entityManager->persist($user);
                $entityManager->flush();

                // do anything else you need here, like send an email

                return $this->redirectToRoute('app_business');
            }

            return $this->render('registration/businessRegister.html.twig', [
                'registrationForm' => $form,
            ]);
        }

        return $this->redirectToRoute('app_login');
    }
}
