<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Factory\ProductFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture {
	public function load( ObjectManager $manager ): void {

		ProductFactory::new()->createMany(20);

		$manager->flush();
	}
}
