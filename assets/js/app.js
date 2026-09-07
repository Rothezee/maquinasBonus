(function () {
    const nav = document.getElementById('site-nav');
    const navToggle = document.getElementById('nav-toggle');

    function setNavOpen(open) {
        if (!nav || !navToggle) return;
        nav.classList.toggle('is-open', open);
        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        navToggle.textContent = open ? 'Cerrar' : 'Menú';
        document.body.classList.toggle('nav-open', open);
    }

    if (navToggle && nav) {
        navToggle.addEventListener('click', function () {
            setNavOpen(!nav.classList.contains('is-open'));
        });
        nav.addEventListener('click', function (event) {
            const link = event.target.closest('a');
            if (link) setNavOpen(false);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') setNavOpen(false);
        });
    }

    const grid = document.getElementById('machine-grid');
    const empty = document.getElementById('empty-state');
    const search = document.getElementById('search');
    const chips = document.getElementById('chips');
    const catCards = document.querySelectorAll('.cat-card');
    const viewer = document.getElementById('machine-viewer');
    const closeViewer = document.getElementById('close-viewer');
    const viewerTrack = document.getElementById('viewer-track');
    const viewerDots = document.getElementById('viewer-dots');
    const viewerTitle = document.getElementById('viewer-title');
    const viewerCategory = document.getElementById('viewer-category');
    const viewerDescription = document.getElementById('viewer-description');
    const viewerStatus = document.getElementById('viewer-status');
    const viewerWhatsapp = document.getElementById('viewer-whatsapp');
    const viewerPrev = document.querySelector('[data-viewer-prev]');
    const viewerNext = document.querySelector('[data-viewer-next]');

    if (!grid) return;

    let current = 'all';
    let viewerIndex = 0;
    let viewerSlides = [];

    function apply() {
        const q = (search && search.value ? search.value : '').trim().toLowerCase();
        let visible = 0;
        grid.querySelectorAll('.machine-card').forEach(function (card) {
            const cats = (card.getAttribute('data-category') || '').split(',').map(function (c) {
                return c.trim();
            }).filter(Boolean);
            const name = card.getAttribute('data-name') || '';
            const matchCat = current === 'all' || cats.indexOf(current) !== -1;
            const matchText = !q || name.indexOf(q) !== -1;
            const show = matchCat && matchText;
            card.hidden = !show;
            if (show) visible += 1;
        });
        if (empty) empty.hidden = visible > 0;
    }

    function setFilter(value) {
        current = value;
        catCards.forEach(function (card) {
            card.classList.toggle('is-active', card.getAttribute('data-filter') === value);
        });
        if (chips) {
            chips.querySelectorAll('.chip').forEach(function (chip) {
                chip.classList.toggle('is-active', chip.getAttribute('data-filter') === value);
            });
        }
        apply();
        const catalog = document.getElementById('catalogo');
        if (catalog && value !== 'all') catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function syncSlideMedia(slide, active) {
        const iframe = slide.querySelector('iframe');
        if (!iframe) return;
        if (active) {
            if (!iframe.getAttribute('src')) {
                const src = iframe.getAttribute('data-src');
                if (src) iframe.setAttribute('src', src);
            }
        } else if (iframe.getAttribute('src')) {
            if (!iframe.getAttribute('data-src')) {
                iframe.setAttribute('data-src', iframe.getAttribute('src'));
            }
            iframe.removeAttribute('src');
        }
    }

    function setupCarousel(root) {
        const slides = Array.from(root.querySelectorAll('[data-slide]'));
        if (slides.length <= 1) return;
        const dots = Array.from(root.querySelectorAll('[data-carousel-goto]'));
        let index = 0;

        function show(i) {
            index = (i + slides.length) % slides.length;
            slides.forEach(function (slide, n) {
                const active = n === index;
                slide.hidden = !active;
                syncSlideMedia(slide, active);
            });
            dots.forEach(function (dot, n) {
                dot.classList.toggle('is-active', n === index);
            });
        }

        const prev = root.querySelector('[data-carousel-prev]');
        const next = root.querySelector('[data-carousel-next]');
        if (prev) prev.addEventListener('click', function (e) { e.stopPropagation(); show(index - 1); });
        if (next) next.addEventListener('click', function (e) { e.stopPropagation(); show(index + 1); });
        dots.forEach(function (dot) {
            dot.addEventListener('click', function (e) {
                e.stopPropagation();
                show(Number(dot.getAttribute('data-carousel-goto')) || 0);
            });
        });
    }

    function normalizeMedia(data) {
        if (Array.isArray(data.media) && data.media.length) {
            return data.media;
        }
        const slides = [];
        (Array.isArray(data.photos) ? data.photos : []).forEach(function (src) {
            slides.push({ type: 'image', src: src });
        });
        (Array.isArray(data.videos) ? data.videos : []).forEach(function (video) {
            slides.push({
                type: 'video',
                embed: video.embed || '',
                url: video.url || '',
                provider: video.type || 'link',
            });
        });
        return slides;
    }

    function renderViewerCarousel() {
        if (!viewerTrack || !viewerDots) return;
        viewerTrack.innerHTML = '';
        viewerDots.innerHTML = '';
        const name = viewerTitle ? viewerTitle.textContent || 'máquina' : 'máquina';

        viewerSlides.forEach(function (slide, i) {
            const active = i === viewerIndex;
            if ((slide.type || '') === 'video') {
                const wrap = document.createElement('div');
                wrap.className = 'carousel-slide is-video';
                wrap.setAttribute('data-slide', String(i));
                wrap.hidden = !active;
                if (slide.embed) {
                    const frame = document.createElement('div');
                    frame.className = 'video-frame';
                    const iframe = document.createElement('iframe');
                    iframe.title = 'Video de ' + name;
                    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                    iframe.allowFullscreen = true;
                    iframe.loading = 'lazy';
                    if (active) iframe.src = slide.embed;
                    else iframe.setAttribute('data-src', slide.embed);
                    frame.appendChild(iframe);
                    wrap.appendChild(frame);
                } else {
                    const link = document.createElement('a');
                    link.className = 'video-link-slide btn btn-ghost';
                    link.href = slide.url || '#';
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.textContent = 'Ver video';
                    wrap.appendChild(link);
                }
                viewerTrack.appendChild(wrap);
            } else {
                const img = document.createElement('img');
                img.src = slide.src || '';
                img.alt = name;
                img.hidden = !active;
                img.setAttribute('data-slide', String(i));
                viewerTrack.appendChild(img);
            }

            if (viewerSlides.length > 1) {
                const dot = document.createElement('button');
                dot.type = 'button';
                const isVideo = (slide.type || '') === 'video';
                dot.className = 'carousel-dot' + (active ? ' is-active' : '') + (isVideo ? ' is-video' : '');
                dot.setAttribute('aria-label', isVideo ? ('Ir a video ' + (i + 1)) : ('Ir a foto ' + (i + 1)));
                dot.addEventListener('click', function () {
                    viewerIndex = i;
                    renderViewerCarousel();
                });
                viewerDots.appendChild(dot);
            }
        });

        const showNav = viewerSlides.length > 1;
        if (viewerPrev) viewerPrev.hidden = !showNav;
        if (viewerNext) viewerNext.hidden = !showNav;
    }

    function openMachine(data) {
        if (!viewer) return;
        viewerSlides = normalizeMedia(data);
        viewerIndex = 0;
        if (viewerTitle) viewerTitle.textContent = data.name || '';
        if (viewerCategory) {
            viewerCategory.textContent = '';
            const labels = (data.category || '').split(' · ').map(function (s) {
                return s.trim();
            }).filter(Boolean);
            if (!labels.length && data.category) {
                labels.push(data.category);
            }
            labels.forEach(function (label) {
                const pill = document.createElement('span');
                pill.className = 'cat-pill';
                pill.textContent = label;
                viewerCategory.appendChild(pill);
            });
        }
        if (viewerDescription) viewerDescription.textContent = data.description || '';
        if (viewerStatus) {
            if (data.rented) {
                viewerStatus.textContent = 'Alquilada';
                viewerStatus.className = 'viewer-status is-rented';
            } else if (data.sold) {
                viewerStatus.textContent = 'Vendida';
                viewerStatus.className = 'viewer-status is-sold';
            } else {
                viewerStatus.textContent = 'Disponible';
                viewerStatus.className = 'viewer-status is-available';
            }
        }
        if (viewerWhatsapp) {
            viewerWhatsapp.href = data.whatsapp || '#';
            viewerWhatsapp.hidden = !!(data.rented || data.sold);
            viewerWhatsapp.textContent = 'Consultar por WhatsApp';
        }
        renderViewerCarousel();
        viewer.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeMachine() {
        if (!viewer) return;
        viewer.hidden = true;
        document.body.style.overflow = '';
        if (viewerTrack) {
            viewerTrack.querySelectorAll('iframe').forEach(function (iframe) {
                if (iframe.getAttribute('src')) {
                    iframe.setAttribute('data-src', iframe.getAttribute('src'));
                    iframe.removeAttribute('src');
                }
            });
            viewerTrack.innerHTML = '';
        }
        if (viewerDots) viewerDots.innerHTML = '';
    }

    grid.querySelectorAll('[data-carousel]').forEach(setupCarousel);

    grid.addEventListener('click', function (event) {
        if (event.target.closest('.carousel-btn, .carousel-dot, a.btn, .video-frame, iframe, .video-link-slide')) return;
        const openBtn = event.target.closest('.js-open-machine');
        const card = event.target.closest('.machine-card');
        if (!card) return;
        if (openBtn || event.target.closest('.photo')) {
            try {
                openMachine(JSON.parse(card.getAttribute('data-machine') || '{}'));
            } catch (e) {}
        }
    });

    if (closeViewer) closeViewer.addEventListener('click', closeMachine);
    if (viewer) {
        viewer.addEventListener('click', function (event) {
            if (event.target === viewer) closeMachine();
        });
    }
    const dealLink = document.getElementById('viewer-deal-link');
    if (dealLink) {
        dealLink.addEventListener('click', function () {
            closeMachine();
        });
    }
    if (viewerPrev) {
        viewerPrev.addEventListener('click', function () {
            if (!viewerSlides.length) return;
            viewerIndex = (viewerIndex - 1 + viewerSlides.length) % viewerSlides.length;
            renderViewerCarousel();
        });
    }
    if (viewerNext) {
        viewerNext.addEventListener('click', function () {
            if (!viewerSlides.length) return;
            viewerIndex = (viewerIndex + 1) % viewerSlides.length;
            renderViewerCarousel();
        });
    }
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && viewer && !viewer.hidden) closeMachine();
    });

    if (chips) {
        chips.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-filter]');
            if (btn) setFilter(btn.getAttribute('data-filter'));
        });
    }

    catCards.forEach(function (card) {
        card.addEventListener('click', function () {
            setFilter(card.getAttribute('data-filter'));
        });
    });

    if (search) search.addEventListener('input', apply);

    const params = new URLSearchParams(window.location.search);
    const initial = params.get('cat');
    if (initial) setFilter(initial);
    else apply();
})();
