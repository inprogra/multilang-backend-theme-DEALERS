<?php

add_action('acf/options_page/save', 'update_local_sliders');

function update_local_sliders($post_id)
{
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post_id !== 'options-homepage') {
        return;
    }

    $sliders = get_field('slider', $post_id);

    $slides_to_update = array_filter($sliders['slides'], fn ($val) => $val['type'] === 'global' && $val['hide_campaign'] !== false);

    $slides_to_update = array_reduce($slides_to_update, function ($carry, $item) {
        $carry[$item['hide_campaign']] = $item['global-campaign'];

        return $carry;
    }, []);

    $all_sites = get_sites();
    $current_blog_id = get_current_blog_id();

    if ($all_sites) {
        foreach ($all_sites as $site) {
            if ($site->blog_id == get_main_site_id()) {
                continue;
            }

            switch_to_blog($site->blog_id);

            $slider_data = get_field('slider', $post_id);

            foreach ($slider_data['slides'] as &$slide) {
                if ($slide['type'] === 'global' && array_key_exists($slide['global-campaign'], $slides_to_update)) {
                    $slide['global-campaign'] = $slides_to_update[$slide['global-campaign']];
                }
            }

            update_field('slider', $slider_data, $post_id);
            acf_flush_value_cache($post_id, 'slider');
        }

        switch_to_blog($current_blog_id);
    }

    $sliders = get_field('slider', $post_id);
    $sliders['slides'] = array_filter($sliders['slides'], fn ($val) => $val['hide_campaign'] === false);

    update_field('slider', $sliders, $post_id);
    acf_flush_value_cache($post_id, 'slider');
}
