<?php
// src/Controller/HomeController.php
namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $products): Response
    {
        // on affiche 8 produits max 
        $best = $products->findBy([], null, 9);

        return $this->render('home/index.html.twig', [
            'products' => $best,
        ]);
    }
}
