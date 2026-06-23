<?php

namespace Tests\Classes;

use Classes\CarSpecificationDataImporter;
use Classes\Exception\CarSpecificationException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CarSpecificationDataImporterTest extends TestCase {

	private $carSpecificationDataImporter;

	protected function setUp(): void {
		parent::setUp();
		$client                             = new Client();
		$this->carSpecificationDataImporter = new CarSpecificationDataImporter( $client );
	}

	public function testShouldThrowExceptionWhenNoVINorCONProvided(): void {
		$this->expectException( CarSpecificationException::class );
		$this->expectExceptionMessage( 'No VIN or CON provided' );
		$this->expectExceptionCode( 400 );

		$this->carSpecificationDataImporter->import( array() );
	}

	// public function testShouldThrowExceptionWhenNInvalidVINProvided(): void
	// {
	// $this->expectException(ClientException::class);
	// $this->expectExceptionMessage('Car not found');
	// $this->expectExceptionCode(400);
	//
	// $getData = $this->getDOLData(400);
	//
	// $getData->getDOLData('InvalidVIN');
	// }

	public function testShouldReturnCarSpecificationDataWhenVINProvided(): void {
		$body = file_get_contents( __DIR__ . '/../mocks/CarSpecification/response-body.txt' );

		$getData = $this->getDOLData( 200, $body );

		$result = $getData->getDOLData( '7JRZS25UCLG040397' );

		self::assertEquals( json_decode( $body, true ), $result );
	}

	public function testShouldReturnCarSpecificationDataWhenCONProvided(): void {
		$body = file_get_contents( __DIR__ . '/../mocks/CarSpecification/response-body.txt' );

		$getData = $this->getDOLData( 200, $body );

		$result = $getData->getDOLData( null, 'testCONNumber' );

		self::assertEquals( json_decode( $body, true ), $result );
	}

	public function testSortDOLDataWhenNoItemsProvided(): void {
		$data = array(
			'items' => null,
		);

		$result = $this->carSpecificationDataImporter->groupDOLData( $data );

		self::assertEquals( array(), $result );
	}

	public function testSortDOLDataWhenTwoItemsOfTheSameSectionIdProvided(): void {
		$data = array(
			'items' => array(
				array(
					'code'        => '25_C',
					'name'        => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
				array(
					'code'        => '13',
					'name'        => 'Inscription',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd2',
				),
			),
		);

		$result = $this->carSpecificationDataImporter->groupDOLData( $data );

		self::assertEquals(
			array(
				'sections' => array(
					'DRIVE' => array(
						'name'  => 'Napęd',
						'items' => array(
							array(
								'code' => '25_C',
								'name' => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
							),
							array(
								'code' => '13',
								'name' => 'Inscription',
							),
						),
					),
				),
			),
			$result
		);
	}

	public function testSortDOLDataWhenNoSecionNameProvided(): void {
		$data = array(
			'items' => array(
				array(
					'code'        => '25_C',
					'name'        => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
				array(
					'code'        => '13',
					'name'        => 'Inscription',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd2',
				),
				array(
					'code'        => '222',
					'name'        => 'EngineItem',
					'sectionId'   => 'SomeSection',
					'sectionName' => '',
				),
			),
		);

		$result = $this->carSpecificationDataImporter->groupDOLData( $data );

		self::assertEquals(
			array(
				'sections' => array(
					'DRIVE' => array(
						'name'  => 'Napęd',
						'items' => array(
							array(
								'code' => '25_C',
								'name' => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
							),
							array(
								'code' => '13',
								'name' => 'Inscription',
							),
						),
					),
				),
			),
			$result
		);
	}

	public function testSortDOLDataWhenNoSecionIdProvided(): void {
		$data = array(
			'items' => array(
				array(
					'code'        => '25_C',
					'name'        => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
				array(
					'code'        => '13',
					'name'        => 'Inscription',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd2',
				),
				array(
					'code'        => '222',
					'name'        => 'EngineItem',
					'sectionId'   => 'UNDEFINED',
					'sectionName' => 'Section Name',
				),
			),
		);

		$result = $this->carSpecificationDataImporter->groupDOLData( $data );

		self::assertEquals(
			array(
				'sections' => array(
					'DRIVE' => array(
						'name'  => 'Napęd',
						'items' => array(
							array(
								'code' => '25_C',
								'name' => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
							),
							array(
								'code' => '13',
								'name' => 'Inscription',
							),
						),
					),
				),
			),
			$result
		);
	}

	public function testSortDOLDataWhenNoNameProvided(): void {
		$data = array(
			'items' => array(
				array(
					'code'        => '25_C',
					'name'        => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
				array(
					'code'        => '13',
					'name'        => 'Inscription',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd2',
				),
				array(
					'code'        => '222',
					'name'        => null,
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Section Name',
				),
			),
		);

		$result = $this->carSpecificationDataImporter->groupDOLData( $data );

		self::assertEquals(
			array(
				'sections' => array(
					'DRIVE' => array(
						'name'  => 'Napęd',
						'items' => array(
							array(
								'code' => '25_C',
								'name' => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
							),
							array(
								'code' => '13',
								'name' => 'Inscription',
							),
						),
					),
				),
			),
			$result
		);
	}

	public function testSortDOLDataWhenTyreLabelsIsNull(): void {
		$data = array(
			'items'      => array(
				array(
					'code'        => '25_C',
					'name'        => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
				array(
					'code'        => '13',
					'name'        => 'Inscription',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
			),
			'tyreLabels' => null,
		);

		$result = $this->carSpecificationDataImporter->groupDOLData( $data );

		self::assertEquals(
			array(
				'sections' => array(
					'DRIVE' => array(
						'name'  => 'Napęd',
						'items' => array(
							array(
								'code' => '25_C',
								'name' => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
							),
							array(
								'code' => '13',
								'name' => 'Inscription',
							),
						),
					),
				),
			),
			$result
		);
	}

	public function testSortDOLDataWhenTyreLabelsAreProvided(): void {
		$data = array(
			'items'      => array(
				array(
					'code'        => '25_C',
					'name'        => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
				array(
					'code'        => '13',
					'name'        => 'Inscription',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
			),
			'tyreLabels' => array(
				array(
					'season'   => 'SUMMER',
					'position' => 'ALL',
					'url'      => 'https://vcc-tyre-label.s3.eu-west-1.amazonaws.com/32209746.jpg',
				),
				array(
					'season'   => 'WINTER',
					'position' => 'ALL',
					'url'      => 'https://vcc-tyre-label.s3.eu-west-1.amazonaws.com/32333051.jpg',
				),
				array(
					'season'   => 'WINTER',
					'position' => 'ALL',
					'url'      => 'https://vcc-tyre-label.s3.eu-west-1.amazonaws.com/32333049.jpg',
				),
				array(
					'season'   => 'SUMMER',
					'position' => 'ALL',
					'url'      => 'https://vcc-tyre-label.s3.eu-west-1.amazonaws.com/32281663.jpg',
				),
			),
		);

		$result = $this->carSpecificationDataImporter->groupDOLData( $data );

		self::assertEquals(
			array(
				'sections'   => array(
					'DRIVE' => array(
						'name'  => 'Napęd',
						'items' => array(
							array(
								'code' => '25_C',
								'name' => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
							),
							array(
								'code' => '13',
								'name' => 'Inscription',
							),
						),
					),
				),
				'tyreLabels' => array(
					'SUMMER' => array(
						array(
							'position' => 'ALL',
							'url'      => 'https://vcc-tyre-label.s3.eu-west-1.amazonaws.com/32209746.jpg',
						),
						array(
							'position' => 'ALL',
							'url'      => 'https://vcc-tyre-label.s3.eu-west-1.amazonaws.com/32281663.jpg',
						),
					),
					'WINTER' => array(
						array(
							'position' => 'ALL',
							'url'      => 'https://vcc-tyre-label.s3.eu-west-1.amazonaws.com/32333051.jpg',
						),
						array(
							'position' => 'ALL',
							'url'      => 'https://vcc-tyre-label.s3.eu-west-1.amazonaws.com/32333049.jpg',
						),
					),
				),
			),
			$result
		);
	}

	public function testSortDOLDataWhenWltpFuelConsumptionIsNull(): void {
		$data = array(
			'items'               => array(
				array(
					'code'        => '25_C',
					'name'        => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
				array(
					'code'        => '13',
					'name'        => 'Inscription',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
			),
			'wltpFuelConsumption' => null,
		);

		$result = $this->carSpecificationDataImporter->groupDOLData( $data );

		self::assertEquals(
			array(
				'sections' => array(
					'DRIVE' => array(
						'name'  => 'Napęd',
						'items' => array(
							array(
								'code' => '25_C',
								'name' => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
							),
							array(
								'code' => '13',
								'name' => 'Inscription',
							),
						),
					),
				),
			),
			$result
		);
	}

	public function testSortDOLDataWhenWltpFuelConsumptionIsProvided(): void {
		$data = array(
			'items'               => array(
				array(
					'code'        => '25_C',
					'name'        => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
				array(
					'code'        => '13',
					'name'        => 'Inscription',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
			),
			'wltpFuelConsumption' => array(
				array(
					'name'        => 'fuelConsumption',
					'description' => 'Zużycie paliwa WLTP',
					'unit'        => 'l/100 km',
					'value'       => '7.4',
				),
				array(
					'name'        => 'lowFuelConsumption',
					'description' => 'Zużycie paliwa WLTP (Low)',
					'unit'        => 'l/100 km',
					'value'       => '10.1',
				),
			),
		);

		$result = $this->carSpecificationDataImporter->groupDOLData( $data );

		self::assertEquals(
			array(
				'sections'        => array(
					'DRIVE' => array(
						'name'  => 'Napęd',
						'items' => array(
							array(
								'code' => '25_C',
								'name' => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
							),
							array(
								'code' => '13',
								'name' => 'Inscription',
							),
						),
					),
				),
				'fuelConsumption' => array(
					'unit'  => 'l/100km',
					'value' => '7.4',
				),
			),
			$result
		);
	}

	public function testSortDOLDataWhenWeightedWltpFuelConsumptionIsProvided(): void {
		$data = array(
			'items'               => array(
				array(
					'code'        => '25_C',
					'name'        => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
				array(
					'code'        => '13',
					'name'        => 'Inscription',
					'sectionId'   => 'DRIVE',
					'sectionName' => 'Napęd',
				),
			),
			'wltpFuelConsumption' => array(
				array(
					'name'        => 'weightedFuelConsumption',
					'description' => 'Zużycie paliwa WLTP (Weighted)',
					'unit'        => 'l/100 km',
					'value'       => '2.8',
				),
			),
		);

		$result = $this->carSpecificationDataImporter->groupDOLData( $data );

		self::assertEquals(
			array(
				'sections'        => array(
					'DRIVE' => array(
						'name'  => 'Napęd',
						'items' => array(
							array(
								'code' => '25_C',
								'name' => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
							),
							array(
								'code' => '13',
								'name' => 'Inscription',
							),
						),
					),
				),
				'fuelConsumption' => array(
					'unit'  => 'l/100km',
					'value' => '2.8',
				),
			),
			$result
		);
	}

	private function getDOLData( $status, $body = null ): CarSpecificationDataImporter {
		$mock    = new MockHandler( array( new Response( $status, array(), $body ) ) );
		$handler = HandlerStack::create( $mock );
		$client  = new Client( array( 'handler' => $handler ) );

		return new CarSpecificationDataImporter( $client );
	}
}
