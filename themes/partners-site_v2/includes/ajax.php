<?php
use Classes\Controller;
use Controllers\StockController;
use Classes\Lead;
use GuzzleHttp\Client;
use Classes\CarSpecificationDataImporter;


$ajaxActions = array( 'searchFilter', 'leadReceiver', 'carSpecification', 'checkVIN', 'storePricing', 'querySteps', 'forwardCarsRequest' );
foreach ( $ajaxActions as $action ) {
	add_action( 'wp_ajax_' . $action, $action );
	add_action( 'wp_ajax_nopriv_' . $action, $action );
}
function get_content($URL)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $URL);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function forwardCarsRequest()
{
	// Get parameters from the request
	$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
	$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1000;
	$sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'new';
	$condition = isset($_GET['condition']) ? sanitize_text_field($_GET['condition']) : 'all';
	
	// Build the API URL
	$apiUrl = 'https://apiv2.easyapi.space/api/cars/' . $page . '/' . $limit . '/' . $sort . '/' . $condition;
	
	// Initialize cURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $apiUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	
	// Execute the request
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error = curl_error($ch);
	curl_close($ch);
	
	// Handle errors
	if ($error) {
		header('Content-Type: application/json');
		http_response_code(500);
		echo json_encode(['error' => 'Failed to fetch data from API', 'details' => $error]);
		wp_die();
	}
	
	// Set appropriate headers and return the response
	header('Content-Type: application/json');
	http_response_code($httpCode);
	echo $response;
	wp_die();
}
function querySteps()
{

	$m = new Memcached();
	$m->addServer('localhost', 11211);
	// $API_DOM = 'https://valuation.poznajvolvo.pl/api/';
	$API_DOM = 'https://recalc-volvo.easyapi.space/api/';
	$data = $_POST;
	$url = str_replace(' ', '+', $data['data']['endpoint']);

	if ($m->get($url)) {
		echo $m->get($url);
	} else {
		$request = get_content($API_DOM . $url);
		$m->set($url, $request);

		echo $request;
	}


	exit();
}
function storePricing()
{
	switch_to_blog(get_current_blog_id());
	$data = $_POST;
	$uID = get_current_blog_id().'-TR-'.date('m').'-'.date('y').'-'.substr(number_format(time() * rand(),0,'',''),0,4);

	$dealer_email = get_field('indicata_setup_settings','options-dealer')['indicata_email'];
	$dealer_address = get_field('indicata_setup_settings','options-dealer')['indicata_address'];
	$dealer_showRoom = get_field('dealerId','options-dealer');

	$data['id'] = $uID;
	$data['confirm'] = 0;
	$data['pricing'] = 0;
	$data['sendToEmails'] = $dealer_email;
	$data['dealerAddress'] = $dealer_address;
	$data['dealerId'] = $dealer_showRoom;
	$data['domain'] = str_replace('/wp','/',get_site_url());
	
	$data = json_encode($data);
	file_put_contents('/var/www/volvocars-partner.pl/partners-site/pricing/' . $uID . '.json', $data);
	restore_current_blog();
	die();
}
function checkVIN()
{
	$vin = $_POST['vin'];
	$client = new Client();
	$check_db = new CarSpecificationDataImporter($client);
	$vin = strtoupper($vin);
	$vin_data = $check_db->getVinomatDol($vin, 'full');
	echo json_encode($vin_data);

	die();
}
function carSpecification() {

	$carSpecification = new \Classes\CarSpecification();
	$carSpecification->update( $_POST );
	die();
}

function searchFilter() {
	$cont = new Classes\Controller();
	$cont->render();
	$stockController = new StockController();
	echo $stockController->filter( $_POST );
	die();
}

function leadReceiver() {
	
	$lead     = new Lead();
	$leadData = array(
		'originUrl'               => sanitize_text_field( $_POST['originUrl'] ),
		'source'                  => sanitize_text_field( $_POST['source'] ),
		'name'                    => sanitize_text_field( $_POST['name'] ),
		'surname'                 => sanitize_text_field( $_POST['surname'] ),
		'phoneNumber'             => sanitize_text_field( $_POST['phoneNumber'] ),
		'email'                   => sanitize_text_field( $_POST['email'] ),		
		'message'                 => sanitize_textarea_field( $_POST['message'] ),
		'referrer'                => sanitize_text_field( $_POST['referrer'] ),
		'dataProcessingConsent'   => isset( $_POST['dataProcessingConsent'] ) && $_POST['dataProcessingConsent'] == 'true',
		'tradeContactConsent'     => isset( $_POST['tradeContactConsent'] ) && $_POST['tradeContactConsent'] == 'true',
		'marketingContactConsent' => isset( $_POST['marketingContactConsent'] ) && $_POST['marketingContactConsent'] == 'true',
	);
	
	if ( isset( $_POST['origin'] ) ) {
		$leadData['origin'] = $_POST['origin'];

		if ( $_POST['origin'] === 'service' ) {
			$leadData['vin']            = sanitize_text_field( $_POST['vin'] );
			$leadData['productionYear'] = sanitize_text_field( $_POST['productionYear'] );
			$leadData['model']          = sanitize_text_field( $_POST['model'] );
			$leadData['services']       = is_array( $_POST['services'] ) ? $_POST['services'] : array();
			if ($_POST['salon']) {
			$leadData['salon']  		= sanitize_text_field( $_POST['salon'] );
			$leadData['showroom'] = $_POST['salon'];
			}
		}

		if ( $_POST['origin'] === 'test-drive' ) {
			$leadData['preferred_models'] = sanitize_text_field( $_POST['preferred_models'] );
			$leadData['preferred_date']   = sanitize_text_field( $_POST['preferred_date'] );
			$leadData['preferred_time']   = is_array( $_POST['preferred_time'] ) ? implode( ', ', $_POST['preferred_time'] ) : '';
		}
	}

	if ( isset( $_POST['destination'] ) ) {
		$destination      = sanitize_text_field( $_POST['destination'] ) ?? false;
		$destinationField = get_field_object( 'field_6062dsa3bd29' );
		if ( ! isset( $destinationField['choices'][ $destination ] ) ) {
			$destination = array_key_first( $destinationField['choices'] );
		}
		$leadData['destination'] = $destination;
	}

	if ( isset( $_POST['showroom'] ) ) {
		$leadData['showroom'] = $_POST['showroom'];
	}

	if ( isset( $_POST['youLead'] ) ) {
		$leadData['youLeadData'] = json_encode( $_POST['youLead'] );
	}
	
	$leadId   = $lead->create( $leadData );
	$response = $lead->send( $leadId );

	echo json_encode( $response );
	wp_die();
}
