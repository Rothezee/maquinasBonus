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
    const photoInput = document.getElementById('form-photo');
    const preview = document.getElementById('form-preview');
    const errorBox = document.getElementById('form-error');
    const list = document.getElementById('admin-list');
    const empty = document.getElementById('admin-empty');
    const submitBtn = document.getElementById('form-submit');

    if (!modal || !form) return;

    function showError(message) {
        errorBox.hidden = !message;
        errorBox.textContent = message || '';
    }

    function openModal(mode, data) {
        form.reset();
        showError('');
        preview.hidden = true;
        preview.removeAttribute('src');
        if (mode === 'edit' && data) {
            title.textContent = 'Editar máquina';
            action.value = 'update';
            idInput.value = data.id;
            nameInput.value = data.name;
            categoryInput.value = data.category;
            descriptionInput.value = data.description;
            rentedInput.checked = data.rented === '1';
            if (data.photo) {
                preview.src = data.photo;
                preview.hidden = false;
            }
            submitBtn.textContent = 'Guardar cambios';
        } else {
            title.textContent = 'Agregar máquina';
            action.value = 'create';
            idInput.value = '';
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

    photoInput.addEventListener('change', function () {
        const file = photoInput.files && photoInput.files[0];
        if (!file) {
            preview.hidden = true;
            return;
        }
        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
    });

    document.addEventListener('click', function (event) {
        const edit = event.target.closest('.js-edit');
        const del = event.target.closest('.js-delete');
        const toggle = event.target.closest('.js-toggle');

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
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        showError('');
        submitBtn.disabled = true;
        const data = new FormData(form);
        if (!rentedInput.checked) data.delete('rented');

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
