/* نرم‌افزار حسابداری - اسکریپت‌های سمت مرورگر (بدون وابستگی خارجی) */
(function () {
  'use strict';

  // ---- ثبت Service Worker برای PWA ----
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('sw.js').catch(function (err) {
        console.warn('Service worker registration failed:', err);
      });
    });
  }

  // ---- دکمه نصب برنامه ----
  var deferredPrompt = null;
  var installButtons = document.querySelectorAll('[data-install]');
  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredPrompt = event;
    installButtons.forEach(function (btn) { btn.hidden = false; });
  });
  installButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!deferredPrompt) return;
      deferredPrompt.prompt();
      deferredPrompt.userChoice.finally(function () {
        deferredPrompt = null;
        installButtons.forEach(function (b) { b.hidden = true; });
      });
    });
  });
  window.addEventListener('appinstalled', function () {
    installButtons.forEach(function (btn) { btn.hidden = true; });
  });

  // ---- تأیید قبل از عملیات حذف (فرم با data-confirm) ----
  document.addEventListener('submit', function (event) {
    var form = event.target;
    var message = form && form.getAttribute && form.getAttribute('data-confirm');
    if (message && !window.confirm(message)) {
      event.preventDefault();
    }
  });

  // ---- چاپ ----
  document.querySelectorAll('[data-print]').forEach(function (btn) {
    btn.addEventListener('click', function () { window.print(); });
  });

  // ---- منوی موبایل ----
  var navToggle = document.querySelector('[data-nav-toggle]');
  var nav = document.getElementById('main-nav');
  if (navToggle && nav) {
    navToggle.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // ---- جمع بدهکار/بستانکار زنده در فرم سند ----
  var voucherForm = document.querySelector('[data-voucher-form]');
  if (voucherForm) {
    var persianToAscii = function (s) {
      return s.replace(/[۰-۹]/g, function (d) { return String(d.charCodeAt(0) - 0x06F0); })
              .replace(/[٠-٩]/g, function (d) { return String(d.charCodeAt(0) - 0x0660); });
    };
    var parseAmount = function (value) {
      var cleaned = persianToAscii(value || '').replace(/[,\s\u066C]/g, '').replace('\u066B', '.');
      if (cleaned === '') return 0;
      var n = Number(cleaned);
      return isFinite(n) && n >= 0 ? n : NaN;
    };
    var format = function (n) {
      return isNaN(n) ? '—' : n.toLocaleString('fa-IR', { maximumFractionDigits: 2 });
    };
    var totalDebitCell = voucherForm.querySelector('[data-total-debit]');
    var totalCreditCell = voucherForm.querySelector('[data-total-credit]');
    var status = voucherForm.querySelector('[data-balance-status]');

    var recalc = function () {
      var debit = 0, credit = 0, invalid = false;
      voucherForm.querySelectorAll('[data-debit]').forEach(function (input) {
        var v = parseAmount(input.value); if (isNaN(v)) invalid = true; else debit += v;
      });
      voucherForm.querySelectorAll('[data-credit]').forEach(function (input) {
        var v = parseAmount(input.value); if (isNaN(v)) invalid = true; else credit += v;
      });
      if (totalDebitCell) totalDebitCell.textContent = format(debit);
      if (totalCreditCell) totalCreditCell.textContent = format(credit);
      if (status) {
        status.classList.remove('ok', 'bad');
        if (invalid) {
          status.textContent = 'یک یا چند مبلغ نامعتبر است.';
          status.classList.add('bad');
        } else if (debit === 0 && credit === 0) {
          status.textContent = 'ردیف‌های خالی نادیده گرفته می‌شوند. هر ردیف فقط بدهکار یا فقط بستانکار است.';
        } else if (Math.abs(debit - credit) < 0.001) {
          status.textContent = 'سند متوازن است.';
          status.classList.add('ok');
        } else {
          status.textContent = 'اختلاف: ' + format(Math.abs(debit - credit)) + ' ریال';
          status.classList.add('bad');
        }
      }
    };
    voucherForm.addEventListener('input', recalc);
    recalc();
  }
})();
