<?php
/**
 * Template Name: Strona Eventu
 */

get_header();

$blogId=get_current_blog_id();
if ($blogId == 36) {
$blogId = 37;
}
$instanceID = get_fields('options-dealer')['event_instance'];
if ($instanceID) {
$blogId = $instanceID;
}

?>
<link rel="stylesheet" href="https://events.dealervolvo.pl/css/render.css">
<script
			  src="https://code.jquery.com/jquery-3.6.0.js"
			  integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
			  crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script> 
<script type="text/javascript" data-name="instance<?= $blogId; ?>" src="https://events.dealervolvo.pl/js/render.js?token=tickets&v=1.0.8&xyz=<?= time(); ?>"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
<div class="events_title">
	<h2><?= the_title(); ?> <span><?php esc_html_e('Concerts, meetings, presentations', 'partners-site_v2'); ?></span></h2>
</div>
<div class="tickets"></div>
<style>
	
	.l-side-form {
		display:none!important;
	}	
	ul.first_step.d-none {
		display:none;
	}
	.row.mobileonly > div {
		text-align:center;
	}
	.buypanel.interline li h4.h4{
		font-family: "Volvo Centum"; 
	}
	.buypanel.interline li h4.h4 .light_theme{
		font-family: "Volvo Centum"; 
	}
	.buypanel .buy-ticket{
		font-family: "Volvo Centum"; 
	}
	.events_title,
		.buypanel.interline {
			
			 li h4.h4 {
				position:relative;
				top:unset;
				transform: none;
			}
			.buy-ticket {
				position:relative;
				transform:unset;
				top:unset!important;
				left:unset!important;
				margin-top:20px;
				color:#000;
				border-color:#000;
			}
		}
		form.payment .save_data {
			color:#fff;
			background:#000;
			border-color:#000;
		}
		form.payment .form-check-label span.more {
			color:#000;
			img {
				display:none!important;
			}
		}
		.buypanel.interline {
			.col-md-6 > div {
			display:flex;
			flex-direction: column;
			align-items: center;
  			justify-content: center;
			height:100%;
			
			span {
				display:block;
				font-size:16px!important;
				line-height:24px!important;
				padding:5px 20px 0 20px!important;
			}
		}
		}
	@media screen and (min-width:1680px) {
	.events_title {
		padding:45px 30px 30px 30px;
		h2 {
			span {
				float:right;
				font-size:16px;
				color:#000;
				margin-top:20px;
				
			}
		}
	}
	.events_title,
	.buypanel.interline {
			max-width:1610px;
		.col-md-6 > div {
			display:flex;
			flex-direction: column;
			align-items: center;
  			justify-content: center;
			height:100%;
			
			span {
				display:block;
				font-size:16px!important;
				line-height:24px!important;
				padding:5px 20px 0 20px!important;
			}
		}
		}
	}
</style>

<?php

get_footer();
