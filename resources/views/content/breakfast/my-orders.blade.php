@extends('layouts/contentNavbarLayout')

@section('title', 'My Breakfast Orders')

@section('page-style')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap');

  .mo-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; }
  .mo-header h4 { font-family:'Playfair Display',serif; font-size:1.6rem; font-weight:700; margin:0; }

  .mo-badge {
    display:inline-flex; align-items:center; gap:.35rem;
    font-size:.72rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
    padding:.3rem .8rem; border-radius:999px;
  }
  .mo-badge.pending    { background:rgba(var(--bs-warning-rgb,255,171,0),.15); color:var(--bs-warning); }
  .mo-badge.processing { background:rgba(var(--bs-info-rgb,3,195,236),.15);    color:var(--bs-info); }
  .mo-badge.completed  { background:rgba(var(--bs-success-rgb,113,221,55),.15);color:var(--bs-success); }
  .mo-badge.cancelled  { background:rgba(var(--bs-danger-rgb,255,62,29),.15);  color:var(--bs-danger); }

  .mo-card {
    background:var(--bs-body-bg);
    border:1.5px solid var(--bs-border-color);
    border-radius:var(--bs-border-radius-lg);
    margin-bottom:1rem;
    overflow:hidden;
    transition:box-shadow .2s;
  }
  .mo-card:hover { box-shadow:var(--bs-box-shadow); }

  .mo-card-head {
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem;
    padding:.9rem 1.25rem;
    border-bottom:1px solid var(--bs-border-color);
    background:var(--bs-tertiary-bg);
  }
  .mo-order-id { font-size:.78rem; color:var(--bs-secondary-color); }
  .mo-order-id strong { color:var(--bs-body-color); }
  .mo-order-time { font-size:.78rem; color:var(--bs-secondary-color); }

  .mo-card-body { padding:1rem 1.25rem; }

  .mo-items { width:100%; border-collapse:collapse; font-size:.83rem; }
  .mo-items th { font-weight:600; color:var(--bs-secondary-color); padding:.35rem .5rem; text-align:left; border-bottom:1px solid var(--bs-border-color); }
  .mo-items td { padding:.45rem .5rem; border-bottom:1px solid var(--bs-border-color); }
  .mo-items tr:last-child td { border-bottom:none; }
  .mo-items .text-end { text-align:right; }

  .mo-total-row { display:flex; justify-content:flex-end; align-items:center; gap:.75rem; margin-top:.75rem; padding-top:.75rem; border-top:1px solid var(--bs-border-color); }
  .mo-total-label { font-size:.85rem; color:var(--bs-secondary-color); }
  .mo-total-val { font-family:'Playfair Display',serif; font-size:1.2rem; font-weight:700; color:var(--bs-primary); }

  .mo-empty { text-align:center; padding:4rem 1rem; color:var(--bs-secondary-color); }
  .mo-empty i { font-size:3rem; display:block; margin-bottom:.75rem; }

  .mo-skeleton { animation:mo-pulse 1.4s ease-in-out infinite; }
  @keyframes mo-pulse { 0%,100%{ opacity:1 } 50%{ opacity:.45 } }
  .mo-skel-line { height:14px; background:var(--bs-tertiary-bg); border-radius:6px; margin-bottom:.5rem; }
</style>
@endsection

@section('content')

<div class="mo-header">
  <div>
    <h4><i class="ri-list-check me-2 text-primary"></i>My Breakfast Orders</h4>
    @if($guest['status'] === 'checked-in')
      <small class="text-muted">Room {{ $guest['room_number'] ?? '—' }}</small>
    @endif
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary btn-sm" id="refreshBtn" onclick="refreshOrders()">
      <i class="ri-refresh-line me-1"></i>Refresh
    </button>
    <a href="{{ route('guest.breakfast.menu') }}" class="btn btn-primary btn-sm">
      <i class="ri-add-line me-1"></i>New Order
    </a>
  </div>
</div>

@if($guest['status'] !== 'checked-in')
  <div class="mo-empty">
    <i class="ri-hotel-line text-warning"></i>
    <h6>Not Checked In</h6>
    <p class="mb-0 small">Order history is available while you are checked in.</p>
  </div>
@else
  <div id="moContainer">
    @if($orders->isEmpty())
      <div class="mo-empty" id="moEmpty">
        <i class="ri-shopping-basket-line"></i>
        <h6>No orders yet</h6>
        <p class="mb-3 small">Your breakfast orders will appear here.</p>
        <a href="{{ route('guest.breakfast.menu') }}" class="btn btn-primary btn-sm">
          <i class="ri-add-line me-1"></i>Order Breakfast
        </a>
      </div>
    @else
      @foreach($orders as $order)
        @include('content.breakfast._order-card', ['order' => $order])
      @endforeach
    @endif
  </div>
@endif

@endsection

@section('page-script')
<script>
  const ORDERS_URL    = '{{ route("guest.breakfast.orders") }}'; {{-- ✅ GET JSON endpoint --}}
  const MENU_URL      = '{{ route("guest.breakfast.menu") }}';
  const IS_CHECKED_IN = {{ $guest['status'] === 'checked-in' ? 'true' : 'false' }};

  const statusMap = {
    pending:    { cls:'pending',    icon:'ri-time-line',         label:'Pending' },
    processing: { cls:'processing', icon:'ri-loader-4-line',     label:'Processing' },
    completed:  { cls:'completed',  icon:'ri-check-double-line', label:'Completed' },
    cancelled:  { cls:'cancelled',  icon:'ri-close-circle-line', label:'Cancelled' },
  };

  function badgeHtml(status) {
    const s = statusMap[status] ?? { cls:'pending', icon:'ri-question-line', label: status };
    return `<span class="mo-badge ${s.cls}"><i class="${s.icon}"></i>${s.label}</span>`;
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function renderOrderCard(o) {
    const items = (o.items ?? []).map(i => `
      <tr>
        <td>${escHtml(i.name ?? '—')}</td>
        <td class="text-end">${i.qty}</td>
        <td class="text-end">₱${parseFloat(i.price).toFixed(2)}</td>
        <td class="text-end">₱${parseFloat(i.subtotal).toFixed(2)}</td>
      </tr>`).join('');

    return `
      <div class="mo-card">
        <div class="mo-card-head">
          <div>
            <div class="mo-order-id">Order <strong>#${o.service_request_id}</strong></div>
            <div class="mo-order-time"><i class="ri-time-line me-1"></i>${escHtml(o.requested_at ?? '—')}</div>
          </div>
          ${badgeHtml(o.status)}
        </div>
        <div class="mo-card-body">
          <table class="mo-items">
            <thead>
              <tr>
                <th>Item</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody>${items}</tbody>
          </table>
          <div class="mo-total-row">
            <span class="mo-total-label">Total</span>
            <span class="mo-total-val">₱${parseFloat(o.total).toFixed(2)}</span>
          </div>
        </div>
      </div>`;
  }

  async function refreshOrders() {
    if (!IS_CHECKED_IN) return;
    const btn = document.getElementById('refreshBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Refreshing…';

    try {
      const res  = await fetch(ORDERS_URL, { headers: { Accept: 'application/json' } });
      const data = await res.json();

      const container = document.getElementById('moContainer');
      if (!container) return;

      if (!data.success || !data.orders?.length) {
        container.innerHTML = `
          <div class="mo-empty" id="moEmpty">
            <i class="ri-shopping-basket-line"></i>
            <h6>No orders yet</h6>
            <p class="mb-3 small">Your breakfast orders will appear here.</p>
            <a href="${MENU_URL}" class="btn btn-primary btn-sm">
              <i class="ri-add-line me-1"></i>Order Breakfast
            </a>
          </div>`;
      } else {
        container.innerHTML = data.orders.map(renderOrderCard).join('');
      }
    } catch (err) {
      console.error(err);
      toastr.error('Failed to refresh orders.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="ri-refresh-line me-1"></i>Refresh';
    }
  }

  /* Minimal toastr shim in case it's not loaded on this page */
  if (typeof toastr === 'undefined') {
    window.toastr = { error: msg => console.error(msg), success: msg => console.log(msg) };
  }
</script>
@endsection