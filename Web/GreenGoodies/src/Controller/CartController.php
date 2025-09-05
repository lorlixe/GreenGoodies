<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart', name: 'app_cart_')]
final class CartController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CartRepository $cartRepo): Response
    {
        // Empêche l'accès si l'utilisateur n'est pas connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        // Utilisateur courant
        $user = $this->getUser();

        // Récupère toutes les lignes de panier de cet utilisateur
        // triées de la plus récente à la plus ancienne.
        $rows = $cartRepo->findBy(['user_id' => $user], ['id' => 'DESC']);

        $items = [];
        $subTotal = 0.0;

        foreach ($rows as $row) {
            $product = $row->getProductId();
            if (!$product) {
                // Si le produit lié a été supprimé, on ignore la ligne.
                continue;
            }

            // la quantité et calcule le total de ligne
            $qty   = max(0, (int) $row->getQuantity());
            $unit  = (float) ($product->getPrice() ?? 0);
            $line  = $unit * $qty;
            $subTotal += $line;

            // Données prêtes à afficher côté Twig (cart/index.html.twig)
            $items[] = [
                'id'         => $row->getId(),
                'product'    => $product,
                'quantity'   => $qty,
                'unit_price' => $unit,
                'line_total' => $line

            ];
        }

        return $this->render('cart/index.html.twig', [
            'items'     => $items,
            'sub_total' => $subTotal,
        ]);
    }

    /**
     * Ajoute un produit au panier. Si la ligne existe déjà pour (user, produit),
     * on incrémente simplement la quantité.
     *
     */
    #[Route('/add/{id}', name: 'add', methods: ['POST'])]
    public function add(
        Product $product,
        Request $request,
        CartRepository $cartRepo,
        EntityManagerInterface $em
    ): Response {
        // Seuls les utilisateurs connectés peuvent ajouter au panier
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $user = $this->getUser();

        // Récupère la quantité envoyée
        $qty = max(1, (int) $request->request->get('quantity', 1));

        // Cherche une ligne existante (même user + même produit)
        $line = $cartRepo->findOneBy(['user_id' => $user, 'product_id' => $product]);

        if ($line) {
            // Ligne déjà présente → on ajoute la quantité
            $line->setQuantity($line->getQuantity() + $qty);
        } else {
            // Nouvelle ligne de panier
            $line = (new Cart())
                ->setUserId($user)
                ->setProductId($product)
                ->setQuantity($qty);

            $em->persist($line);
        }

        // Sauvegarde en BDD
        $em->flush();

        $this->addFlash('success', 'Produit ajouté au panier.');
        return $this->redirectToRoute('app_cart_index'); // redirige vers la page panier
    }

    /**
     * Vide entièrement le panier de l'utilisateur connecté.
     * Également protégé par un token CSRF.
     */
    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clear(CartRepository $cartRepo, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');


        $user = $this->getUser();

        // Supprime toutes les lignes du panier pour cet utilisateur
        foreach ($cartRepo->findBy(['user_id' => $user]) as $row) {
            $em->remove($row);
        }

        $em->flush();

        $this->addFlash('success', 'Panier vidé.');
        return $this->redirectToRoute('app_cart_index'); // redirige vers la page panier
    }
}
