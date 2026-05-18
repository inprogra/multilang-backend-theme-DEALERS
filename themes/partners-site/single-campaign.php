<?php
get_header();

use \Controllers\CampaignController;

$campaignController = new CampaignController();

echo $campaignController->render();

get_footer();
