/* ============================================================================
   Breakfast Menu – AJAX JS
   No page reloads. All mutations hit the API and update the DOM in place.
   ============================================================================ */

document.addEventListener('DOMContentLoaded', function () {

    // ── Tiny helpers ────────────────────────────────────────────────────────

    const csrf = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function toast(icon, title, text = '') {
        Swal.fire({
            icon, title, text,
            timer: 3000, toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timerProgressBar: true,
        });
    }

    function showError(text) {
        Swal.fire({ icon: 'error', title: 'Error!', text, confirmButtonText: 'OK' });
    }

    // ── Loading indicators ───────────────────────────────────────────────────

    function showLoading(label = 'Processing...') {
        Swal.fire({
            title: label,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });
    }

    function hideLoading() {
        Swal.close();
    }

    function setSubmitLoading(btn, loading, originalHTML) {
        if (loading) {
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...`;
        } else {
            btn.disabled = false;
            btn.innerHTML = originalHTML || btn.dataset.originalHtml || btn.innerHTML;
        }
    }

    async function apiFetch(url, method, formData = null) {
        const isMultipart = formData instanceof FormData;
        const options = {
            method,
            headers: {
                'X-CSRF-TOKEN': csrf(),
                'Accept': 'application/json',
                ...(isMultipart ? {} : { 'Content-Type': 'application/json' }),
            },
            body: formData ?? null,
        };
        const res  = await fetch(url, options);
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Something went wrong.');
        return data;
    }

    // ── Flash messages on first load ────────────────────────────────────────

    const successEl = document.querySelector('[data-success-message]');
    const errorEl   = document.querySelector('[data-error-message]');
    if (successEl) toast('success', 'Success!', successEl.getAttribute('data-success-message'));
    if (errorEl)   showError(errorEl.getAttribute('data-error-message'));

    // ── Bootstrap tooltips ──────────────────────────────────────────────────

    function initTooltips(scope = document) {
        scope.querySelectorAll('[data-bs-toggle="tooltip"]')
             .forEach(el => bootstrap.Tooltip.getOrCreateInstance(el));
    }
    initTooltips();

    // ── Live filtering (works for both table rows and cards) ────────────────

    const searchInput        = document.getElementById('searchTable');
    const availabilityFilter = document.getElementById('filterAvailability');

    function filterItems() {
        const term  = searchInput.value.toLowerCase().trim();
        const avail = availabilityFilter.value;

        // Table rows
        document.querySelectorAll('#breakfastTableBody tr').forEach(row => {
            const match = (row.getAttribute('data-meal-name') || '').includes(term)
                       && (avail === '' || row.getAttribute('data-available') === avail);
            row.style.display = match ? '' : 'none';
        });

        // Cards
        document.querySelectorAll('.breakfast-card').forEach(card => {
            const match = (card.getAttribute('data-meal-name') || '').includes(term)
                       && (avail === '' || card.getAttribute('data-available') === avail);
            card.style.display = match ? '' : 'none';
        });
    }

    searchInput.addEventListener('keyup', filterItems);
    availabilityFilter.addEventListener('change', filterItems);

    document.getElementById('resetFilters').addEventListener('click', () => {
        searchInput.value = '';
        availabilityFilter.value = '';
        filterItems();
    });

    // ── Table / Card view toggle ────────────────────────────────────────────

    const tableView = document.getElementById('tableView');
    const cardView  = document.getElementById('cardView');
    const btnTable  = document.getElementById('viewTable');
    const btnCards  = document.getElementById('viewCards');

    btnTable.addEventListener('click', () => {
        tableView.style.display = '';
        cardView.style.display  = 'none';
        btnTable.classList.add('active');
        btnCards.classList.remove('active');
    });

    btnCards.addEventListener('click', () => {
        tableView.style.display = 'none';
        cardView.style.display  = '';
        btnCards.classList.add('active');
        btnTable.classList.remove('active');
    });

    // ── Image preview (add form) ────────────────────────────────────────────

    document.getElementById('add_image').addEventListener('change', function () {
        previewImage(this, document.getElementById('add_img_preview'));
    });

    // ── Image preview (edit forms – delegated) ──────────────────────────────

    document.addEventListener('change', function (e) {
        if (!e.target.matches('input[id^="edit_image_"]')) return;
        const id      = e.target.id.replace('edit_image_', '');
        const preview = document.getElementById(`edit_img_preview_${id}`);
        if (preview) previewImage(e.target, preview);
    });

    // ── ADD form submit ─────────────────────────────────────────────────────

    document.getElementById('addBreakfastForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = this.querySelector('[type="submit"]');
        setSubmitLoading(btn, true);

        try {
            const fd   = new FormData(this);
            const data = await apiFetch(this.dataset.storeUrl, 'POST', fd);

            appendTableRow(data.item);
            appendCard(data.item);
            appendEditModal(data.item, data.edit_url);
            updateStats(data.stats);
            updateEmptyState();
            initTooltips();

            bootstrap.Modal.getInstance(document.getElementById('addBreakfastModal')).hide();
            this.reset();
            resetImgPreview(document.getElementById('add_img_preview'));
            toast('success', 'Added!', `"${data.item.meal_name}" added successfully.`);
        } catch (err) {
            showError(err.message);
        } finally {
            setSubmitLoading(btn, false);
        }
    });

    // ── EDIT forms submit (delegated) ───────────────────────────────────────

    document.addEventListener('submit', async function (e) {
        if (!e.target.matches('.edit-breakfast-form')) return;
        e.preventDefault();

        const form = e.target;
        const id   = form.dataset.id;
        const btn  = form.querySelector('[type="submit"]');
        setSubmitLoading(btn, true);

        try {
            // FormData handles file uploads; simulate PUT with _method field
            const fd = new FormData(form);
            fd.append('_method', 'PUT');

            const data = await apiFetch(form.dataset.updateUrl, 'POST', fd);

            updateTableRow(id, data.item);
            updateCard(id, data.item);
            syncEditModal(id, data.item);
            syncViewModal(id, data.item);
            updateStats(data.stats);
            initTooltips();

            bootstrap.Modal.getInstance(document.getElementById(`editBreakfastModal${id}`)).hide();
            toast('success', 'Updated!', `"${data.item.meal_name}" updated successfully.`);
        } catch (err) {
            showError(err.message);
        } finally {
            setSubmitLoading(btn, false);
        }
    });

    // ── Availability toggle switches (delegated) ────────────────────────────

    document.addEventListener('change', async function (e) {
        if (!e.target.matches('.availability-toggle')) return;

        const toggle    = e.target;
        const id        = toggle.dataset.id;
        const mealName  = toggle.dataset.name;
        const willEnable = toggle.checked;

        // Optimistic UI – flip immediately, disable all matching toggles
        setAllToggles(id, willEnable, true);

        try {
            const url  = document.getElementById(`toggleUrl${id}`).value;
            const data = await apiFetch(url, 'PATCH');

            // Sync everything with server truth
            updateTableRow(id, data.item);
            updateCard(id, data.item);
            syncViewModal(id, data.item);
            syncEditModalSwitch(id, data.item.is_available);
            updateStats(data.stats);

            toast(
                data.item.is_available ? 'success' : 'warning',
                'Updated!',
                `"${mealName}" is now ${data.item.is_available ? 'available' : 'unavailable'}.`
            );
        } catch (err) {
            // Revert on failure
            setAllToggles(id, !willEnable, false);
            showError(err.message);
        } finally {
            setAllToggles(id, undefined, false); // just re-enable, keep current state
        }
    });

}); // end DOMContentLoaded


// ── Delete ──────────────────────────────────────────────────────────────────

async function confirmDelete(itemId, mealName) {
    const result = await Swal.fire({
        title: 'Delete Item?',
        html: `You are about to delete <strong>${mealName}</strong>.<br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
    });

    if (!result.isConfirmed) return;

    // Show loading overlay
    Swal.fire({
        title: 'Deleting...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const url  = document.getElementById(`deleteUrl${itemId}`).value;
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const res  = await fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Something went wrong.');

        // Remove table row
        document.querySelector(`#breakfastTableBody tr[data-id="${itemId}"]`)?.remove();
        // Remove card
        document.querySelector(`.breakfast-card[data-id="${itemId}"]`)?.remove();
        // Remove modals
        document.getElementById(`viewBreakfastModal${itemId}`)?.remove();
        document.getElementById(`editBreakfastModal${itemId}`)?.remove();

        updateStats(data.stats);
        updateEmptyState();
        renumberRows();

        Swal.fire({
            icon: 'success', title: 'Deleted!',
            text: `"${mealName}" has been removed.`,
            timer: 3000, toast: true, position: 'top-end',
            showConfirmButton: false, timerProgressBar: true,
        });
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error!', text: err.message, confirmButtonText: 'OK' });
    }
}


// ── DOM builders & updaters ──────────────────────────────────────────────────

function appendTableRow(item) {
    const tbody = document.getElementById('breakfastTableBody');
    const num   = tbody.querySelectorAll('tr').length + 1;
    const tr    = document.createElement('tr');
    tr.setAttribute('data-id', item.breakfast_id);
    tr.setAttribute('data-meal-name', item.meal_name.toLowerCase());
    tr.setAttribute('data-available', item.is_available ? '1' : '0');
    tr.innerHTML = buildTableRowHTML(num, item);
    tbody.appendChild(tr);
}

function updateTableRow(id, item) {
    const row = document.querySelector(`#breakfastTableBody tr[data-id="${id}"]`);
    if (!row) return;
    const num = row.querySelector('.row-num')?.textContent || 0;
    row.setAttribute('data-meal-name', item.meal_name.toLowerCase());
    row.setAttribute('data-available', item.is_available ? '1' : '0');
    row.innerHTML = buildTableRowHTML(num, item);
}

function buildTableRowHTML(num, item) {
    const price      = fmtPrice(item.price);
    const badgeCls   = item.is_available ? 'bg-label-success' : 'bg-label-danger';
    const badgeTxt   = item.is_available ? 'Available' : 'Unavailable';
    const imgHtml    = item.image_url
        ? `<img src="${item.image_url}" class="rounded" style="width:60px;height:45px;object-fit:cover;" alt="${escHtml(item.meal_name)}">`
        : `<div class="bg-label-secondary rounded d-flex align-items-center justify-content-center" style="width:60px;height:45px;"><i class="ri ri-image-line text-body-secondary"></i></div>`;
    const descHtml   = item.description
        ? `<span class="text-truncate d-inline-block" style="max-width:260px;" data-bs-toggle="tooltip" data-bs-placement="top" title="${escHtml(item.description)}">${escHtml(item.description)}</span>`
        : `<span class="text-body-secondary">—</span>`;

    return `
        <td><span class="row-num">${num}</span></td>
        <td>${imgHtml}</td>
        <td>
          <div class="d-flex align-items-center">
            <i class="icon-base ri ri-bowl-line icon-20px text-primary me-2"></i>
            <span class="fw-medium">${escHtml(item.meal_name)}</span>
          </div>
        </td>
        <td>${descHtml}</td>
        <td><span class="fw-medium text-primary">₱${price}</span></td>
        <td>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input availability-toggle" type="checkbox" role="switch"
                   data-id="${item.breakfast_id}" data-name="${escJs(item.meal_name)}"
                   ${item.is_available ? 'checked' : ''}>
          </div>
        </td>
        <td>
          <div class="dropdown">
            <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown">
              <i class="icon-base ri ri-more-2-line"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="javascript:void(0);"
                 data-bs-toggle="modal" data-bs-target="#viewBreakfastModal${item.breakfast_id}">
                <i class="icon-base ri ri-eye-line me-2"></i>View Details
              </a>
              <a class="dropdown-item" href="javascript:void(0);"
                 data-bs-toggle="modal" data-bs-target="#editBreakfastModal${item.breakfast_id}">
                <i class="icon-base ri ri-edit-line me-2"></i>Edit
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-danger" href="javascript:void(0);"
                 onclick="confirmDelete(${item.breakfast_id}, '${escJs(item.meal_name)}')">
                <i class="icon-base ri ri-delete-bin-line me-2"></i>Delete
              </a>
            </div>
          </div>
          <input type="hidden" id="toggleUrl${item.breakfast_id}" value="${item.toggle_url}">
          <input type="hidden" id="deleteUrl${item.breakfast_id}" value="${item.delete_url}">
        </td>`;
}

// ── Card helpers ─────────────────────────────────────────────────────────────

function appendCard(item) {
    const wrapper = document.createElement('div');
    wrapper.innerHTML = buildCardHTML(item);
    document.getElementById('breakfastCardBody').appendChild(wrapper.firstElementChild);
}

function updateCard(id, item) {
    const card = document.querySelector(`.breakfast-card[data-id="${id}"]`);
    if (!card) return;
    const wrapper = document.createElement('div');
    wrapper.innerHTML = buildCardHTML(item);
    card.replaceWith(wrapper.firstElementChild);
}

function buildCardHTML(item) {
    const price    = fmtPrice(item.price);
    const badgeCls = item.is_available ? 'bg-label-success' : 'bg-label-danger';
    const badgeTxt = item.is_available ? 'Available' : 'Unavailable';
    const labelTxt = item.is_available ? 'Available' : 'Unavailable';
    const imgHtml  = item.image_url
        ? `<img src="${item.image_url}" class="card-img-top" style="height:180px;object-fit:cover;" alt="${escHtml(item.meal_name)}">`
        : `<div class="bg-label-secondary d-flex align-items-center justify-content-center" style="height:180px;"><i class="ri ri-image-line icon-48px text-body-secondary"></i></div>`;
    const descHtml = item.description
        ? `<p class="text-body-secondary small mb-2" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">${escHtml(item.description)}</p>`
        : '';
    const opacity  = item.is_available ? '' : 'opacity-65';

    return `
        <div class="col-xl-3 col-md-4 col-sm-6 breakfast-card"
             data-id="${item.breakfast_id}"
             data-meal-name="${item.meal_name.toLowerCase()}"
             data-available="${item.is_available ? '1' : '0'}">
          <div class="card h-100 ${opacity}">
            ${imgHtml}
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="card-title mb-0 fw-semibold">${escHtml(item.meal_name)}</h6>
                <span class="badge ${badgeCls} ms-2 flex-shrink-0">${badgeTxt}</span>
              </div>
              ${descHtml}
              <div class="mt-auto">
                <p class="fw-bold text-primary mb-3">₱${price}</p>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input availability-toggle" type="checkbox" role="switch"
                           data-id="${item.breakfast_id}" data-name="${escJs(item.meal_name)}"
                           ${item.is_available ? 'checked' : ''}>
                    <label class="form-check-label small">${labelTxt}</label>
                  </div>
                  <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown">
                      <i class="ri ri-more-2-line"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a class="dropdown-item" href="javascript:void(0);"
                         data-bs-toggle="modal" data-bs-target="#viewBreakfastModal${item.breakfast_id}">
                        <i class="icon-base ri ri-eye-line me-2"></i>View Details
                      </a>
                      <a class="dropdown-item" href="javascript:void(0);"
                         data-bs-toggle="modal" data-bs-target="#editBreakfastModal${item.breakfast_id}">
                        <i class="icon-base ri ri-edit-line me-2"></i>Edit
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item text-danger" href="javascript:void(0);"
                         onclick="confirmDelete(${item.breakfast_id}, '${escJs(item.meal_name)}')">
                        <i class="icon-base ri ri-delete-bin-line me-2"></i>Delete
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>`;
}

// ── Modal sync helpers ────────────────────────────────────────────────────────

function appendEditModal(item, editUrl) {
    const div = document.createElement('div');
    div.innerHTML = `
        <div class="modal fade" id="editBreakfastModal${item.breakfast_id}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="icon-base ri ri-edit-line me-2"></i>Edit: ${escHtml(item.meal_name)}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form class="edit-breakfast-form"
                    data-id="${item.breakfast_id}"
                    data-update-url="${editUrl}"
                    enctype="multipart/form-data">
                <div class="modal-body">
                  <div class="row g-4">
                    <div class="col-12">
                      <label class="form-label">Item Image</label>
                      <div class="d-flex align-items-center gap-3">
                        <div id="edit_img_preview_${item.breakfast_id}"
                             class="rounded border d-flex align-items-center justify-content-center bg-label-secondary flex-shrink-0"
                             style="width:100px;height:75px;overflow:hidden;">
                          ${item.image_url
                            ? `<img src="${item.image_url}" style="width:100%;height:100%;object-fit:cover;">`
                            : `<i class="ri ri-image-line icon-32px text-body-secondary"></i>`}
                        </div>
                        <div class="flex-grow-1">
                          <input type="file" class="form-control"
                                 id="edit_image_${item.breakfast_id}" name="image"
                                 accept="image/jpg,image/jpeg,image/png,image/gif,image/webp">
                          <small class="text-muted">Leave empty to keep current image · Max 2 MB</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-8">
                      <label class="form-label">Meal Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="meal_name"
                             required maxlength="150" value="${escHtml(item.meal_name)}">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Price (₱) <span class="text-danger">*</span></label>
                      <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" name="price"
                               required min="0" step="0.01" value="${item.price}">
                      </div>
                    </div>
                    <div class="col-12">
                      <label class="form-label">Description</label>
                      <textarea class="form-control" name="description" rows="3" maxlength="500">${escHtml(item.description || '')}</textarea>
                    </div>
                    <div class="col-12">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_available" value="1"
                               id="edit_is_available_${item.breakfast_id}"
                               ${item.is_available ? 'checked' : ''}>
                        <label class="form-check-label" for="edit_is_available_${item.breakfast_id}">Available for ordering</label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">
                    <i class="icon-base ri ri-save-line me-1"></i>Save Changes
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>`;
    document.body.appendChild(div.firstElementChild);
}

// Sync edit modal fields after update
function syncEditModal(id, item) {
    const modal = document.getElementById(`editBreakfastModal${id}`);
    if (!modal) return;
    modal.querySelector('.modal-title').innerHTML =
        `<i class="icon-base ri ri-edit-line me-2"></i>Edit: ${escHtml(item.meal_name)}`;
    modal.querySelector('[name="meal_name"]').value   = item.meal_name;
    modal.querySelector('[name="price"]').value       = item.price;
    modal.querySelector('[name="description"]').value = item.description || '';
    modal.querySelector('[name="is_available"]').checked = !!item.is_available;

    const preview = modal.querySelector(`#edit_img_preview_${id}`);
    if (preview) {
        preview.innerHTML = item.image_url
            ? `<img src="${item.image_url}" style="width:100%;height:100%;object-fit:cover;">`
            : `<i class="ri ri-image-line icon-32px text-body-secondary"></i>`;
    }
}

// Sync view-details modal after edit or toggle
function syncViewModal(id, item) {
    const modal = document.getElementById(`viewBreakfastModal${id}`);
    if (!modal) return;

    // Title
    modal.querySelector('.modal-title').innerHTML =
        `<i class="icon-base ri ri-bowl-line me-2"></i>${escHtml(item.meal_name)}`;

    // Rebuild body content
    const body = modal.querySelector(`#viewBody${id}`);
    if (!body) return;

    const imgHtml = item.image_url
        ? `<img src="${item.image_url}" class="img-fluid rounded w-100" style="max-height:280px;object-fit:cover;" alt="${escHtml(item.meal_name)}">`
        : `<div class="bg-label-secondary rounded d-flex align-items-center justify-content-center" style="height:220px;"><div class="text-center"><i class="ri ri-image-line icon-48px text-body-secondary"></i><p class="text-body-secondary mb-0 mt-2 small">No Image</p></div></div>`;

    const badgeCls = item.is_available ? 'bg-success' : 'bg-danger';
    const badgeTxt = item.is_available ? 'Available' : 'Unavailable';
    const descHtml = item.description
        ? `<p class="text-body-secondary mb-0">${escHtml(item.description)}</p>`
        : `<p class="text-body-secondary fst-italic mb-0">No description provided.</p>`;

    body.innerHTML = `
        <div class="row g-4">
          <div class="col-md-5">${imgHtml}</div>
          <div class="col-md-7 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex align-items-center gap-2 mb-3">
                <h4 class="mb-0">${escHtml(item.meal_name)}</h4>
                <span class="badge ${badgeCls}">${badgeTxt}</span>
              </div>
              <h3 class="text-primary mb-3">₱${fmtPrice(item.price)}</h3>
              ${descHtml}
            </div>
            <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input availability-toggle" type="checkbox" role="switch"
                       data-id="${item.breakfast_id}" data-name="${escJs(item.meal_name)}"
                       ${item.is_available ? 'checked' : ''}>
                <label class="form-check-label">${badgeTxt}</label>
              </div>
              <button type="button" class="btn btn-primary btn-sm"
                      data-bs-toggle="modal" data-bs-target="#editBreakfastModal${item.breakfast_id}">
                <i class="ri ri-edit-line me-1"></i>Edit
              </button>
            </div>
          </div>
        </div>`;
}

// Only flip the switch state in edit modal (after a toggle action)
function syncEditModalSwitch(id, isAvailable) {
    const modal = document.getElementById(`editBreakfastModal${id}`);
    if (!modal) return;
    const cb = modal.querySelector('[name="is_available"]');
    if (cb) cb.checked = isAvailable;
}

// Set all toggle switches for a given item id — state=undefined means "don't change checked, just set disabled"
function setAllToggles(id, state, disabled) {
    document.querySelectorAll(`.availability-toggle[data-id="${id}"]`)
            .forEach(t => {
                if (state !== undefined) t.checked = state;
                if (disabled !== undefined) t.disabled = disabled;
            });
}

// ── Stats & utility ──────────────────────────────────────────────────────────

function updateStats(stats) {
    if (!stats) return;
    const el = key => document.querySelector(`[data-stat="${key}"]`);
    if (el('total'))       el('total').textContent       = stats.total_items;
    if (el('available'))   el('available').textContent   = stats.available_items;
    if (el('unavailable')) el('unavailable').textContent = stats.unavailable_items;
    if (el('avg_price'))   el('avg_price').textContent   =
        '₱' + parseFloat(stats.avg_price || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateEmptyState() {
    const hasRows  = document.querySelectorAll('#breakfastTableBody tr').length > 0;
    const empty    = document.getElementById('emptyState');
    if (empty) empty.style.display = hasRows ? 'none' : '';
}

function renumberRows() {
    document.querySelectorAll('#breakfastTableBody tr').forEach((row, i) => {
        const el = row.querySelector('.row-num');
        if (el) el.textContent = i + 1;
    });
}

function previewImage(input, previewEl) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        previewEl.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
    };
    reader.readAsDataURL(input.files[0]);
}

function resetImgPreview(previewEl) {
    if (previewEl) previewEl.innerHTML = `<i class="ri ri-image-line icon-32px text-body-secondary"></i>`;
}

function fmtPrice(val) {
    return parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function escJs(str) {
    return String(str ?? '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
}