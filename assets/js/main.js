/* =========================================================
   CONTRAVENÇÃO — interações do tema
   ========================================================= */
(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const CV = window.CV || { i18n: {} };
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Toast ---------- */
  const toast = $('#toast');
  let toastTimer;
  const notify = (msg) => {
    if (!toast || !msg) return;
    toast.textContent = msg;
    toast.classList.add('is-visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 2400);
  };

  /* ---------- Header fixo ---------- */
  const header = $('#siteHeader');
  const toTop = $('#toTop');
  const onScroll = () => {
    const y = window.scrollY;
    header?.classList.toggle('is-stuck', y > 60);
    toTop?.classList.toggle('is-visible', y > 800);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  toTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }));

  /* ---------- Hero slider ---------- */
  const slides = $$('.hero__slide');
  const heroDots = $('.hero__dots');
  if (slides.length > 1 && heroDots) {
    let heroIndex = 0, heroTimer;
    const goHero = (i) => {
      slides[heroIndex].classList.remove('is-active');
      heroDots.children[heroIndex].classList.remove('is-active');
      heroDots.children[heroIndex].setAttribute('aria-current', 'false');
      heroIndex = (i + slides.length) % slides.length;
      slides[heroIndex].classList.add('is-active');
      heroDots.children[heroIndex].classList.add('is-active');
      heroDots.children[heroIndex].setAttribute('aria-current', 'true');
    };
    const startHero = () => { clearInterval(heroTimer); heroTimer = setInterval(() => goHero(heroIndex + 1), 6500); };
    slides.forEach((_, i) => {
      const b = document.createElement('button');
      b.type = 'button';
      b.setAttribute('aria-label', `Slide ${i + 1}`);
      b.setAttribute('aria-current', i === 0 ? 'true' : 'false');
      if (i === 0) b.classList.add('is-active');
      b.addEventListener('click', () => { goHero(i); startHero(); });
      heroDots.appendChild(b);
    });
    if (!reduceMotion) startHero();
  }

  /* ---------- Carrossel (scroll-snap + dots) ---------- */
  const carousels = new Map();
  $$('[data-carousel]').forEach((root) => {
    const dots = $('.carousel__dots', root);
    const track = () => $('.carousel__track:not([hidden])', root);
    const perView = (t) => Math.max(1, Math.round(t.clientWidth / (t.firstElementChild?.offsetWidth || 1)));
    const sync = () => {
      const t = track();
      if (!t) return;
      const i = Math.round(t.scrollLeft / t.clientWidth);
      [...dots.children].forEach((d, k) => d.classList.toggle('is-active', k === i));
    };
    const build = () => {
      const t = track();
      dots.innerHTML = '';
      if (!t) return;
      const n = Math.ceil(t.children.length / perView(t));
      if (n < 2) return;
      for (let i = 0; i < n; i++) {
        const b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', `Página ${i + 1}`);
        b.addEventListener('click', () => track().scrollTo({ left: i * track().clientWidth, behavior: reduceMotion ? 'auto' : 'smooth' }));
        dots.appendChild(b);
      }
      sync();
    };
    $$('.carousel__track', root).forEach((t) => t.addEventListener('scroll', sync, { passive: true }));
    window.addEventListener('resize', build);
    carousels.set(root, build);
    build();
  });

  /* ---------- Abas das vitrines ---------- */
  $$('[data-showcase]').forEach((section) => {
    const tabs = $$('[role="tab"]', section);
    if (!tabs.length) return;
    const panelsRoot = $('.tab-panels', section);
    let busy = false;

    const activate = (btn) => {
      if (busy || btn.classList.contains('is-active')) return;
      busy = true;
      const current = $('[role="tabpanel"]:not([hidden])', section);
      const next = document.getElementById(btn.getAttribute('aria-controls'));
      tabs.forEach((b) => {
        const on = b === btn;
        b.classList.toggle('is-active', on);
        b.setAttribute('aria-selected', on ? 'true' : 'false');
        b.tabIndex = on ? 0 : -1;
      });
      current.classList.add('is-switching');
      setTimeout(() => {
        current.hidden = true;
        current.classList.remove('is-switching');
        next.classList.add('is-switching');
        next.hidden = false;
        next.scrollLeft = 0;
        void next.offsetWidth; // reflow para a transição de entrada
        next.classList.remove('is-switching');
        carousels.get(panelsRoot)?.();
        busy = false;
      }, reduceMotion ? 0 : 300);
    };

    tabs.forEach((btn, i) => {
      btn.addEventListener('click', () => activate(btn));
      btn.addEventListener('keydown', (e) => {
        const dir = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
        if (!dir) return;
        e.preventDefault();
        const to = tabs[(i + dir + tabs.length) % tabs.length];
        to.focus();
        activate(to);
      });
    });
  });

  /* ---------- Depoimentos ---------- */
  $$('[data-testimonials]').forEach((root) => {
    const items = $$('.testimonial', root);
    if (items.length < 2) return;
    let i = 0, timer;
    const go = (n) => {
      items[i].classList.remove('is-active');
      items[i].setAttribute('aria-hidden', 'true');
      i = (n + items.length) % items.length;
      items[i].classList.add('is-active');
      items[i].removeAttribute('aria-hidden');
    };
    const start = () => { if (reduceMotion) return; clearInterval(timer); timer = setInterval(() => go(i + 1), 7000); };
    $('.arrow--prev', root)?.addEventListener('click', () => { go(i - 1); start(); });
    $('.arrow--next', root)?.addEventListener('click', () => { go(i + 1); start(); });
    start();
  });

  /* ---------- Parallax do banner ---------- */
  const parallax = $$('[data-parallax]');
  if (parallax.length && !reduceMotion) {
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
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
    }, { threshold: 0.15 });
    $$('.reveal').forEach((el) => io.observe(el));
  } else {
    $$('.reveal').forEach((el) => el.classList.add('is-in'));
  }

  /* ---------- Drawers, busca e overlay ---------- */
  const overlay = $('#overlay');
  let openPanel = null, lastFocus = null;
  const setExpanded = (id, v) => $$(`[aria-controls="${id}"][data-open]`).forEach((b) => b.setAttribute('aria-expanded', v));
  const open = (id) => {
    const panel = document.getElementById(id);
    if (!panel) return false;
    close(false);
    lastFocus = document.activeElement;
    openPanel = panel;
    overlay.hidden = false;
    void overlay.offsetWidth;
    panel.classList.add('is-open');
    panel.setAttribute('aria-hidden', 'false');
    overlay.classList.add('is-open');
    document.body.classList.add('is-locked');
    setExpanded(id, 'true');
    setTimeout(() => ($('input[type="search"], input:not([type="hidden"])', panel) || $('[data-close]', panel))?.focus(), 50);
    return true;
  };
  const close = (restore = true) => {
    if (!openPanel) return;
    openPanel.classList.remove('is-open');
    openPanel.setAttribute('aria-hidden', 'true');
    setExpanded(openPanel.id, 'false');
    overlay.classList.remove('is-open');
    setTimeout(() => { if (!openPanel) overlay.hidden = true; }, 350);
    document.body.classList.remove('is-locked');
    openPanel = null;
    if (restore) lastFocus?.focus?.();
  };
  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-open]');
    if (opener) {
      // No carrinho/checkout o ícone leva para a página (o drawer não faz sentido lá).
      if (opener.dataset.open === 'cartDrawer' && document.body.matches('.woocommerce-cart, .woocommerce-checkout')) return;
      if (open(opener.dataset.open)) e.preventDefault();
      return;
    }
    if (e.target.closest('[data-close]')) { e.preventDefault(); close(); }
  });
  overlay?.addEventListener('click', () => close());
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

  /* ---------- WooCommerce: carrinho AJAX (eventos jQuery do Woo) ---------- */
  if (window.jQuery) {
    const $body = window.jQuery(document.body);
    // Cards (AJAX): abre o drawer do carrinho já atualizado pelos fragments.
    // No carrinho (cards das recomendações): aviso + o bloco do carrinho recarrega os itens.
    $body.on('added_to_cart', (_e, _fragments, _hash, $button) => {
      const name = $button?.data?.('product_name');
      if (document.body.classList.contains('woocommerce-cart')) {
        notify(name ? `${name} ${CV.i18n.added}` : `Produto ${CV.i18n.added}`);
        document.body.dispatchEvent(new CustomEvent('wc-blocks_added_to_cart', { bubbles: true }));
        return;
      }
      open('cartDrawer');
    });
    $body.on('ajax_request_not_sent.adding_to_cart', () => notify(CV.i18n.error));

    // Produto variável: o preço da cor escolhida substitui o preço principal
    // (o bloco .woocommerce-variation-price foi removido do template).
    const $form = window.jQuery('.summary form.variations_form');
    const priceEl = document.querySelector('.summary > .price');
    if ($form.length && priceEl) {
      const original = priceEl.innerHTML;
      $form.on('found_variation', (_e, variation) => {
        if (variation?.price_html) {
          const tmp = document.createElement('div');
          tmp.innerHTML = variation.price_html;
          priceEl.innerHTML = (tmp.querySelector('.price') || tmp).innerHTML;
        }
      });
      $form.on('reset_data', () => { priceEl.innerHTML = original; });
    }
  }

  /* ---------- Vídeo do produto na galeria ---------- */
  // O Woo já toca/pausa o slide ativo; aqui: botão de som (clicar no vídeo também
  // alterna) e fallback que pausa o vídeo fora da tela ou fora do slide ativo.
  $$('.cv-video').forEach((wrap) => {
    const video = $('video', wrap);
    const btn = $('.cv-video__sound', wrap);
    if (!video || !btn) return;
    video.muted = true; // garante autoplay permitido pelo navegador

    const setSound = (on) => {
      video.muted = !on;
      wrap.classList.toggle('is-unmuted', on);
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
      btn.setAttribute('aria-label', on ? 'Desativar som' : 'Ativar som');
      if (on) video.play?.()?.catch?.(() => {});
    };
    const toggle = (e) => { e.preventDefault(); e.stopPropagation(); setSound(video.muted); };
    btn.addEventListener('click', toggle);
    video.addEventListener('click', toggle);

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(([entry]) => {
        const active = !wrap.closest('.flex-viewport') || wrap.closest('.flex-active-slide');
        if (entry.isIntersecting && active && !reduceMotion) {
          video.play()?.catch?.(() => {});
        } else {
          video.pause();
          if (!entry.isIntersecting || !active) setSound(false);
        }
      }, { threshold: 0.5 }).observe(wrap);
    }
  });

  // Marca a miniatura do vídeo (ícone de play) quando o flexslider monta as thumbs.
  const videoGallery = $('.woocommerce-product-gallery.has-video');
  if (videoGallery) {
    const markThumb = () => {
      const thumbs = $$('.flex-control-thumbs li', videoGallery);
      if (!thumbs.length) return false;
      const slides = $$('.woocommerce-product-gallery__wrapper > .woocommerce-product-gallery__image', videoGallery);
      slides.forEach((s, i) => s.classList.contains('cv-gallery-video') && thumbs[i]?.classList.add('cv-thumb-video'));
      return true;
    };
    // Clique em qualquer ponto da miniatura vale como clique na <img> (que é o que o flexslider escuta).
    videoGallery.addEventListener('click', (e) => {
      const li = e.target.closest('.flex-control-thumbs li');
      if (li && e.target === li) li.querySelector('img')?.click();
    });
    // Vídeo em 1º: a cor padrão selecionada no carregamento faz o Woo deslizar até a
    // foto dela. Enquanto o cliente não mexer na cor, mantém a galeria no vídeo.
    const firstIsVideo = $('.woocommerce-product-gallery__wrapper > .woocommerce-product-gallery__image', videoGallery)?.classList.contains('cv-gallery-video');
    const varForm = $('.summary form.variations_form');
    if (firstIsVideo && varForm && window.jQuery) {
      let touched = false;
      varForm.addEventListener('change', (e) => { if (e.isTrusted) touched = true; }, true); // só mudança real do usuário
      varForm.addEventListener('click', (e) => { if (e.isTrusted && e.target.closest('.variations')) touched = true; }, true);
      window.jQuery(varForm).on('found_variation', () => {
        if (touched) return;
        setTimeout(() => window.jQuery(videoGallery).data('flexslider')?.flexAnimate(0, true), 300);
      });
    }
    if (!markThumb()) {
      const mo = new MutationObserver(() => { if (markThumb()) mo.disconnect(); });
      mo.observe(videoGallery, { childList: true, subtree: true });
    }
  }

  /* ---------- Lista de desejos ---------- */
  const COOKIE = 'cv_wishlist';
  const readWish = () => {
    const m = document.cookie.match(new RegExp(`(?:^|; )${COOKIE}=([^;]*)`));
    return m ? decodeURIComponent(m[1]).split(',').map(Number).filter(Boolean) : [];
  };
  const paintWish = (ids) => {
    $$('[data-wish]').forEach((b) => {
      const on = ids.includes(+b.dataset.wish);
      b.classList.toggle('is-active', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
      b.setAttribute('aria-label', on ? 'Remover da lista de desejos' : 'Adicionar à lista de desejos');
    });
    $$('.cv-wish-count').forEach((c) => (c.textContent = ids.length));
  };
  paintWish(readWish());

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-wish]');
    if (!btn || btn.disabled) return;
    e.preventDefault();
    btn.disabled = true;
    try {
      const body = new URLSearchParams({ action: 'cv_wishlist_toggle', nonce: CV.nonce, product_id: btn.dataset.wish });
      const res = await fetch(CV.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' });
      const json = await res.json();
      if (!json.success) throw new Error();
      paintWish(json.data.ids);
      notify(json.data.added ? CV.i18n.wishAdded : CV.i18n.wishRemoved);

      // Na página da lista: some com o card removido.
      const page = btn.closest('[data-wishlist-page]');
      if (page && !json.data.added) {
        btn.closest('.product')?.remove();
        if (!json.data.ids.length) {
          $('.products', page)?.remove();
          $('.wishlist__empty', page).hidden = false;
        }
      }
    } catch {
      notify(CV.i18n.error);
    } finally {
      btn.disabled = false;
    }
  });
})();
