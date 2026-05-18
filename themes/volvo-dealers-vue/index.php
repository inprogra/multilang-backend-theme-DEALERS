<?php
/**
 * The main template file
 *
 * @package Volvo_Dealers_Vue
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get theme URI for assets
$theme_uri = get_template_directory_uri();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    
    <!-- Vue App Mount Point -->
    <div id="app">
        <!-- Loading state - will be replaced by Vue -->
        <div class="loading-screen" style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #141414; color: #fff; font-family: Arial, sans-serif;">
            <div style="text-align: center;">
                <h1 style="font-size: 24px; margin-bottom: 16px;">VOLVO</h1>
                <p>Ładowanie...</p>
            </div>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
