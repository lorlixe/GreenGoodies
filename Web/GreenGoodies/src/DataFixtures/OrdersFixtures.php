<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory as FakerFactory;

class OrdersFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        $usersCount = 20; // correspond à user_1..user_20 créés dans UsersFixtures

        for ($i = 1; $i <= 30; $i++) {
            $order = new Order();

            /** @var User $user */
            $user = $this->getReference('user_' . random_int(1, $usersCount), User::class);
            $order->setUserId($user);

            // Date de validation dans les 3 derniers mois
            $order->setValidationDate($faker->dateTimeBetween('-3 months', 'now'));

            // Montant total réaliste
            $order->setTotalAmount($faker->randomFloat(2, 10, 300));

            $manager->persist($order);

            // Référence utile si tu veux créer des OrderProduct ensuite
            $this->addReference('order_' . $i, $order);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class, // s'assure que les users existent avant
        ];
    }
}
