/* =========================================================
   CAPONE — interações da home
   ========================================================= */
(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const products = window.CAPONE_PRODUCTS || [];
  const brl = (v) => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

  /* ---------- Header fixo ---------- */
  const header = $('#siteHeader');
  const toTop = $('#toTop');
  const onScroll = () => {
    const y = window.scrollY;
    header.classList.toggle('is-stuck', y > 60);
    toTop.classList.toggle('is-visible', y > 800);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  /* ---------- Hero slider ---------- */
  const slides = $$('.hero__slide');
  const heroDots = $('.hero__dots');
  let heroIndex = 0, heroTimer;
  const goHero = (i) => {
    slides[heroIndex].classList.remove('is-active');
    heroDots.children[heroIndex].classList.remove('is-active');
    heroIndex = (i + slides.length) % slides.length;
    slides[heroIndex].classList.add('is-active');
    heroDots.children[heroIndex].classList.add('is-active');
  };
  const startHero = () => { clearInterval(heroTimer); heroTimer = setInterval(() => goHero(heroIndex + 1), 6500); };
  slides.forEach((_, i) => {
    const b = document.createElement('button');
    b.type = 'button';
    b.setAttribute('aria-label', `Slide ${i + 1}`);
    if (i === 0) b.classList.add('is-active');
    b.addEventListener('click', () => { goHero(i); startHero(); });
    heroDots.appendChild(b);
  });
  if (slides.length > 1) startHero();

  /* ---------- Card de produto (equivale a content-product.php) ---------- */
  const img = (p, n) => `https://picsum.photos/seed/capone-p${p.id}-${n}/600/840`;
  const stars = (r) => Array.from({ length: 5 }, (_, i) =>
    `<svg class="${i < Math.round(r) ? 'on' : ''}"><use href="#i-star"/></svg>`).join('');
  const card = (p) => {
    const off = p.regular ? Math.round((1 - p.price / p.regular) * 100) : 0;
    return `
      <li class="product" data-id="${p.id}">
        <div class="product__media">
          <a href="#" aria-label="${p.name}">
            <img src="${img(p, 1)}" alt="${p.name}" loading="lazy">
            <img class="img-hover" src="${img(p, 2)}" alt="" loading="lazy">
          </a>
          ${off ? `<span class="product__badge">-${off}%</span>` : ''}
          <div class="product__actions">
            <button type="button" data-wish aria-label="Adicionar à lista de desejos"><svg><use href="#i-heart"/></svg></button>
            <button type="button" aria-label="Visualização rápida"><svg><use href="#i-eye"/></svg></button>
          </div>
          <button class="product__add" type="button" data-add="${p.id}">Adicionar ao carrinho</button>
        </div>
        <h3 class="product__title"><a href="#">${p.name}</a></h3>
        <div class="product__price">${brl(p.price)}${p.regular ? `<del>${brl(p.regular)}</del>` : ''}</div>
        <div class="product__rating"><span class="stars">${stars(p.rating)}</span>${p.rating.toFixed(1)} (${p.reviews})</div>
      </li>`;
  };

  const render = (list, cat, limit) => {
    const items = products.filter((p) => p.cats.includes(cat)).slice(0, limit);
    list.innerHTML = items.map(card).join('');
  };

  /* ---------- Abas ---------- */
  $$('[data-tabs]').forEach((tabs) => {
    const list = document.getElementById(tabs.dataset.tabs);
    const limit = list.classList.contains('products--carousel') ? 8 : 4;
    const first = $('.tabs__btn.is-active', tabs) || $('.tabs__btn', tabs);
    render(list, first.dataset.cat, limit);

    tabs.addEventListener('click', (e) => {
      const btn = e.target.closest('.tabs__btn');
      if (!btn || btn.classList.contains('is-active')) return;
      $$('.tabs__btn', tabs).forEach((b) => b.classList.toggle('is-active', b === btn));
      list.classList.add('is-switching');
      setTimeout(() => {
        render(list, btn.dataset.cat, limit);
        list.scrollLeft = 0;
        list.classList.remove('is-switching');
        list.dispatchEvent(new Event('rendered'));
      }, 300);
    });
  });

  /* ---------- Carrossel (scroll-snap + dots) ---------- */
  $$('[data-carousel]').forEach((root) => {
    const track = $('.carousel__track', root);
    const dots = $('.carousel__dots', root);
    const perView = () => Math.max(1, Math.round(track.clientWidth / (track.firstElementChild?.offsetWidth || 1)));
    const pages = () => Math.max(1, Math.ceil(track.children.length / perView()));
    const build = () => {
      dots.innerHTML = '';
      const n = pages();
      if (n < 2) return;
      for (let i = 0; i < n; i++) {
        const b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', `Página ${i + 1}`);
        b.addEventListener('click', () => track.scrollTo({ left: i * track.clientWidth, behavior: 'smooth' }));
        dots.appendChild(b);
      }
      sync();
    };
    const sync = () => {
      const i = Math.round(track.scrollLeft / track.clientWidth);
      [...dots.children].forEach((d, k) => d.classList.toggle('is-active', k === i));
    };
    track.addEventListener('scroll', sync, { passive: true });
    track.addEventListener('rendered', build);
    window.addEventListener('resize', build);
    build();
  });

  /* ---------- Depoimentos ---------- */
  $$('[data-testimonials]').forEach((root) => {
    const items = $$('.testimonial', root);
    let i = 0, timer;
    const go = (n) => {
      items[i].classList.remove('is-active');
      i = (n + items.length) % items.length;
      items[i].classList.add('is-active');
    };
    const start = () => { clearInterval(timer); timer = setInterval(() => go(i + 1), 7000); };
    $('.arrow--prev', root).addEventListener('click', () => { go(i - 1); start(); });
    $('.arrow--next', root).addEventListener('click', () => { go(i + 1); start(); });
    start();
  });

  /* ---------- Parallax do banner ---------- */
  const parallax = $$('[data-parallax]');
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (parallax.length && !reduce) {
    let ticking = false;
    const update = () => {
      parallax.forEach((el) => {
        const r = el.parentElement.getBoundingClientRect();
        if (r.bottom < 0 || r.top > innerHeight) return;
        const progress = (r.top + r.height / 2 - innerHeight / 2) / innerHeight;
        el.style.transform = `translate3d(0, ${progress * -10}%, 0)`;
      });
      ticking = false;
    };
    window.addEventListener('scroll', () => { if (!ticking) { requestAnimationFrame(update); ticking = true; } }, { passive: true });
    update();
  }

  /* ---------- Reveal ao rolar ---------- */
  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
  }, { threshold: 0.15 });
  $$('.reveal').forEach((el) => io.observe(el));

  /* ---------- Drawers, busca e overlay ---------- */
  const overlay = $('#overlay');
  let openPanel = null;
  const open = (id) => {
    close();
    openPanel = document.getElementById(id);
    openPanel.classList.add('is-open');
    overlay.classList.add('is-open');
    document.body.classList.add('is-locked');
    $('input', openPanel)?.focus();
  };
  const close = () => {
    if (!openPanel) return;
    openPanel.classList.remove('is-open');
    overlay.classList.remove('is-open');
    document.body.classList.remove('is-locked');
    openPanel = null;
  };
  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-open]');
    if (opener) { e.preventDefault(); open(opener.dataset.open); return; }
    if (e.target.closest('[data-close]')) { e.preventDefault(); close(); }
  });
  overlay.addEventListener('click', close);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

  /* ---------- Mini-carrinho (simula os fragments AJAX do Woo) ---------- */
  const cart = new Map();
  const toast = $('#toast');
  let toastTimer;
  const notify = (msg) => {
    toast.textContent = msg;
    toast.classList.add('is-visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 2200);
  };
  const renderCart = () => {
    const lines = [...cart.values()];
    const qty = lines.reduce((s, l) => s + l.qty, 0);
    const total = lines.reduce((s, l) => s + l.qty * l.p.price, 0);
    $('#cartCount').textContent = qty;
    $('#miniCart').innerHTML = lines.map(({ p, qty: q }) => `
      <li>
        <img src="${img(p, 1)}" alt="">
        <div><strong>${p.name}</strong><div class="qty">${q} × ${brl(p.price)}</div></div>
        <button class="remove" type="button" data-remove="${p.id}" aria-label="Remover">×</button>
      </li>`).join('');
    $('#miniCartEmpty').hidden = qty > 0;
    $('#miniCartFoot').hidden = qty === 0;
    $('#miniCartTotal').textContent = brl(total);
  };
  document.addEventListener('click', (e) => {
    const add = e.target.closest('[data-add]');
    if (add) {
      const p = products.find((x) => x.id === +add.dataset.add);
      const line = cart.get(p.id) || { p, qty: 0 };
      line.qty++;
      cart.set(p.id, line);
      renderCart();
      notify(`${p.name} adicionado ao carrinho`);
      return;
    }
    const rm = e.target.closest('[data-remove]');
    if (rm) { cart.delete(+rm.dataset.remove); renderCart(); return; }
    const wish = e.target.closest('[data-wish]');
    if (wish) {
      wish.classList.toggle('is-active');
      notify(wish.classList.contains('is-active') ? 'Adicionado à lista de desejos' : 'Removido da lista de desejos');
    }
  });
  renderCart();
})();
