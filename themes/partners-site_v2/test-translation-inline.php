<?php
/**
 * Inline translation test
 * Add this to the top of any page template to test translations
 */

// Force load Czech translations
$locale = get_locale();
$mofile = get_template_directory() . '/languages/partners-site_v2-' . $locale . '.mo';

echo "<!-- Translation Debug -->";
echo "<!-- Locale: " . $locale . " -->";
echo "<!-- MO File: " . $mofile . " -->";
echo "<!-- File Exists: " . (file_exists($mofile) ? 'YES' : 'NO') . " -->";

if (file_exists($mofile)) {
    $loaded = load_textdomain('partners-site_v2', $mofile);
    echo "<!-- Load Result: " . ($loaded ? 'SUCCESS' : 'FAILED') . " -->";
}

// Test translation
$test = __('First Name', 'partners-site_v2');
echo "<!-- Translation Test: Imię = " . $test . " (should be Jméno) -->";
echo "<!-- Translation Working: " . ($test === 'Jméno' ? 'YES' : 'NO') . " -->";
echo "<!-- End Translation Debug -->";
