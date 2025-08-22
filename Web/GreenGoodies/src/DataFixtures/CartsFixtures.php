<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CartsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $usersCount    = 20; // users: user_1..user_20 (+ user_admin)
        $productsCount = 20; // products: product_1..product_20

        for ($i = 1; $i <= 40; $i++) {
            $cart = new Cart();

            /** @var User $user */
            $user = $this->getReference('user_' . random_int(1, $usersCount), User::class);
            /** @var Product $product */
            $product = $this->getReference('product_' . random_int(1, $productsCount), Product::class);

            $cart->setUserId($user);
            $cart->setProductId($product);
            $cart->setQuantity(random_int(1, 5));

            $manager->persist($cart);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            ProductsFixtures::class,
        ];
    }
}
