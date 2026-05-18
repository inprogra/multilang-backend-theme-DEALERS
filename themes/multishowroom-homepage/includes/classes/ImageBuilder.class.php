<?php

namespace MultishowroomHomepage\Classes;

use \MultishowroomHomepage\Classes\Cache;
use \MultishowroomHomepage\Classes\MultisiteFixer;

class ImageBuilder
{
    private $attachmentId;
    private $hash;
    private $image;

    public function __construct($attachmentId, $alt = null)
    {
        $this->attachmentId = $attachmentId;
        $this->hash = Cache::getAttachmentHash($attachmentId);

        if (!$alt) {
            $alt = get_post_meta($attachmentId, '_wp_attachment_image_alt', true);
        }

        $this->image = [
            'alt' => $alt,
            'sizes' => [],
            'mediaQueries' => [],
            'defaultMediaQuery' => []
        ];
    }

    public function get()
    {
        return $this->image;
    }

    public function addSize($size, $crop = true, $networkHomeUrl = false)
    {
        $size = $this->generate($this->attachmentId, $size, $crop, $networkHomeUrl);

        $this->image['sizes'][] = $size;
    }

    public function addMediaQuery($mediaQuery, $size, $default = false)
    {
        if ($default) {
            $this->image['defaultMediaQuery'] = [
                'size' => $size
            ];
        } else {
            $this->image['mediaQueries'][] = [
                'mediaQuery' => $mediaQuery,
                'size' => $size
            ];
        }
    }

    private function generate($attachmentId, $size, $crop = true, $networkHomeUrl = false): array
    {
        $generatedImage = fly_get_attachment_image_src($attachmentId, $size, $crop);
        if ($generatedImage === false || empty($generatedImage)) {
            return [];
        }

        $generatedImage['src'] = Cache::buildHashUrl(MultisiteFixer::buildUrl($generatedImage['src'], null, $networkHomeUrl), $this->hash);

        return $generatedImage;
    }

}
