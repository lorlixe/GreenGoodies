<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class APIController extends AbstractController
{
    #[Route('/api', name: 'app_api', priority: 3)]
    public function index(ProductRepository $productRepo): JsonResponse
    {
        return $this->json($productRepo->findAll(), 200, [], ["groups" => "product"]);
    }
}
