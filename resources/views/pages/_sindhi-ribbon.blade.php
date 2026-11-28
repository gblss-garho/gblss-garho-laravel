{{-- Sindhi Culture Day: slim greeting ribbon + heritage ticker. The ajrak patterns on tabs/cards come from body.sc-theme (see home-fx.css). --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <defs>
        <symbol id="sc-topi" viewBox="0 0 120 90">
            <path d="M8 74 Q6 22 60 16 Q114 22 112 74 L86 74 A26 26 0 0 0 34 74 Z" fill="#A31D35" stroke="#F2C94C" stroke-width="2.5"/>
            <path d="M34 72 A26 26 0 0 1 86 72" fill="none" stroke="#12204F" stroke-width="8" stroke-linecap="round"/>
            <path d="M34 72 A26 26 0 0 1 86 72" fill="none" stroke="#F2C94C" stroke-width="2" stroke-dasharray="3 4"/>
            <g fill="#fff" stroke="#F2C94C" stroke-width="1.2"><circle cx="60" cy="34" r="6"/><circle cx="42" cy="44" r="4.5"/><circle cx="78" cy="44" r="4.5"/><circle cx="26" cy="58" r="3.6"/><circle cx="94" cy="58" r="3.6"/></g>
            <g fill="#12204F"><circle cx="60" cy="34" r="2.2"/><circle cx="42" cy="44" r="1.7"/><circle cx="78" cy="44" r="1.7"/></g>
        </symbol>
        <symbol id="sc-mohenjo" viewBox="0 0 100 70"><g fill="currentColor"><rect x="8" y="58" width="84" height="6"/><rect x="16" y="50" width="68" height="8"/><rect x="26" y="42" width="48" height="8"/><rect x="40" y="26" width="20" height="16"/><path d="M42 26 Q50 10 58 26 Z"/><rect x="48.5" y="6" width="3" height="9"/></g></symbol>
        <symbol id="sc-makli" viewBox="0 0 100 70"><g fill="currentColor"><rect x="6" y="58" width="88" height="6"/><rect x="12" y="38" width="22" height="20"/><path d="M12 38 Q23 18 34 38 Z"/><rect x="40" y="32" width="24" height="26"/><path d="M40 32 Q52 8 64 32 Z"/><rect x="70" y="40" width="20" height="18"/><path d="M70 40 Q80 22 90 40 Z"/></g></symbol>
        <symbol id="sc-fort" viewBox="0 0 100 70"><g fill="currentColor"><rect x="4" y="30" width="92" height="30"/><rect x="2" y="16" width="16" height="44" rx="3"/><rect x="82" y="16" width="16" height="44" rx="3"/><rect x="40" y="38" width="20" height="22" rx="10" fill="#12204F"/></g></symbol>
        <symbol id="sc-kotdiji" viewBox="0 0 100 70"><g fill="currentColor"><rect x="10" y="26" width="80" height="34"/><rect x="6" y="14" width="18" height="46"/><rect x="76" y="14" width="18" height="46"/><rect x="38" y="8" width="24" height="52"/><path d="M38 8 L50 0 L62 8 Z"/><rect x="45" y="40" width="10" height="20" rx="5" fill="#12204F"/></g></symbol>
        <symbol id="sc-masjid" viewBox="0 0 100 70"><g fill="currentColor"><rect x="6" y="58" width="88" height="6"/><rect x="10" y="40" width="80" height="18"/><path d="M12 40 Q22 22 32 40 Z"/><path d="M56 40 Q66 22 76 40 Z"/><path d="M32 40 Q50 6 68 40 Z"/></g></symbol>
        <symbol id="sc-bhit" viewBox="0 0 100 70"><g fill="currentColor"><rect x="6" y="58" width="88" height="6"/><rect x="26" y="36" width="48" height="22"/><path d="M28 36 Q50 4 72 36 Z"/><rect x="14" y="24" width="7" height="34"/><rect x="79" y="24" width="7" height="34"/><path d="M13 24 L17.5 14 L22 24 Z"/><path d="M78 24 L82.5 14 L87 24 Z"/></g></symbol>
    </defs>
</svg>

<div class="sc-rib" lang="sd">
    <div class="sc-rib-in">
        <svg class="t" aria-label="Sindhi topi"><use href="#sc-topi"/></svg>
        <div class="sc-rib-tx">
            <span class="sd" dir="rtl">سنڌي ثقافت جو ڏينهن مبارڪ!</span>
            <small>Happy Sindhi Culture Day &middot; Ajrak &middot; Topi &middot; Sindh</small>
        </div>
        <div class="sc-day"><b>6</b><span>DEC</span></div>
    </div>
    <div class="sc-tick" aria-label="Historical sites of Sindh">
        <div class="sc-tick-run">
            @for ($r = 0; $r < 2; $r++)
                <span class="sc-tk"><svg><use href="#sc-mohenjo"/></svg><b>Mohenjo-daro</b><span class="sd" dir="rtl">موئن جو دڙو</span><i>&#9670;</i></span>
                <span class="sc-tk"><svg><use href="#sc-makli"/></svg><b>Makli Necropolis</b><span class="sd" dir="rtl">مڪلي</span><i>&#9670;</i></span>
                <span class="sc-tk"><svg><use href="#sc-fort"/></svg><b>Ranikot Fort</b><span class="sd" dir="rtl">رني ڪوٽ</span><i>&#9670;</i></span>
                <span class="sc-tk"><svg><use href="#sc-kotdiji"/></svg><b>Kot Diji Fort</b><span class="sd" dir="rtl">ڪوٽ ڏيجي</span><i>&#9670;</i></span>
                <span class="sc-tk"><svg><use href="#sc-masjid"/></svg><b>Shah Jahan Mosque</b><span class="sd" dir="rtl">شاهجهان مسجد</span><i>&#9670;</i></span>
                <span class="sc-tk"><svg><use href="#sc-bhit"/></svg><b>Bhit Shah</b><span class="sd" dir="rtl">ڀٽ شاهه</span><i>&#9670;</i></span>
            @endfor
        </div>
    </div>
</div>
