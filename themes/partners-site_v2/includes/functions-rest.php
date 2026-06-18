<?php

function volvo_global_car_model_adjust_permalinks() {
    // Check if FooterController class exists
    if (!class_exists('Classes\Model')) {
        return;
    }

    add_filter( 'post_type_link', array( '\Classes\Model', 'adjustPermalinks' ), 1, 2 );
}
volvo_global_car_model_adjust_permalinks();