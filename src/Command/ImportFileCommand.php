<?php

namespace App\Command;


use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ImportDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(
	name: 'app:update-product-list',
	description: 'Use product.csv file to update database.',
)]
class ImportFileCommand extends Command {

	private array $csvParsingOptions = [
		'finder_in'       => 'public/uploads/',
		'finder_name'     => 'Produkty.csv',
		'ignoreFirstLine' => true
	];

	private ImportDataService $service;
	private EntityManagerInterface $entityManager;
	private ProductRepository $productRepository;
	private CategoryRepository $categoryRepository;

	public function __construct(
		ImportDataService $service,
		EntityManagerInterface $entityManager,
		ProductRepository $productRepository,
		CategoryRepository $categoryRepository
	) {
		parent::__construct();
		$this->service            = $service;
		$this->entityManager      = $entityManager;
		$this->productRepository  = $productRepository;
		$this->categoryRepository = $categoryRepository;
	}

	protected function configure(): void {
		$this
			->setDescription( 'Update product records' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {

		$existingCount = 0;
		$newCount      = 0;

		$csv = $this->service->parseCSV( $this->csvParsingOptions, );

		foreach ( $csv as $product ) {
			$product          = $this->service->mapProduct( $product );
			$existingProduct  = $this->productRepository->findOneBy( [ 'productIndex' => $product['index'] ] );
			$existingCategory = $this->categoryRepository->findOneBy( [ 'name' => $product['category'] ] );

			if ( $existingProduct !== null ) {
				$this->service->logDuplicatedProduct( $existingProduct );
				$existingCount ++;
			} else {
				if ( ! $existingCategory ) {
					$newCategory = $this->service->createNewCategory( $product );
				}
				$category = $newCategory ?? $existingCategory;
				$this->service->createNewProduct( $product, $category );
				$newCount ++;
			}
		}

		$this->entityManager->flush();

		$io = new SymfonyStyle( $input, $output );

		$io->success( "Database is updated. There was $existingCount duplicates and $newCount new products added" );
		$this->service->deleteFile();

		return Command::SUCCESS;
	}
}
