<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderProductsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $ordersCount   = 30; // adapter si tes OrdersFixtures en créent un autre nombre
        $productsCount = 20; // adapter si tes ProductsFixtures en créent un autre nombre

        for ($o = 1; $o <= $ordersCount; $o++) {
            /** @var Order $order */
            $order = $this->getReference('order_' . $o, Order::class);

            $linesCount = random_int(1, 4);
            $sum = 0.0;

            for ($i = 1; $i <= $linesCount; $i++) {
                /** @var Product $product */
                $product = $this->getReference('product_' . random_int(1, $productsCount), Product::class);

                $qty = random_int(1, 4);
                $unit = (float) $product->getPrice();

                $line = new OrderProduct();
                $line->setOrderReference($order);
                $line->setProductId($product);
                $line->setQuantity($qty);
                $line->setUnitPrice($unit);

                $sum += $qty * $unit;

                $manager->persist($line);
                // Optionnel: $this->addReference("order_{$o}_line_{$i}", $line);
            }

            // Met à jour le montant total de la commande selon les lignes
            $order->setTotalAmount(round($sum, 2));
            $manager->persist($order);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrdersFixtures::class,   // fournit order_1..order_N
            ProductsFixtures::class, // fournit product_1..product_N
        ];
    }
}
