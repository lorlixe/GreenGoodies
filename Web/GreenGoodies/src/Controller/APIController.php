<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class APIController extends AbstractController
{
    #[Route('/api/products', name: 'app_api', priority: 3)]
    public function index(ProductRepository $productRepo): JsonResponse
    {
        return $this->json($productRepo->findAll(), 200, [], ["groups" => "product"]);
    }



    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $users,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $jwt
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        // la spec parle de "username" - on accepte username OU email
        $identifier = (string)($data['username'] ?? $data['email'] ?? '');
        $password   = (string)($data['password'] ?? '');

        if ($identifier === '' || $password === '') {
            return $this->json(['error' => 'username et password requis.'], 400);
        }

        $user = $users->findOneBy(['email' => $identifier]);
        if (!$user || !$hasher->isPasswordValid($user, $password)) {
            // Identifiants incorrects → 401
            return $this->json(['error' => 'Identifiants invalides.'], 401);
        }

        if (!$user->isApiActive()) {
            // Accès API non activé → 403
            return $this->json(['error' => 'Accès API non activé.'], 403);
        }

        // OK → 200 + token
        $token = $jwt->create($user);

        return $this->json(['token' => $token], 200);
    }
}
