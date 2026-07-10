<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderFormType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\Clock\now;

final class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll();
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
            'orders' => $orders,
        ]);
    }

    #[Route('/order/{id}', name: 'app_order_view')]
    public function view(int $id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);
        return $this->render('order/view.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('new/order', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();

        $order->setConsumer($this->getUser()->getConsumer());
        $order->setCreated_at(now());

        $form = $this->createForm(OrderFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('app_order');
        }

        return $this->render('order/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/order/edit/{id}', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getConsumer() !== $order->getConsumer()) {
            return $this->redirectToRoute('app_order');
        }

        $form = $this->createForm(OrderFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_order');
        }

        return $this->render('order/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/order/delete/{id}', name: 'app_order_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, int $id, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $thisConsumer = $this->getUser()->getConsumer();
        $thisBusiness = $this->getUser()->getBusiness();
        $order = $orderRepository->find($id);
        if (($thisConsumer !== null && $thisConsumer !== $order->getConsumer()) ||
            ($thisBusiness !== null && $thisBusiness !== $order->getBusiness())) {
            return $this->redirectToRoute('app_order');
        }


        $entityManager->remove($order);
        $entityManager->flush();

        return $this->redirectToRoute('app_order');
    }
}
