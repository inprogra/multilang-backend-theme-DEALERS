<?php

namespace Tests\Classes;

use Classes\CarSpecification;
use PHPUnit\Framework\TestCase;

class CarSpecificationTest extends TestCase {


	private $carSpecification;

	protected function setUp(): void {
		parent::setUp();
		$this->carSpecification = new CarSpecification();
	}

	public function testNoData(): void {
		$data = $this->carSpecification->validateData( array() );

		self::assertEquals( array(), $data );
	}

	public function testVINProvided(): void {
		$data = $this->carSpecification->validateData(
			array(
				'VIN' => 'testVINNumber',
			)
		);

		self::assertEquals(
			array(
				'VIN' => 'testVINNumber',
			),
			$data
		);
	}

	public function testVINAndCONProvided(): void {
		$data = $this->carSpecification->validateData(
			array(
				'VIN' => 'testVINNumber',
				'CON' => 'testCONNumber',
			)
		);

		self::assertEquals(
			array(
				'VIN' => 'testVINNumber',
				'CON' => 'testCONNumber',
			),
			$data
		);
	}

	public function testVINAndCONAndPostIDProvided(): void {
		$data = $this->carSpecification->validateData(
			array(
				'VIN'    => 'testVINNumber',
				'CON'    => 'testCONNumber',
				'postId' => 12,
			)
		);

		self::assertEquals(
			array(
				'VIN'    => 'testVINNumber',
				'CON'    => 'testCONNumber',
				'postId' => 12,
			),
			$data
		);
	}

	public function testExtraFieldsProvided(): void {
		$data = $this->carSpecification->validateData(
			array(
				'extraField1' => 'some value 1',
				'VIN'         => 'testVINNumber',
				'CON'         => 'testCONNumber',
				'extraField2' => 'some value 2',
			)
		);

		self::assertEquals(
			array(
				'VIN' => 'testVINNumber',
				'CON' => 'testCONNumber',
			),
			$data
		);
	}

	public function testconvertCarSpecificationDataImporterFormatToACF(): void {
		$data = array(
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
		);

		$result = $this->carSpecification->convertCarSpecificationDataImporterFormatToACF( $data );

		self::assertEquals(
			array(
				array(
					'name'  => 'Napęd',
					'items' => array(
						array(
							'name' => 'T5 250 KM automatyczna Geartronic, 8 biegów, AWD',
						),
						array(
							'name' => 'Inscription',
						),
					),
				),
			),
			$result
		);
	}
}
