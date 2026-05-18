<?php

add_filter( 'gettext', 'modifyTranslation', 20, 2 );

function modifyTranslation( $translation, $text ) {
	// Only apply this custom translation for Polish sites
	if ( get_locale() === 'pl_PL' && $text == '<a href="%1$s" %2$s>Describe the purpose of the image%3$s</a>. Leave empty if the image is purely decorative.' ) {
		return '<strong>Alternatywny tekst obrazków ma duży wpływ na ranking SEO strony. Zalecamy wpisywać tutaj krótki opis tego co znajduje się na obrazku.</strong>';
	}
	return $translation;
}
