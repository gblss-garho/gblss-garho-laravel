@extends('layouts.portal')

@section('title', 'Kids Corner')

@section('content')
    @include('pages._hero', ['pill' => 'Kids Corner — Dictionary & Mind Games'])
    <style>
        .kc-nav, .kc-chips { display:flex; gap:8px; overflow-x:auto; padding-bottom:6px; margin:0 0 14px; }
        .kc-nav button, .kc-chip { flex:none; border:0; border-radius:999px; padding:9px 16px; font-weight:600; font-size:14px; background:#EAF3EF; color:#0F5132; cursor:pointer; }
        .kc-nav button.on, .kc-chip.on { background:#0F5132; color:#fff; }
        .kc-nav button.on { background:linear-gradient(135deg,#DBB43C,#C9A227); color:#1F2A24; }
        .kc-input { width:100%; box-sizing:border-box; margin:0 0 12px; border-radius:16px; background:#F6F4EC; border:1.5px solid #E3DCC7; padding:14px 16px; font-size:16px; }
        .kc-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:12px; }
        .kc-card { background:#fff; border:1px solid #E9E2CC; border-radius:16px; padding:14px; box-shadow:0 6px 16px -8px rgba(15,81,50,.16); margin:0 0 12px; }
        .kc-center { text-align:center; } .kc-big { font-size:40px; }
        .kc-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; color:#0F5132; }
        .kc-tag { font-size:11px; background:#FDF3D8; color:#8B7B1E; border-radius:999px; padding:2px 8px; }
        .kc-ur { text-align:right; font-size:20px; direction:rtl; } .kc-sd { text-align:right; font-size:17px; color:#0F5132; direction:rtl; }
        .kc-meta { color:#8B7B1E; font-size:13px; font-weight:600; margin:6px 0 10px; }
        .kc-bar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; }
        .kc-two { display:grid; grid-template-columns:1fr 1fr; gap:10px; max-width:560px; } .kc-col { display:grid; gap:8px; align-content:start; }
        .kc-opt { display:block; width:100%; text-align:center; padding:11px 12px; border-radius:12px; border:1px solid #E3DCC7; background:#FAF6EC; color:#1F2A24; font-size:15px; cursor:pointer; margin:0 0 8px; }
        .kc-col .kc-opt { margin:0; }
        .kc-opt.sel { border-color:#C9A227; background:#FDF3D8; } .kc-opt.ok { background:#DFF3E6; border-color:#3FD17A; color:#0F5132; } .kc-opt.bad { background:#FDEEE9; border-color:#E3897A; color:#8B2E2E; }
        .kc-num { font-size:20px; font-weight:700; }
        .kc-gold { border:0; border-radius:999px; padding:12px 22px; font-weight:700; background:linear-gradient(135deg,#DBB43C,#C9A227); color:#1F2A24; cursor:pointer; margin-top:8px; }
        .kc-ghost { border:1.5px solid #0F5132; border-radius:999px; padding:6px 14px; background:#fff; color:#0F5132; cursor:pointer; }
        .kc-q { font-weight:700; color:#0F5132; margin:0 0 12px; } .kc-math { font-size:32px; font-weight:800; color:#0F5132; margin:0 0 14px; }
        .kc-mem { display:grid; gap:8px; max-width:460px; } .kc-mem.e { grid-template-columns:repeat(4,1fr); } .kc-mem.w { grid-template-columns:repeat(3,1fr); }
        .kc-tile { aspect-ratio:1; border-radius:14px; border:1px solid #E3DCC7; background:#EAF3EF; font-size:26px; padding:4px; cursor:pointer; word-break:break-word; }
        .kc-mem.w .kc-tile { font-size:14px; aspect-ratio:auto; min-height:64px; } .kc-tile.up { background:#fff; } .kc-tile.ok { background:#DFF3E6; border-color:#3FD17A; }
    </style>
    <p class="sd sd-note">English ↔ Urdu ↔ Sindhi dictionary aur mazedaar games.</p>
    <div class="kc-nav">
        <button type="button" data-kids-tab="dictionary" class="on">📖 Dictionary</button>
        <button type="button" data-kids-tab="wordmatch">🔗 Word Match</button>
        <button type="button" data-kids-tab="memory">🧠 Memory Game</button>
        <button type="button" data-kids-tab="quiz">❓ Quiz</button>
        <button type="button" data-kids-tab="math">➕ Math Puzzle</button>
    </div>
    <div id="kids-app" data-src="{{ asset('js/kids-data.json') }}"><div id="kids-panel">Loading…</div></div>
    <script src="{{ asset('js/kids-corner.js') }}" defer></script>
@endsection
