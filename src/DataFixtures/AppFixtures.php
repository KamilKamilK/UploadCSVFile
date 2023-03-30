<?php

namespace App\DataFixtures;

use App\Factory\CategoryFactory;
use App\Factory\ProductFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture {
	public function load( ObjectManager $manager ): void {

		CategoryFactory::new()->createMany( 5 );
		ProductFactory::new()->createMany( 20, function () {
			return [
				'category' => CategoryFactory::random()
			];
		});

		$manager->flush();
	}
}
