<?php
/** @var string $mode @var ?array $product @var array $crumbs */
use App\Core\Settings;
use App\Core\View;
$errors = flash('errors') ?: [];
$isQuote = $mode === 'quote';
$productId = $product['id'] ?? (int) ($_GET['product'] ?? 0);

$offices = [];
foreach ([['office1_label', 'office1_address'], ['office2_label', 'office2_address']] as [$lk, $ak]) {
    if ($addr = trim((string) Settings::get($ak, ''))) {
        $offices[] = ['label' => Settings::get($lk, ''), 'address' => $addr];
    }
}
$phones = array_values(array_filter([Settings::raw('contact_phone'), Settings::raw('contact_phone2')]));
$email  = Settings::raw('contact_email');
$hours  = Settings::get('working_hours', '');
$socials = [
  'linkedin'  => 'M4.98 3.5a2.5 2.5 0 11-.02 5 2.5 2.5 0 01.02-5zM3 8.98h4V21H3zM9 8.98h3.8v1.64h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1V21h-4v-5.32c0-1.27-.02-2.9-1.77-2.9-1.77 0-2.04 1.38-2.04 2.8V21H9z',
  'instagram' => 'M12 2.2c3.2 0 3.6 0 4.85.07 1.17.05 1.8.25 2.23.42.56.22.96.48 1.38.9.42.42.68.82.9 1.38.17.42.37 1.06.42 2.23.06 1.27.07 1.65.07 4.85s0 3.58-.07 4.85c-.05 1.17-.25 1.8-.42 2.23a3.7 3.7 0 01-.9 1.38 3.7 3.7 0 01-1.38.9c-.42.17-1.06.37-2.23.42-1.27.06-1.65.07-4.85.07s-3.58 0-4.85-.07c-1.17-.05-1.8-.25-2.23-.42a3.7 3.7 0 01-1.38-.9 3.7 3.7 0 01-.9-1.38c-.17-.42-.37-1.06-.42-2.23C2.2 15.58 2.2 15.2 2.2 12s0-3.58.07-4.85c.05-1.17.25-1.8.42-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.17 1.06-.37 2.23-.42C8.42 2.2 8.8 2.2 12 2.2zm0 3.05A6.75 6.75 0 1012 18.75 6.75 6.75 0 0012 5.25zm0 11.13a4.38 4.38 0 110-8.76 4.38 4.38 0 010 8.76zm6.9-11.4a1.58 1.58 0 11-3.15 0 1.58 1.58 0 013.15 0z',
  'facebook'  => 'M14 8.5h2.5V5h-2.5C11.8 5 10 6.8 10 9v2H8v3.5h2V22h3.5v-7.5H16l.5-3.5h-3V9c0-.3.2-.5.5-.5z',
  'youtube'   => 'M22 12s0-3.2-.4-4.7a2.6 2.6 0 00-1.8-1.8C18.3 5 12 5 12 5s-6.3 0-7.8.5a2.6 2.6 0 00-1.8 1.8C2 8.8 2 12 2 12s0 3.2.4 4.7c.2.9.9 1.6 1.8 1.8C5.7 19 12 19 12 19s6.3 0 7.8-.5a2.6 2.6 0 001.8-1.8C22 15.2 22 12 22 12zm-12 3V9l5 3z',
];
?>
<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow"><?= e($isQuote ? t('nav.quote', 'Request a Quote') : t('nav.contact', 'Contact')) ?></p>
    <h1><?= e($isQuote ? t('quote.title', 'Request a Quote') : t('contact.title', 'Get in touch')) ?></h1>
    <?= View::renderPartial('site/partials/breadcrumb', ['items' => $crumbs ?? []]) ?>
  </div>
</section>

<section class="section contact-section">
  <div class="wrap">
    <p class="section-intro"><?= e($isQuote ? t('quote.intro', 'Tell us what you need and we will prepare an offer.') : t('contact.intro', 'Get in touch with our team.')) ?></p>

    <div class="contact-layout" data-reveal>
      <aside class="contact-info">
        <div class="contact-info-inner">
          <p class="eyebrow"><?= e(Settings::get('site_name', 'Gürdoğa Kooperatifi')) ?></p>
          <h2><?= e(t('contact.info_title', 'Contact details')) ?></h2>

          <?php foreach ($offices as $o): ?>
            <div class="ci-block">
              <span class="ci-ic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              </span>
              <div>
                <?php if ($o['label']): ?><span class="ci-label"><?= e($o['label']) ?></span><?php endif; ?>
                <p><?= nl2br(e($o['address'])) ?></p>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="ci-block">
            <span class="ci-ic">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 1.9.6 2.8a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.8.6a2 2 0 0 1 1.7 2Z"/></svg>
            </span>
            <div>
              <span class="ci-label"><?= e(t('form.phone', 'Phone')) ?></span>
              <?php foreach ($phones as $ph): ?>
                <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $ph)) ?>"><?= e($ph) ?></a>
              <?php endforeach; ?>
            </div>
          </div>

          <?php if ($email): ?>
          <div class="ci-block">
            <span class="ci-ic">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6"/></svg>
            </span>
            <div>
              <span class="ci-label"><?= e(t('form.email', 'Email')) ?></span>
              <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($hours): ?>
          <div class="ci-block">
            <span class="ci-ic">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            </span>
            <div>
              <span class="ci-label"><?= e(t('contact.hours', 'Working hours')) ?></span>
              <p><?= nl2br(e($hours)) ?></p>
            </div>
          </div>
          <?php endif; ?>

          <?php $anySocial = false; foreach ($socials as $k => $d) { if (social_url(Settings::raw('social_' . $k))) { $anySocial = true; } }
          if ($anySocial): ?>
          <div class="ci-social">
            <?php foreach ($socials as $k => $d): if ($u = social_url(Settings::raw('social_' . $k))): ?>
              <a href="<?= e($u) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($k)) ?>"><svg viewBox="0 0 24 24" fill="currentColor"><path d="<?= $d ?>"/></svg></a>
            <?php endif; endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </aside>

      <div class="contact-form-card">
        <h2><?= e($isQuote ? t('quote.title', 'Request a Quote') : t('contact.form_title', 'Send us a message')) ?></h2>
        <p class="muted"><?= e(t('contact.form_intro', 'Fill in the form and we will reply as soon as possible.')) ?></p>

        <form method="post" action="<?= e(lang_url($isQuote ? 'quote' : 'contact')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= (int) $productId ?>">
          <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
          <?php if ($productId): ?>
            <p class="form-note"><?= e(t('product.request_quote', 'Request a quote for this product')) ?><?= $product ? ' — ' . e($product['sku'] ?: ('#' . $product['id'])) : '' ?></p>
          <?php endif; ?>
          <div class="grid-2">
            <label><span><?= e(t('form.name', 'Full name')) ?> *</span><input type="text" name="name" value="<?= e(old('name')) ?>" required>
              <?php if (isset($errors['name'])): ?><em class="err"><?= e($errors['name']) ?></em><?php endif; ?></label>
            <label><span><?= e(t('form.company', 'Company')) ?></span><input type="text" name="company" value="<?= e(old('company')) ?>"></label>
            <label><span><?= e(t('form.email', 'Email')) ?> *</span><input type="email" name="email" value="<?= e(old('email')) ?>" required>
              <?php if (isset($errors['email'])): ?><em class="err"><?= e($errors['email']) ?></em><?php endif; ?></label>
            <label><span><?= e(t('form.phone', 'Phone')) ?></span><input type="text" name="phone" value="<?= e(old('phone')) ?>"></label>
            <label><span><?= e(t('form.country', 'Country')) ?></span><input type="text" name="country" value="<?= e(old('country')) ?>"></label>
          </div>
          <label><span><?= e(t('form.message', 'Message')) ?> *</span><textarea name="message" rows="5" required><?= e(old('message')) ?></textarea>
            <?php if (isset($errors['message'])): ?><em class="err"><?= e($errors['message']) ?></em><?php endif; ?></label>
          <button class="btn btn-primary" type="submit"><?= e(t('form.send', 'Send')) ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></button>
        </form>
      </div>
    </div>

    <?php if ($embed = Settings::raw('map_embed')): ?>
      <div class="contact-map" data-reveal><?= $embed ?></div>
    <?php endif; ?>
  </div>
</section>
