<?php

namespace Tests\Helpers;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../helpers/helpers.php';

class PolishSuffixesTest extends TestCase {

	public function testShouldReturnSingleWhenValueEqualsMinusOne(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', -1 );

		self::assertEquals( 'auto', $value );
	}

	public function testShouldReturnSingleWhenValueEqualsOne(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 1 );

		self::assertEquals( 'auto', $value );
	}

	public function testShouldReturnFewWhenValueEqualsTwo(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 2 );

		self::assertEquals( 'auta', $value );
	}

	public function testShouldReturnFewWhenValueEqualsThree(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 3 );

		self::assertEquals( 'auta', $value );
	}

	public function testShouldReturnFewWhenValueEqualsFour(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 4 );

		self::assertEquals( 'auta', $value );
	}

	public function testShouldReturnManyWhenValueEqualsFive(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 5 );

		self::assertEquals( 'aut', $value );
	}

	public function testShouldReturnManyWhenValueEqualsTen(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 10 );

		self::assertEquals( 'aut', $value );
	}

	public function testShouldReturnManyWhenValueEqualsEleven(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 11 );

		self::assertEquals( 'aut', $value );
	}

	public function testShouldReturnManyWhenValueEqualsTwelve(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 12 );

		self::assertEquals( 'aut', $value );
	}

	public function testShouldReturnManyWhenValueEqualsTwentyOne(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 21 );

		self::assertEquals( 'aut', $value );
	}

	public function testShouldReturnManyWhenValueEqualsTwentyTwo(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 22 );

		self::assertEquals( 'auta', $value );
	}

	public function testShouldReturnManyWhenValueEqualsTwentyThree(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 23 );

		self::assertEquals( 'auta', $value );
	}

	public function testShouldReturnManyWhenValueEqualsTwentyFive(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 25 );

		self::assertEquals( 'aut', $value );
	}

	public function testShouldReturnManyWhenValueEqualsOneHundredAndOne(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 101 );

		self::assertEquals( 'aut', $value );
	}

	public function testShouldReturnManyWhenValueEqualsOneHundredAndTwo(): void {
		$value = polishSuffixes( 'auto', 'auta', 'aut', 102 );

		self::assertEquals( 'auta', $value );
	}
}
