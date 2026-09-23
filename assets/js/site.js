(function () {
  'use strict';

  /* ---- header: mega / dropdown / language ---- */
  var openables = [];
  function register(sel) {
    document.querySelectorAll(sel).forEach(function (el) {
      var btn = el.querySelector('button');
      if (!btn) return;
      openables.push(el);
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var willOpen = !el.classList.contains('open');
        openables.forEach(function (o) {
          o.classList.remove('open');
          var b = o.querySelector('button');
          if (b) b.setAttribute('aria-expanded', 'false');
        });
        if (willOpen) { el.classList.add('open'); btn.setAttribute('aria-expanded', 'true'); }
      });
    });
  }
  register('[data-mega]');
  register('[data-dd]');
  register('[data-lang]');
  document.addEventListener('click', function () {
    openables.forEach(function (o) { o.classList.remove('open'); });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') openables.forEach(function (o) { o.classList.remove('open'); });
  });

  /* ---- mobile nav ---- */
  var mToggle = document.querySelector('[data-nav-toggle]');
  var mNav = document.querySelector('[data-mobile-nav]');
  if (mToggle && mNav) {
    mToggle.addEventListener('click', function () {
      var open = mNav.classList.toggle('open');
      mToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.style.overflow = open ? 'hidden' : '';
    });
  }

  /* ---- header shadow on scroll + back-to-top ---- */
  var header = document.querySelector('.site-header');
  var toTop = document.querySelector('[data-back-to-top]');
  var onScroll = function () {
    var y = window.scrollY;
    if (header) header.style.boxShadow = y > 10 ? '0 12px 34px -20px rgba(0,0,0,.28)' : 'none';
    if (toTop) toTop.classList.toggle('show', y > 600);
  };
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
  if (toTop) toTop.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  /* ---- hero slider ---- */
  var slider = document.querySelector('[data-slider]');
  if (slider) {
    var slides = [].slice.call(slider.querySelectorAll('.slide'));
    var cur = slider.querySelector('[data-slide-cur]');
    var i = 0, timer, DELAY = 6500, paused = false;
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };

    var play = function () {
      clearTimeout(timer);
      if (slides.length < 2 || paused) return;
      timer = setTimeout(function () { go(i + 1); }, DELAY);
    };
    var go = function (n) {
      i = (n + slides.length) % slides.length;
      slides.forEach(function (s, k) {
        var rel = (k - i + slides.length) % slides.length;
        var pos = rel === 0 ? 'pos-main' : (rel === 1 && slides.length > 1 ? 'pos-sec1' : (rel === 2 && slides.length > 2 ? 'pos-sec2' : 'pos-hidden'));
        s.classList.remove('pos-main', 'pos-sec1', 'pos-sec2', 'pos-hidden');
        s.classList.add(pos);
        s.classList.toggle('active', k === i);
      });
      if (cur) cur.textContent = pad(i + 1);
      play();
    };
    var prev = slider.querySelector('[data-slide-prev]');
    var next = slider.querySelector('[data-slide-next]');
    if (prev) prev.addEventListener('click', function () { go(i - 1); });
    if (next) next.addEventListener('click', function () { go(i + 1); });
    slider.addEventListener('mouseenter', function () { paused = true; clearTimeout(timer); });
    slider.addEventListener('mouseleave', function () { paused = false; play(); });
    go(0);
  }

  /* ---- product gallery thumbs ---- */
  var main = document.querySelector('.pg-main img');
  var mainSource = document.querySelector('.pg-main source');
  document.querySelectorAll('.pg-thumb').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var full = btn.getAttribute('data-full');
      if (main && full) {
        if (mainSource) { mainSource.removeAttribute('srcset'); }
        main.removeAttribute('srcset');
        main.src = full;
      }
      document.querySelectorAll('.pg-thumb').forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
    });
  });

  /* ---- quote modal ---- */
  var modal = document.querySelector('[data-quote-modal]');
  if (modal) {
    var mForm = modal.querySelector('[data-quote-form]');
    var mSuccess = modal.querySelector('[data-quote-success]');
    var mAlert = modal.querySelector('[data-quote-alert]');
    var mProdNote = modal.querySelector('[data-quote-product]');
    var mProdInput = modal.querySelector('[data-quote-product-id]');
    var mSubmit = modal.querySelector('[data-quote-submit]');
    var mSubmitLabel = modal.querySelector('[data-quote-submit-label]');
    var noteTpl = mProdNote ? mProdNote.getAttribute('data-tpl') || '{name}' : '';
    var lastFocus = null;

    var clearErrors = function () {
      modal.querySelectorAll('.err').forEach(function (e) { e.textContent = ''; e.classList.remove('show'); });
      modal.querySelectorAll('.invalid').forEach(function (e) { e.classList.remove('invalid'); });
      if (mAlert) { mAlert.textContent = ''; mAlert.classList.remove('show'); }
    };

    var openModal = function (productId, productName) {
      clearErrors();
      if (mForm) { mForm.hidden = false; mForm.reset(); }
      if (mSuccess) mSuccess.hidden = true;
      if (mProdInput) mProdInput.value = productId || '';
      if (mProdNote) {
        if (productName) { mProdNote.textContent = productName; mProdNote.hidden = false; }
        else { mProdNote.hidden = true; }
      }
      lastFocus = document.activeElement;
      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
      var first = modal.querySelector('input[name="name"]');
      if (first) setTimeout(function () { first.focus(); }, 250);
    };

    var closeModal = function () {
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('modal-open');
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    };

    document.addEventListener('click', function (e) {
      var trigger = e.target.closest('[data-quote-open]');
      if (trigger) {
        e.preventDefault();
        openModal(trigger.getAttribute('data-product-id'), trigger.getAttribute('data-product-name'));
        return;
      }
      if (e.target.closest('[data-quote-close]')) { closeModal(); }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
    });

    if (mForm) {
      mForm.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        mSubmit.disabled = true;
        var origLabel = mSubmitLabel ? mSubmitLabel.textContent : '';
        if (mSubmitLabel) mSubmitLabel.textContent = '…';

        fetch(mForm.action, {
          method: 'POST',
          body: new FormData(mForm),
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (r) { return r.json().catch(function () { return { ok: false, message: 'Bir hata oluştu. Lütfen tekrar deneyin.' }; }); })
          .then(function (res) {
            if (res.ok) {
              if (mForm) mForm.hidden = true;
              if (mSuccess) {
                var txt = mSuccess.querySelector('[data-quote-success-text]');
                if (txt && res.message) txt.textContent = res.message;
                mSuccess.hidden = false;
              }
            } else {
              var errs = res.errors || {};
              Object.keys(errs).forEach(function (field) {
                var box = modal.querySelector('[data-err="' + field + '"]');
                var input = mForm.querySelector('[name="' + field + '"]');
                if (box) { box.textContent = errs[field]; box.classList.add('show'); }
                if (input) input.classList.add('invalid');
              });
              if (mAlert && res.message) { mAlert.textContent = res.message; mAlert.classList.add('show'); }
            }
          })
          .catch(function () {
            if (mAlert) { mAlert.textContent = 'Gönderilemedi. İnternet bağlantınızı kontrol edin.'; mAlert.classList.add('show'); }
          })
          .finally(function () {
            mSubmit.disabled = false;
            if (mSubmitLabel) mSubmitLabel.textContent = origLabel;
          });
      });
    }
  }

  /* ---- reveal on scroll ---- */
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('[data-reveal]').forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll('[data-reveal]').forEach(function (el) { el.classList.add('in'); });
  }
})();
