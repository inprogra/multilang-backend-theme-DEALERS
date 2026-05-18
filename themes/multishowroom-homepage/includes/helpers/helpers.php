<?php
function getSVG($file)
{
    $path = \MultishowroomHomepage\Classes\Cache::getAsset($file . '.svg', true);

    if (file_exists($path)) {
        return file_get_contents($path);
    }

    return null;
}