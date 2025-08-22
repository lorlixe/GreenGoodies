<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Doctrine\DBAL\Types\Types;

class ProductsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        for ($i = 1; $i <= 20; $i++) {
            $p = new Product();
            $p->setName($faker->unique()->words(3, true))
                ->setShortDescription($faker->sentence(12))
                ->setLongDescription($faker->paragraphs(3, true))
                ->setPrice($faker->randomFloat(2, 1, 150)) // 1.00 à 150.00
                ->setImage('product_' . $i . '.jpg'); // ou une URL si tu préfères

            $manager->persist($p);
            $this->addReference('product_' . $i, $p);
        }

        $manager->flush();
    }
}
