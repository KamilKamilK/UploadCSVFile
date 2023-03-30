<?php

namespace App\Controller;

use App\Service\ImportDataService;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportDataController extends AbstractController {
	private ImportDataService $service;

	public function __construct( ImportDataService $service ) {

		$this->service = $service;
	}

	#[Route( '/{page<\d+>}', name: 'app_import_data' )]
	public function saveFile( Request $request, int $page = 1 ): Response {
		if ( isset( $_POST["Import"] ) ) {
			if ( $_FILES["file"]["size"] > 0 ) {
				$destination = $this->getParameter( 'kernel.project_dir' ) . '/public/uploads';
				$this->service->saveFile( $request, $destination );
			}
		}

		$products   = $this->service->getAllProductsQuery();
		$pagerfanta = $this->service->setPager( $products, $page );

		return $this->render( 'importData/import.html.twig', [
			'products' => $pagerfanta
		] );
	}
}
