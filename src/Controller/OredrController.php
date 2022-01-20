<?php

namespace App\Controller;
use App\Entity\Order;
use App\Entity\Dish;
use App\Repository\OrderRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OredrController extends AbstractController
{
    /**
     * @Route("/order", name="order")
     */
    public function index(OrderRepository $orderRepository): Response
    {
        $order= $orderRepository->findBy(
            ['meal' => 'meal1']
        );

        return $this->render('oredr/index.html.twig', [
            'orders'=>$order
        ]);
    }

    /**
     * @Route("/the_order/{id}", name="the_order")
     */
    public function ordre(Dish $dish , EntityManagerInterface $entityManager){
        $order= new Order();
        $order->setMeal("meal1");
        $order->setName($dish->getName());
        $order->SetOnumber($dish->getId());
        $order->setPrice($dish->getPrice());
        $order->setStatus("offen");

        $entityManager->persist($order);
        $entityManager->flush();
        
        $this->addFlash('order', $order->getName(). 'was added to the order');

        return $this->redirect($this->generateUrl('menu'));


    }
     /**
     * @Route("/status/{id},{status}", name="status")
     */
    public function status($id , $status , EntityManagerInterface $entityManager ){

        $order = $entityManager->getRepository(Order::class)->find($id);

        $order->setStatus($status);
        $entityManager->flush();
        return $this->redirect(($this->generateUrl('order')));
    }
    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete($id , OrderRepository $br , EntityManagerInterface $entityManager)
    {
        $order = $br->find($id);
        $entityManager->remove($order);
        $entityManager->flush();

        return $this->redirect($this->generateUrl('order'));
    }
}
