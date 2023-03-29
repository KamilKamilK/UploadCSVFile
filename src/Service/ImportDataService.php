<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class ImportDataService {
	private string $projectDir;
	private LoggerInterface $logger;
	private ProductRepository $productRepository;

	public function __construct( string $projectDir, LoggerInterface $logger, ProductRepository $productRepository ) {
		$this->projectDir        = $projectDir;
		$this->logger            = $logger;
		$this->productRepository = $productRepository;
	}

	public function saveFile( Request $request, $destination ): void {
		/** @var UploadedFile $uploadedFile */
		$uploadedFile = $request->files->get( 'file' );

		$originalFilename = pathinfo( $uploadedFile->getClientOriginalName(), PATHINFO_FILENAME );
		$newFilename      = $originalFilename . '.' . $uploadedFile->guessClientExtension();
		$uploadedFile->move( $destination, $newFilename );
	}

	public function getCsvAsArray() {
		$inputFile = $this->projectDir . '/public/uploads/Produkty.csv';
		$decoder   = new Serializer( [ new ObjectNormalizer() ], [ new CsvEncoder() ] );

		return $decoder->decode( file_get_contents( $inputFile ), 'csv' );
	}

	public function explodeProduct( $product ): array {
		$productArr = explode( ';', array_values( $product )[0] );

		return [ 'name' => $productArr[0], 'index' => $productArr[1] ];

	}

	public function createNewProduct( $product ): Product {

		return ( new Product() )
			->setName( $product['name'] )
			->setProductIndex( (int)$product['index'] )
			->setCreatedAt( new \DateTime( '@' . strtotime( 'now' ) ) );
	}

	public function logDuplicatedProduct( $product ): void {
		$this->logger->info( sprintf( 'Product name %s with index %s is duplicated',
			$product['name'],
			$product['index'] ) );
	}

	public function deleteFile(): void {
		$inputFile = $this->projectDir . '/public/uploads/Produkty.csv';
		unset( $inputFile );
	}

	public function getAllProductsQuery(): QueryBuilder {
		return $this->productRepository->getAllProductsQueryBuilder();
	}

	public function getAllProducts(  ): array {
		return $this->productRepository->findAll();
	}

//	public function setPager( $products ): Pagerfanta {
//		$pagerfanta = new Pagerfanta( new QueryAdapter( $products ) );
//		$pagerfanta->setMaxPerPage( 10 );
//		$pagerfanta->setCurrentPage( 1 );
//
//		return $pagerfanta;
//	}
}