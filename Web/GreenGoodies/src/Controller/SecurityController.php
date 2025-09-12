<?php

namespace App\Controller;

use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home'); // déjà authentifié → home
        }
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/delete', name: 'app_user_delete')]
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        CartRepository $cartRepo,
        OrderRepository $orderRepo,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->getUser();
        if (!$this->isCsrfTokenValid('account_delete', (string) $request->request->get('_token'))) {
        }
        // 1) Supprimer les lignes de panier
        foreach ($cartRepo->findBy(['user_id' => $user]) as $row) {
            $em->remove($row);
        }
        // 2) Supprimer les commandes
        foreach ($orderRepo->findBy(['user_id' => $user]) as $order) {
            foreach ($order->getOrderProducts() as $op) {
                $em->remove($op);
            }
            $em->remove($order);
        }

        // 3) Supprimer l'utilisateur
        $em->remove($user);
        $em->flush();

        // --- Déconnexion propre ---
        $tokenStorage->setToken(null); // enlève l'authentification
        $session->invalidate();        // détruit la session

        $this->addFlash('success', 'Votre compte a bien été supprimé.');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/activeapi/enable', name: 'api_enable', methods: ['POST'])]
    public function enableApi(EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($user->isApiActive()) {
            $this->addFlash('info', 'Votre accès API est déjà actif.');
        }

        // Active le flag
        $user->setApiActive(true);

        $em->flush();

        $this->addFlash('success', 'Accès API activé.');
        return $this->redirectToRoute('orders_show');
    }
    #[Route('/activeapi/disable', name: 'api_disable', methods: ['POST'])]
    public function disableApi(EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Active le flag
        $user->setApiActive(false);

        $em->flush();

        $this->addFlash('success', 'Accès API activé.');
        return $this->redirectToRoute('orders_show');
    }
}
