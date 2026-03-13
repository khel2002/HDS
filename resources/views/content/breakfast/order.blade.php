@extends('layouts/contentNavbarLayout')

@section('title', 'Order Breakfast')

@section('page-style')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap');

  /* ── Hero ───────────────────────────────────────────────────────── */
  .ob-hero {
    position:relative; height:220px;
    border-radius:var(--bs-border-radius-lg,.625rem);
    overflow:hidden; margin-bottom:1.5rem;
    background:url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1600') center/cover no-repeat;
  }
  .ob-hero-overlay { position:absolute; inset:0; background:linear-gradient(to top,rgba(10,9,25,.9) 45%,rgba(10,9,25,.3)); }
  .ob-hero-body { position:absolute; bottom:0; left:0; right:0; padding:1.75rem 2rem; z-index:2; }
  .ob-hero-title { font-family:'Playfair Display',serif; font-size:2rem; font-weight:700; color:#fff; margin:0 0 .25rem; }
  .ob-hero-sub { color:rgba(255,255,255,.7); font-size:.88rem; margin:0; }

  /* ── Layout ──────────────────────────────────────────────────────── */
  .ob-layout { display:grid; grid-template-columns:1fr 360px; gap:1.5rem; align-items:start; }
  @media(max-width:991px){ .ob-layout{ grid-template-columns:1fr; } }

  /* ── Grid ────────────────────────────────────────────────────────── */
  .ob-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
  @media(max-width:1199px){ .ob-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:575px)  { .ob-grid{ grid-template-columns:1fr; } }

  /* ── Card ────────────────────────────────────────────────────────── */
  .ob-card {
    background:var(--bs-body-bg);
    border:1.5px solid var(--bs-border-color);
    border-radius:var(--bs-border-radius-lg);
    overflow:hidden; display:flex; flex-direction:column;
    cursor:pointer;
    transition:box-shadow .2s,transform .2s,border-color .18s;
    position:relative;
  }
  .ob-card:hover { box-shadow:var(--bs-box-shadow); transform:translateY(-3px); border-color:var(--bs-primary); }
  .ob-card.selected { border-color:var(--bs-primary); box-shadow:0 0 0 3px rgba(var(--bs-primary-rgb,105,108,255),.18); }
  .ob-check { display:none; position:absolute; top:.55rem; right:.55rem; width:24px; height:24px; background:var(--bs-primary); color:#fff; border-radius:50%; font-size:.75rem; align-items:center; justify-content:center; z-index:3; }
  .ob-card.selected .ob-check { display:flex; }

  .ob-img-wrap { height:140px; overflow:hidden; background:var(--bs-tertiary-bg); }
  .ob-img-wrap img { width:100%; height:100%; object-fit:cover; transition:transform .35s; }
  .ob-card:hover .ob-img-wrap img { transform:scale(1.07); }
  .ob-img-ph { width:100%; height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.3rem; color:var(--bs-secondary-color); }
  .ob-img-ph i { font-size:2rem; }

  .ob-card-body { padding:.8rem .9rem .9rem; flex:1; display:flex; flex-direction:column; }
  .ob-card-name { font-weight:600; font-size:.88rem; margin:0 0 .25rem; color:var(--bs-heading-color); }
  .ob-card-desc { font-size:.75rem; color:var(--bs-secondary-color); margin:0; flex:1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
  .ob-card-footer { display:flex; align-items:center; justify-content:space-between; margin-top:.65rem; padding-top:.6rem; border-top:1px solid var(--bs-border-color); }
  .ob-price { font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:700; color:var(--bs-primary); }

  .ob-add-btn { width:30px; height:30px; border-radius:50%; border:none; background:var(--bs-primary); color:#fff; font-size:1.1rem; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:background .15s,transform .12s; flex-shrink:0; }
  .ob-add-btn:hover { opacity:.85; transform:scale(1.1); }
  .ob-card.selected .ob-add-btn { background:var(--bs-danger); }
  .ob-add-btn .ico-add { display:flex; align-items:center; justify-content:center; }
  .ob-add-btn .ico-rem { display:none; align-items:center; justify-content:center; }
  .ob-card.selected .ob-add-btn .ico-add { display:none; }
  .ob-card.selected .ob-add-btn .ico-rem { display:flex; }

  /* ── Sidebar / Basket ────────────────────────────────────────────── */
  .ob-sidebar { position:sticky; top:80px; }
  .ob-basket-card {
    border:1.5px solid rgba(var(--bs-primary-rgb,105,108,255),.25);
    border-radius:var(--bs-border-radius-lg);
    background:var(--bs-body-bg);
    overflow:hidden;
  }
  .ob-basket-hdr { padding:1rem 1.25rem; border-bottom:1px solid var(--bs-border-color); display:flex; align-items:center; justify-content:space-between; }
  .ob-basket-hdr h6 { font-family:'Playfair Display',serif; font-size:1rem; margin:0; }
  .ob-basket-body { padding:1rem 1.25rem; min-height:80px; }
  .ob-basket-empty { text-align:center; padding:1.5rem 1rem; color:var(--bs-secondary-color); font-size:.83rem; }
  .ob-basket-empty i { font-size:1.8rem; display:block; margin-bottom:.4rem; }

  .ob-item { display:flex; align-items:center; gap:.75rem; padding:.6rem 0; border-bottom:1px solid var(--bs-border-color); }
  .ob-item:last-child { border-bottom:none; }
  .ob-item-name { flex:1; font-size:.83rem; font-weight:500; }
  .ob-qty { display:flex; align-items:center; gap:.2rem; }
  .ob-qty-btn { width:22px; height:22px; border-radius:50%; border:1px solid var(--bs-border-color); background:var(--bs-body-bg); font-size:.8rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .15s; padding:0; line-height:1; }
  .ob-qty-btn:hover { background:var(--bs-primary); color:#fff; border-color:transparent; }
  .ob-qty-val { font-size:.82rem; font-weight:600; min-width:18px; text-align:center; }
  .ob-item-sub { font-size:.82rem; font-weight:600; color:var(--bs-primary); white-space:nowrap; }

  .ob-basket-footer { padding:1rem 1.25rem; border-top:1px solid var(--bs-border-color); }
  .ob-total-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:.9rem; }
  .ob-total-label { font-size:.85rem; color:var(--bs-secondary-color); }
  .ob-total-val { font-family:'Playfair Display',serif; font-size:1.3rem; font-weight:700; color:var(--bs-primary); }

  /* ── Not checked-in banner ───────────────────────────────────────── */
  .ob-locked { border:1.5px dashed var(--bs-border-color); border-radius:var(--bs-border-radius-lg); padding:2.5rem 1.5rem; text-align:center; color:var(--bs-secondary-color); }
  .ob-locked i { font-size:2.5rem; display:block; margin-bottom:.75rem; }
</style>
@endsection

@section('content')

{{-- Hero --}}
<div class="ob-hero">
  <div class="ob-hero-overlay"></div>
  <div class="ob-hero-body">
    <h1 class="ob-hero-title">Order Breakfast</h1>
    <p class="ob-hero-sub">
      @if($guest['status'] === 'checked-in')
        Room {{ $guest['room_number'] ?? '—' }} · Select items and submit your order
      @else
        Browse available items — ordering requires check-in
      @endif
    </p>
  </div>
</div>

@if($guest['status'] !== 'checked-in')
  {{-- Not checked in – show menu read-only with locked basket --}}
  <div class="ob-locked mb-4">
    <i class="ri-lock-2-line text-warning"></i>
    <h6 class="mb-1">Check-in Required</h6>
    <p class="mb-3 small">You need to be checked in to place a breakfast order.</p>
    <a href="{{ route('guest.breakfast.menu') }}" class="btn btn-outline-primary btn-sm">
      <i class="ri-arrow-left-line me-1"></i>Back to Menu
    </a>
  </div>
@endif

<div class="ob-layout">

  {{-- Left – Menu Grid --}}
  <div>
    @if($menuItems->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="ri-restaurant-line d-block mb-2" style="font-size:2.5rem;"></i>
        <p class="mb-0">No items available right now.</p>
      </div>
    @else
      <div class="ob-grid" id="obGrid">
        @foreach($menuItems as $item)
          <div class="ob-card"
               data-id="{{ $item->breakfast_id }}"
               data-name="{{ e($item->meal_name) }}"
               data-price="{{ $item->price }}"
               onclick="{{ $guest['status'] === 'checked-in' ? 'obToggle(this)' : 'toastr.info(\"Check-in required to order.\")' }}">

            <span class="ob-check"><i class="ri-check-line"></i></span>

            <div class="ob-img-wrap">
              @if($item->image_path)
                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ e($item->meal_name) }}" loading="lazy">
              @else
                <div class="ob-img-ph"><i class="ri-bowl-line"></i></div>
              @endif
            </div>

            <div class="ob-card-body">
              <p class="ob-card-name">{{ $item->meal_name }}</p>
              @if($item->description)
                <p class="ob-card-desc">{{ $item->description }}</p>
              @endif
              <div class="ob-card-footer">
                <span class="ob-price">₱{{ number_format($item->price, 2) }}</span>
                @if($guest['status'] === 'checked-in')
                  <button class="ob-add-btn"
                          onclick="event.stopPropagation(); obToggle(this.closest('.ob-card'))"
                          title="Add / Remove">
                    <span class="ico-add"><i class="ri-add-line"></i></span>
                    <span class="ico-rem"><i class="ri-subtract-line"></i></span>
                  </button>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Right – Basket --}}
  <div class="ob-sidebar">
    @if($guest['status'] === 'checked-in')
      <div class="ob-basket-card">
        <div class="ob-basket-hdr">
          <h6><i class="ri-shopping-basket-2-line me-1 text-primary"></i>Your Order</h6>
          <span class="badge bg-label-primary" id="obCount">0 items</span>
        </div>
        <div class="ob-basket-body" id="obBasketBody">
          <div class="ob-basket-empty" id="obEmpty">
            <i class="ri-add-circle-line"></i>
            Select items from the menu
          </div>
        </div>
        <div class="ob-basket-footer" id="obFooter" style="display:none;">
          <div class="ob-total-row">
            <span class="ob-total-label">Total</span>
            <span class="ob-total-val" id="obTotal">₱0.00</span>
          </div>
          <button class="btn btn-primary w-100" id="obSubmitBtn" onclick="obSubmit()">
            <i class="ri-send-plane-line me-1"></i>Submit Order
          </button>
          <a href="{{ route('guest.breakfast.my-orders') }}" class="btn btn-outline-secondary w-100 mt-2 btn-sm">
            <i class="ri-list-check me-1"></i>View My Orders
          </a>
        </div>
      </div>
    @else
      <div class="ob-locked">
        <i class="ri-lock-2-line text-warning"></i>
        <p class="mb-0 small">Check-in required to order</p>
      </div>
    @endif
  </div>

</div>
@endsection

@section('page-script')
<script>
  const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const ORDER_URL  = '{{ route("guest.breakfast.store") }}';
  const basket     = {};

  function obToggle(card) {
    const { id, name, price } = card.dataset;
    if (basket[id]) {
      delete basket[id];
      card.classList.remove('selected');
    } else {
      basket[id] = { name, price: parseFloat(price), qty: 1 };
      card.classList.add('selected');
    }
    renderBasket();
  }

  function obQty(id, delta) {
    if (!basket[id]) return;
    const newQty = basket[id].qty + delta;
    if (newQty < 1) {
      delete basket[id];
      document.querySelector(`.ob-card[data-id="${id}"]`)?.classList.remove('selected');
    } else {
      basket[id].qty = newQty;
    }
    renderBasket();
  }

  function renderBasket() {
    const body   = document.getElementById('obBasketBody');
    const footer = document.getElementById('obFooter');
    const empty  = document.getElementById('obEmpty');
    const count  = document.getElementById('obCount');
    const total  = document.getElementById('obTotal');
    if (!body) return;

    const keys = Object.keys(basket);
    count.textContent = keys.length + ' item' + (keys.length !== 1 ? 's' : '');

    if (!keys.length) {
      empty.style.display = '';
      footer.style.display = 'none';
      // Remove any existing rows
      body.querySelectorAll('.ob-item').forEach(el => el.remove());
      return;
    }

    empty.style.display = 'none';
    footer.style.display = '';

    // Rebuild item rows
    body.querySelectorAll('.ob-item').forEach(el => el.remove());
    let sum = 0;
    keys.forEach(id => {
      const { name, price, qty } = basket[id];
      sum += price * qty;
      const div = document.createElement('div');
      div.className = 'ob-item';
      div.innerHTML = `
        <span class="ob-item-name">${escHtml(name)}</span>
        <div class="ob-qty">
          <button class="ob-qty-btn" onclick="obQty('${id}',-1)">−</button>
          <span class="ob-qty-val">${qty}</span>
          <button class="ob-qty-btn" onclick="obQty('${id}',1)">+</button>
        </div>
        <span class="ob-item-sub">₱${(price * qty).toFixed(2)}</span>`;
      body.insertBefore(div, footer);
    });
    total.textContent = '₱' + sum.toFixed(2);
  }

  async function obSubmit() {
    const keys = Object.keys(basket);
    if (!keys.length) { toastr.warning('Please select at least one item.'); return; }

    const btn = document.getElementById('obSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Placing order…';

    const items = keys.map(id => ({ breakfast_id: parseInt(id), quantity: basket[id].qty }));

    try {
      const res  = await fetch(ORDER_URL, {
        method:  'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
        body:    JSON.stringify({ service_type:'food', description:'Breakfast order', items }),
      });
      const data = await res.json();

      if (data.success) {
        toastr.success('Order placed! Redirecting to My Orders…');
        setTimeout(() => window.location = '{{ route("guest.breakfast.my-orders") }}', 1600);
      } else {
        toastr.error(data.error ?? 'Something went wrong.');
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-send-plane-line me-1"></i>Submit Order';
      }
    } catch (err) {
      console.error(err);
      toastr.error('Request failed. Check your connection.');
      btn.disabled = false;
      btn.innerHTML = '<i class="ri-send-plane-line me-1"></i>Submit Order';
    }
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
</script>
@endsection