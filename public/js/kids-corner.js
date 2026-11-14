(function () {
  var root = document.getElementById('kids-app');
  if (!root) return;
  var D = null;
  function shuffle(a) { a = a.slice(); for (var i = a.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)); var t = a[i]; a[i] = a[j]; a[j] = t; } return a; }
  function el(tag, cls, html) { var e = document.createElement(tag); if (cls) e.className = cls; if (html != null) e.textContent = html; return e; }
  var UR = "'Noto Nastaliq Urdu','CustomSindhi',serif", SD = "'CustomSindhi','Noto Serif',serif";
  var panel = document.getElementById('kids-panel');
  var tabs = document.querySelectorAll('[data-kids-tab]');
  var sections = { dictionary: dictionary, wordmatch: wordMatch, memory: memory, quiz: quiz, math: math };
  function show(name) {
    tabs.forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-kids-tab') === name); });
    panel.innerHTML = '';
    sections[name]();
  }
  function btn(text, fn, cls) { var b = el('button', cls || '', text); b.type = 'button'; b.addEventListener('click', fn); return b; }
  function chips(opts, cur, fn) {
    var w = el('div', 'kc-chips');
    opts.forEach(function (o) { var b = btn(o[1], function () { fn(o[0]); }, 'kc-chip' + (o[0] === cur ? ' on' : '')); w.appendChild(b); });
    return w;
  }

  function dictionary() {
    var q = '', cat = 'All';
    var cats = ['All'].concat(Array.from(new Set(D.dict.map(function (w) { return w.cat; }))));
    var inp = el('input'); inp.placeholder = 'Search a word (English, Urdu or Sindhi)…'; inp.className = 'kc-input';
    var chipBox = el('div'), count = el('p', 'kc-meta'), grid = el('div', 'kc-grid');
    function draw() {
      chipBox.innerHTML = ''; chipBox.appendChild(chips(cats.map(function (c) { return [c, c]; }), cat, function (c) { cat = c; draw(); }));
      var s = q.trim().toLowerCase();
      var list = D.dict.filter(function (w) { return (cat === 'All' || w.cat === cat) && (!s || w.en.toLowerCase().indexOf(s) >= 0 || w.ur.indexOf(q.trim()) >= 0 || w.sd.indexOf(q.trim()) >= 0); });
      count.textContent = list.length + ' word' + (list.length === 1 ? '' : 's');
      grid.innerHTML = '';
      list.forEach(function (w) {
        var c = el('div', 'kc-card');
        var top = el('div', 'kc-top'); top.appendChild(el('b', '', w.en)); top.appendChild(el('span', 'kc-tag', w.cat)); c.appendChild(top);
        var u = el('div', 'kc-ur', w.ur); u.style.fontFamily = UR; c.appendChild(u);
        var s2 = el('div', 'kc-sd', w.sd); s2.style.fontFamily = SD; c.appendChild(s2);
        grid.appendChild(c);
      });
    }
    inp.addEventListener('input', function () { q = inp.value; draw(); });
    panel.appendChild(inp); panel.appendChild(chipBox); panel.appendChild(count); panel.appendChild(grid); draw();
  }

  function wordMatch() {
    var lang = 'ur', N = 6, round, left, right, sl = null, sr = null, matched = [], mistakes = 0, busy = false;
    var box = el('div'); panel.appendChild(box);
    function fresh() {
      round = shuffle(D.dict).slice(0, N).map(function (w, i) { return { en: w.en, ur: w.ur, sd: w.sd, id: i }; });
      left = shuffle(round); right = shuffle(round); sl = sr = null; matched = []; mistakes = 0; busy = false;
    }
    function draw() {
      box.innerHTML = '';
      var bar = el('div', 'kc-bar');
      bar.appendChild(chips([['ur', 'English ↔ Urdu'], ['sd', 'English ↔ Sindhi']], lang, function (l) { lang = l; fresh(); draw(); }));
      bar.appendChild(el('span', 'kc-meta', 'Mistakes: ' + mistakes)); box.appendChild(bar);
      if (matched.length === N) {
        var d = el('div', 'kc-card kc-center'); d.appendChild(el('div', 'kc-big', '🎉')); d.appendChild(el('b', '', 'All matched!'));
        d.appendChild(el('p', 'kc-meta', 'Mistakes: ' + mistakes)); d.appendChild(btn('Play Again', function () { fresh(); draw(); }, 'kc-gold')); box.appendChild(d); return;
      }
      var g = el('div', 'kc-two');
      [['l', left, sl], ['r', right, sr]].forEach(function (col) {
        var c = el('div', 'kc-col');
        col[1].forEach(function (w) {
          var done = matched.indexOf(w.id) >= 0;
          var b = btn(col[0] === 'l' ? w.en : w[lang], function () { if (busy || done) return; if (col[0] === 'l') sl = w.id; else sr = w.id; check(); draw(); }, 'kc-opt' + (done ? ' ok' : '') + (col[2] === w.id ? ' sel' : ''));
          if (col[0] === 'r') b.style.fontFamily = lang === 'ur' ? UR : SD;
          b.disabled = done; c.appendChild(b);
        });
        g.appendChild(c);
      });
      box.appendChild(g);
    }
    function check() {
      if (sl === null || sr === null) return;
      if (sl === sr) { matched.push(sl); sl = sr = null; }
      else { mistakes++; busy = true; setTimeout(function () { sl = sr = null; busy = false; draw(); }, 550); }
    }
    fresh(); draw();
  }

  function memory() {
    var mode = 'emoji', lang = 'ur', level = 'Medium', L = { Easy: 6, Medium: 8, Hard: 12 };
    var cards, flipped, moves, locked, box = el('div'); panel.appendChild(box);
    function deck() {
      var n = L[level], cs = [];
      if (mode === 'emoji') { shuffle(D.emojis).slice(0, n).forEach(function (e) { cs.push({ k: e, f: e }, { k: e, f: e }); }); }
      else {
        var seen = {}, uniq = D.dict.filter(function (w) { if (seen[w.en]) return false; seen[w.en] = 1; return true; });
        shuffle(uniq).slice(0, n).forEach(function (w) { cs.push({ k: w.en, f: w.en }, { k: w.en, f: lang === 'ur' ? w.ur : w.sd, t: true }); });
      }
      cards = shuffle(cs).map(function (c, i) { c.id = i; c.fl = false; c.m = false; return c; }); flipped = []; moves = 0; locked = false;
    }
    function draw() {
      box.innerHTML = '';
      box.appendChild(chips([['emoji', 'Emoji'], ['word', 'Words']], mode, function (m) { mode = m; deck(); draw(); }));
      if (mode === 'word') box.appendChild(chips([['ur', 'Urdu'], ['sd', 'Sindhi']], lang, function (l) { lang = l; deck(); draw(); }));
      box.appendChild(chips([['Easy', 'Easy'], ['Medium', 'Medium'], ['Hard', 'Hard']], level, function (l) { level = l; deck(); draw(); }));
      var found = cards.filter(function (c) { return c.m; }).length / 2;
      var bar = el('div', 'kc-bar'); bar.appendChild(el('span', 'kc-meta', 'Moves: ' + moves + ' · Pairs: ' + found + ' / ' + L[level]));
      bar.appendChild(btn('↻ Restart', function () { deck(); draw(); }, 'kc-ghost')); box.appendChild(bar);
      if (found === L[level]) { var w = el('div', 'kc-card kc-center'); w.appendChild(el('div', 'kc-big', '🏆')); w.appendChild(el('b', '', 'Solved in ' + moves + ' moves!')); box.appendChild(w); }
      var g = el('div', 'kc-mem ' + (mode === 'word' ? 'w' : 'e'));
      cards.forEach(function (c) {
        var show = c.fl || c.m;
        var b = btn(show ? c.f : (mode === 'word' ? '?' : ''), function () { flip(c); }, 'kc-tile' + (show ? ' up' : '') + (c.m ? ' ok' : ''));
        if (show && c.t) b.style.fontFamily = lang === 'ur' ? UR : SD;
        g.appendChild(b);
      });
      box.appendChild(g);
    }
    function flip(c) {
      if (locked || c.fl || c.m || flipped.length === 2) return;
      c.fl = true; flipped.push(c);
      if (flipped.length === 2) {
        moves++; var a = flipped[0], b = flipped[1];
        if (a.k === b.k) { setTimeout(function () { a.m = b.m = true; flipped = []; draw(); }, 350); }
        else { locked = true; setTimeout(function () { a.fl = b.fl = false; flipped = []; locked = false; draw(); }, 800); }
      }
      draw();
    }
    deck(); draw();
  }

  function quiz() {
    var order = shuffle(D.quiz.map(function (_, i) { return i; })), idx = 0, sel = null, score = 0, box = el('div'); panel.appendChild(box);
    function draw() {
      box.innerHTML = '';
      if (idx >= order.length) {
        var d = el('div', 'kc-card kc-center'); d.appendChild(el('div', 'kc-big', '🏆')); d.appendChild(el('b', '', 'Score: ' + score + ' / ' + order.length));
        d.appendChild(btn('Play Again', function () { order = shuffle(order); idx = 0; sel = null; score = 0; draw(); }, 'kc-gold')); box.appendChild(d); return;
      }
      var q = D.quiz[order[idx]];
      box.appendChild(el('p', 'kc-meta', 'Question ' + (idx + 1) + ' / ' + order.length + ' · Score: ' + score));
      var c = el('div', 'kc-card'); var qq = el('p', 'kc-q', q.q); qq.style.fontFamily = SD; c.appendChild(qq);
      q.options.forEach(function (o, i) {
        var cls = 'kc-opt'; if (sel !== null) { if (i === q.a) cls += ' ok'; else if (i === sel) cls += ' bad'; }
        var b = btn(o, function () { if (sel !== null) return; sel = i; if (i === q.a) score++; draw(); }, cls); b.style.fontFamily = SD; b.disabled = sel !== null; c.appendChild(b);
      });
      box.appendChild(c);
      if (sel !== null) box.appendChild(btn(idx + 1 === order.length ? 'See Score' : 'Next Question', function () { sel = null; idx++; draw(); }, 'kc-gold'));
    }
    draw();
  }

  function math() {
    var level = 'easy', score = 0, att = 0, sel = null, p, box = el('div'); panel.appendChild(box);
    function ri(a, b) { return Math.floor(Math.random() * (b - a + 1)) + a; }
    function gen() {
      var a, b, op, c, t;
      if (level === 'easy') { op = Math.random() < .5 ? '+' : '-'; a = ri(1, 20); b = ri(1, 20); if (op === '-' && b > a) { t = a; a = b; b = t; } c = op === '+' ? a + b : a - b; }
      else if (level === 'medium') { op = ['+', '-', '×'][ri(0, 2)]; if (op === '×') { a = ri(2, 12); b = ri(2, 12); c = a * b; } else { a = ri(10, 60); b = ri(10, 60); if (op === '-' && b > a) { t = a; a = b; b = t; } c = op === '+' ? a + b : a - b; } }
      else { op = ['×', '÷', '+', '-'][ri(0, 3)]; if (op === '×') { a = ri(6, 15); b = ri(2, 12); c = a * b; } else if (op === '÷') { b = ri(2, 12); c = ri(2, 12); a = b * c; } else { a = ri(30, 99); b = ri(10, 99); if (op === '-' && b > a) { t = a; a = b; b = t; } c = op === '+' ? a + b : a - b; } }
      var o = [c]; while (o.length < 4) { var f = c + (ri(-6, 6) || 1); if (f >= 0 && o.indexOf(f) < 0) o.push(f); }
      return { text: a + ' ' + op + ' ' + b, c: c, o: shuffle(o) };
    }
    function draw() {
      box.innerHTML = '';
      box.appendChild(chips([['easy', 'Easy'], ['medium', 'Medium'], ['hard', 'Hard']], level, function (l) { level = l; score = att = 0; sel = null; p = gen(); draw(); }));
      box.appendChild(el('p', 'kc-meta', 'Score: ' + score + ' / ' + att));
      var c = el('div', 'kc-card kc-center'); c.appendChild(el('p', 'kc-math', p.text + ' = ?'));
      var g = el('div', 'kc-two');
      p.o.forEach(function (v) {
        var cls = 'kc-opt kc-num'; if (sel !== null) { if (v === p.c) cls += ' ok'; else if (v === sel) cls += ' bad'; }
        var b = btn(String(v), function () { if (sel !== null) return; sel = v; att++; if (v === p.c) score++; draw(); }, cls); b.disabled = sel !== null; g.appendChild(b);
      });
      c.appendChild(g); box.appendChild(c);
      if (sel !== null) box.appendChild(btn('Next Question', function () { sel = null; p = gen(); draw(); }, 'kc-gold'));
    }
    p = gen(); draw();
  }

  fetch(root.getAttribute('data-src')).then(function (r) { return r.json(); }).then(function (j) {
    D = j;
    tabs.forEach(function (b) { b.addEventListener('click', function () { show(b.getAttribute('data-kids-tab')); }); });
    show('dictionary');
  }).catch(function () { panel.textContent = 'Kids Corner load nahi ho saka. Page dobara kholein.'; });
})();
