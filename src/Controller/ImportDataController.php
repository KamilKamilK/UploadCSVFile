<?php

namespace App\Controller;

use App\Service\ImportDataService;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
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

	#[Route( '/', name: 'app_security', methods: 'POST' )]
	public function insertPassword(): Response {
		if ( isset( $_GET['password'] ) ) {
			$password      = md5( $_GET['password'] );
			$codedPassword = '9df3b01c60df20d13843841ff0d4482c';
			if ( $password == $codedPassword ) {
				echo "&rarr; I'll be logged in. Wait 2 seconds .";
				session_start();
				$_SESSION["session"] = "LoginSession";
				header( "Refresh: 2; import/1" );
			}
			if ( $password !== $codedPassword ) {
				echo '&rarr; Password is wrong!! Try again.';
			}
		}

		return $this->render( 'security.html.twig' );
	}

	#[Route( '/import/{page<\d+>}', name: 'app_import_data' )]
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
