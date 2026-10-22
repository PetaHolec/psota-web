/* PSOTA Group – front-end behaviour (no dependencies) */
(() => {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const root = document.documentElement;

  /* ---------- runtime strings per language ---------- */
  const L = document.documentElement.lang || 'cs';
  const STR = {
    cs: { closedToday: 'Dnes zavřeno', openUntil: 'Otevřeno do ', opensAt: 'Otevírá v ', closed: 'Zavřeno', fix: 'Zkontrolujte prosím označená pole a potvrďte souhlas.',
          mail: 'Otevírám váš e-mailový program. Zprávu stačí odeslat. Pokud se nic neotevřelo, napište nám na info@psota-znojmo.cz.', subj: 'Zpráva z webu PSOTA – ', name: 'Jméno', email: 'E-mail',
          close: 'Zavřít', prev: 'Předchozí', next: 'Další' },
    en: { closedToday: 'Closed today', openUntil: 'Open until ', opensAt: 'Opens at ', closed: 'Closed', fix: 'Please check the highlighted fields and confirm your consent.',
          mail: 'Opening your e-mail program. Just send the message. If nothing opened, write to us at info@psota-znojmo.cz.', subj: 'Message from the PSOTA website – ', name: 'Name', email: 'E-mail',
          close: 'Close', prev: 'Previous', next: 'Next' },
    de: { closedToday: 'Heute geschlossen', openUntil: 'Geöffnet bis ', opensAt: 'Öffnet um ', closed: 'Geschlossen', fix: 'Bitte prüfen Sie die markierten Felder und bestätigen Sie Ihre Einwilligung.',
          mail: 'Ihr E-Mail-Programm wird geöffnet. Senden Sie die Nachricht einfach ab. Falls sich nichts öffnet, schreiben Sie uns an info@psota-znojmo.cz.', subj: 'Nachricht von der PSOTA-Website – ', name: 'Name', email: 'E-Mail',
          close: 'Schließen', prev: 'Zurück', next: 'Weiter' },
    pl: { closedToday: 'Dziś zamknięte', openUntil: 'Otwarte do ', opensAt: 'Otwarcie o ', closed: 'Zamknięte', fix: 'Sprawdź zaznaczone pola i potwierdź zgodę.',
          mail: 'Otwieram Twój program pocztowy. Wystarczy wysłać wiadomość. Jeśli nic się nie otworzyło, napisz do nas na info@psota-znojmo.cz.', subj: 'Wiadomość ze strony PSOTA – ', name: 'Imię i nazwisko', email: 'E-mail',
          close: 'Zamknij', prev: 'Poprzednie', next: 'Następne' },
  };
  const T = STR[L] || STR.cs;

  /* ---------- header shrink + scroll progress ---------- */
  const header = $('[data-header]');
  const bar = $('.progress');
  const onScroll = () => {
    const y = scrollY;
    header?.classList.toggle('is-stuck', y > 30);
    const h = root.scrollHeight - innerHeight;
    bar?.style.setProperty('--p', h > 0 ? (y / h).toFixed(4) : 0);
  };
  addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- split headline into animated words ---------- */
  $$('.split-words').forEach((el) => {
    let i = 0;
    const walk = (node) => {
      [...node.childNodes].forEach((n) => {
        if (n.nodeType === 3) {
          const frag = document.createDocumentFragment();
          n.textContent.split(/(\s+)/).forEach((t) => {
            if (!t) return;
            if (/^\s+$/.test(t)) { frag.append(' '); return; }
            const w = document.createElement('span');
            w.className = 'w';
            const s = document.createElement('span');
            s.textContent = t;
            s.style.setProperty('--i', i++);
            w.append(s);
            frag.append(w);
          });
          n.replaceWith(frag);
        } else if (n.nodeType === 1 && !n.classList.contains('phero__ico')) walk(n);
      });
    };
    walk(el);
  });

  /* ---------- reveal on scroll ---------- */
  const io = 'IntersectionObserver' in window && !reduce
    ? new IntersectionObserver((es) => es.forEach((e) => {
        if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
      }), { threshold: 0.01, rootMargin: '0px 0px -6% 0px' })
    : null;
  $$('.reveal').forEach((el) => (io ? io.observe(el) : el.classList.add('is-in')));

  /* ---------- animated counters ---------- */
  const fmt = (n, sep) => (sep ? n.toLocaleString('cs-CZ').replace(/\s/g, ' ') : String(n));
  const count = (el) => {
    const to = +el.dataset.count, sep = !!el.dataset.sep, dur = 1900;
    if (reduce) { el.textContent = fmt(to, sep); return; }
    const t0 = performance.now();
    const tick = (t) => {
      const p = Math.min(1, (t - t0) / dur);
      const ease = 1 - Math.pow(1 - p, 4);
      el.textContent = fmt(Math.round(to * ease), sep);
      if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };
  const cio = new IntersectionObserver((es) => es.forEach((e) => {
    if (e.isIntersecting) { $$('[data-count]', e.target).forEach(count); cio.unobserve(e.target); }
  }), { threshold: 0.35 });
  $$('[data-stats]').forEach((s) => cio.observe(s));

  /* ---------- hero slider (ken burns + progress tabs) ---------- */
  const hero = $('[data-hero]');
  if (hero) {
    const slides = $$('.hero__slide', hero), tabs = $$('.hero__tab', hero);
    const DUR = 6500;
    hero.style.setProperty('--slide', DUR + 'ms');
    let cur = 0, timer;
    const go = (i) => {
      cur = (i + slides.length) % slides.length;
      slides.forEach((s, k) => {
        s.classList.remove('is-on');
        if (k === cur) { void s.offsetWidth; s.classList.add('is-on'); }
      });
      tabs.forEach((t, k) => {
        t.classList.remove('is-on');
        if (k === cur) { void t.offsetWidth; t.classList.add('is-on'); }
      });
    };
    const loop = () => { clearInterval(timer); if (!reduce) timer = setInterval(() => go(cur + 1), DUR); };
    tabs.forEach((t, i) => {
      t.addEventListener('mouseenter', () => { go(i); loop(); });
      t.addEventListener('focus', () => { go(i); loop(); });
    });
    document.addEventListener('visibilitychange', () => (document.hidden ? clearInterval(timer) : loop()));
    go(0); loop();
  }

  /* ---------- gentle parallax ---------- */
  const para = $$('.band__bg, [data-parallax] img, .phero__bg');
  if (para.length && !reduce) {
    let raf = 0;
    const upd = () => {
      raf = 0;
      para.forEach((el) => {
        const host = el.closest('.band, [data-parallax], .phero') || el.parentElement;
        const r = host.getBoundingClientRect();
        if (r.bottom < 0 || r.top > innerHeight) return;
        const k = (r.top + r.height / 2 - innerHeight / 2) / innerHeight;
        el.style.translate = `0 ${(-k * 40).toFixed(1)}px`;
      });
    };
    addEventListener('scroll', () => { if (!raf) raf = requestAnimationFrame(upd); }, { passive: true });
    upd();
  }

  /* ---------- timeline: road fills, truck drives ---------- */
  const tl = $('[data-timeline]');
  if (tl) {
    const upd = () => {
      const r = tl.getBoundingClientRect();
      const p = Math.min(1, Math.max(0, (innerHeight * 0.55 - r.top) / r.height));
      tl.style.setProperty('--p', p.toFixed(4));
    };
    addEventListener('scroll', upd, { passive: true }); upd();
  }

  /* ---------- opening hours ("open now") ---------- */
  const dayNames = ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'];
  const pragueNow = () => {
    const p = new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Prague', weekday: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' })
      .formatToParts(new Date()).reduce((o, x) => ((o[x.type] = x.value), o), {});
    const wd = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(p.weekday);
    return { wd, min: +p.hour * 60 + +p.minute };
  };
  const toMin = (s) => { const [h, m] = s.split(':').map(Number); return h * 60 + m; };
  const paintHours = () => {
    const { wd, min } = pragueNow();
    $$('.open-badge').forEach((b) => {
      let map; try { map = JSON.parse(b.dataset.hours); } catch { return; }
      const t = map[wd]; const label = $('b', b);
      b.classList.remove('is-open', 'is-closed');
      if (!t) { b.classList.add('is-closed'); label.textContent = T.closedToday; return; }
      const [a, z] = t.split('-').map(toMin);
      if (min >= a && min < z) { b.classList.add('is-open'); label.textContent = T.openUntil + t.split('-')[1]; }
      else if (min < a) { b.classList.add('is-closed'); label.textContent = T.opensAt + t.split('-')[0]; }
      else { b.classList.add('is-closed'); label.textContent = T.closed; }
    });
    $$('[data-hours-table] tr').forEach((tr) => tr.classList.toggle('is-today', tr.dataset.day === dayNames[wd]));
  };
  paintHours(); setInterval(paintHours, 60000);

  /* ---------- navigation ---------- */
  const burger = $('.burger');
  burger?.addEventListener('click', () => {
    const open = document.body.classList.toggle('nav-open');
    burger.setAttribute('aria-expanded', open);
  });
  $$('.nav__more').forEach((b) => b.addEventListener('click', () => {
    const li = b.closest('.has-sub');
    const on = li.classList.toggle('is-open');
    b.setAttribute('aria-expanded', on);
  }));
  addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { document.body.classList.remove('nav-open'); $$('.is-open').forEach((x) => x.classList.remove('is-open')); closeLb(); }
  });
  // keep active sub-nav pill in view on small screens
  $('.subnav .is-on:not(.subnav__home)')?.scrollIntoView({ inline: 'center', block: 'nearest' });
  $('[data-top]')?.addEventListener('click', (e) => { e.preventDefault(); scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' }); });

  /* ---------- page transition on internal links ---------- */
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') return;
    const u = new URL(a.href, location.href);
    if (u.origin !== location.origin || u.pathname === location.pathname || /\.(pdf|jpe?g|png)$/i.test(u.pathname) || a.hasAttribute('download') || a.dataset.lightbox) return;
    if (reduce) return;
    e.preventDefault();
    document.body.classList.add('is-leaving');
    setTimeout(() => (location.href = a.href), 180);
  });
  addEventListener('pageshow', (e) => { if (e.persisted) document.body.classList.remove('is-leaving'); });

  /* ---------- magnetic buttons ---------- */
  if (!reduce && matchMedia('(hover:hover)').matches) {
    $$('.magnetic').forEach((el) => {
      el.addEventListener('mousemove', (e) => {
        const r = el.getBoundingClientRect();
        el.style.transform = `translate(${((e.clientX - r.left) / r.width - 0.5) * 14}px, ${((e.clientY - r.top) / r.height - 0.5) * 10 - 2}px)`;
      });
      el.addEventListener('mouseleave', () => (el.style.transform = ''));
    });
  }

  /* ---------- menu tabs ---------- */
  $$('[data-tabs]').forEach((box) => {
    const btns = $$('[data-tab]', box), panes = $$('.menu-tabs__pane', box);
    btns.forEach((b, i) => b.addEventListener('click', () => {
      btns.forEach((x) => x.classList.toggle('is-on', x === b));
      panes.forEach((p, k) => p.classList.toggle('is-on', k === i));
    }));
  });

  /* ---------- lightbox ---------- */
  let lb, items = [], idx = 0;
  const ico = (d) => `<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${d}</svg>`;
  const build = () => {
    lb = document.createElement('div');
    lb.className = 'lb'; lb.setAttribute('role', 'dialog'); lb.setAttribute('aria-modal', 'true');
    lb.innerHTML = `<button class="lb__x" aria-label="${T.close}">${ico('<path d="M18 6 6 18M6 6l12 12"/>')}</button>
      <button class="lb__p" aria-label="${T.prev}">${ico('<path d="m15 18-6-6 6-6"/>')}</button>
      <button class="lb__n" aria-label="${T.next}">${ico('<path d="m9 18 6-6-6-6"/>')}</button>
      <img alt=""><div class="lb__c"></div>`;
    document.body.append(lb);
    lb.addEventListener('click', (e) => { if (e.target === lb || e.target.closest('.lb__x')) closeLb(); });
    $('.lb__p', lb).addEventListener('click', () => show(idx - 1));
    $('.lb__n', lb).addEventListener('click', () => show(idx + 1));
  };
  const show = (i) => {
    idx = (i + items.length) % items.length;
    const im = $('img', lb), a = items[idx];
    im.style.opacity = 0;
    im.onload = () => (im.style.opacity = 1);
    im.src = a.href; im.alt = $('img', a)?.alt || '';
    $('.lb__c', lb).textContent = `${idx + 1} / ${items.length}`;
    const multi = items.length > 1;
    $('.lb__p', lb).style.display = $('.lb__n', lb).style.display = multi ? '' : 'none';
  };
  function closeLb() { lb?.classList.remove('is-on'); }
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[data-lightbox]');
    if (!a) return;
    e.preventDefault();
    if (!lb) build();
    items = $$(`a[data-lightbox="${a.dataset.lightbox}"]`);
    show(items.indexOf(a));
    lb.classList.add('is-on');
  });
  addEventListener('keydown', (e) => {
    if (!lb?.classList.contains('is-on')) return;
    if (e.key === 'ArrowRight') show(idx + 1);
    if (e.key === 'ArrowLeft') show(idx - 1);
  });

  /* ---------- contact forms ---------- */
  $$('form[data-contact]').forEach((form) => {
    const msg = $('.cform__msg', form);
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      $$('.has-err', form).forEach((x) => x.classList.remove('has-err'));
      msg.className = 'cform__msg';
      let bad = false;
      $$('input[required],textarea[required]', form).forEach((f) => {
        const ok = f.type === 'checkbox' ? f.checked : f.type === 'email' ? /^\S+@\S+\.\S+$/.test(f.value) : f.value.trim().length > 1;
        if (!ok) { bad = true; f.closest('.field, .check')?.classList.add('has-err'); }
      });
      if (bad) { msg.textContent = T.fix; msg.classList.add('err'); return; }
      // statický web bez serveru: otevře se e-mailový program zákazníka s předvyplněnou zprávou
      const f = new FormData(form);
      const subject = T.subj + f.get('name');
      const body = `${T.name}: ${f.get('name')}\n${T.email}: ${f.get('email')}\n\n${f.get('message')}`;
      location.href = `mailto:info@psota-znojmo.cz?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
      msg.textContent = T.mail;
      msg.classList.add('ok');
    });
  });

})();
