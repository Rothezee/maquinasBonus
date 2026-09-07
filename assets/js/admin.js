(function () {
    const modal = document.getElementById('machine-modal');
    const form = document.getElementById('machine-form');
    const openCreate = document.getElementById('open-create');
    const closeModal = document.getElementById('close-modal');
    const title = document.getElementById('modal-title');
    const action = document.getElementById('form-action');
    const idInput = document.getElementById('form-id');
    const nameInput = document.getElementById('form-name');
    const categoryInput = document.getElementById('form-category');
    const descriptionInput = document.getElementById('form-description');
    const rentedInput = document.getElementById('form-rented');
    const soldInput = document.getElementById('form-sold');
    const photosInput = document.getElementById('form-photos');
    const videosInput = document.getElementById('form-videos');
    const keepInput = document.getElementById('form-keep-photos');
    const keepBox = document.getElementById('photo-keep');
    const previewGrid = document.getElementById('photo-preview-grid');
    const errorBox = document.getElementById('form-error');
    const list = document.getElementById('admin-list');
    const empty = document.getElementById('admin-empty');
    const submitBtn = document.getElementById('form-submit');

    if (!modal || !form) return;

    let keepPhotos = [];

    function showError(message) {
        errorBox.hidden = !message;
        errorBox.textContent = message || '';
    }

    function syncKeepInput() {
        keepInput.value = JSON.stringify(keepPhotos);
    }

    function renderKeepPhotos() {
        keepBox.innerHTML = '';
        if (!keepPhotos.length) {
            keepBox.hidden = true;
            syncKeepInput();
            return;
        }
        keepBox.hidden = false;
        keepPhotos.forEach(function (src, index) {
            const item = document.createElement('div');
            item.className = 'photo-keep-item';
            item.innerHTML =
                '<img src="' + src + '" alt="">' +
                '<button type="button" class="btn btn-tiny btn-danger" data-remove="' + index + '">Quitar</button>';
            keepBox.appendChild(item);
        });
        syncKeepInput();
    }

    function renderNewPreviews() {
        previewGrid.innerHTML = '';
        const files = photosInput.files ? Array.from(photosInput.files) : [];
        if (!files.length) {
            previewGrid.hidden = true;
            return;
        }
        previewGrid.hidden = false;
        files.forEach(function (file) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;
            previewGrid.appendChild(img);
        });
    }

    function openModal(mode, data) {
        form.reset();
        showError('');
        keepPhotos = [];
        previewGrid.hidden = true;
        previewGrid.innerHTML = '';
        keepBox.hidden = true;
        keepBox.innerHTML = '';
        syncKeepInput();

        if (mode === 'edit' && data) {
            title.textContent = 'Editar máquina';
            action.value = 'update';
            idInput.value = data.id;
            nameInput.value = data.name;
            categoryInput.value = data.category;
            descriptionInput.value = data.description;
            rentedInput.checked = data.rented === '1';
            soldInput.checked = data.sold === '1';
            try {
                keepPhotos = JSON.parse(data.photos || '[]') || [];
            } catch (e) {
                keepPhotos = [];
            }
            let videos = [];
            try {
                videos = JSON.parse(data.videos || '[]') || [];
            } catch (e2) {
                videos = [];
            }
            videosInput.value = videos.join('\n');
            renderKeepPhotos();
            submitBtn.textContent = 'Guardar cambios';
        } else {
            title.textContent = 'Agregar máquina';
            action.value = 'create';
            idInput.value = '';
            videosInput.value = '';
            submitBtn.textContent = 'Guardar máquina';
        }
        modal.hidden = false;
        nameInput.focus();
    }

    function close() {
        modal.hidden = true;
    }

    openCreate.addEventListener('click', function () {
        openModal('create');
    });
    closeModal.addEventListener('click', close);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) close();
    });

    photosInput.addEventListener('change', renderNewPreviews);

    keepBox.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-remove]');
        if (!btn) return;
        const index = Number(btn.getAttribute('data-remove'));
        keepPhotos.splice(index, 1);
        renderKeepPhotos();
    });

    document.addEventListener('click', function (event) {
        const edit = event.target.closest('.js-edit');
        const del = event.target.closest('.js-delete');
        const toggle = event.target.closest('.js-toggle');
        const toggleSale = event.target.closest('.js-toggle-sold');

        if (edit) {
            openModal('edit', edit.dataset);
        }

        if (del && confirm('¿Quitamos esta máquina del catálogo?')) {
            send({ action: 'delete', id: del.dataset.id }).then(function (res) {
                if (!res.ok) return alert(res.error || 'No se pudo eliminar');
                location.reload();
            });
        }

        if (toggle) {
            send({ action: 'toggle', id: toggle.dataset.id }).then(function (res) {
                if (!res.ok) return alert(res.error || 'No se pudo actualizar');
                location.reload();
            });
        }

        if (toggleSale) {
            send({ action: 'toggle_sold', id: toggleSale.dataset.id }).then(function (res) {
                if (!res.ok) return alert(res.error || 'No se pudo actualizar');
                location.reload();
            });
        }
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        showError('');
        submitBtn.disabled = true;
        syncKeepInput();
        const data = new FormData(form);
        if (!rentedInput.checked) data.delete('rented');
        if (!soldInput.checked) data.delete('sold');

        fetch('api.php', { method: 'POST', body: data })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.ok) {
                    showError(res.error || 'No se pudo guardar');
                    return;
                }
                location.reload();
            })
            .catch(function () {
                showError('Error de red. Probá de nuevo.');
            })
            .finally(function () {
                submitBtn.disabled = false;
            });
    });

    function send(fields) {
        const data = new FormData();
        data.append('csrf', form.querySelector('[name="csrf"]').value);
        Object.keys(fields).forEach(function (key) {
            data.append(key, fields[key]);
        });
        return fetch('api.php', { method: 'POST', body: data }).then(function (r) { return r.json(); });
    }

    if (list && empty) {
        empty.hidden = list.querySelectorAll('.admin-card').length > 0;
    }
})();
