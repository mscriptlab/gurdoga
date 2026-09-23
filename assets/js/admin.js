(function () {
  'use strict';

  /* ---- language / content tabs ---- */
  document.querySelectorAll('[data-tt]').forEach(function (group) {
    var tabs = group.querySelectorAll('.tt-tab');
    var panels = group.querySelectorAll('.tt-panel');
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var id = tab.getAttribute('data-tt-tab');
        tabs.forEach(function (t) { t.classList.remove('active'); });
        panels.forEach(function (p) { p.classList.remove('active'); });
        tab.classList.add('active');
        var panel = group.querySelector('#' + (window.CSS && CSS.escape ? CSS.escape(id) : id));
        if (panel) panel.classList.add('active');
      });
    });
  });

  /* ---- grouped, linked language tabs (settings / translations / menus) ---- */
  document.querySelectorAll('[data-langtabs]').forEach(function (root) {
    var tabs = root.querySelectorAll('[data-langtab]');
    var scope = root.getAttribute('data-langtabs');
    var apply = function (code) {
      tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-langtab') === code); });
      document.querySelectorAll('[data-langcol="' + scope + '"]').forEach(function (col) {
        col.hidden = col.getAttribute('data-lang') !== code;
      });
    };
    tabs.forEach(function (t) {
      t.addEventListener('click', function () { apply(t.getAttribute('data-langtab')); });
    });
    if (tabs[0]) apply(tabs[0].getAttribute('data-langtab'));
  });

  /* ---- sidebar collapsible groups (state remembered) ---- */
  var store;
  try { store = JSON.parse(localStorage.getItem('gurdoga_admin_nav') || '{}'); } catch (e) { store = {}; }
  document.querySelectorAll('[data-group]').forEach(function (group) {
    var key = group.getAttribute('data-key');
    var hasActive = !!group.querySelector('a.active');
    if (store[key] === true && !hasActive) group.classList.add('collapsed');
    var label = group.querySelector('.a-group-label');
    if (label) label.addEventListener('click', function () {
      var collapsed = group.classList.toggle('collapsed');
      store[key] = collapsed;
      try { localStorage.setItem('gurdoga_admin_nav', JSON.stringify(store)); } catch (e) {}
    });
  });

  /* ---- mobile sidebar ---- */
  var side = document.querySelector('[data-side]');
  var toggle = document.querySelector('[data-side-toggle]');
  if (side && toggle) {
    toggle.addEventListener('click', function () { side.classList.toggle('open'); });
    document.querySelector('.a-content').addEventListener('click', function () { side.classList.remove('open'); });
  }

  /* ---- product image dropzone: drag-drop upload with SweetAlert feedback ---- */
  var dz = document.querySelector('[data-dropzone]');
  if (dz && window.Swal) {
    var input = dz.querySelector('#dz-input');
    var grid = document.querySelector('[data-img-grid]');
    var uploadUrl = dz.getAttribute('data-upload-url');
    var csrf = dz.getAttribute('data-csrf');

    var cellHtml = function (im) {
      var coverBit = im.isCover
        ? '<span class="pill pill-read" data-cover-badge>kapak</span>'
        : '<button type="button" class="link" data-make-cover="' + im.id + '">kapak yap</button>';
      return '' +
        '<div class="img-cell" draggable="true" data-img-id="' + im.id + '" data-path="' + im.path + '">' +
        '  <div class="img-cell-media">' +
        '    <img src="' + im.url + '" alt="">' +
        '    <a class="img-dl" href="' + im.url + '" download title="Bilgisayara indir">' +
        '      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 5-5m-5 5-5-5M4 21h16"/></svg>' +
        '    </a>' +
        '  </div>' +
        '  <div class="img-cell-act" style="font-size:.7rem;color:#8a8f9c">#' + im.id + '</div>' +
        '  <div class="img-cell-act">' + coverBit +
        '    <button type="button" class="link danger" data-delete-img="' + im.id + '">sil</button>' +
        '  </div>' +
        '</div>';
    };

    var doUpload = function (files) {
      if (!files || !files.length) return;
      var fd = new FormData();
      for (var i = 0; i < files.length; i++) fd.append('images[]', files[i]);
      fd.append('_csrf', csrf);
      dz.classList.add('busy');
      fetch(uploadUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.images && res.images.length && grid) {
            var existing = {};
            grid.querySelectorAll('[data-img-id]').forEach(function (c) { existing[c.getAttribute('data-img-id')] = true; });
            res.images.forEach(function (im) {
              if (!existing[String(im.id)]) grid.insertAdjacentHTML('beforeend', cellHtml(im));
            });
          }
          Swal.fire({
            icon: res.ok ? 'success' : 'error',
            title: res.ok ? 'Yüklendi' : 'Bazı görseller yüklenemedi',
            text: res.message,
            timer: res.ok ? 1600 : undefined,
            showConfirmButton: !res.ok
          });
        })
        .catch(function () {
          Swal.fire({ icon: 'error', title: 'Yüklenemedi', text: 'Bir hata oluştu, tekrar deneyin.' });
        })
        .finally(function () { dz.classList.remove('busy'); input.value = ''; });
    };

    input.addEventListener('change', function () { doUpload(input.files); });
    ['dragenter', 'dragover'].forEach(function (ev) {
      dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.add('drag'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('drag'); });
    });
    dz.addEventListener('drop', function (e) {
      if (e.dataTransfer && e.dataTransfer.files) doUpload(e.dataTransfer.files);
    });

    /* delete / make-cover on dynamically-inserted cells (delegated) */
    document.addEventListener('click', function (e) {
      var delBtn = e.target.closest('[data-delete-img]');
      if (delBtn) {
        var imgId = delBtn.getAttribute('data-delete-img');
        Swal.fire({
          icon: 'warning', title: 'Görsel silinsin mi?', showCancelButton: true,
          confirmButtonText: 'Sil', cancelButtonText: 'Vazgeç'
        }).then(function (r) {
          if (!r.isConfirmed) return;
          var fd = new FormData();
          fd.append('_csrf', csrf);
          fetch(dz.getAttribute('data-delete-url-tpl').replace('__ID__', imgId), {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
          }).then(function () {
            var cell = grid.querySelector('[data-img-id="' + imgId + '"]');
            if (cell) cell.remove();
            Swal.fire({ icon: 'success', title: 'Silindi', timer: 1200, showConfirmButton: false });
          }).catch(function () {
            Swal.fire({ icon: 'error', title: 'Silinemedi' });
          });
        });
        return;
      }
      var coverBtn = e.target.closest('[data-make-cover]');
      if (coverBtn) {
        var cImgId = coverBtn.getAttribute('data-make-cover');
        var cell = grid.querySelector('[data-img-id="' + cImgId + '"]');
        var path = cell ? cell.getAttribute('data-path') : '';
        var fd2 = new FormData();
        fd2.append('_csrf', csrf);
        fd2.append('cover_image', path);
        fetch(dz.getAttribute('data-cover-url'), {
          method: 'POST', body: fd2, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function () {
          grid.querySelectorAll('[data-cover-badge]').forEach(function (b) {
            var btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'link';
            btn.setAttribute('data-make-cover', b.closest('[data-img-id]').getAttribute('data-img-id'));
            btn.textContent = 'kapak yap';
            b.replaceWith(btn);
          });
          coverBtn.outerHTML = '<span class="pill pill-read" data-cover-badge>kapak</span>';
          Swal.fire({ icon: 'success', title: 'Kapak güncellendi', timer: 1200, showConfirmButton: false });
        }).catch(function () {
          Swal.fire({ icon: 'error', title: 'Güncellenemedi' });
        });
        return;
      }
    });

    /* drag-to-reorder */
    if (grid) {
      var dragEl = null;
      grid.addEventListener('dragstart', function (e) {
        var cell = e.target.closest('.img-cell');
        if (!cell) return;
        dragEl = cell;
        cell.classList.add('dragging');
      });
      grid.addEventListener('dragend', function () {
        if (dragEl) dragEl.classList.remove('dragging');
        dragEl = null;
        var order = [].slice.call(grid.querySelectorAll('[data-img-id]')).map(function (c) { return c.getAttribute('data-img-id'); });
        var fd = new FormData();
        fd.append('_csrf', csrf);
        order.forEach(function (id) { fd.append('order[]', id); });
        fetch(dz.getAttribute('data-reorder-url'), {
          method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function () {
          Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: 'Sıra kaydedildi', timer: 1200, showConfirmButton: false });
        });
      });
      grid.addEventListener('dragover', function (e) {
        e.preventDefault();
        var cell = e.target.closest('.img-cell');
        if (!cell || cell === dragEl || !dragEl) return;
        var rect = cell.getBoundingClientRect();
        var after = (e.clientX - rect.left) > rect.width / 2;
        cell.parentNode.insertBefore(dragEl, after ? cell.nextSibling : cell);
      });
    }
  }

  /* ---- rich-text "insert image" button (pages / posts / product description) ---- */
  document.querySelectorAll('[data-rte-toolbar]').forEach(function (bar) {
    var btn = bar.querySelector('[data-rte-insert-image]');
    var fileInput = bar.querySelector('[data-rte-file]');
    var textarea = document.getElementById(bar.getAttribute('data-target'));
    var uploadUrl = bar.getAttribute('data-upload-url');
    var csrf = bar.getAttribute('data-csrf');
    if (!btn || !fileInput || !textarea) return;

    btn.addEventListener('click', function () { fileInput.click(); });

    fileInput.addEventListener('change', function () {
      var file = fileInput.files[0];
      if (!file) return;
      var fd = new FormData();
      fd.append('file', file);
      fd.append('_csrf', csrf);
      btn.disabled = true;
      fetch(uploadUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.ok) {
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Yüklenemedi', text: res.message || '' });
            return;
          }
          var tag = '<img src="' + res.url + '" alt="" loading="lazy">';
          var start = textarea.selectionStart != null ? textarea.selectionStart : textarea.value.length;
          var end = textarea.selectionEnd != null ? textarea.selectionEnd : textarea.value.length;
          textarea.setRangeText(tag, start, end, 'end');
          textarea.focus();
          if (window.Swal) Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: 'Görsel eklendi', timer: 1400, showConfirmButton: false });
        })
        .catch(function () {
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Yüklenemedi', text: 'Bir hata oluştu, tekrar deneyin.' });
        })
        .finally(function () { btn.disabled = false; fileInput.value = ''; });
    });
  });
})();
