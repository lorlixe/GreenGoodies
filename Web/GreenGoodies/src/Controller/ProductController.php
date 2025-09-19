<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }



    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product, CartRepository $cartRepository): Response
    {

        $cartItem = $cartRepository->findOneBy(['product_id' => $product]);

        $quantity = $cartItem?->getQuantity() ?? 0;

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'quantity' => $quantity,

        ]);
    }
}
