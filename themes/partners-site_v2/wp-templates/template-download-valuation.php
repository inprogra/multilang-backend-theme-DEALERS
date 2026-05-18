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
            <p>Twoja przeglądarka nie obsługuje wyświetlania plików PDF. Możesz pobrać PDF za pomocą poniższego linku:
                <a href="<?php echo esc_url($pdf_url); ?>" target="_blank">Pobierz wycenę</a>.
            </p>
        </object>
    </div>

<?php
    get_footer();
?>
