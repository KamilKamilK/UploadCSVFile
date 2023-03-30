<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportFileTest extends WebTestCase
{
	public function testImportFile(  )
	{
		$client = static::createClient();

		$uploadedFile = new UploadedFile(
			__DIR__.'/../fixtures/Produkty.csv',
			'Produkty.csv'
		);

		$client->request('POST', '/', [], [
			'file' => $uploadedFile
		]);

		$this->assertResponseIsSuccessful();
	}
}