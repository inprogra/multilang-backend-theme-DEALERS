<?php

namespace Tests\Classes;

use Classes\Cache;
use Classes\ImageBuilder;
use Brain\Monkey\Functions;
use Classes\MultisiteFixer;
use Tests\PluginTestCase;

class ImageBuilderTest extends PluginTestCase {

	private $imageBuilder;

	protected function setUp(): void {
		parent::setUp();
		Functions\expect( 'get_post_meta' )
			->andReturn( 'image alt' );
	}

	public function testCreateImageWithoutAltProvided(): void {
		$this->imageBuilder = new ImageBuilder( 123 );
		$result             = $this->imageBuilder->get();

		self::assertEquals(
			array(
				'alt'   => 'image alt',
				'sizes' => array(),
			),
			$result
		);
	}

	public function testCreateImageWithAltProvided(): void {
		$this->imageBuilder = new ImageBuilder( 123, 'image alt provided' );
		$result             = $this->imageBuilder->get();

		self::assertEquals(
			array(
				'alt'   => 'image alt provided',
				'sizes' => array(),
			),
			$result
		);
	}

	public function testOneAddSize(): void {
		Functions\expect( 'fly_get_attachment_image_src' )
			->with( 123, array( 1280, 1024 ), true )
			->andReturn(
				array(
					'src'    => 'http://karlik.volvocars-partner.local/app/uploads/sites/2/fly-images/50/volvo-xc90-1-288x162-c.png',
					'width'  => 1280,
					'height' => 1024,
				)
			);
		$cacheMock = $this->createMock( Cache::class );
		$cacheMock->method( 'buildHashUrl' )
			->willReturn( 'http://karlik.volvocars-partner.local/app/uploads/sites/2/fly-images/50/volvo-xc90-1-288x162-c.png?hash=123456' );

		$this->imageBuilder = new ImageBuilder( 123 );
		$this->imageBuilder->addSize( '(min-width: 1920px)', array( 1280, 1024 ) );

		self::assertEquals(
			array(
				'alt'   => 'image alt',
				'sizes' => array(
					'mediaQuery' => '(min-width: 1920px)',
					'width'      => 1280,
					'height'     => 1024,
					'src'        => 'http://karlik.volvocars-partner.local/app/uploads/sites/2/fly-images/50/volvo-xc90-1-288x162-c.png?hash=123456',
				),
			),
			$this->imageBuilder->get()
		);
	}
}
