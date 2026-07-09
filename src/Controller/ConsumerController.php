<?php

namespace App\Controller;

use App\Entity\Consumer;
use App\Form\ConsumerFormType;
use App\Repository\ConsumerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConsumerController extends AbstractController
{
    #[Route('/consumer', name: 'app_consumer')]
    public function index(ConsumerRepository $consumerRepository): Response
    {
        $consumers = $consumerRepository->findAll();
        return $this->render('consumer/index.html.twig', [
            'controller_name' => 'ConsumerController',
            'consumers' => $consumers,
        ]);
    }

    #[Route('/consumer/{id}', name: 'app_consumer_view')]
    public function view(int $id, ConsumerRepository $consumerRepository, Security $security): Response
    {
        $user = $security->getUser();

        $consumer = $consumerRepository->find($id);
        if (in_array('ROLE_ADMIN', $user->getRoles()) || $user->getConsumer() === $consumer) {
            return $this->render('consumer/view.html.twig', [
                'consumer' => $consumer,
            ]);
        }

        return $this->render('consumer/view.html.twig', [
            'consmer' => $user->getConsumer(),
        ]);
    }

    #[Route('new/consumer', name: 'app_consumer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $consumer = new Consumer();

        $form = $this->createForm(ConsumerFormType::class, $consumer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($consumer);
            $entityManager->flush();

            return $this->redirectToRoute('app_consumer');
        }

        return $this->render('consumer/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/consumer/edit/{id}', name: 'app_consumer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Consumer $consumer, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user->getConsumer() === $consumer) {
            $form = $this->createForm(ConsumerFormType::class, $consumer);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                return $this->redirectToRoute('app_consumer_view', [
                    'id' => $consumer->getId(),
                ]);
            }

            return $this->render('consumer/edit.html.twig', [
                'form' => $form,
            ]);
        }

        return $this->redirectToRoute('app_consumer_view',[
            'id' => $user->getConsumer()->getId(),
        ]);
    }

    #[Route('/consumer/delete/{id}', name: 'app_consumer_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, int $id, ConsumerRepository $consumerRepository, EntityManagerInterface $entityManager): Response
    {
        $consumer = $consumerRepository->find($id);
        $entityManager->remove($consumer);
        $entityManager->flush();

        return $this->redirectToRoute('app_consumer');
    }
}

