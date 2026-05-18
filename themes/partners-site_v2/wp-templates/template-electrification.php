<?php

/**
 * Template Name: Elektryfikacja
 * 
 */

 


 
 
     get_header();
 ?>
<div class="electrification electrification-nav container">
    <ul class="electric-nav">
        <li <?php if (strpos($_SERVER["REQUEST_URI"] ,'/samochody') !== false) { ?> class="active" <?php } ?>>
            <a href="/samochody-elektryfikacja">Samochody</a>
        </li>
        <li <?php if (strpos($_SERVER["REQUEST_URI"] ,'/potencjal') !== false) { ?> class="active" <?php } ?>>
            <a href="/potencjal-elektryfikacja">Potencjał</a>
        </li>
        <li <?php if (strpos($_SERVER["REQUEST_URI"] ,'/obsluga') !== false) { ?> class="active" <?php } ?>>
            <a href="/obsluga-eletryfikacja">Obsługa</a>
        </li>
    </ul>
    <style>
        ul.electric-nav {
            display:flex;
            width:100%;
            list-style:none;
            padding-top:24px;
            padding-left:0;
        }
        ul.electric-nav li.active {
            text-align:center;
            flex-grow:1;
            padding-bottom:14px;
            border-bottom:2px solid #1C6BBA;
        }
        ul.electric-nav li {
            text-align:center;
            flex-grow:1;
            padding-bottom:14px;
            border-bottom:2px solid #D5D5D5;
        }
        ul.electric-nav li:nth-child(2) {
            margin:0 24px;
        }
        ul.electric-nav li a {
            color:#D5D5D5;
            font-size:16px;
            line-height:24px;
            font-weight:600;
            margin-bottom:14px;

        }
        ul.electric-nav li.active a {
            color:#333;
            font-size:16px;
            line-height:24px;
            font-weight:600;
            margin-bottom:14px;

        }
        @media screen and (max-width:997px) {
            ul.electric-nav li {
                border-bottom:0!important;
            }
        }
    </style>
</div>
 <?php
 while (have_posts()) {
     the_post();
     the_content();
 }
 
 get_footer('electric');
     
 
 