<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/orders', name: 'orders_')]
final class OrderController extends AbstractController
{

    /**
     * Valide définitivement la commande :
     * - crée Order
     * - crée les OrderProduct depuis le panier
     * - vide le panier
     */
    #[Route('/confirm', name: 'confirm', methods: ['POST'])]
    public function confirm(
        Request $request,
        CartRepository $cartRepo,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $user = $this->getUser();
        $rows = $cartRepo->findBy(['user_id' => $user], ['id' => 'ASC']);
        if (!$rows) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        // Crée la commande
        $order = new Order();
        $order->setUserId($user);
        $order->setValidationDate(new \DateTime());
        $orderTotal = 0.0;

        $em->persist($order);

        // Pour chaque ligne de panier, on crée une ligne de commande
        foreach ($rows as $row) {
            $product = $row->getProductId();
            if (!$product) {
                continue;
            }

            $qty  = max(1, (int) $row->getQuantity());
            $unit = (float) ($product->getPrice() ?? 0);

            $op = new OrderProduct();
            $op->setOrderReference($order);
            $op->setProductId($product);
            $op->setQuantity($qty);
            $op->setUnitPrice($unit);

            $orderTotal += ($qty * $unit);

            $em->persist($op);

            // on supprime la ligne de panier
            $em->remove($row);
        }

        $order->setTotalAmount($orderTotal);

        // On sauvegarde tout d’un coup
        $em->flush();

        $this->addFlash('success', 'Commande validée, merci !');

        // Redirige vers une page "commande" ou l’historique
        return $this->redirectToRoute('orders_show', ['id' => $order->getId()]);
    }

    /**
     * toutes les commandes).
     */
    #[Route('/', name: 'show', methods: ['GET'])]
    public function show(OrderRepository $orderRepo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');


        // Utilisateur courant
        $user = $this->getUser();

        // Récupère toutes les commandes de cet utilisateur
        // triées de la plus récente à la plus ancienne.

        $rows = $orderRepo->findBy(['user_id' => $user], ['validation_date' => 'DESC', 'id' => 'DESC']);

        $orders = array_map(fn($o) => [
            'id'    => $o->getId(),
            'date'  => $o->getValidationDate(),
            'total' => $o->getTotalAmount(),
        ], $rows);

        return $this->render('account/index.html.twig', [
            'orders'     => $orders,
        ]);
    }
}
