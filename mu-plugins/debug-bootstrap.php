<?php

function my_bootstrap_log(string $label): void
{
    if (!defined('APP_START_TIME')) {
        return;
    }

    $log_file = WP_CONTENT_DIR . '/../debug-time-bootstrap.log';

    $message = sprintf(
        "[%s] %s: %.4f\n",
        date('Y-m-d H:i:s'),
        $label,
        microtime(true) - APP_START_TIME
    );

    file_put_contents(
        $log_file,
        $message,
        FILE_APPEND | LOCK_EX
    );
}

function my_start_hook_logging() {
    add_action('all', 'my_log_current_hook_methods');
}

function my_log_current_hook_methods($hook_name) {
    if (!defined('APP_START_TIME')) {
        return;
    }
    global $wp_filter;
    
    // Ignoruj powtarzalne filtry tłumaczeń, aby log był czytelny
    if (in_array($hook_name, ['gettext', 'gettext_with_context', 'ngettext'])) {
        return;
    }

    $log_file = WP_CONTENT_DIR . '/../debug-time-bootstrap.log';
    $output = "HOOK: [$hook_name] - " . (microtime(true) - APP_START_TIME) ."\n";

    // Sprawdź, jakie metody/funkcje są przypisane do tego konkretnego hooka
    if (isset($wp_filter[$hook_name])) {
        foreach ($wp_filter[$hook_name] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $function_info = 'Nieznana funkcja';
                $file_info = 'Nieznany plik';

                try {
                    if (is_string($callback['function'])) {
                        $ref = new ReflectionFunction($callback['function']);
                        $function_info = $callback['function'];
                        $file_info = $ref->getFileName() . ':' . $ref->getStartLine();
                    } elseif (is_array($callback['function'])) {
                        $ref = new ReflectionMethod($callback['function'][0], $callback['function'][1]);
                        $class_name = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                        $function_info = $class_name . '::' . $callback['function'][1];
                        $file_info = $ref->getFileName() . ':' . $ref->getStartLine();
                    } elseif ($callback['function'] instanceof Closure) {
                        $ref = new ReflectionFunction($callback['function']);
                        $function_info = 'Funkcja anonimowa (Closure)';
                        $file_info = $ref->getFileName() . ':' . $ref->getStartLine();
                    }
                } catch (Exception $e) {
                    // Ignoruj błędy reflection dla funkcji wbudowanych w PHP
                }

                $output .= "  -> Priorytet: $priority | Wykonuje: $function_info\n";
                $output .= "     Plik: $file_info\n";
            }
        }
    }

    file_put_contents($log_file, $output . str_repeat('-', 40) . "\n", FILE_APPEND);
}

function my_stop_hook_logging() {
    remove_action('all', 'my_log_current_hook_methods');
}

if ($_GET['api-test-log']) {

    //add_action('setup_theme', 'my_start_hook_logging', PHP_INT_MAX);
    //add_action('after_setup_theme', 'my_stop_hook_logging', PHP_INT_MIN);

    add_action(
        'muplugins_loaded',
        fn() => my_bootstrap_log('muplugins_loaded')

    );

    add_action(
        'plugins_loaded',
        fn() => my_bootstrap_log('plugins_loaded')
    );

    add_action(
        'setup_theme',
        fn() => my_bootstrap_log('setup_theme first'),
        PHP_INT_MIN
    );

    add_action(
        'setup_theme',
        fn() => my_bootstrap_log('setup_theme last'),
        PHP_INT_MAX
    );

    add_action(
        'after_setup_theme',
        fn() => my_bootstrap_log('after_setup_theme first'),
        PHP_INT_MIN
    );

    add_action(
        'after_setup_theme',
        fn() => my_bootstrap_log('after_setup_theme last'),
        PHP_INT_MAX
    );

    add_action(
        'wp_loaded',
        fn() => my_bootstrap_log('wp_loaded')
    );

    add_action(
        'parse_request',
        fn() => my_bootstrap_log('parse_request')
    );

    add_action(
        'init',
        fn() => my_bootstrap_log('init')
    );

    add_action(
        'rest_api_init',
        fn() => my_bootstrap_log('rest_api_init')
    );

    add_action(
        'shutdown',
        fn() => my_bootstrap_log('shutdown')
    );
}