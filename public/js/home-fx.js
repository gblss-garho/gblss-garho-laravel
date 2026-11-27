/* Home page effects: scroll reveal + number counters. Safe to fail: the page works without it. */
(function () {
    'use strict';
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce || !('IntersectionObserver' in window)) { return; }

    var sel = '.hm-sec, .hm-stat, .hm-fc, .hm-m, .hm-t, .hm-dark, .hm-soft, .hm-lead, .sc-site, .sc-band';
    var els = document.querySelectorAll(sel);
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) { return; }
            e.target.classList.add('fx-in');
            io.unobserve(e.target);
            var c = e.target.querySelector && e.target.querySelector('[data-count]');
            if (c) { count(c); }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

    els.forEach(function (el, i) {
        // do not reveal an element that sits inside another revealed block (avoids double fade)
        if (el.parentElement && el.parentElement.closest('.fx-r') && !el.classList.contains('hm-stat') && !el.classList.contains('hm-fc') && !el.classList.contains('hm-m') && !el.classList.contains('hm-t') && !el.classList.contains('sc-site')) { return; }
        el.classList.add('fx-r');
        el.style.setProperty('--d', ((i % 4) * 0.08) + 's');
        io.observe(el);
    });

    // counters: <b data-count> inside .hm-stat, filled from the existing number
    document.querySelectorAll('.hm-stat b').forEach(function (b) {
        var n = parseInt(b.textContent.replace(/[^0-9]/g, ''), 10);
        if (/^\s*\d{1,5}\s*$/.test(b.textContent) && n > 0 && n < 100000 && !(n > 1800 && n < 2100)) {
            b.setAttribute('data-count', n);
        }
    });

    function count(b) {
        var to = parseInt(b.getAttribute('data-count'), 10), t0 = null, dur = 1100;
        if (!to) { return; }
        b.removeAttribute('data-count');
        function step(t) {
            if (t0 === null) { t0 = t; }
            var p = Math.min(1, (t - t0) / dur), v = Math.round(to * (1 - Math.pow(1 - p, 3)));
            b.textContent = v;
            if (p < 1) { requestAnimationFrame(step); } else { b.textContent = to; }
        }
        b.textContent = 0;
        requestAnimationFrame(step);
    }
})();
