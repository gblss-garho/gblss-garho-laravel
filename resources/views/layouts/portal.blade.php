<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name')) &middot; GBLSS Garho</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --g:#0F5132; --g2:#1B7A4D; --g3:#0a3a24; --gold:#DBB43C; --gold2:#c9a22c; --bg:#F7F5EE; --ink:#2b2b2b; --muted:#6b6b6b; --red:#9b2c2c; }
        * { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body { margin:0; background:var(--bg); color:var(--ink); font-family: 'Noto Serif', Georgia, 'Times New Roman', serif; line-height:1.55; min-height:100vh; display:flex; flex-direction:column; }
        a { color: var(--g2); }
        .wrap { width:100%; max-width: 1000px; margin: 0 auto; padding: 0 16px; }

        /* header */
        .site-head { background: linear-gradient(160deg, var(--g), var(--g3)); color:#fff; border-bottom: 4px solid var(--gold); }
        .brand { display:flex; align-items:center; gap:12px; padding: 12px 0 8px; text-decoration:none; color:#fff; }
        .brand img { width:46px; height:46px; border-radius:50%; background:#fff; object-fit:cover; border:2px solid var(--gold); flex:none; }
        .brand b { display:block; font-size: 17px; line-height:1.2; }
        .brand small { display:block; opacity:.8; font-size: 12px; font-family: ui-monospace, Menlo, Consolas, monospace; }
        .nav { display:flex; gap:8px; overflow-x:auto; padding: 4px 0 12px; scrollbar-width:none; }
        .nav::-webkit-scrollbar { display:none; }
        .nav a, .nav button { flex:none; white-space:nowrap; text-decoration:none; color:#fff; font: inherit; font-size:14px; font-weight:bold; padding: 7px 14px; border-radius:999px; border:1px solid transparent; background:transparent; cursor:pointer; }
        .nav a:hover, .nav button:hover { background: rgba(255,255,255,.12); }
        .nav a.on { background: var(--gold); color:#222; }
        .nav form { margin:0; flex:none; }
        .nav .out { background: rgba(255,255,255,.14); border-color: rgba(255,255,255,.25); }

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
        .nav button { margin-top:0; }

        /* tables */
        table { border-collapse: collapse; width:100%; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.06); margin: 8px 0 14px; }
        table[border] { border: 0; }
        th { background: var(--g); color:#fff; text-align:left; padding: 9px 10px; font-size:14px; }
        td { padding: 9px 10px; border-bottom:1px solid #eee; font-size:15px; }
        tr:nth-child(even) td { background:#fbf9f2; }
        .scroll { overflow-x:auto; }

        /* messages */
        .ok { background:#e6f4ec; color:#14532d; border:1px solid #b7dfc6; border-radius:10px; padding: 10px 14px; }
        .err { background:#fbeaea; color: var(--red); border:1px solid #efc4c4; border-radius:10px; padding: 10px 14px; }
        .ok p, .err p { margin: 2px 0; }
        video { max-width:100%; height:auto; border-radius:12px; }

        .site-foot { background: var(--g3); color:#cfd8d2; text-align:center; padding: 18px 12px; font-size: 13px; font-family: ui-monospace, Menlo, Consolas, monospace; border-top: 4px solid var(--gold); }
    </style>
</head>
<body>
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
            ['Gallery', route('public.gallery', [], false), 'gallery*'],
            ['About', route('public.about', [], false), 'about*'],
            ['Staff Login', route('login', [], false), 'login'],
        ];
    }
@endphp
<header class="site-head">
    <div class="wrap">
        <a class="brand" href="{{ route('home', [], false) }}">
            <img src="{{ $logo }}" alt="">
            <span>
                <b>{{ $schoolName }}</b>
                <small>Town Committee Garho, Taluka Ghorabari, District Thatta</small>
            </span>
        </a>
        <nav class="nav">
            @foreach ($nav as [$label, $url, $pattern])
                <a href="{{ $url }}" class="{{ request()->is($pattern) ? 'on' : '' }}">{{ $label }}</a>
            @endforeach
            @auth
                <form method="POST" action="{{ route('logout', [], false) }}">
                    @csrf
                    <button type="submit" class="out">Logout</button>
                </form>
            @endauth
        </nav>
    </div>
</header>

<main>
    <div class="wrap">
        @yield('content')
    </div>
</main>

<footer class="site-foot">{{ $schoolName }} &mdash; records maintained per General Register</footer>
</body>
</html>
