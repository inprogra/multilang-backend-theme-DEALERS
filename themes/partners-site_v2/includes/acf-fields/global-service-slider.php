<?php

add_action('acf/options_page/save', 'update_local_sliders');

function update_local_sliders($post_id)
{
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post_id !== 'options-homepage') {
        return;
    }

    if (get_current_blog_id() !== get_main_site_id()) {
        return;
    }

    $sliders = get_field('slider', $post_id);

    $slides_to_update = array_filter(
        $sliders['slides'],
        fn ($val) => $val['type'] === 'global' && $val['hide_campaign'] !== false
    );

    $slides_to_update = array_reduce($slides_to_update, function ($carry, $item) {
        $carry[$item['hide_campaign']] = $item['global-campaign'];
        return $carry;
    }, []);

    $forced_slots = [];

    if (!empty($sliders['slides'])) {
        foreach ($sliders['slides'] as $index => $slide) {
            if (
                $slide['type'] === 'global' &&
                !empty($slide['force_campaign']) &&
                !empty($slide['global-campaign'])
            ) {
                $forced_slots[$index] = $slide['global-campaign'];
            }
        }
    }

    $all_sites = get_sites();
    $current_blog_id = get_current_blog_id();

    if ($all_sites) {
        foreach ($all_sites as $site) {

            if ($site->blog_id == get_main_site_id()) {
                continue;
            }

            switch_to_blog($site->blog_id);

            $slider_data = get_field('slider', $post_id);

            if (!empty($slider_data['slides'])) {

                foreach ($slider_data['slides'] as $index => &$slide) {

                    if (
                        $slide['type'] === 'global' &&
                        array_key_exists($slide['global-campaign'], $slides_to_update)
                    ) {
                        $slide['global-campaign'] = $slides_to_update[$slide['global-campaign']];
                    }

                    if (array_key_exists($index, $forced_slots)) {
                        $slide['type'] = 'global';
                        $slide['global-campaign'] = $forced_slots[$index];
                    }
                }
            }

            update_field('slider', $slider_data, $post_id);
            acf_flush_value_cache($post_id, 'slider');
        }

        switch_to_blog($current_blog_id);
    }

    $sliders = get_field('slider', $post_id);
    $sliders['slides'] = array_filter(
        $sliders['slides'],
        fn ($val) => $val['hide_campaign'] === false
    );

    update_field('slider', $sliders, $post_id);
    acf_flush_value_cache($post_id, 'slider');
}
