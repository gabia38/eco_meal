<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\Package;
use App\Form\PackageFormType;
use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PackageController extends AbstractController
{
    #[Route('/package', name: 'app_package')]
    public function index(PackageRepository $packageRepository): Response
    {
        $packages = $packageRepository->findAll();
        return $this->render('package/all.html.twig', [
            'controller_name' => 'PackageController',
            'packages' => $packages,
        ]);
    }

    #[Route('/business/{id}/packages', name: 'app_business_packages', methods: ['GET'])]
    public function showBusinessPackages(PackageRepository $packageRepository, Business $business): Response
    {
        $packages = $packageRepository->findBy(['business' => $business]);
        return $this->render('package/index.html.twig', [
            'controller_name' => 'PackageController',
            'packages' => $packages,
            'business' => $business,
        ]);
    }

    #[Route('/package/{id}', name: 'app_package_view')]
    public function view(int $id, PackageRepository $packageRepository): Response
    {
        $package = $packageRepository->find($id);
        return $this->render('package/view.html.twig', [
            'package' => $package,
        ]);
    }

    #[Route('business/{id}/new/package', name: 'app_package_new', methods: ['GET', 'POST'])]
    public function new(Business $business, Request $request, EntityManagerInterface $entityManager): Response
    {
        $package = new Package();

        $form = $this->createForm(PackageFormType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $package->setBusiness($business);
            $entityManager->persist($package);
            $entityManager->flush();

            return $this->redirectToRoute('app_business_packages',[
                "id" => $business->getId(),
            ]);
        }

        return $this->render('package/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('business/{business_id}/package/edit/{id}', name: 'app_package_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(expr: 'repository.find(business_id)')] Business $business,
                         Package $package, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PackageFormType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($package);
            $entityManager->flush();

            return $this->redirectToRoute('app_business_packages',[
                "id" => $business->getId(),
            ]);
        }

        return $this->render('package/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('package/delete/{id}', name: 'app_package_delete', methods: ['GET','POST'])]
    public function delete(Request $request, int $id, PackageRepository $packageRepository, EntityManagerInterface $entityManager): Response
    {
        $package = $packageRepository->find($id);

        $id = $package->getBusiness()->getId();

        $entityManager->remove($package);
        $entityManager->flush();

        return $this->redirectToRoute('app_business_packages',[
            'id' => $id,
        ]);
    }
}
