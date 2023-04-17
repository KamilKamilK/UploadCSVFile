<?php

namespace App\Controller;

use App\Service\ImportDataService;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportDataController extends AbstractController {
	private ImportDataService $service;

	public function __construct( ImportDataService $service ) {

		$this->service = $service;
	}

	#[Route( '/import/{page<\d+>}', name: 'app_import_data' )]
	#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
	public function importFile( Request $request, int $page = 1 ): Response {
		if ( isset( $_POST["Import"] ) ) {
			if ( $_FILES["file"]["size"] > 0 ) {
				/** @var UploadedFile $uploadedFile */
				$uploadedFile = $request->files->get( 'file' );
				$destination = $this->getParameter( 'kernel.project_dir' ) . '/public/uploads';

				$this->service->saveFile( $uploadedFile, $destination );
			}
		}

		$products   = $this->service->getAllProductsQuery();
		$pagerfanta = $this->service->setPager( $products, $page );

		return $this->render( 'importData/import.html.twig', [
			'products' => $pagerfanta
		] );
	}
}
