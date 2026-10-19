<style>
    :root { --g:#0F5132; --g2:#1B7A4D; --gold:#DBB43C; --bg:#F7F5EE; }
    * { box-sizing: border-box; }
    body { margin:0; background:var(--bg); color:#2b2b2b; font-family: Georgia, 'Times New Roman', serif; }
    .wrap { max-width: 980px; margin: 0 auto; padding: 0 16px; }
    .top { background: var(--g); border-bottom: 4px solid var(--gold); padding: 12px 0; text-align:center; }
    .top a { color:#fff; text-decoration:none; margin: 0 9px; font-size: 15px; } .top a:hover { color: var(--gold); }
    h1 { color: var(--g); margin: 24px 0 6px; } h2 { color: var(--g); margin: 24px 0 10px; font-size: 20px; }
    .card { background:#fff; border-radius:14px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,.06); margin-bottom: 12px; }
    .row { border-bottom:1px solid #eee; padding: 8px 0; } .row:last-child { border:0; } .row b { color: var(--g); }
    .grid { display:grid; grid-template-columns: 1fr; gap: 12px; } @media (min-width:700px){ .grid { grid-template-columns: repeat(2, 1fr); } }
    .pic { width:72px; height:72px; border-radius:50%; object-fit:cover; background:var(--g2); color:#fff; display:flex; align-items:center; justify-content:center; font-size:26px; flex:none; }
    .flex { display:flex; gap:14px; align-items:center; }
    footer { background:#0a3a24; color:#ddd; text-align:center; padding: 20px 12px; margin-top: 30px; font-size: 14px; }
</style>
<div class="top">
    <a href="{{ route('home', [], false) }}">Home</a>
    <a href="{{ route('public.about', [], false) }}">About</a>
    <a href="{{ route('public.staff', [], false) }}">Staff</a>
    <a href="{{ route('public.toppers', [], false) }}">Toppers</a>
    <a href="{{ route('public.gallery', [], false) }}">Gallery</a>
    <a href="{{ route('public.lookup', [], false) }}">Parents Corner</a>
</div>
