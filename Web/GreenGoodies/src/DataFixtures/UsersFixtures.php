<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Un admin de test
        $admin_1 = new User();
        $admin_1->setFirstName('Admin')
            ->setLastName('Test')
            ->setEmail('admin@example.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setCguAccepted(true);

        $admin_1->setPassword(
            $this->passwordHasher->hashPassword($admin_1, 'admin1234')
        );
        $manager->persist($admin_1);
        $this->addReference('user_admin', $admin_1);

        // Plusieurs utilisateurs
        for ($i = 1; $i <= 20; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->unique()->safeEmail())
                ->setRoles(['ROLE_USER'])
                ->setCguAccepted($faker->boolean(90));
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'password')
            );

            $manager->persist($user);
            $this->addReference('user_' . $i, $user);
        }

        $manager->flush();
    }
}
