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
    const viewerVideos = document.getElementById('viewer-videos');
    const viewerWhatsapp = document.getElementById('viewer-whatsapp');
    const viewerPrev = document.querySelector('[data-viewer-prev]');
    const viewerNext = document.querySelector('[data-viewer-next]');

    if (!grid) return;

    let current = 'all';
    let viewerIndex = 0;
    let viewerPhotos = [];

    function apply() {
        const q = (search && search.value ? search.value : '').trim().toLowerCase();
        let visible = 0;
        grid.querySelectorAll('.machine-card').forEach(function (card) {
            const cat = card.getAttribute('data-category') || '';
            const name = card.getAttribute('data-name') || '';
            const matchCat = current === 'all' || cat === current;
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

    function setupCarousel(root) {
        const slides = Array.from(root.querySelectorAll('[data-slide]'));
        if (slides.length <= 1) return;
        const dots = Array.from(root.querySelectorAll('[data-carousel-goto]'));
        let index = 0;

        function show(i) {
            index = (i + slides.length) % slides.length;
            slides.forEach(function (slide, n) {
                slide.hidden = n !== index;
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

    function renderViewerCarousel() {
        if (!viewerTrack || !viewerDots) return;
        viewerTrack.innerHTML = '';
        viewerDots.innerHTML = '';
        viewerPhotos.forEach(function (src, i) {
            const img = document.createElement('img');
            img.src = src;
            img.alt = viewerTitle ? viewerTitle.textContent || '' : '';
            img.hidden = i !== viewerIndex;
            img.setAttribute('data-slide', String(i));
            viewerTrack.appendChild(img);

            if (viewerPhotos.length > 1) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'carousel-dot' + (i === viewerIndex ? ' is-active' : '');
                dot.setAttribute('aria-label', 'Ir a foto ' + (i + 1));
                dot.addEventListener('click', function () {
                    viewerIndex = i;
                    renderViewerCarousel();
                });
                viewerDots.appendChild(dot);
            }
        });
        const showNav = viewerPhotos.length > 1;
        if (viewerPrev) viewerPrev.hidden = !showNav;
        if (viewerNext) viewerNext.hidden = !showNav;
    }

    function openMachine(data) {
        if (!viewer) return;
        viewerPhotos = Array.isArray(data.photos) && data.photos.length ? data.photos : [];
        viewerIndex = 0;
        if (viewerTitle) viewerTitle.textContent = data.name || '';
        if (viewerCategory) viewerCategory.textContent = data.category || '';
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
        if (viewerVideos) {
            viewerVideos.innerHTML = '';
            const videos = Array.isArray(data.videos) ? data.videos : [];
            if (videos.length) {
                viewerVideos.hidden = false;
                const heading = document.createElement('h3');
                heading.textContent = 'Videos';
                viewerVideos.appendChild(heading);
                videos.forEach(function (video) {
                    if (video.embed) {
                        const wrap = document.createElement('div');
                        wrap.className = 'video-frame';
                        const iframe = document.createElement('iframe');
                        iframe.src = video.embed;
                        iframe.title = 'Video de ' + (data.name || 'máquina');
                        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                        iframe.allowFullscreen = true;
                        iframe.loading = 'lazy';
                        wrap.appendChild(iframe);
                        viewerVideos.appendChild(wrap);
                    } else {
                        const link = document.createElement('a');
                        link.className = 'btn btn-ghost';
                        link.href = video.url;
                        link.target = '_blank';
                        link.rel = 'noopener';
                        link.textContent = 'Ver video';
                        viewerVideos.appendChild(link);
                    }
                });
            } else {
                viewerVideos.hidden = true;
            }
        }
        renderViewerCarousel();
        viewer.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeMachine() {
        if (!viewer) return;
        viewer.hidden = true;
        document.body.style.overflow = '';
        if (viewerVideos) viewerVideos.innerHTML = '';
        if (viewerTrack) viewerTrack.innerHTML = '';
    }

    grid.querySelectorAll('[data-carousel]').forEach(setupCarousel);

    grid.addEventListener('click', function (event) {
        if (event.target.closest('.carousel-btn, .carousel-dot, a.btn')) return;
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
            if (!viewerPhotos.length) return;
            viewerIndex = (viewerIndex - 1 + viewerPhotos.length) % viewerPhotos.length;
            renderViewerCarousel();
        });
    }
    if (viewerNext) {
        viewerNext.addEventListener('click', function () {
            if (!viewerPhotos.length) return;
            viewerIndex = (viewerIndex + 1) % viewerPhotos.length;
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
