(function () {
    const grid = document.getElementById('machine-grid');
    const empty = document.getElementById('empty-state');
    const search = document.getElementById('search');
    const chips = document.getElementById('chips');
    const catCards = document.querySelectorAll('.cat-card');
    if (!grid) return;

    let current = 'all';

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
        document.querySelectorAll('[data-filter]').forEach(function (el) {
            el.classList.toggle('is-active', el.getAttribute('data-filter') === value || (value === 'all' && el.getAttribute('data-filter') === 'all' && el.classList.contains('chip')));
        });
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
