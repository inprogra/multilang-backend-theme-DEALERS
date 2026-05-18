<?php

if (!defined('ABSPATH')) {
    exit;
}

class Feed_Endpoint
{
    public function __construct()
    {
        add_action('parse_request', [$this, 'handle_request']);
    }

public function handle_request($query)
    {
        $request = $query->request;
        
        if (strpos($request, 'feeds/') === 0) {
            if (preg_match('#^feeds/([^/]+)(?:/(csv|xml))?/?$#', $request, $matches)) {
                $template_slug = $matches[1];
                $format = isset($matches[2]) ? $matches[2] : '';
                
                $blog_id = get_current_blog_id();
                
                $template = Feed_Template::get_template_by_slug($template_slug, $blog_id);
                
                if (!$template) {
                    $template = Feed_Template::get_template_by_slug($template_slug, 1);
                    if ($template) {
                        $blog_id = 1;
                    }
                }
                
                if (!$template) {
                    status_header(404);
                    $query->is_404 = false;
                    echo 'Template not found: ' . esc_html($template_slug);
                    exit;
                }

                $generator = new Feed_Generator($template, $blog_id);
                
                if ($format === 'csv' || ($format === '' && $template->get_format() !== 'xml')) {
                    $output = $generator->generate_csv();
                    $content_type = 'text/csv; charset=utf-8';
                    $filename = $template_slug . '.csv';
                } else {
                    $output = $generator->generate_xml();
                    $content_type = 'application/xml; charset=utf-8';
                    $filename = $template_slug . '.xml';
                }
                
                nocache_headers();
                
                header('Content-Type: ' . $content_type);
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($output));
                
                $query->is_404 = false;
                echo $output;
                exit;
            }
        }
    }

    public static function get_feed_url($template, $blog_id = null, $format = null)
    {
        if ($blog_id === null) {
            $blog_id = get_current_blog_id();
        }

        $blog = get_blog_details($blog_id);
        $slug = $template instanceof Feed_Template ? $template->get_slug() : $template;
        
        if ($format) {
            $url = trailingslashit($blog->path) . 'feeds/' . $slug . '/' . $format;
        } else {
            $url = trailingslashit($blog->path) . 'feeds/' . $slug;
        }
        
        return $url;
    }
}
