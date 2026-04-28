/**
 * staff_menu_script.js
 * Staff breakfast menu — availability toggle only.
 * No add / edit / delete logic.
 */

document.addEventListener('DOMContentLoaded', function () {

  // ── Helpers ──────────────────────────────────────────────────────────────

  const csrf = () =>
    document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
  });

  function showSuccess(msg) { Toast.fire({ icon: 'success', title: msg }); }
  function showError(msg)   { Swal.fire({ icon: 'error', title: 'Error!', text: msg, confirmButtonText: 'OK' }); }

  // ── Session flash messages ────────────────────────────────────────────────

  const successEl = document.querySelector('[data-success-message]');
  const errorEl   = document.querySelector('[data-error-message]');
  if (successEl) showSuccess(successEl.getAttribute('data-success-message'));
  if (errorEl)   showError(errorEl.getAttribute('data-error-message'));

  // ── Bootstrap tooltips ────────────────────────────────────────────────────

  document.querySelectorAll('[data-bs-toggle="tooltip"]')
          .forEach(el => bootstrap.Tooltip.getOrCreateInstance(el));

  // ── View toggle (table ↔ cards) ───────────────────────────────────────────

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

  // ── Live filtering ────────────────────────────────────────────────────────

  const searchInput        = document.getElementById('searchTable');
  const availabilityFilter = document.getElementById('filterAvailability');

  function filterItems() {
    const term  = searchInput.value.toLowerCase().trim();
    const avail = availabilityFilter.value;

    // Table rows
    document.querySelectorAll('#breakfastTableBody tr').forEach(row => {
      const match =
        (row.getAttribute('data-meal-name') || '').includes(term) &&
        (avail === '' || row.getAttribute('data-available') === avail);
      row.style.display = match ? '' : 'none';
    });

    // Cards
    document.querySelectorAll('.breakfast-card').forEach(card => {
      const match =
        (card.getAttribute('data-meal-name') || '').includes(term) &&
        (avail === '' || card.getAttribute('data-available') === avail);
      card.style.display = match ? '' : 'none';
    });
  }

  searchInput.addEventListener('keyup', filterItems);
  availabilityFilter.addEventListener('change', filterItems);

  document.getElementById('resetFilters').addEventListener('click', () => {
    searchInput.value        = '';
    availabilityFilter.value = '';
    filterItems();
  });

  // ── Availability toggle (delegated) ───────────────────────────────────────

  document.addEventListener('change', async function (e) {
    if (!e.target.matches('.availability-toggle')) return;

    const toggle     = e.target;
    const id         = toggle.dataset.id;
    const mealName   = toggle.dataset.name;
    const url        = toggle.dataset.toggleUrl;
    const willEnable = toggle.checked;

    // Optimistic UI: flip all matching toggles & disable them
    setAllToggles(id, willEnable, true);

    try {
      const res  = await fetch(url, {
        method:  'PATCH',
        headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Something went wrong.');

      const isAvailable = data.item.is_available;

      // Sync all toggles to server truth
      setAllToggles(id, isAvailable, false);

      // Update data-available on table row and card
      document.querySelectorAll(
        `#breakfastTableBody tr[data-id="${id}"],
         .breakfast-card[data-id="${id}"]`
      ).forEach(el => el.setAttribute('data-available', isAvailable ? '1' : '0'));

      // Update card opacity & badge
      const card = document.querySelector(`.breakfast-card[data-id="${id}"]`);
      if (card) {
        const inner = card.querySelector('.card');
        if (inner) {
          inner.classList.toggle('opacity-65', !isAvailable);
        }
        const badge = card.querySelector('.badge');
        if (badge) {
          badge.className = `badge ${isAvailable ? 'bg-label-success' : 'bg-label-danger'} ms-2 flex-shrink-0`;
          badge.textContent = isAvailable ? 'Available' : 'Unavailable';
        }
        const label = card.querySelector('.form-check-label');
        if (label) label.textContent = isAvailable ? 'Available' : 'Unavailable';
      }

      // Update view-modal badge
      const modal = document.getElementById(`viewBreakfastModal${id}`);
      if (modal) {
        const modalBadge = modal.querySelector('.item-badge');
        if (modalBadge) {
          modalBadge.className = `item-badge badge ${isAvailable ? 'bg-success' : 'bg-danger'}`;
          modalBadge.textContent = isAvailable ? 'Available' : 'Unavailable';
        }
      }

      // Update stats
      updateStats(data.stats);

      showSuccess(
        `"${mealName}" is now ${isAvailable ? 'available' : 'unavailable'}.`
      );
    } catch (err) {
      // Revert on failure
      setAllToggles(id, !willEnable, false);
      showError(err.message);
    }
  });

  // ── Helpers ───────────────────────────────────────────────────────────────

  function setAllToggles(id, state, disabled) {
    document.querySelectorAll(`.availability-toggle[data-id="${id}"]`)
            .forEach(t => {
              if (state    !== undefined) t.checked  = state;
              if (disabled !== undefined) t.disabled = disabled;
            });
  }

  function updateStats(stats) {
    if (!stats) return;
    const el = key => document.querySelector(`[data-stat="${key}"]`);
    if (el('total'))       el('total').textContent       = stats.total_items;
    if (el('available'))   el('available').textContent   = stats.available_items;
    if (el('unavailable')) el('unavailable').textContent = stats.unavailable_items;
    if (el('avg_price'))   el('avg_price').textContent   =
      '₱' + parseFloat(stats.avg_price || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
  }

});