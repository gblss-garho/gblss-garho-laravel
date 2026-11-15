<div class="lk-card" id="ai-tutor">
    <div class="lk-title">🤖 AI Tutor <span class="sd" style="font-size:14px">(پڙهائي ۾ مدد)</span></div>
    <p class="sd" style="margin:0 0 10px">Apne school ke subjects ke sawal poochein — Sindhi, Urdu, Roman Urdu ya English mein. Roz {{ config('ai_tutor.daily_limit') }} sawal. Chats school safety ke liye dekhi ja sakti hain.</p>
    <div id="ai-log" style="max-height:340px;overflow-y:auto;margin-bottom:10px"></div>
    <form id="ai-form">
        <textarea id="ai-msg" rows="3" maxlength="{{ config('ai_tutor.max_message_chars') }}" dir="auto" placeholder="Apna sawal yahan likhein…" required></textarea>
        <div class="btn-row"><button type="submit" id="ai-send">Poochein</button><span id="ai-left" class="sd"></span></div>
    </form>
</div>
<script>
(function () {
  var form = document.getElementById('ai-form'), log = document.getElementById('ai-log'), msg = document.getElementById('ai-msg'), send = document.getElementById('ai-send'), left = document.getElementById('ai-left');
  var gr = @json($student->gr_number), dob = @json($student->dob ? $student->dob->format('Y-m-d') : ''), token = @json(csrf_token()), url = @json(route('public.ai-tutor', [], false));
  function bubble(text, mine) {
    var d = document.createElement('div'); d.className = 'fb-item'; d.setAttribute('dir', 'auto');
    d.style.cssText = 'white-space:pre-wrap;line-height:1.7;' + (mine ? 'background:#FDF3D8' : '');
    d.textContent = text; log.appendChild(d); log.scrollTop = log.scrollHeight; return d;
  }
  form.addEventListener('submit', function (e) {
    e.preventDefault(); var text = msg.value.trim(); if (!text) return;
    bubble(text, true); msg.value = ''; send.disabled = true; var wait = bubble('…', false);
    fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ gr_number: gr, dob: dob, message: text }) })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
      .then(function (x) {
        if (x.ok) { wait.textContent = x.j.reply; left.textContent = 'Aaj baqi sawal: ' + x.j.remaining; }
        else { wait.textContent = (x.j && (x.j.error || (x.j.errors && Object.values(x.j.errors)[0][0]))) || 'Kuch masla aa gaya.'; }
      })
      .catch(function () { wait.textContent = 'AI Tutor se connect nahi ho saka.'; })
      .then(function () { send.disabled = false; log.scrollTop = log.scrollHeight; });
  });
})();
</script>
