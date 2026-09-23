<?php /** 404 */ ?>
<section class="section center" style="padding:clamp(90px,16vw,180px) 0">
  <div class="wrap">
    <p class="eyebrow" style="justify-content:center">404</p>
    <h1><?= e(t('error.404_title', 'Page not found')) ?></h1>
    <p class="lead"><?= e(t('error.404_text', 'The page you are looking for does not exist.')) ?></p>
    <p style="margin-top:24px"><a class="btn btn-primary" href="<?= e(lang_url('')) ?>"><?= e(t('nav.home', 'Home')) ?></a></p>
  </div>
</section>
