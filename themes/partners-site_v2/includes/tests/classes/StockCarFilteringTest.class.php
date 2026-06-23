<?php

namespace Tests\Classes;

use Roots\WPConfig\Config;
use PHPUnit\Framework\TestCase;

class StockCarFilteringTest extends TestCase {


	public function setUp(): void {
		parent::setUp();
		$_ENV['TESTING_ENV']  = true;
		$_SERVER['HTTP_HOST'] = 'volvocars-partner.local';

		require_once __DIR__ . '/../../../../../../wp/wp-load.php';

		// Set up the WordPress query.
		wp();
	}

	public function testIfTrueIsTrue() {

		global $wpdb;

		echo $wpdb->dbname;

		$charset_collate = $wpdb->get_charset_collate();

		$table_name = 'wp_test_table';

		$sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$array = dbDelta( $sql );

		$result = $wpdb->insert(
			$table_name,
			array(
				'time' => current_time( 'mysql' ),
				'name' => 'test',
				'text' => 'test2',
			)
		);

		//var_dump( $array );

		$this->assertTrue( true, true );
		// $this->assertEquals(true, true);
	}
}
