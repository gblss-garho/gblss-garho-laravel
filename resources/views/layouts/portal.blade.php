<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name')) &middot; GBLSS Garho</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:wght@500;600;700&family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @font-face { font-family: 'CustomSindhi'; src: url('{{ asset('fonts/CustomSindhi.ttf') }}') format('truetype'); font-display: swap; }
        :root { --g:#0F5132; --g2:#1B7A4D; --g3:#0a3a24; --gold:#DBB43C; --gold2:#c9a22c; --bg:#F7F5EE; --ink:#2b2b2b; --muted:#6b6b6b; --red:#9b2c2c; }
        * { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body { margin:0; background:var(--bg); color:var(--ink); font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'CustomSindhi', sans-serif; line-height:1.55; min-height:100vh; display:flex; flex-direction:column; }
        a { color: var(--g2); }
        h1, h2, h3, .brand b { font-family: 'Noto Serif', Georgia, serif; }
        body.has-bg::before { content:""; position:fixed; inset:0; z-index:-1; background: linear-gradient(#F7F5EFF2,#F7F5EFF2), var(--bgimg) center/cover no-repeat; }
        .wrap { width:100%; max-width: 1000px; margin: 0 auto; padding: 0 16px; }

        /* Sindhi / Urdu text: browser shapes it, CustomSindhi supplies the letters */
        .sd, .err, .ok { unicode-bidi: plaintext; }
        .sd { font-family: 'CustomSindhi', 'Noto Nastaliq Urdu', serif; font-size: 1.08em; line-height: 1.9; }
        .sd-note { color: var(--muted); }
        .highlights { display:grid; grid-template-columns:1fr; gap:10px; margin: 6px 0 18px; }
        @media (min-width:700px){ .highlights { grid-template-columns: repeat(2,1fr); } }
        .highlights div { background:#fff; border-radius:12px; padding:12px 14px; border-right:4px solid var(--gold); box-shadow:0 2px 10px rgba(0,0,0,.06); text-align:right; }

        /* header */
        .site-head { background: linear-gradient(160deg, #12613F, #0B3D26); color:#fff; position:sticky; top:0; z-index:40; box-shadow:0 6px 20px rgba(11,61,38,.25); }
        .site-head .top { display:flex; align-items:center; justify-content:space-between; gap:12px; padding: 12px 0 10px; }
        .brand { display:flex; align-items:center; gap:12px; text-decoration:none; color:#fff; min-width:0; }
        .brand img { width:46px; height:46px; border-radius:50%; background:#ffffff14; object-fit:cover; border:2px solid rgba(201,162,39,.55); flex:none; padding:2px; }
        .brand b { display:block; font-size: 15px; line-height:1.2; font-weight:700; }
        .brand small { display:block; opacity:.72; font-size: 11px; font-family: 'JetBrains Mono', ui-monospace, Menlo, Consolas, monospace; line-height:1.35; }
        .brand .dev { display:block; opacity:.55; font-size: 9px; letter-spacing:.5px; margin-top:2px; font-family:'JetBrains Mono', ui-monospace, monospace; }
        .head-right { display:flex; align-items:center; gap:8px; flex:none; }
        .flag { width:44px; height:44px; border-radius:50%; background:#0b3d26; border:1.5px solid rgba(201,162,39,.5); display:flex; align-items:center; justify-content:center; }
        .head-btn { display:inline-block; font: inherit; font-size:12px; font-weight:700; padding:8px 14px; border-radius:999px; background: linear-gradient(135deg,#DBB43C,#C9A227); color:#1F2A24; border:0; text-decoration:none; margin:0; box-shadow:0 3px 10px rgba(201,162,39,.4); cursor:pointer; }
        .head-btn:hover { background: linear-gradient(135deg,#DBB43C,#C9A227); transform: translateY(-1px); }
        .head-form { margin:0; }
        .ajrak { height:5px; background: linear-gradient(90deg,#C9A227,#0F5132 50%,#C9A227); }
        .nav { display:flex; gap:6px; overflow-x:auto; padding: 0 0 10px; scrollbar-width:none; }
        .nav::-webkit-scrollbar { display:none; }
        .nav a { flex:none; white-space:nowrap; text-decoration:none; color:#FAF6ECBB; font: inherit; font-size:14px; font-weight:500; padding: 6px 14px; border-radius:999px; }
        .nav a:hover { background: rgba(255,255,255,.12); color:#fff; }
        .nav a.on { background: linear-gradient(135deg,#DBB43C,#C9A227); color:#1F2A24; font-weight:600; }

        /* mobile bottom nav + More sheet (as in the old site) */
        .m-nav, .more-sheet, .more-backdrop { display:none; }
        @media (max-width:768px) {
            .nav { display:none; }
            .m-nav { display:flex; position:fixed; left:10px; right:10px; bottom:10px; z-index:70; background:rgba(6,63,49,.96); backdrop-filter:blur(14px); border:1px solid rgba(217,169,26,.22); border-radius:18px; padding:6px; box-shadow:0 14px 36px rgba(0,0,0,.22); justify-content:space-around; }
            .m-nav a, .m-nav button { color:#fff; font: inherit; font-size:10px; font-weight:600; padding:7px 9px; border-radius:12px; min-width:58px; text-align:center; text-decoration:none; background:transparent; border:0; margin:0; cursor:pointer; }
            .m-nav a.on, .m-nav button.on { background:#D9A91A; color:#17231F; }
            .m-nav i { display:block; font-style:normal; font-size:17px; line-height:1; margin-bottom:2px; }
            .more-backdrop.open { display:block; position:fixed; inset:0; background:rgba(11,44,84,.45); z-index:68; }
            .more-sheet.open { display:flex; flex-direction:column; position:fixed; left:10px; right:10px; bottom:78px; z-index:69; background:#fff; border:1px solid #E3DCC7; border-radius:18px; box-shadow:0 18px 44px rgba(0,0,0,.28); max-height:65vh; overflow:hidden; }
            .more-head { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:linear-gradient(135deg,#0F5132,#0B3D26); color:#FAF6EC; font-weight:800; font-size:14px; letter-spacing:.4px; flex-shrink:0; }
            .more-head button { background:rgba(255,255,255,.12); color:#FAF6EC; border-radius:999px; width:30px; height:30px; font-size:14px; line-height:1; padding:0; margin:0; }
            .more-list { overflow-y:auto; padding:8px; }
            .more-list a { display:flex; align-items:center; gap:12px; padding:11px 10px; border-radius:12px; font-size:13.5px; font-weight:600; color:#1F2A24; text-decoration:none; border-bottom:1px solid #F1EDE0; }
            .more-list a:last-child { border-bottom:0; }
            .more-list a.on { background:linear-gradient(135deg,#FCEFC0,#F3DFA0); color:#0B3D26; }
            .more-list .ic { width:30px; height:30px; flex:none; border-radius:9px; background:#EAF3EF; display:flex; align-items:center; justify-content:center; font-size:15px; }
            .more-list .lb { flex:1; } .more-list .ar { color:#B9AF98; font-size:16px; }
            .m-nav button:hover, .more-head button:hover { background:transparent; }
            .m-nav button.on:hover { background:#D9A91A; }
            main { padding-bottom: 86px; }
    
        /* ---- phase B: shared old-site components ---- */
        .page-title h1 { background: linear-gradient(100deg,#0F5132,#1B7A4D 45%,#C9A227); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent; font-weight:700; }
        .page-title[data-n]:before { content:attr(data-n); flex:none; width:40px; height:40px; border-radius:50%; background:#0a3a24; color:#D9A91A; display:flex; align-items:center; justify-content:center; font-family:'JetBrains Mono',ui-monospace,monospace; font-size:13px; font-weight:700; box-shadow:0 4px 12px rgba(10,58,36,.3); }
        .hero-lk { position:relative; overflow:hidden; text-align:center; color:#fff; border-radius:24px; margin: 22px 0 22px; padding: 34px 18px 38px; background: linear-gradient(160deg,#12613F,#0B3D26); box-shadow:0 12px 30px rgba(10,58,36,.25); }
        .hero-lk:after { content:""; position:absolute; left:0; right:0; bottom:0; height:6px; background:linear-gradient(90deg,#C9A227,#0F5132 50%,#C9A227); }
        .hero-lk .ring { width:94px; height:94px; margin:0 auto 14px; border-radius:50%; border:3px solid #C9A227; background:#ffffff14; padding:4px; display:flex; }
        .hero-lk .ring img { width:100%; height:100%; border-radius:50%; object-fit:cover; background:transparent; border:0; }
        .hero-lk h2 { font-family:'Noto Serif',Georgia,serif; color:#fff; font-size:30px; line-height:1.3; margin:0 0 14px; font-weight:700; }
        .hero-lk .pill { display:inline-block; background:rgba(255,255,255,.14); border-radius:999px; padding:10px 22px; font-family:'JetBrains Mono',ui-monospace,monospace; font-size:12px; letter-spacing:.14em; text-transform:uppercase; font-weight:600; }
        .hero-lk .dev { display:block; margin-top:20px; opacity:.55; font-family:'JetBrains Mono',ui-monospace,monospace; font-size:13px; letter-spacing:.12em; }
        .lk-card { background:#fff; border:1px solid #E9E2CC; border-radius:24px; padding:26px 22px; box-shadow:0 1px 2px rgba(15,81,50,.04), 0 10px 24px -8px rgba(15,81,50,.14); margin:0 0 20px; }
        .lk-title { display:flex; align-items:center; gap:12px; margin:0 0 16px; font-family:'Noto Serif','CustomSindhi',serif; font-weight:700; font-size:22px; color:#0F5132; }
        .lk-title:before { content:""; width:14px; height:14px; border-radius:50%; background:#D9A91A; flex:none; }
        .lk-card input:not([type=checkbox]):not([type=radio]):not([type=file]):not([type=submit]), .lk-card select, .lk-card textarea { max-width:none; border-radius:16px; background:#F6F4EC; border:1.5px solid #E3DCC7; padding:14px 16px; }
        .lk-card label { color:#6f6a58; letter-spacing:.14em; }
        .btn-row { display:flex; gap:12px; flex-wrap:wrap; margin-top:6px; }
        .btn-row button, .btn-row .btn { padding:13px 34px; box-shadow:0 8px 20px rgba(15,81,50,.25); }
        .btn-row .ghost { background:transparent; color:#0F5132; border:1.5px solid #0F5132; box-shadow:none; }
        button, input[type=submit], .btn { background: linear-gradient(135deg,#0F5132,#0B3D26); box-shadow:0 6px 16px rgba(15,81,50,.22); }
        button:hover, .btn:hover { background: linear-gradient(135deg,#1B7A4D,#0F5132); }
        .btn.gold, button.gold { background: linear-gradient(135deg,#DBB43C,#C9A227); }
        .btn.ghost, button.ghost { background: transparent; box-shadow:none; }
        .pane { background:#fff; border:1px solid #E9E2CC; border-radius:26px; overflow:hidden; box-shadow:0 10px 26px -8px rgba(15,81,50,.16); margin:0 0 22px; }
        .pane-h { display:flex; align-items:center; gap:16px; padding:22px; color:#fff; background:linear-gradient(135deg,#0F6B47,#2d3a1a 120%); }
        .pane-h .ico { width:60px; height:60px; border-radius:18px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center; font-size:28px; flex:none; }
        .pane-h b { display:block; font-family:'Noto Serif',serif; font-size:24px; }
        .pane-h small { display:block; font-family:'JetBrains Mono',ui-monospace,monospace; font-size:13px; letter-spacing:.16em; text-transform:uppercase; color:#e8d79a; margin-top:2px; }
        .pane-b { padding:22px; }
        .pane-b .tiles { margin:0; }
        .wel { display:flex; align-items:center; gap:16px; flex-wrap:wrap; justify-content:space-between; color:#fff; border-radius:26px; padding:22px; margin:0 0 22px; background:linear-gradient(135deg,#0F6B47,#063F31); box-shadow:0 12px 30px rgba(6,63,49,.28); }
        .wel .l { display:flex; align-items:center; gap:16px; min-width:0; }
        .wel .av { width:84px; height:84px; flex:none; border-radius:50%; padding:4px; background:linear-gradient(135deg,#C9A227,#9bd08a); }
        .wel .av img, .wel .av div { width:100%; height:100%; border-radius:50%; object-fit:cover; background:#fff; color:#0F5132; display:flex; align-items:center; justify-content:center; font-size:30px; }
        .wel h2 { color:#fff; margin:0; font-size:32px; line-height:1.15; }
        .wel .sub { font-family:'JetBrains Mono',ui-monospace,monospace; color:#e8d79a; font-size:14px; letter-spacing:.06em; margin-top:6px; }
        .wel .r { text-align:right; } .wel .r b { display:block; color:#D9A91A; font-size:18px; } .wel .r span { font-family:'Noto Serif',serif; font-size:26px; font-weight:700; }
        .tb-grid { display:grid; grid-template-columns:1fr; gap:18px; } @media (min-width:700px){ .tb-grid { grid-template-columns:repeat(3,1fr); } }
        .tb { background:#fff; border:1px solid #E9E2CC; border-radius:22px; overflow:hidden; box-shadow:0 10px 24px -8px rgba(15,81,50,.14); }
        .tb .cover { height:150px; background:#E8F1EA; display:flex; align-items:center; justify-content:center; font-size:54px; }
        .tb .cover img { width:100%; height:100%; object-fit:cover; }
        .tb .bd { padding:16px 18px 18px; } .tb .bd small { display:block; font-family:'JetBrains Mono',monospace; color:#8B7B1E; font-weight:700; font-size:13px; }
        .tb .bd b { display:block; font-family:'Noto Serif',serif; font-size:18px; color:#0F5132; margin:4px 0 12px; }
        .tb .open { display:block; text-align:center; background:#12513a; color:#fff; border-radius:12px; padding:12px; text-decoration:none; font-weight:700; }
        .fb-sum { display:flex; align-items:center; gap:12px; margin:8px 0 14px; font-weight:600; } .fb-sum .st { color:#D9A91A; font-size:30px; letter-spacing:4px; }
        .fb-item { background:#fff; border:1px solid #E9E2CC; border-radius:18px; padding:16px 18px; margin-bottom:12px; box-shadow:0 6px 16px -8px rgba(15,81,50,.16); }
        .fb-item .st { color:#D9A91A; } .fb-item small { color:var(--muted); }
        .tp { display:flex; align-items:center; gap:14px; background:#FDF1E1; border-radius:16px; padding:12px 14px; margin-bottom:10px; }

        .site-foot { padding-bottom: 90px; }
        }

        /* page */
        main { flex:1; padding-bottom: 20px; }
        .page-title { display:flex; align-items:center; gap:10px; margin: 24px 0 14px; }
        .page-title h1, main h1 { color: var(--g); font-size: 26px; margin: 22px 0 10px; }
        .page-title h1 { margin:0; }
        .page-title:after { content:""; flex:1; height:2px; background: linear-gradient(90deg, rgba(219,180,60,.55), transparent); }
        main h2 { color: var(--g); font-size: 20px; margin: 22px 0 8px; }
        main h3 { color: var(--g); font-size: 17px; margin: 18px 0 6px; }
        main p { margin: 8px 0; }

        .hero-card { background: linear-gradient(160deg, var(--g), var(--g3)); color:#fff; border-radius:18px; border-bottom:5px solid var(--gold); text-align:center; padding: 26px 16px; margin: 20px 0 18px; box-shadow:0 4px 16px rgba(0,0,0,.12); }
        .hero-card img { width:64px; height:64px; border-radius:50%; background:#fff; border:3px solid var(--gold); object-fit:cover; }
        .hero-card h2 { color:#fff; margin: 10px 0 8px; font-size: 22px; }
        .hero-card .pill { display:inline-block; background: rgba(255,255,255,.16); border-radius:999px; padding: 4px 14px; font-size:12px; letter-spacing:.08em; text-transform:uppercase; font-family: ui-monospace, Menlo, Consolas, monospace; }

        .card, .panel { background:#fff; border-radius:14px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,.07); margin: 0 0 14px; }
        .narrow { max-width: 460px; margin-left:auto; margin-right:auto; }
        .card h2:first-child { margin-top:0; }

        /* dashboard tiles */
        .tiles { display:grid; grid-template-columns: 1fr; gap: 12px; margin: 10px 0 18px; }
        @media (min-width:560px) { .tiles { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width:860px) { .tiles { grid-template-columns: repeat(3, 1fr); } }
        .tile { display:block; background:#fff; border-radius:14px; padding:16px; text-decoration:none; color: var(--ink); box-shadow:0 2px 10px rgba(0,0,0,.07); border-top:4px solid var(--gold); transition: transform .12s ease, box-shadow .12s ease; }
        .tile:hover { transform: translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,.12); }
        .tile b { display:block; color: var(--g); font-size: 17px; }
        .tile span { display:block; color: var(--muted); font-size: 13px; margin-top:3px; }
        .who { color: var(--muted); font-size: 14px; margin: 0 0 6px; }

        /* forms */
        label { display:block; font-size: 12px; letter-spacing:.06em; text-transform:uppercase; color: var(--muted); font-family: ui-monospace, Menlo, Consolas, monospace; margin: 12px 0 4px; }
        label input[type=checkbox] { margin-right:6px; }
        input:not([type=checkbox]):not([type=radio]):not([type=file]):not([type=submit]), select, textarea {
            width:100%; max-width: 520px; padding: 10px 12px; border:1.5px solid #d9d4c3; border-radius:10px; background:#fff; font: inherit; color: var(--ink);
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--g2); box-shadow: 0 0 0 3px rgba(27,122,77,.15); }
        textarea { min-height: 96px; }
        button, input[type=submit], .btn { display:inline-block; background: var(--g); color:#fff; border:0; border-radius:999px; padding: 10px 22px; font: inherit; font-weight:bold; cursor:pointer; text-decoration:none; margin-top: 10px; }
        button:hover, .btn:hover { background: var(--g2); }
        button:disabled { opacity:.55; cursor:not-allowed; }
        .btn.gold, button.gold { background: var(--gold); color:#222; } .btn.gold:hover, button.gold:hover { background: var(--gold2); }
        .btn.ghost, button.ghost { background: transparent; color: var(--g); border:1.5px solid var(--g); }

        /* tables */
        table { border-collapse: collapse; width:100%; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.06); margin: 8px 0 14px; }
        table[border] { border: 0; }
        th { background: var(--g); color:#fff; text-align:left; padding: 9px 10px; font-size:14px; }
        td { padding: 9px 10px; border-bottom:1px solid #eee; font-size:15px; }
        tr:nth-child(even) td { background:#fbf9f2; }
        .scroll { overflow-x:auto; }
        main > .wrap { overflow-x:auto; }
        main hr { border:0; border-top:1px solid #e3dfd0; margin: 18px 0; }
        main ul { padding-left: 20px; }
        main form { margin: 6px 0; }

        /* messages */
        .ok { background:#e6f4ec; color:#14532d; border:1px solid #b7dfc6; border-radius:10px; padding: 10px 14px; }
        .err { background:#fbeaea; color: var(--red); border:1px solid #efc4c4; border-radius:10px; padding: 10px 14px; }
        .ok p, .err p { margin: 2px 0; }
        video { max-width:100%; height:auto; border-radius:12px; }


        /* public pages */
        .notice-bar { display:flex; align-items:center; background:#7A1226; color:#fff; height:46px; border-radius:12px; overflow:hidden; margin: 18px 0 20px; }
        .notice-bar .lbl { flex:none; background:#5A0D1B; height:100%; display:flex; align-items:center; padding:0 12px; font-weight:700; font-size:12px; letter-spacing:1px; font-family:'JetBrains Mono', ui-monospace, monospace; }
        .notice-bar .run { flex:1; overflow:hidden; white-space:nowrap; height:100%; display:flex; align-items:center; }
        .notice-bar .run div { display:inline-block; padding-left:100%; animation: tick 40s linear infinite; font-size:14px; font-weight:600; }
        .notice-bar:hover .run div { animation-play-state: paused; }
        @keyframes tick { to { transform: translateX(-100%); } }
        .hero-card .ur { font-size:22px; opacity:.92; }
        .hero-card .motto { color: var(--gold); font-style:italic; margin: 6px 0 14px; }
        .hero-card .btn { margin: 4px; } .hero-card .btn.ghost { color: var(--gold); border-color: var(--gold); }
        .stats { display:grid; grid-template-columns: repeat(2,1fr); gap:12px; margin: 0 0 20px; }
        @media (min-width:700px){ .stats { grid-template-columns: repeat(4,1fr); } }
        .stat { background:#fff; border-radius:14px; padding:18px 10px; text-align:center; box-shadow:0 2px 10px rgba(0,0,0,.07); border-top:4px solid var(--gold); }
        .stat b { display:block; font-size:30px; color: var(--g); } .stat span { font-size:13px; color: var(--muted); }
        .bar { display:flex; align-items:center; gap:8px; margin:8px 0; } .bar i { display:block; height:12px; background: var(--g2); border-radius:6px; }
        .row { border-bottom:1px solid #eee; padding:8px 0; } .row:last-child { border:0; } .row small { color: var(--muted); } .row b { color: var(--g); }
        .grid { display:grid; grid-template-columns:1fr; gap:12px; } @media (min-width:700px){ .grid { grid-template-columns: repeat(2,1fr); } }
        .grid .card { margin:0; }
        .flex { display:flex; gap:14px; align-items:center; }
        .pic { width:72px; height:72px; border-radius:50%; object-fit:cover; background: var(--g2); color:#fff; display:flex; align-items:center; justify-content:center; font-size:26px; flex:none; border:3px solid var(--gold); }
        .photos { display:grid; grid-template-columns: repeat(2,1fr); gap:8px; } @media (min-width:700px){ .photos { grid-template-columns: repeat(4,1fr); } }
        .photos img { width:100%; height:120px; object-fit:cover; border-radius:10px; display:block; }
        .photos figcaption { font-size:13px; color: var(--muted); padding: 3px 2px; } .photos figure { margin:0; }
        .stars { display:flex; flex-direction:row-reverse; justify-content:flex-end; gap:4px; }
        .stars input { position:absolute; opacity:0; width:0; height:0; }
        .stars label { font-size:34px; line-height:1; color:#d9d4c3; cursor:pointer; margin:0; text-transform:none; letter-spacing:0; font-family:inherit; }
        .stars input:checked ~ label, .stars label:hover, .stars label:hover ~ label { color: var(--gold); }
        .stars input:focus-visible + label { outline:2px solid var(--g2); }

        .site-foot { background: var(--g3); color:#cfd8d2; text-align:center; padding: 18px 12px; font-size: 13px; font-family: ui-monospace, Menlo, Consolas, monospace; border-top: 4px solid var(--gold); }
    </style>
</head>
@php $info = \Illuminate\Support\Facades\DB::table('school_info')->first(); @endphp
<body class="{{ !empty($info->school_photo_url) ? 'has-bg' : '' }}"@if (!empty($info->school_photo_url)) style="--bgimg:url('{{ $info->school_photo_url }}')"@endif>
@php
    $u = auth()->user();
    $role = $u->role ?? null;
    $info = \Illuminate\Support\Facades\DB::table('school_info')->first();
    $schoolName = $info->name ?? 'Government Boys Lower Secondary School Garho';
    // Put the school logo at public/images/logo.png to use it here; otherwise the emblem is shown.
    $logo = file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg');
    $nav = [];
    if ($role === 'teacher') {
        $nav = [
            ['Dashboard', route('teacher.dashboard', [], false), 'teacher/dashboard*'],
            ['Attendance', route('teacher.attendance.index', [], false), 'teacher/attendance*'],
            ['Chehra Enroll', route('teacher.face.enroll', [], false), 'teacher/face*'],
            ['QR Attendance', route('teacher.qr.scan', [], false), 'teacher/qr*'],
            ['Absentees', route('teacher.absentees', [], false), 'teacher/absentees*'],
            ['Homework', route('teacher.homework.index', [], false), 'teacher/homework*'],
            ['Chhutti', route('teacher.leave.index', [], false), 'teacher/leave*'],
        ];
    } elseif ($role === 'admin') {
        $nav = [
            ['Dashboard', route('admin.dashboard', [], false), 'admin/dashboard*'],
            ['Students', route('admin.students.index', [], false), 'admin/students*'],
            ['Teachers', route('admin.teachers.index', [], false), 'admin/teachers*'],
            ['Results', route('admin.results.index', [], false), 'admin/results*'],
            ['ID Cards', route('admin.id-cards.index', [], false), 'admin/id-cards*'],
            ['Admit Cards', route('admin.admit-cards.index', [], false), 'admin/admit-cards*'],
            ['Settings', route('admin.settings.edit', [], false), 'admin/settings*'],
        ];
    } else {
        $nav = [
            ['Home', route('home', [], false), '/'],
            ['Parents Corner', route('public.lookup', [], false), 'parent-lookup*'],
            ['Staff', route('public.staff', [], false), 'staff*'],
            ['Results', route('public.results', [], false), 'results*'],
            ['Toppers', route('public.toppers', [], false), 'toppers*'],
            ['ID Card', route('public.id-cards', [], false), 'id-cards*'],
            ['Gallery', route('public.gallery', [], false), 'gallery*'],
            ['Textbooks', route('public.textbooks', [], false), 'textbooks*'],
            ['About', route('public.about', [], false), 'about*'],
            ['Feedback', route('public.feedback', [], false), 'feedback*'],
        ];
    }
@endphp
@php
    $dash = $role === 'teacher' ? route('teacher.dashboard', [], false) : ($role === 'admin' ? route('admin.dashboard', [], false) : route('login', [], false));
    $pub = [
        ['Staff', route('public.staff', [], false), 'staff*', '🧑‍🏫'],
        ['Results', route('public.results', [], false), 'results*', '📊'],
        ['Toppers', route('public.toppers', [], false), 'toppers*', '🏆'],
        ['ID Card', route('public.id-cards', [], false), 'id-cards*', '🪪'],
        ['Gallery', route('public.gallery', [], false), 'gallery*', '🖼️'],
        ['Text Books', route('public.textbooks', [], false), 'textbooks*', '📚'],
        ['About', route('public.about', [], false), 'about*', 'ℹ️'],
        ['Feedback', route('public.feedback', [], false), 'feedback*', '📝'],
    ];
    if ($role === 'admin') {
        $bottom = [
            ['Home', route('home', [], false), '/', '⌂'],
            ['Dashboard', $dash, 'admin/dashboard*', '▦'],
            ['Students', route('admin.students.index', [], false), 'admin/students*', '👥'],
            ['Results', route('admin.results.index', [], false), 'admin/results*', '🏆'],
        ];
        $more = [
            ['Teachers', route('admin.teachers.index', [], false), 'admin/teachers*', '🧑‍🏫'],
            ['ID Cards', route('admin.id-cards.index', [], false), 'admin/id-cards*', '🪪'],
            ['Staff ID Cards', route('admin.staff-id-cards.index', [], false), 'admin/staff-id-cards*', '🪪'],
            ['Admit Cards', route('admin.admit-cards.index', [], false), 'admin/admit-cards*', '🎫'],
            ['Results History', route('admin.results.history', [], false), 'admin/results-history*', '📊'],
            ['Notices & Calendar', route('admin.content.index', [], false), 'admin/content*', '📅'],
            ['Leaving Certificates', route('admin.leaving-certificates.index', [], false), 'admin/leaving-certificates*', '📄'],
            ['Promotions', route('admin.promotions.history', [], false), 'admin/promotions*', '🎓'],
            ['Book Bank', route('admin.bookbank.index', [], false), 'admin/bookbank*', '📖'],
            ['Teacher Leave', route('admin.leave.index', [], false), 'admin/leave*', '🏖️'],
            ['Gallery & Textbooks', route('admin.media.index', [], false), 'admin/media*', '🖼️'],
            ['School Settings', route('admin.settings.edit', [], false), 'admin/settings*', '⚙️'],
            ['Security Logs', route('admin.security.index', [], false), 'admin/security*', '🔐'],
        ];
    } else {
        $bottom = [
            ['Home', route('home', [], false), '/', '⌂'],
            ['Dashboard', $dash, 'teacher/dashboard*', '▦'],
            ['Homework', $role === 'teacher' ? route('teacher.homework.index', [], false) : route('login', [], false), 'teacher/homework*', '▤'],
            ['Parents', route('public.lookup', [], false), 'parent-lookup*', '♧'],
        ];
        $more = [];
        if ($role === 'teacher') {
            $more = [
                ['Attendance', route('teacher.attendance.index', [], false), 'teacher/attendance*', '⏱️'],
                ['Chehra Enroll', route('teacher.face.enroll', [], false), 'teacher/face*', '🔒'],
                ['QR Attendance', route('teacher.qr.scan', [], false), 'teacher/qr*', '📷'],
                ['Absentees', route('teacher.absentees', [], false), 'teacher/absentees*', '📋'],
                ['Chhutti', route('teacher.leave.index', [], false), 'teacher/leave*', '🏖️'],
            ];
        }
    }
    $more = array_merge($more, $pub);
    if (! $u) { $more[] = ['Staff Login', route('login', [], false), 'login', '🔑']; }
    $moreActive = collect($more)->contains(fn ($m) => request()->is($m[2]));
@endphp
<header class="site-head">
    <div class="wrap">
        <div class="top">
            <a class="brand" href="{{ route('home', [], false) }}">
                <img src="{{ $logo }}" alt="">
                <span>
                    <b>{{ $schoolName }}</b>
                    <small>Town Committee Garho, Taluka Ghorabari, District Thatta</small>
                    <small class="dev">Designed &amp; Developed by Sir Waris Ghafoor</small>
                </span>
            </a>
            <div class="head-right">
                <span class="flag" aria-hidden="true"><svg width="26" height="26" viewBox="0 0 24 24"><path d="M15.5 4.2a8 8 0 1 0 0 15.6A9.2 9.2 0 0 1 15.5 4.2z" fill="#F7F5EE"/><path d="M18.2 8.6l.7 1.6 1.7.2-1.3 1.2.4 1.7-1.5-.9-1.5.9.4-1.7-1.3-1.2 1.7-.2z" fill="#F7F5EE"/></svg></span>
                @auth
                    <form class="head-form" method="POST" action="{{ route('logout', [], false) }}">
                        @csrf
                        <button type="submit" class="head-btn">&#10003; Staff &mdash; Logout</button>
                    </form>
                @else
                    <a class="head-btn" href="{{ route('login', [], false) }}">Staff Login</a>
                @endauth
            </div>
        </div>
        <nav class="nav">
            @foreach ($nav as [$label, $url, $pattern])
                <a href="{{ $url }}" class="{{ request()->is($pattern) ? 'on' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>
    </div>
    <div class="ajrak"></div>
</header>

<div class="more-backdrop" id="moreBackdrop"></div>
<div class="more-sheet" id="moreSheet" role="dialog" aria-label="More">
    <div class="more-head"><span>More</span><button type="button" id="moreClose" aria-label="Close">&#10005;</button></div>
    <div class="more-list">
        @foreach ($more as [$label, $url, $pattern, $icon])
            <a href="{{ $url }}" class="{{ request()->is($pattern) ? 'on' : '' }}"><span class="ic">{{ $icon }}</span><span class="lb">{{ $label }}</span><span class="ar">&rsaquo;</span></a>
        @endforeach
    </div>
</div>
<nav class="m-nav" aria-label="Main">
    @foreach ($bottom as [$label, $url, $pattern, $icon])
        <a href="{{ $url }}" class="{{ request()->is($pattern) ? 'on' : '' }}"><i>{{ $icon }}</i>{{ $label }}</a>
    @endforeach
    <button type="button" id="moreBtn" class="{{ $moreActive ? 'on' : '' }}"><i>&#8943;</i>More</button>
</nav>
<script>
(function () {
    var sheet = document.getElementById('moreSheet'), back = document.getElementById('moreBackdrop');
    function set(open) { sheet.classList.toggle('open', open); back.classList.toggle('open', open); }
    document.getElementById('moreBtn').addEventListener('click', function () { set(!sheet.classList.contains('open')); });
    document.getElementById('moreClose').addEventListener('click', function () { set(false); });
    back.addEventListener('click', function () { set(false); });
})();
</script>

<main>
    <div class="wrap">
        @yield('content')
    </div>
</main>

<footer class="site-foot">{{ $schoolName }} &mdash; records maintained per General Register</footer>
</body>
</html>
