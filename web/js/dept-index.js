document.addEventListener('DOMContentLoaded', () => {

    // ── EDIT MODAL ──────────────────────────────────────────────
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (e) {
            const btn  = e.relatedTarget;
            document.getElementById('editDeptId').value   = btn.getAttribute('data-id');
            document.getElementById('editDeptName').value = btn.getAttribute('data-name');
        });
    }

    document.getElementById('editSaveBtn')?.addEventListener('click', function () {
        const id   = document.getElementById('editDeptId').value;
        const name = document.getElementById('editDeptName').value.trim();
        if (!name) return;

        fetch(updateDeptUrl + '&id=' + id + '&name=' + encodeURIComponent(name), { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // Update card name in DOM without full reload
                    const card = document.querySelector(`.dept-wrapper[data-dept-id="${id}"]`);
                    if (card) {
                        card.querySelector('.fw-bold.text-dark').textContent = name;
                        // Update data-name so re-opening the modal shows the new name
                        card.querySelector('[data-bs-target="#editModal"]').setAttribute('data-name', name);
                    }
                    bootstrap.Modal.getInstance(editModal).hide();
                } else {
                    alert(res.error ?? 'Erro ao guardar.');
                }
            })
            .catch(err => console.error(err));
    });

    // ── CREATE MODAL ─────────────────────────────────────────────
    document.getElementById('createSaveBtn')?.addEventListener('click', function () {
        const name = document.getElementById('createDeptName').value.trim();
        if (!name) return;

        fetch(createDeptUrl + '&name=' + encodeURIComponent(name), { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // Reload to show the new card
                    window.location.reload();
                } else {
                    alert(res.error ?? 'Erro ao criar departamento.');
                }
            })
            .catch(err => console.error(err));
    });

    // ── EXPAND / COLLAPSE MEMBERS ────────────────────────────────
    window.toggleExpand = function (btnEl, deptId) {
        const wrapper   = btnEl.closest('.dept-wrapper');
        const container = wrapper.querySelector('.aff-users-container');
        const isOpen    = btnEl.classList.contains('active');

        // Collapse all others first
        document.querySelectorAll('.dept-wrapper').forEach(w => {
            const c = w.querySelector('.aff-users-container');
            const b = w.querySelector('.expand-btn');
            if (c) { c.style.display = 'none'; c.innerHTML = ''; }
            if (b) { b.classList.remove('active'); b.innerHTML = '<i class="fas fa-users me-1"></i>Ver membros'; }
        });

        if (isOpen) return; // was open → just close

        // Show loading state
        btnEl.classList.add('active');
        btnEl.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>A carregar...';
        container.style.display = 'block';
        container.innerHTML = '';

        fetch(getUsersAffiliatedUrl + '&id=' + deptId)
            .then(r => r.json())
            .then(users => {
                btnEl.innerHTML = '<i class="fas fa-times me-1"></i>Fechar';

                if (!users.length) {
                    container.innerHTML = `
                        <div class="text-center text-muted py-3" style="font-size:.82rem;">
                            <i class="fas fa-user-slash mb-2 d-block" style="opacity:.4;"></i>
                            Sem colaboradores neste departamento.
                        </div>`;
                    return;
                }

                const grid = document.createElement('div');
                grid.className = 'row g-2';

                users.forEach(u => {
                    const initial = u.Username ? u.Username.charAt(0).toUpperCase() : '?';
                    grid.insertAdjacentHTML('beforeend', `
                        <div class="col-md-6 col-sm-12">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-3"
                                 style="background:#f8f8ff; border:1px solid #ede9fe;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                     style="width:32px;height:32px;background:#6366f1;font-size:.8rem;">
                                    ${initial}
                                </div>
                                <div style="overflow:hidden;">
                                    <div class="fw-semibold text-dark text-truncate" style="font-size:.82rem;">${u.Username}</div>
                                    <div class="text-muted" style="font-size:.7rem;">#${u.Userid}</div>
                                </div>
                            </div>
                        </div>`);
                });

                container.appendChild(grid);
            })
            .catch(err => {
                btnEl.innerHTML = '<i class="fas fa-users me-1"></i>Ver membros';
                btnEl.classList.remove('active');
                container.style.display = 'none';
                console.error(err);
            });
    };

});
