<?php

if ($_SERVER["REQUEST_URI"] == '/event-register/' || $_SERVER["REQUEST_URI"] == '/event-register') {

?>
<?php
get_header();
?>

<div class="l-not-found">
    <div class="container">
       
        <div class="l-not-found__content content">
            <div class="content__text-wrapper">
              
                <img style="transform:none!important;" src="https://volvocarwarszawa.pl/app/themes/partners-site/assets/public/not-found-img.png" alt="" class="content__img">
            </div>
        </div>
        <div class="l-not-found__bottom">
            <h1 class="h2 l-not-found__heading" >
                <span style="margin-bottom:20px;display:block;">Dziękujemy za rejestrację na wydarzenie.<br/>Wkrótce prześlemy Twoją wejściówkę.</span>
                <br>
                <a href="/" style="color:#000;">Przejdź na&nbsp;stronę główną</a>
            </h1>
        </div>
    </div>
</div>	



<?php	
} else {


	get_header();

while (have_posts()) {
    the_post();
    the_content();
}

get_footer();
	
}
