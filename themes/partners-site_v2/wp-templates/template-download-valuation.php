<?php
/**
 * Template Name: Download Valuation
 */

    get_header(); 

    $url = explode('/', $query->request);
    $pdf_url = 'https://recalc-volvo.easyapi.space/' . $url[1] . '.pdf';
    $pdf_contents = @file_get_contents($pdf_url); 

?>

    <div class="pdf-container" style="height: 100vh; width: 100%;">
        <object data="<?php echo esc_url($pdf_url); ?>" type="application/pdf" style="height: 100vh; width: 100%;">
            <p><?php esc_html_e('Your browser does not support the display of PDF files. You can download the PDF using the link below', 'partners-site_v2'); ?>:
                <a href="<?php echo esc_url($pdf_url); ?>" target="_blank"><?php esc_html_e('Download price quote', 'partners-site_v2'); ?></a>.
            </p>
        </object>
    </div>

<?php
    get_footer();
?>
