<?php /* Global quote modal — opened by any [data-quote-open] trigger. */ ?>
<div class="modal" data-quote-modal aria-hidden="true">
  <div class="modal-overlay" data-quote-close></div>
  <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="quoteModalTitle">
    <button type="button" class="modal-close" data-quote-close aria-label="<?= e(t('common.close', 'Close')) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>

    <div class="modal-body">
      <p class="eyebrow"><?= e(t('nav.quote', 'Request a Quote')) ?></p>
      <h2 id="quoteModalTitle"><?= e(t('quote.title', 'Request a Quote')) ?></h2>
      <p class="modal-lead"><?= e(t('quote.intro', 'Tell us what you need and we will prepare an offer.')) ?></p>

      <form class="modal-form" method="post" action="<?= e(lang_url('quote')) ?>" data-quote-form>
        <?= csrf_field() ?>
        <input type="hidden" name="ajax" value="1">
        <input type="hidden" name="product_id" value="" data-quote-product-id>
        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px">

        <p class="form-note" data-quote-product hidden></p>

        <div class="grid-2">
          <label><span><?= e(t('form.name', 'Full name')) ?> *</span><input type="text" name="name" required><em class="err" data-err="name"></em></label>
          <label><span><?= e(t('form.company', 'Company')) ?></span><input type="text" name="company"></label>
          <label><span><?= e(t('form.email', 'Email')) ?> *</span><input type="email" name="email" required><em class="err" data-err="email"></em></label>
          <label><span><?= e(t('form.phone', 'Phone')) ?></span><input type="text" name="phone"></label>
          <label><span><?= e(t('form.country', 'Country')) ?></span><input type="text" name="country"></label>
        </div>
        <label><span><?= e(t('form.message', 'Message')) ?> *</span><textarea name="message" rows="4" required></textarea><em class="err" data-err="message"></em></label>

        <p class="modal-alert" data-quote-alert hidden></p>
        <button class="btn btn-primary" type="submit" data-quote-submit>
          <span data-quote-submit-label><?= e(t('form.send', 'Send')) ?></span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </button>
      </form>

      <div class="modal-success" data-quote-success hidden>
        <div class="modal-success-mark">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h3><?= e(t('form.thanks_title', 'Thank you')) ?></h3>
        <p data-quote-success-text><?= e(t('form.thanks', 'Thank you — we will get back to you shortly.')) ?></p>
        <button type="button" class="btn btn-ghost" data-quote-close><?= e(t('common.close', 'Close')) ?></button>
      </div>
    </div>
  </div>
</div>
