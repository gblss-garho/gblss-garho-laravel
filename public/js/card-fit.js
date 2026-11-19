/* Scales fixed-size card previews (.pc-fit) to the available width. */
(function () {
    function fit() {
        [].forEach.call(document.querySelectorAll('.pc-fit'), function (w) {
            var c = w.firstElementChild, cw = +w.dataset.w, avail = w.clientWidth || cw, s = Math.min(1, avail / cw);
            c.style.transform = 'scale(' + s + ')';
            w.style.height = (c.offsetHeight * s) + 'px';
        });
    }
    window.addEventListener('load', fit); window.addEventListener('resize', fit);
    if (document.readyState !== 'loading') fit(); else document.addEventListener('DOMContentLoaded', fit);
})();
