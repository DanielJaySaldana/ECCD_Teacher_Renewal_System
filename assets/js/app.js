(function () {
  const tables = document.querySelectorAll('table[data-searchable]');
  if (!tables.length) return;

  tables.forEach((table) => {
    const wrap = table.closest('.card-body') || table.parentElement;
    const input = document.createElement('input');
    input.type = 'search';
    input.className = 'form-control form-control-sm mb-2';
    input.placeholder = 'Search';
    wrap.prepend(input);

    input.addEventListener('input', () => {
      const q = input.value.trim().toLowerCase();
      table.querySelectorAll('tbody tr').forEach((tr) => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });
})();

(function(){
  const frame = document.getElementById('previewFrame');
  const title = document.getElementById('previewTitle');
  if(!frame || !title) return;

  document.querySelectorAll('a.doc-preview').forEach((a)=>{
    a.addEventListener('click', (e)=>{
      e.preventDefault();
      frame.src = a.getAttribute('href');
      title.textContent = a.dataset.title || 'Preview';
    });
  });
})();

(function(){
  document.querySelectorAll('[data-toggle-password]').forEach((btn)=>{
    btn.addEventListener('click', ()=>{
      const sel = btn.getAttribute('data-toggle-password');
      const input = sel ? document.querySelector(sel) : null;
      if(!input) return;
      if(btn.type === 'checkbox') {
        input.type = btn.checked ? 'text' : 'password';
        return;
      }
      input.type = (input.type === 'password') ? 'text' : 'password';
      btn.textContent = (input.type === 'password') ? 'Show' : 'Hide';
    });
  });
})();

(function(){
  const els = document.querySelectorAll('[data-countdown]');
  if(!els.length) return;

  function tick(){
    els.forEach((el)=>{
      const t = el.getAttribute('data-countdown');
      if(!t) return;
      const target = new Date(t.replace(' ', 'T'));
      const now = new Date();
      let ms = target.getTime() - now.getTime();
      if(isNaN(ms)) { el.textContent = ''; return; }
      if(ms < 0) ms = 0;
      const s = Math.floor(ms/1000);
      const m = Math.floor(s/60);
      const r = s%60;
      el.textContent = String(m).padStart(2,'0') + ':' + String(r).padStart(2,'0');
    });
  }
  tick();
  setInterval(tick, 1000);
})();


(function(){
  function computeAge(value){
    if(!value) return '';
    const dob = new Date(value + 'T00:00:00');
    if (Number.isNaN(dob.getTime())) return '';
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    const dayDiff = today.getDate() - dob.getDate();
    if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
      age -= 1;
    }
    return age >= 0 ? String(age) : '';
  }

  document.querySelectorAll('input[data-age-source]').forEach((input)=>{
    const target = document.querySelector(input.getAttribute('data-age-source') || '');
    if(!target) return;

    const sync = ()=>{
      target.value = computeAge(input.value);
    };

    input.addEventListener('input', sync);
    input.addEventListener('change', sync);
    sync();
  });
})();
