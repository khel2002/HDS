@extends('layouts/contentNavbarLayout')

@section('title', 'Room Service')

@section('page-style')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --rs-primary:  var(--bs-primary, #696CFF);
    --rs-border:   var(--bs-border-color);
    --rs-card-bg:  var(--bs-body-bg);
    --rs-radius:   14px;
    --rs-serif:    'DM Serif Display', Georgia, serif;
    --rs-sans:     'DM Sans', system-ui, sans-serif;
  }
  * { font-family: var(--rs-sans); }

  /* ── Hero ───────────────────────────────────────────────────── */
  .rs-hero {
    position:relative; height:200px;
    border-radius:var(--rs-radius); overflow:hidden; margin-bottom:1.5rem;
    background:url('https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=1600') center/cover no-repeat;
  }
  .rs-hero::after {
    content:''; position:absolute; inset:0;
    background:linear-gradient(135deg,rgba(15,20,40,.93) 0%,rgba(15,20,40,.55) 60%,transparent 100%);
  }
  .rs-hero-body { position:absolute; bottom:0; left:0; right:0; padding:1.5rem 2rem; z-index:2; }
  .rs-eyebrow {
    display:inline-flex; align-items:center; gap:.4rem;
    background:var(--rs-primary); color:#fff;
    font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase;
    padding:.22rem .8rem; border-radius:999px; margin-bottom:.6rem;
  }
  .rs-hero-title { font-family:var(--rs-serif); font-size:2rem; color:#fff; margin:0 0 .2rem; line-height:1.2; }
  .rs-hero-sub   { color:rgba(255,255,255,.65); font-size:.84rem; margin:0; font-weight:300; }
  .rs-hero-sub strong { color:#c9f0ff; font-weight:600; }

  /* ── Notice banner ──────────────────────────────────────────── */
  .rs-notice {
    display:flex; align-items:flex-start; gap:1rem;
    padding:1rem 1.25rem; border-radius:var(--rs-radius);
    margin-bottom:1.5rem; border:1.5px solid;
  }
  .rs-notice.success { background:rgba(113,221,55,.07); border-color:rgba(113,221,55,.25); color:var(--bs-success); }
  .rs-notice.info    { background:rgba(105,108,255,.07); border-color:rgba(105,108,255,.2);  color:var(--rs-primary); }
  .rs-notice-icon { font-size:1.25rem; flex-shrink:0; margin-top:.05rem; }
  .rs-notice h6 { font-size:.85rem; font-weight:700; margin:0 0 .2rem; }
  .rs-notice p  { font-size:.78rem; margin:0; color:var(--bs-secondary-color); }

  /* ── Page layout ────────────────────────────────────────────── */
  .rs-layout {
    display:grid; grid-template-columns:1fr 320px;
    gap:1.5rem; align-items:start;
  }
  @media (max-width:991px) { .rs-layout { grid-template-columns:1fr; } }

  /* ── Category tabs ──────────────────────────────────────────── */
  .rs-tabs { display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:1.25rem; }
  .rs-tab {
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.35rem 1rem; border-radius:999px;
    border:1.5px solid var(--rs-border);
    background:var(--rs-card-bg); color:var(--bs-body-color);
    font-size:.79rem; font-weight:500; cursor:pointer; transition:all .15s;
  }
  .rs-tab i { font-size:.9rem; }
  .rs-tab:hover  { border-color:var(--rs-primary); color:var(--rs-primary); }
  .rs-tab.active { background:var(--rs-primary); border-color:var(--rs-primary); color:#fff; }

  /* ── Category section ───────────────────────────────────────── */
  .rs-section { margin-bottom:1.75rem; }
  .rs-section.hidden { display:none; }
  .rs-cat-hdr {
    display:flex; align-items:center; gap:.65rem;
    margin-bottom:.9rem; padding-bottom:.65rem;
    border-bottom:1.5px solid var(--rs-border);
  }
  .rs-cat-icon {
    width:36px; height:36px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:1.05rem;
  }
  .rs-cat-icon.housekeeping { background:rgba(23,162,184,.13); color:#17a2b8; }
  .rs-cat-icon.toiletries   { background:rgba(40,167,69,.13);  color:#28a745; }
  .rs-cat-icon.technical    { background:rgba(255,193,7,.18);   color:#c49a00; }
  .rs-cat-icon.comfort      { background:rgba(105,108,255,.13); color:#696CFF; }
  .rs-cat-hdr h6 { font-family:var(--rs-serif); font-size:1.05rem; margin:0; font-style:italic; }
  .rs-cat-hdr small { margin-left:auto; font-size:.74rem; color:var(--bs-secondary-color); font-style:italic; }

  /* ── Item grid ──────────────────────────────────────────────── */
  .rs-grid {
    display:grid; grid-template-columns:repeat(2, 1fr); gap:.75rem;
  }
  @media (max-width:575px) { .rs-grid { grid-template-columns:1fr; } }

  /* ── Item card ──────────────────────────────────────────────── */
  .rs-item {
    background:var(--rs-card-bg);
    border:1.5px solid var(--rs-border);
    border-radius:12px; padding:.9rem 1rem;
    display:flex; align-items:flex-start; gap:.75rem;
    position:relative; cursor:pointer;
    transition:border-color .15s, box-shadow .15s, transform .15s;
    user-select:none;
  }
  .rs-item:not(.unavailable):hover {
    border-color:var(--rs-primary);
    box-shadow:0 4px 16px rgba(105,108,255,.12);
    transform:translateY(-1px);
  }
  .rs-item.selected {
    border-color:var(--rs-primary);
    box-shadow:0 0 0 3px rgba(105,108,255,.15);
    background:rgba(105,108,255,.03);
  }
  .rs-item.unavailable { opacity:.42; pointer-events:none; }

  /* selected check badge */
  .rs-check {
    display:none; position:absolute; top:.55rem; right:.55rem;
    width:20px; height:20px; border-radius:50%;
    background:var(--rs-primary); color:#fff;
    font-size:.65rem; align-items:center; justify-content:center;
  }
  .rs-item.selected .rs-check { display:flex; }

  /* item icon */
  .rs-item-icon {
    width:38px; height:38px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:1.1rem;
    background:var(--bs-tertiary-bg); color:var(--bs-secondary-color);
    transition:background .15s, color .15s;
  }
  .rs-item.selected .rs-item-icon { background:rgba(105,108,255,.12); color:var(--rs-primary); }

  .rs-item-body { flex:1; min-width:0; }
  .rs-item-name {
    font-size:.83rem; font-weight:600; margin:0 0 .15rem;
    color:var(--bs-heading-color); line-height:1.3;
  }
  .rs-item-desc {
    font-size:.72rem; color:var(--bs-secondary-color); margin:0;
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
  }
  .rs-unavail-pill {
    display:inline-block; margin-top:.3rem;
    font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em;
    color:var(--bs-danger); opacity:.75;
  }

  /* qty stepper (shown when selected + requires_quantity) */
  .rs-qty {
    display:none; align-items:center; gap:.3rem; margin-top:.55rem;
  }
  .rs-item.selected .rs-qty { display:flex; }
  .rs-qty-btn {
    width:24px; height:24px; border-radius:50%; flex-shrink:0;
    border:1.5px solid var(--rs-border); background:var(--rs-card-bg);
    font-size:.8rem; font-weight:700; line-height:1; padding:0;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; transition:all .13s;
  }
  .rs-qty-btn.minus:hover { background:rgba(255,71,87,.1); border-color:#FF4757; color:#FF4757; }
  .rs-qty-btn.plus:hover  { background:var(--rs-primary); border-color:var(--rs-primary); color:#fff; }
  .rs-qty-val { font-size:.8rem; font-weight:700; min-width:18px; text-align:center; }

  /* ── Sidebar / Basket ───────────────────────────────────────── */
  .rs-sidebar { position:sticky; top:80px; }
  .rs-basket {
    border-radius:var(--rs-radius);
    border:1.5px solid rgba(105,108,255,.2);
    background:var(--rs-card-bg); overflow:hidden;
    box-shadow:0 4px 20px rgba(0,0,0,.06);
  }
  .rs-basket-head {
    padding:.85rem 1.15rem; border-bottom:1px solid var(--rs-border);
    display:flex; align-items:center; justify-content:space-between;
    background:rgba(105,108,255,.04);
  }
  .rs-basket-head h6 { font-family:var(--rs-serif); font-size:.95rem; margin:0; font-style:italic; }
  .rs-basket-count {
    font-size:.72rem; font-weight:700; letter-spacing:.04em;
    background:var(--rs-primary); color:#fff; padding:.2rem .65rem; border-radius:999px;
  }

  .rs-basket-body { padding:.85rem 1.15rem; min-height:72px; }
  .rs-basket-empty { text-align:center; padding:1.4rem .5rem; color:var(--bs-secondary-color); font-size:.8rem; }
  .rs-basket-empty i { font-size:1.8rem; display:block; margin-bottom:.4rem; opacity:.5; }

  /* basket line */
  .rs-line {
    display:flex; align-items:center; gap:.5rem;
    padding:.5rem 0; border-bottom:1px solid var(--rs-border);
    animation:rsSlideIn .14s ease;
  }
  .rs-line:last-child { border-bottom:none; }
  @keyframes rsSlideIn { from{opacity:0;transform:translateX(6px)} to{opacity:1;transform:none} }
  .rs-line-icon { font-size:.95rem; flex-shrink:0; color:var(--rs-primary); width:20px; text-align:center; }
  .rs-line-info { flex:1; min-width:0; }
  .rs-line-name { font-size:.79rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .rs-line-cat  { font-size:.68rem; color:var(--bs-secondary-color); text-transform:capitalize; }
  .rs-line-qty  { font-size:.76rem; font-weight:700; color:var(--rs-primary); white-space:nowrap; flex-shrink:0; }

  /* ── Notes textarea ─────────────────────────────────────────── */
  .rs-notes-section { padding:.75rem 1.15rem; border-top:1px solid var(--rs-border); }
  .rs-notes-label {
    display:flex; align-items:center; gap:.35rem;
    font-size:.72rem; font-weight:600; letter-spacing:.04em;
    text-transform:uppercase; color:var(--bs-secondary-color); margin-bottom:.45rem;
  }
  .rs-notes-textarea {
    width:100%; padding:.55rem .75rem; font-size:.8rem;
    font-family:var(--rs-sans); color:var(--bs-body-color);
    background:var(--bs-tertiary-bg); border:1.5px solid var(--rs-border);
    border-radius:10px; resize:none; line-height:1.5;
    transition:border-color .15s, box-shadow .15s;
  }
  .rs-notes-textarea::placeholder { color:var(--bs-secondary-color); opacity:.7; font-size:.78rem; }
  .rs-notes-textarea:focus {
    outline:none; border-color:var(--rs-primary);
    box-shadow:0 0 0 3px rgba(105,108,255,.12);
    background:var(--rs-card-bg);
  }
  .rs-notes-hint {
    font-size:.68rem; color:var(--bs-secondary-color);
    margin-top:.3rem; display:flex; justify-content:space-between;
  }

  /* ── Basket footer ──────────────────────────────────────────── */
  .rs-basket-foot { padding:.85rem 1.15rem; border-top:1px solid var(--rs-border); }
  .rs-summary-row {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:.85rem; font-size:.82rem;
  }
  .rs-summary-lbl { color:var(--bs-secondary-color); text-transform:uppercase; letter-spacing:.05em; font-size:.74rem; }
  .rs-summary-val { font-family:var(--rs-serif); font-size:1.2rem; color:var(--rs-primary); }

  /* ── Locked ─────────────────────────────────────────────────── */
  .rs-locked {
    border:2px dashed var(--rs-border); border-radius:var(--rs-radius);
    padding:2.25rem 1.5rem; text-align:center; color:var(--bs-secondary-color);
  }
  .rs-locked i { font-size:2rem; display:block; margin-bottom:.6rem; opacity:.6; }
  .rs-locked h6 { font-family:var(--rs-serif); font-size:.98rem; margin:0 0 .3rem; font-style:italic; }
  .rs-locked p  { font-size:.78rem; margin:0; }
</style>
@endsection

@section('content')
@php
  $checkedIn = $guest['status'] === 'checked-in';

  $categoryLabels = ['housekeeping'=>'Housekeeping','toiletries'=>'Toiletries','technical'=>'Technical','comfort'=>'Comfort'];
  $categoryIcons  = ['housekeeping'=>'ri-brush-line','toiletries'=>'ri-flask-line','technical'=>'ri-tools-line','comfort'=>'ri-sofa-line'];
@endphp

{{-- Hero --}}
<div class="rs-hero">
  <div class="rs-hero-body">
    <div class="rs-eyebrow"><i class="ri-concierge-bell-line" style="font-size:.65rem;"></i> Available 24 hours</div>
    <h1 class="rs-hero-title">Room Service</h1>
    <p class="rs-hero-sub">
      @if($checkedIn)
        Room <strong>{{ $guest['room_number'] ?? '—' }}</strong> &middot; Select services and submit your request
      @else
        Comfort and convenience, delivered to your door
      @endif
    </p>
  </div>
</div>

{{-- Notice --}}
@if($checkedIn)
  <div class="rs-notice success">
    <span class="rs-notice-icon"><i class="ri-shield-check-line"></i></span>
    <div>
      <h6>Request to Your Room</h6>
      <p>Choose what you need, add optional notes, then tap <strong>Submit Request</strong> — our staff will attend to you promptly.</p>
    </div>
  </div>
@else
  <div class="rs-notice info">
    <span class="rs-notice-icon"><i class="ri-information-line"></i></span>
    <div>
      <h6>Browse Our Services</h6>
      <p>Room service requests are available once you have checked in. Call ext. <strong>2000</strong> for immediate assistance.</p>
    </div>
  </div>
@endif

@php $hasAny = $items->isNotEmpty(); @endphp

@if($hasAny)
<div class="rs-layout">

  {{-- LEFT: catalog --}}
  <div>

    {{-- Category tabs --}}
    <div class="rs-tabs">
      <button class="rs-tab active" data-cat="all">
        <i class="ri-grid-line"></i> All
      </button>
      @foreach($categories as $cat)
        @if($items->has($cat))
          <button class="rs-tab" data-cat="{{ $cat }}">
            <i class="ri {{ $categoryIcons[$cat] }}"></i>
            {{ $categoryLabels[$cat] }}
          </button>
        @endif
      @endforeach
    </div>

    {{-- Category sections --}}
    @foreach($categories as $cat)
      @if($items->has($cat))
        @php $catItems = $items->get($cat); @endphp
        <div class="rs-section" data-section="{{ $cat }}">
          <div class="rs-cat-hdr">
            <div class="rs-cat-icon {{ $cat }}">
              <i class="ri {{ $categoryIcons[$cat] }}"></i>
            </div>
            <h6>{{ $categoryLabels[$cat] }}</h6>
            <small>{{ $catItems->where('is_available', true)->count() }} available</small>
          </div>
          <div class="rs-grid">
            @foreach($catItems as $item)
              <div class="rs-item {{ !$item->is_available ? 'unavailable' : '' }}"
                   id="rsItem-{{ $item->item_id }}"
                   data-id="{{ $item->item_id }}"
                   data-name="{{ e($item->item_name) }}"
                   data-cat="{{ $item->category }}"
                   data-icon="{{ $item->icon }}"
                   data-requires-qty="{{ $item->requires_quantity ? '1' : '0' }}"
                   data-available="{{ $item->is_available ? '1' : '0' }}"
                   onclick="rsToggle({{ $item->item_id }})">

                <span class="rs-check"><i class="ri-check-line" style="font-size:.7rem;"></i></span>

                <div class="rs-item-icon">
                  <i class="ri {{ $item->icon }}"></i>
                </div>

                <div class="rs-item-body">
                  <p class="rs-item-name">{{ $item->item_name }}</p>
                  @if($item->description)
                    <p class="rs-item-desc">{{ $item->description }}</p>
                  @endif
                  @if(!$item->is_available)
                    <span class="rs-unavail-pill">Currently unavailable</span>
                  @endif

                  {{-- Qty stepper for quantifiable items --}}
                  @if($item->is_available && $checkedIn && $item->requires_quantity)
                    <div class="rs-qty" id="rsQty-{{ $item->item_id }}" onclick="event.stopPropagation()">
                      <button type="button" class="rs-qty-btn minus" onclick="rsDec({{ $item->item_id }})">−</button>
                      <span class="rs-qty-val" id="rsQtyVal-{{ $item->item_id }}">1</span>
                      <button type="button" class="rs-qty-btn plus" onclick="rsInc({{ $item->item_id }})">+</button>
                    </div>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    @endforeach

  </div>

  {{-- RIGHT: request basket --}}
  <div class="rs-sidebar">
    @if($checkedIn)
      <div class="rs-basket">

        <div class="rs-basket-head">
          <h6><i class="ri-concierge-bell-line me-1 text-primary"></i>Your Request</h6>
          <span class="rs-basket-count" id="rsCount">0 items</span>
        </div>

        <div class="rs-basket-body" id="rsBasketBody">
          <div class="rs-basket-empty" id="rsEmpty">
            <i class="ri-add-circle-line"></i>
            Select services from the catalog
          </div>
        </div>

        {{-- Notes --}}
        <div class="rs-notes-section" id="rsNotesSection" style="display:none;">
          <div class="rs-notes-label"><i class="ri-chat-3-line"></i> Additional Notes</div>
          <textarea id="rsDescription" class="rs-notes-textarea" rows="3" maxlength="500"
            placeholder="e.g. Please bring by 9 PM, soft pillow preferred, room is on 2nd floor…"></textarea>
          <div class="rs-notes-hint">
            <span>Optional &mdash; seen by staff only</span>
            <span id="rsCharCount">0 / 500</span>
          </div>
        </div>

        {{-- Footer --}}
        <div class="rs-basket-foot" id="rsFooter" style="display:none;">
          <div class="rs-summary-row">
            <span class="rs-summary-lbl">Items selected</span>
            <span class="rs-summary-val" id="rsTotalItems">0</span>
          </div>
          <button type="button" class="btn btn-primary w-100" id="rsSubmitBtn" onclick="rsSubmit()">
            <i class="ri-send-plane-line me-1"></i>Submit Request
          </button>
          <a href="{{ route('guest.room-service.my-requests') }}"
             class="btn btn-outline-secondary w-100 mt-2 btn-sm">
            <i class="ri-list-check me-1"></i>View My Requests
          </a>
        </div>

      </div>

    @else
      <div class="rs-locked">
        <i class="ri-lock-2-line text-warning"></i>
        <h6>Check-in Required</h6>
        <p>Room service requests are available once you are checked in.</p>
      </div>
    @endif
  </div>

</div>
@else
  <div class="text-center py-5 text-body-secondary">
    <i class="ri-service-line" style="font-size:3rem; display:block; margin-bottom:.75rem; opacity:.4;"></i>
    <p class="mb-0">No room service items available at the moment.</p>
  </div>
@endif
@endsection

@section('page-script')
<style>
  #rs-toast-wrap {
    position:fixed; bottom:1.5rem; right:1.5rem;
    display:flex; flex-direction:column; gap:.5rem;
    z-index:9999; pointer-events:none;
  }
  .rs-toast {
    display:flex; align-items:center; gap:.7rem;
    padding:.75rem 1.1rem; border-radius:10px;
    font-size:.84rem; font-weight:500; min-width:240px; max-width:320px;
    box-shadow:0 8px 24px rgba(0,0,0,.15); pointer-events:auto; color:#fff;
    animation:rsToastIn .22s ease forwards;
  }
  .rs-toast.success { background:#2E7D32; }
  .rs-toast.error   { background:#C62828; }
  .rs-toast.warning { background:#E65100; }
  .rs-toast.info    { background:#1565C0; }
  .rs-toast.hide { animation:rsToastOut .2s ease forwards; }
  @keyframes rsToastIn  { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:none} }
  @keyframes rsToastOut { from{opacity:1;transform:none} to{opacity:0;transform:translateX(20px)} }
</style>
<div id="rs-toast-wrap"></div>
<script>
  /* ── Toast ───────────────────────────────────────────────────── */
  const _rsIcons = { success:'ri-checkbox-circle-line', error:'ri-error-warning-line', warning:'ri-alert-line', info:'ri-information-line' };
  function rsToast(type, msg) {
    const wrap = document.getElementById('rs-toast-wrap');
    const el   = document.createElement('div');
    el.className = 'rs-toast ' + type;
    el.innerHTML = `<i class="ri ${_rsIcons[type]}"></i><span>${msg}</span>`;
    wrap.appendChild(el);
    setTimeout(() => { el.classList.add('hide'); setTimeout(() => el.remove(), 220); }, 3400);
  }

  const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const STORE_URL     = '{{ route("guest.room-service.store") }}';
  const MY_URL        = '{{ route("guest.room-service.my-requests") }}';
  const IS_CHECKED_IN = {{ $checkedIn ? 'true' : 'false' }};

  /* ── Basket state ────────────────────────────────────────────── */
  // basket[itemId] = { name, category, icon, requiresQty, qty }
  const rsBasket = {};

  /* ── Category tab filter ─────────────────────────────────────── */
  document.querySelectorAll('.rs-tab').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.rs-tab').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      const cat = this.dataset.cat;
      document.querySelectorAll('.rs-section').forEach(sec => {
        sec.classList.toggle('hidden', cat !== 'all' && sec.dataset.section !== cat);
      });
    });
  });

  /* ── Toggle item selection ───────────────────────────────────── */
  function rsToggle(id) {
    if (!IS_CHECKED_IN) { rsToast('info', 'Check in to submit room service requests.'); return; }
    const el = document.getElementById('rsItem-' + id);
    if (!el || el.dataset.available !== '1') return;

    if (rsBasket[id]) {
      delete rsBasket[id];
      el.classList.remove('selected');
    } else {
      rsBasket[id] = {
        name:        el.dataset.name,
        category:    el.dataset.cat,
        icon:        el.dataset.icon,
        requiresQty: el.dataset.requiresQty === '1',
        qty:         1,
      };
      el.classList.add('selected');
    }
    _rsRenderBasket();
  }

  /* ── Qty stepper ─────────────────────────────────────────────── */
  function rsInc(id) {
    if (!rsBasket[id]) return;
    if (rsBasket[id].qty >= 20) { rsToast('warning', 'Maximum 20 per item.'); return; }
    rsBasket[id].qty++;
    const v = document.getElementById('rsQtyVal-' + id);
    if (v) v.textContent = rsBasket[id].qty;
    _rsRenderBasket();
  }

  function rsDec(id) {
    if (!rsBasket[id]) return;
    if (rsBasket[id].qty <= 1) return; // don't go below 1; deselect by clicking the card
    rsBasket[id].qty--;
    const v = document.getElementById('rsQtyVal-' + id);
    if (v) v.textContent = rsBasket[id].qty;
    _rsRenderBasket();
  }

  /* ── Render basket sidebar ───────────────────────────────────── */
  function _rsRenderBasket() {
    const body    = document.getElementById('rsBasketBody');
    const footer  = document.getElementById('rsFooter');
    const empty   = document.getElementById('rsEmpty');
    const notes   = document.getElementById('rsNotesSection');
    const countEl = document.getElementById('rsCount');
    const totalEl = document.getElementById('rsTotalItems');
    if (!body) return;

    body.querySelectorAll('.rs-line').forEach(el => el.remove());

    const keys  = Object.keys(rsBasket);
    const count = keys.reduce((s, id) => s + rsBasket[id].qty, 0);

    countEl.textContent = count + ' item' + (count !== 1 ? 's' : '');

    if (!keys.length) {
      empty.style.display  = '';
      footer.style.display = 'none';
      notes.style.display  = 'none';
      return;
    }

    empty.style.display  = 'none';
    footer.style.display = '';
    notes.style.display  = '';
    totalEl.textContent  = count;

    keys.forEach(id => {
      const { name, category, icon, requiresQty, qty } = rsBasket[id];
      const div = document.createElement('div');
      div.className = 'rs-line';
      div.innerHTML = `
        <span class="rs-line-icon"><i class="ri ${_esc(icon)}"></i></span>
        <div class="rs-line-info">
          <div class="rs-line-name">${_esc(name)}</div>
          <div class="rs-line-cat">${_esc(category)}</div>
        </div>
        <span class="rs-line-qty">${requiresQty ? '×' + qty : '✓'}</span>`;
      body.appendChild(div);
    });
  }

  /* ── Char counter ────────────────────────────────────────────── */
  const rsDescEl     = document.getElementById('rsDescription');
  const rsCharCount  = document.getElementById('rsCharCount');
  if (rsDescEl) {
    rsDescEl.addEventListener('input', () => {
      const len = rsDescEl.value.length;
      rsCharCount.textContent = `${len} / 500`;
      rsCharCount.style.color = len >= 470 ? '#E65100' : '';
    });
  }

  /* ── Submit ──────────────────────────────────────────────────── */
  async function rsSubmit() {
    const keys = Object.keys(rsBasket);
    if (!keys.length) { rsToast('warning', 'Please select at least one service.'); return; }

    const description = (rsDescEl?.value ?? '').trim() || 'Room service request';
    const items       = keys.map(id => ({
      item_id:  parseInt(id),
      quantity: rsBasket[id].qty,
    }));

    const btn = document.getElementById('rsSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting…';

    try {
      const res  = await fetch(STORE_URL, {
        method:  'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
        body:    JSON.stringify({ description, items }),
      });
      const data = await res.json();

      if (data.success) {
        rsToast('success', 'Request submitted! Redirecting…');
        setTimeout(() => window.location = MY_URL, 1600);
      } else {
        rsToast('error', data.error ?? 'Something went wrong.');
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-send-plane-line me-1"></i>Submit Request';
      }
    } catch (err) {
      console.error(err);
      rsToast('error', 'Request failed. Check your connection.');
      btn.disabled = false;
      btn.innerHTML = '<i class="ri-send-plane-line me-1"></i>Submit Request';
    }
  }

  function _esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
</script>
@endsection