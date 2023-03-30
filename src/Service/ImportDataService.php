<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;


class ImportDataService {
	private string $projectDir;
	private LoggerInterface $logger;
	private ProductRepository $productRepository;
	private EntityManagerInterface $entityManager;

	public function __construct( string $projectDir, LoggerInterface $logger, ProductRepository $productRepository, EntityManagerInterface $entityManager ) {
		$this->projectDir        = $projectDir;
		$this->logger            = $logger;
		$this->productRepository = $productRepository;
		$this->entityManager     = $entityManager;
	}

	public function saveFile( $uploadedFile, $destination ): void {
		$this->validateFileType( $uploadedFile );

		$originalFilename = pathinfo( $uploadedFile->getClientOriginalName(), PATHINFO_FILENAME );
		$newFilename      = $originalFilename . '.' . $uploadedFile->guessExtension();
		$uploadedFile->move( $destination, $newFilename );
	}

	public function mapProduct( $product ): array {
		return [ 'name' => $product[0], 'index' => $product[1], 'category' => $product[2] ];
	}

	public function createNewProduct( $product, Category $category ): Product {

		$newProduct = ( new Product() )
			->setName( $product['name'] )
			->setProductIndex( $product['index'] )
			->setCategory( $category )
			->setCreatedAt( new \DateTime() )
			->setUpdatedAt( new \DateTime() );

		$this->entityManager->persist( $newProduct );
		return $newProduct;
	}

	public function createNewCategory( $product ): Category {
		$newCategory = ( new Category() )
			->setName( $product['category'] );

		$this->entityManager->persist( $newCategory );
		$this->entityManager->flush();

		return $newCategory;
	}

	public function logDuplicatedProduct( Product $product ): void {
		$this->logger->info( sprintf( 'Product name %s with index %s is duplicated',
			$product->getName(),
			$product->getProductIndex()
		) );
	}

	public function deleteFile(): void {
		$inputFile  = $this->projectDir . '/public/uploads/Produkty.csv';
		$filesystem = new Filesystem();
		$filesystem->remove( $inputFile );
	}

	public function getAllProductsQuery(): QueryBuilder {
		return $this->productRepository->getAllProductsQueryBuilder();
	}

	public function setPager( $products, $page ): Pagerfanta {
		$pagerfanta = new Pagerfanta( new QueryAdapter( $products ) );
		$pagerfanta->setMaxPerPage( 10 );
		$pagerfanta->setCurrentPage( $page );

		return $pagerfanta;
	}

	public function validateFileType( $uploadedFile ): void {
		if ( $uploadedFile->getMimeType() !== 'text/csv' ) {
			throw new \Exception( 'File is not in CSV format' );
		}
	}

	public function parseCSV($csvParsingOptions): array {
		$ignoreFirstLine = $csvParsingOptions['ignoreFirstLine'];

		$finder = new Finder();
		$finder->files()
		       ->in($csvParsingOptions['finder_in'])
		       ->name($csvParsingOptions['finder_name'])
		;


		foreach ($finder as $file) { $csv = $file; }

		$rows = array();
		if (($handle = fopen($csv->getRealPath(), "r")) !== FALSE) {
			$i = 0;
			while (($data = fgetcsv($handle, null, ";")) !== FALSE) {
				$i++;
				if ($ignoreFirstLine && $i == 1) { continue; }
				$rows[] = $data;
			}
			fclose($handle);
		}

		return $rows;
	}
}