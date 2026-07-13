<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\BusinessType;
use App\Form\BusinessFormType;
use App\Repository\BusinessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BusinessController extends AbstractController
{
    #[Route('/business', name: 'app_business')]
    public function index(BusinessRepository $businessRepository): Response
    {
        $businesses = $businessRepository->findAll();
        return $this->render('business/index.html.twig', [
            'controller_name' => 'BusinessController',
            'businesses' => $businesses,
        ]);

    }

    #[Route('/business/{id}', name: 'app_business_view')]
    public function view(int $id, BusinessRepository $businessRepository): Response
    {
        $business = $businessRepository->find($id);

        if ($this->getUser()->getBusiness() !== null && $this->getUser()->getBusiness() !== $business) {
            return $this->render('business/view.html.twig', [
                'business' => $this->getUser()->getBusiness(),
            ]);
        }
        return $this->render('business/view.html.twig', [
            'business' => $business,
        ]);


    }

    #[Route('/new/business', name: 'app_business_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $business = new Business();

            $form = $this->createForm(BusinessFormType::class, $business);
            $form = $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($business);
                $entityManager->flush();

                return $this->redirectToRoute('app_business');
            }

            return $this->render('business/new.html.twig', [
                'form' => $form,
            ]);
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/business/edit/{id}', name: 'app_business_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Business $business, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BusinessFormType::class, $business);
        $form = $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_business');
        }

        return $this->render('business/edit.html.twig', [
            'form' => $form,
        ]);

        return $this->redirectToRoute('app_login');
    }

    #[Route('/business/delete/{id}', name: 'app_business_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, int $id, BusinessRepository $businessRepository, EntityManagerInterface $entityManager): Response
    {
        $business = $businessRepository->find($id);
        $entityManager->remove($business);
        $entityManager->flush();

        return $this->redirectToRoute('app_business');
    }
}
