<?php

namespace App\Command;

use App\Entity\Product;
use App\Service\ImportDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function Zenstruck\Foundry\instantiate;


#[AsCommand(
	name: 'app:update-product-list',
	description: 'Use product.csv file to update database.',
)]
class ImportFileCommand extends Command {
	private ImportDataService $service;
	private EntityManagerInterface $entityManager;

	public function __construct( ImportDataService $service, EntityManagerInterface $entityManager ) {
		parent::__construct();
		$this->service       = $service;
		$this->entityManager = $entityManager;
	}

	protected function configure(): void {
		$this
			->setDescription( 'Update product records' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {

		$existingCount = 0;
		$newCount      = 0;
		$products      = $this->service->getCsvAsArray();

		$productRepo = $this->entityManager->getRepository( Product::class );

		foreach ( $products as $product ) {
			$product         = $this->service->explodeProduct( $product );
			$existingProduct = $productRepo->findOneBy( [ 'productIndex' => $product['index'] ] );


			if ( $existingProduct ) {
				$this->service->logDuplicatedProduct( $existingProduct );
				$existingCount ++;
			} else {
				$newProduct = $this->service->createNewProduct( $product );
				$newCount ++;
				$this->entityManager->persist( $newProduct );
			}
		}

		$this->entityManager->flush();


		$io = new SymfonyStyle( $input, $output );

		$io->success( "Database is updated. There was $existingCount duplicates and $newCount new products added" );
		$this->service->deleteFile();

		return Command::SUCCESS;
	}
}
