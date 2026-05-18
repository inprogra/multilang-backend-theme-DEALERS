<?php
/**
 * Template Name: Pricing thanks
 */


get_header();
?>

<div class="l-not-found">
    <div class="container">
       
        <div class="l-not-found__content content">
            <div class="content__text-wrapper">
              
                <img style="transform:none!important;" src="/app/themes/partners-site/assets/public/not-found-img.png" alt="" class="content__img">
            </div>
        </div>
        <div class="l-not-found__bottom">
            <h1 class="h2 l-not-found__heading" >
                <span style="margin-bottom:20px;display:block;">Dziękujemy za potwierdzenie.</span>
                <p class="greeninfo"><span style="display:block;">&#x2713;</span> Email został potwierdzony. Wkrótce otrzymasz szacunkową wycenę Twojego pojazdu.</p>
                <br>
                <a href="/" class="content__submit-button a-button">Przejdź na&nbsp;stronę główną</a>
            </h1>
        </div>
    </div>
</div>	
<style>
p.greeninfo {
    position:relative;
    margin:0 auto;
    font-weight:400;
    line-height:20px;
    text-align:left;
    color: #69875C;
    font-size:12px;
    display:block;
    padding:12px 40px 12px 55px;
    background-color:rgba(105, 135, 92, 0.1);
    max-width:442px;
}
p.greeninfo span {
    display: block;
    position: absolute;
    top: 12px;    
    left: 40px;
}
.l-not-found a {
    font-size:14px;
    line-height:20px;
    padding:10px 24px;    
}
.l-not-found h1 {
    font-size:32px;
    line-height:36px;
    color:#444;
}
.l-not-found__bottom {
    border:0;
}
</style>
<?php

//get_footer();