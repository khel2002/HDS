@extends('layouts/contentNavbarLayout')

@section('title', 'Breakfast Menu')

@section('page-style')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --bm-gold:      #C9A84C;
    --bm-gold-lt:   #F0D98C;
    --bm-dark:      #1A1714;
    --bm-warm:      #F8F5F0;
    --bm-card-bg:   var(--bs-body-bg);
    --bm-border:    var(--bs-border-color);
    --bm-radius:    14px;
    --bm-serif:     'DM Serif Display', Georgia, serif;
    --bm-sans:      'DM Sans', system-ui, sans-serif;
    --bm-primary:   var(--bs-primary, #696CFF);
    --bm-success:   var(--bs-success, #71DD37);
  }

  * { font-family: var(--bm-sans); }

  /* ── Hero ──────────────────────────────────────────────────── */
  .bm-hero {
    position: relative;
    height: 220px;
    border-radius: var(--bm-radius);
    overflow: hidden;
    margin-bottom: 1.5rem;
    background: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1600') center/cover no-repeat;
  }
  .bm-hero::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(26,23,20,.92) 0%, rgba(26,23,20,.5) 60%, transparent 100%);
  }
  .bm-hero-body {
    position: absolute; bottom: 0; left: 0; right: 0;
    padding: 1.5rem 2rem; z-index: 2;
  }
  .bm-hero-eyebrow {
    display: inline-flex; align-items: center; gap: .4rem;
    background: var(--bm-gold); color: var(--bm-dark);
    font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
    padding: .22rem .8rem; border-radius: 999px; margin-bottom: .6rem;
  }
  .bm-hero-title {
    font-family: var(--bm-serif);
    font-size: 2rem; color: #fff; margin: 0 0 .2rem; line-height: 1.2;
  }
  .bm-hero-sub { color: rgba(255,255,255,.65); font-size: .84rem; margin: 0; font-weight: 300; }
  .bm-hero-sub strong { color: var(--bm-gold-lt); font-weight: 600; }

  /* ── Notice banner ─────────────────────────────────────────── */
  .bm-notice {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: 1rem 1.25rem;
    border-radius: var(--bm-radius);
    margin-bottom: 1.5rem;
    border: 1.5px solid;
  }
  .bm-notice.success {
    background: rgba(113,221,55,.07);
    border-color: rgba(113,221,55,.25);
    color: var(--bm-success);
  }
  .bm-notice.info {
    background: rgba(105,108,255,.07);
    border-color: rgba(105,108,255,.2);
    color: var(--bm-primary);
  }
  .bm-notice-icon { font-size: 1.25rem; flex-shrink: 0; margin-top: .05rem; }
  .bm-notice h6 { font-size: .85rem; font-weight: 700; margin: 0 0 .2rem; }
  .bm-notice p  { font-size: .78rem; margin: 0; color: var(--bs-secondary-color); }

  /* ── Filter bar ────────────────────────────────────────────── */
  .bm-filterbar {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .75rem; margin-bottom: 1.25rem;
  }
  .bm-filters { display: flex; gap: .4rem; flex-wrap: wrap; }
  .bm-filter {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .32rem .9rem; border-radius: 999px;
    border: 1.5px solid var(--bm-border);
    background: var(--bm-card-bg); color: var(--bs-body-color);
    font-size: .79rem; font-weight: 500; cursor: pointer;
    transition: all .15s ease;
  }
  .bm-filter:hover  { border-color: var(--bm-primary); color: var(--bm-primary); }
  .bm-filter.active { background: var(--bm-primary); border-color: var(--bm-primary); color: #fff; }
  .bm-filter .pill {
    font-size: .65rem; font-weight: 700;
    background: rgba(255,255,255,.25); color: inherit;
    padding: .05rem .4rem; border-radius: 999px;
  }
  .bm-filter:not(.active) .pill { background: var(--bs-tertiary-bg); color: var(--bs-secondary-color); }

  /* ── Layout ────────────────────────────────────────────────── */
  .bm-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
  }
  @media (max-width: 991px) { .bm-layout { grid-template-columns: 1fr; } }

  /* ── Section header ────────────────────────────────────────── */
  .bm-sec-hdr {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1rem;
  }
  .bm-sec-hdr h5 {
    font-family: var(--bm-serif);
    font-size: 1.15rem; margin: 0;
  }
  .bm-sec-hdr small { color: var(--bs-secondary-color); font-size: .78rem; font-style: italic; }

  /* ── Menu grid ─────────────────────────────────────────────── */
  .bm-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .9rem;
  }
  @media (max-width: 1399px) { .bm-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 575px)   { .bm-grid { grid-template-columns: 1fr; } }

  /* ── Menu card ─────────────────────────────────────────────── */
  .bm-card {
    background: var(--bm-card-bg);
    border: 1.5px solid var(--bm-border);
    border-radius: var(--bm-radius);
    overflow: hidden;
    display: flex; flex-direction: column;
    position: relative;
    transition: box-shadow .2s, transform .2s, border-color .15s;
  }
  .bm-card:not(.unavailable):hover {
    box-shadow: 0 8px 24px rgba(0,0,0,.1);
    transform: translateY(-2px);
    border-color: var(--bm-primary);
  }
  .bm-card.selected {
    border-color: var(--bm-primary);
    box-shadow: 0 0 0 3px rgba(105,108,255,.15);
  }
  .bm-card.unavailable { opacity: .5; filter: grayscale(.4); }

  /* selected checkmark badge */
  .bm-sel-badge {
    display: none;
    position: absolute; top: .5rem; right: .5rem; z-index: 4;
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--bm-primary); color: #fff;
    font-size: .7rem; align-items: center; justify-content: center;
  }
  .bm-card.selected .bm-sel-badge { display: flex; }

  /* image */
  .bm-img {
    height: 140px; overflow: hidden; flex-shrink: 0;
    background: var(--bs-tertiary-bg); position: relative;
  }
  .bm-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s ease;
  }
  .bm-card:not(.unavailable):hover .bm-img img { transform: scale(1.06); }
  .bm-img-ph {
    width: 100%; height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: .25rem; color: var(--bs-secondary-color);
  }
  .bm-img-ph i { font-size: 1.8rem; }
  .bm-img-ph span { font-size: .7rem; }
  .bm-unavail-tag {
    position: absolute; bottom: .5rem; left: .5rem;
    background: rgba(0,0,0,.65); color: #fff;
    font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
    padding: .15rem .55rem; border-radius: 999px;
  }

  /* card body */
  .bm-card-body {
    padding: .85rem .95rem .9rem;
    flex: 1; display: flex; flex-direction: column;
  }
  .bm-card-name {
    font-weight: 600; font-size: .88rem;
    color: var(--bs-heading-color);
    margin: 0 0 .2rem; line-height: 1.3;
  }
  .bm-card-desc {
    font-size: .74rem; color: var(--bs-secondary-color);
    flex: 1; margin: 0 0 .65rem;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
  }
  .bm-card-foot {
    display: flex; align-items: center; justify-content: space-between;
    padding-top: .6rem; border-top: 1px solid var(--bm-border);
  }
  .bm-price {
    font-family: var(--bm-serif);
    font-size: 1.05rem; font-weight: 400; color: var(--bm-primary);
  }

  /* ── Quantity control (inline on card) ─────────────────────── */
  .bm-qty-ctrl {
    display: flex; align-items: center; gap: .3rem;
  }
  .bm-qty-btn {
    width: 28px; height: 28px; border-radius: 50%;
    border: 1.5px solid var(--bm-border);
    background: var(--bm-card-bg); color: var(--bs-body-color);
    font-size: .9rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s; flex-shrink: 0;
    line-height: 1; padding: 0;
  }
  .bm-qty-btn.minus:hover { background: rgba(255,71,87,.1); border-color: #FF4757; color: #FF4757; }
  .bm-qty-btn.plus:hover  { background: var(--bm-primary); border-color: var(--bm-primary); color: #fff; }
  .bm-qty-btn.add-btn { width: 30px; height: 30px; background: transparent; border: 1.5px solid var(--bm-primary); color: var(--bm-primary); font-size: 1.15rem; }
  .bm-qty-btn.add-btn:hover { background: var(--bm-primary); color: #fff; transform: scale(1.08); }
  .bm-qty-num {
    font-size: .82rem; font-weight: 700;
    min-width: 20px; text-align: center;
    color: var(--bs-heading-color);
  }

  /* ── Sidebar / Basket ──────────────────────────────────────── */
  .bm-sidebar { position: sticky; top: 80px; }

  .bm-basket {
    border-radius: var(--bm-radius);
    border: 1.5px solid rgba(105,108,255,.2);
    background: var(--bm-card-bg);
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.06);
  }
  .bm-basket-head {
    padding: .85rem 1.15rem;
    border-bottom: 1px solid var(--bm-border);
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(105,108,255,.04);
  }
  .bm-basket-head h6 {
    font-family: var(--bm-serif);
    font-size: .95rem; margin: 0; font-style: italic;
  }
  .bm-basket-count {
    font-size: .72rem; font-weight: 700; letter-spacing: .04em;
    background: var(--bm-primary); color: #fff;
    padding: .2rem .65rem; border-radius: 999px;
  }

  .bm-basket-body { padding: .85rem 1.15rem; min-height: 80px; }
  .bm-basket-empty {
    text-align: center; padding: 1.5rem .5rem;
    color: var(--bs-secondary-color); font-size: .8rem;
  }
  .bm-basket-empty i { font-size: 1.9rem; display: block; margin-bottom: .4rem; opacity: .5; }

  /* basket line items */
  .bm-line {
    display: flex; align-items: center; gap: .6rem;
    padding: .55rem 0; border-bottom: 1px solid var(--bm-border);
    animation: bmSlideIn .15s ease;
  }
  .bm-line:last-child { border-bottom: none; }
  @keyframes bmSlideIn { from { opacity:0; transform:translateX(8px); } to { opacity:1; transform:none; } }
  .bm-line-info { flex: 1; min-width: 0; }
  .bm-line-name { font-size: .81rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--bs-heading-color); }
  .bm-line-unit { font-size: .72rem; color: var(--bs-secondary-color); }
  .bm-line-qty  { display: flex; align-items: center; gap: .2rem; }
  .bm-line-qbtn {
    width: 22px; height: 22px; border-radius: 50%;
    border: 1px solid var(--bm-border); background: var(--bm-card-bg);
    font-size: .75rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .13s; padding: 0; line-height: 1;
  }
  .bm-line-qbtn.minus:hover { background: rgba(255,71,87,.12); border-color: #FF4757; color: #FF4757; }
  .bm-line-qbtn.plus:hover  { background: var(--bm-primary); border-color: var(--bm-primary); color: #fff; }
  .bm-line-qval { font-size: .8rem; font-weight: 700; min-width: 16px; text-align: center; }
  .bm-line-sub  { font-size: .82rem; font-weight: 700; color: var(--bm-primary); white-space: nowrap; }

  /* ── Notes section ─────────────────────────────────────────── */
  .bm-notes-section {
    padding: .75rem 1.15rem;
    border-top: 1px solid var(--bm-border);
  }
  .bm-notes-label {
    display: flex; align-items: center; gap: .35rem;
    font-size: .72rem; font-weight: 600; letter-spacing: .04em;
    text-transform: uppercase; color: var(--bs-secondary-color);
    margin-bottom: .45rem;
  }
  .bm-notes-label i { font-size: .8rem; }
  .bm-notes-textarea {
    width: 100%;
    padding: .55rem .75rem;
    font-size: .8rem;
    font-family: var(--bm-sans);
    color: var(--bs-body-color);
    background: var(--bs-tertiary-bg);
    border: 1.5px solid var(--bm-border);
    border-radius: 10px;
    resize: none;
    transition: border-color .15s, box-shadow .15s;
    line-height: 1.5;
  }
  .bm-notes-textarea::placeholder { color: var(--bs-secondary-color); opacity: .7; font-size: .78rem; }
  .bm-notes-textarea:focus {
    outline: none;
    border-color: var(--bm-primary);
    box-shadow: 0 0 0 3px rgba(105,108,255,.12);
    background: var(--bm-card-bg);
  }
  .bm-notes-hint {
    font-size: .68rem; color: var(--bs-secondary-color);
    margin-top: .3rem; display: flex; justify-content: space-between;
  }

  /* basket footer */
  .bm-basket-foot { padding: .85rem 1.15rem; border-top: 1px solid var(--bm-border); }
  .bm-total-row { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .85rem; }
  .bm-total-lbl { font-size: .78rem; color: var(--bs-secondary-color); text-transform: uppercase; letter-spacing: .06em; }
  .bm-total-amt { font-family: var(--bm-serif); font-size: 1.35rem; color: var(--bm-primary); }

  /* ── Locked (not checked in) ───────────────────────────────── */
  .bm-locked {
    border: 2px dashed var(--bm-border);
    border-radius: var(--bm-radius);
    padding: 2.25rem 1.5rem; text-align: center;
    color: var(--bs-secondary-color);
  }
  .bm-locked i { font-size: 2rem; display: block; margin-bottom: .6rem; opacity: .6; }
  .bm-locked h6 { font-family: var(--bm-serif); font-size: .98rem; margin: 0 0 .3rem; color: var(--bs-heading-color); font-style: italic; }
  .bm-locked p { font-size: .78rem; margin: 0; }

  /* ── Empty state ───────────────────────────────────────────── */
  .bm-empty { text-align: center; padding: 4rem 1rem; color: var(--bs-secondary-color); }
  .bm-empty i { font-size: 2.8rem; display: block; margin-bottom: .75rem; opacity: .45; }
</style>
@endsection

@section('content')
@php
  $checkedIn  = $guest['status'] === 'checked-in';
  $availCount = $menuItems->where('is_available', true)->count();
  $totalCount = $menuItems->count();
@endphp

{{-- Hero --}}
<div class="bm-hero">
  <div class="bm-hero-body">
    <div class="bm-hero-eyebrow">
      <i class="ri-time-line" style="font-size:.65rem;"></i> Serving daily 6:00 AM – 11:00 AM
    </div>
    <h1 class="bm-hero-title">Breakfast Menu</h1>
    <p class="bm-hero-sub">
      @if($checkedIn)
        Room <strong>{{ $guest['room_number'] ?? '—' }}</strong> &middot; Select items and submit your order
      @else
        Fresh ingredients, lovingly prepared every morning
      @endif
    </p>
  </div>
</div>

{{-- Notice --}}
@if($checkedIn)
  <div class="bm-notice success">
    <span class="bm-notice-icon"><i class="ri-shield-check-line"></i></span>
    <div>
      <h6>Order to Your Room</h6>
      <p>Pick items below and tap <strong>Submit Order</strong> — delivered straight to your door.</p>
    </div>
  </div>
@else
  <div class="bm-notice info">
    <span class="bm-notice-icon"><i class="ri-information-line"></i></span>
    <div>
      <h6>Browse Our Menu</h6>
      <p>Ordering is available once you have checked in. Call ext. <strong>2000</strong> for assistance.</p>
    </div>
  </div>
@endif

{{-- Filter bar --}}
<div class="bm-filterbar">
  <div class="bm-filters">
    <button class="bm-filter active" data-filter="all">
      All <span class="pill">{{ $totalCount }}</span>
    </button>
    <button class="bm-filter" data-filter="available">
      Available <span class="pill">{{ $availCount }}</span>
    </button>
    <button class="bm-filter" data-filter="unavailable">
      Unavailable <span class="pill">{{ $totalCount - $availCount }}</span>
    </button>
  </div>
</div>

@if($totalCount > 0)
<div class="bm-layout">

  {{-- LEFT: menu grid --}}
  <div>
    <div class="bm-sec-hdr">
      <h5>Today's Breakfast</h5>
      <small>{{ $availCount }} item{{ $availCount != 1 ? 's' : '' }} available</small>
    </div>

    <div class="bm-grid" id="bmGrid">
      @foreach($menuItems as $item)
        <div class="bm-card {{ !$item->is_available ? 'unavailable' : '' }}"
             id="card-{{ $item->breakfast_id }}"
             data-id="{{ $item->breakfast_id }}"
             data-name="{{ e($item->meal_name) }}"
             data-price="{{ $item->price }}"
             data-available="{{ $item->is_available ? '1' : '0' }}">

          <span class="bm-sel-badge"><i class="ri-check-line"></i></span>

          <div class="bm-img">
            @if($item->image_path)
              <img src="{{ asset('storage/' . $item->image_path) }}"
                   alt="{{ e($item->meal_name) }}" loading="lazy">
            @else
              <div class="bm-img-ph">
                <i class="ri-bowl-line"></i>
                <span>No image</span>
              </div>
            @endif
            @if(!$item->is_available)
              <span class="bm-unavail-tag">Unavailable</span>
            @endif
          </div>

          <div class="bm-card-body">
            <p class="bm-card-name">{{ $item->meal_name }}</p>
            @if($item->description)
              <p class="bm-card-desc">{{ $item->description }}</p>
            @else
              <p class="bm-card-desc" style="opacity:0;pointer-events:none;">—</p>
            @endif

            <div class="bm-card-foot">
              <span class="bm-price">₱{{ number_format($item->price, 2) }}</span>

              @if($item->is_available && $checkedIn)
                <div class="bm-qty-ctrl" id="ctrl-{{ $item->breakfast_id }}">
                  <button type="button"
                          class="bm-qty-btn add-btn"
                          id="addbtn-{{ $item->breakfast_id }}"
                          onclick="bmAdd({{ $item->breakfast_id }})"
                          title="Add to order">
                    <i class="ri-add-line"></i>
                  </button>
                  <span class="bm-qty-ctrl" id="stepper-{{ $item->breakfast_id }}" style="display:none;">
                    <button type="button" class="bm-qty-btn minus"
                            onclick="bmDec({{ $item->breakfast_id }})">−</button>
                    <span class="bm-qty-num" id="qnum-{{ $item->breakfast_id }}">1</span>
                    <button type="button" class="bm-qty-btn plus"
                            onclick="bmInc({{ $item->breakfast_id }})">+</button>
                  </span>
                </div>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- RIGHT: basket / lock --}}
  <div class="bm-sidebar">
    @if($checkedIn)
      <div class="bm-basket">

        {{-- Basket header --}}
        <div class="bm-basket-head">
          <h6><i class="ri-shopping-basket-2-line me-1 text-primary"></i>Your Order</h6>
          <span class="bm-basket-count" id="bmCount">0 items</span>
        </div>

        {{-- Line items --}}
        <div class="bm-basket-body" id="bmBasketBody">
          <div class="bm-basket-empty" id="bmEmpty">
            <i class="ri-add-circle-line"></i>
            Select items from the menu
          </div>
        </div>

        {{-- ── Notes / Special Instructions ─────────────────────────── --}}
        <div class="bm-notes-section" id="bmNotesSection" style="display:none;">
          <div class="bm-notes-label">
            <i class="ri-chat-3-line"></i> Special Instructions
          </div>
          <textarea
            id="bmDescription"
            class="bm-notes-textarea"
            rows="3"
            maxlength="255"
            placeholder="e.g. No mushrooms, extra syrup, allergy info…"
          ></textarea>
          <div class="bm-notes-hint">
            <span>Optional &mdash; will be seen by kitchen staff</span>
            <span id="bmCharCount">0 / 255</span>
          </div>
        </div>

        {{-- Basket footer (total + actions) --}}
        <div class="bm-basket-foot" id="bmFooter" style="display:none;">
          <div class="bm-total-row">
            <span class="bm-total-lbl">Total</span>
            <span class="bm-total-amt" id="bmTotal">₱0.00</span>
          </div>
          <button type="button" class="btn btn-primary w-100" id="bmSubmitBtn" onclick="bmSubmit()">
            <i class="ri-send-plane-line me-1"></i>Submit Order
          </button>
          <a href="{{ route('guest.breakfast.my-orders') }}"
             class="btn btn-outline-secondary w-100 mt-2 btn-sm">
            <i class="ri-list-check me-1"></i>View My Orders
          </a>
        </div>

      </div>

    @else
      <div class="bm-locked">
        <i class="ri-lock-2-line text-warning"></i>
        <h6>Check-in Required</h6>
        <p>Ordering is available once you are checked in.</p>
      </div>
    @endif
  </div>

</div>
@else
  <div class="bm-empty">
    <i class="ri-restaurant-line"></i>
    <p class="mb-0">No breakfast items available at the moment.</p>
  </div>
@endif
@endsection

@section('page-script')
<style>
  /* ── Self-contained toast ─────────────────────────────────── */
  #bm-toast-wrap {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    display: flex; flex-direction: column; gap: .5rem;
    z-index: 9999; pointer-events: none;
  }
  .bm-toast {
    display: flex; align-items: center; gap: .7rem;
    padding: .75rem 1.1rem;
    border-radius: 10px;
    font-size: .84rem; font-weight: 500;
    min-width: 240px; max-width: 320px;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
    pointer-events: auto;
    animation: bmToastIn .22s ease forwards;
    color: #fff;
  }
  .bm-toast.success { background: #2E7D32; }
  .bm-toast.error   { background: #C62828; }
  .bm-toast.warning { background: #E65100; }
  .bm-toast.info    { background: #1565C0; }
  .bm-toast i { font-size: 1rem; flex-shrink: 0; }
  .bm-toast.hide { animation: bmToastOut .2s ease forwards; }
  @keyframes bmToastIn  { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:none; } }
  @keyframes bmToastOut { from { opacity:1; transform:none; } to { opacity:0; transform:translateX(20px); } }
</style>
<div id="bm-toast-wrap"></div>
<script>
  /* ── Toast helper ────────────────────────────────────────── */
  const _icons = { success:'ri-checkbox-circle-line', error:'ri-error-warning-line', warning:'ri-alert-line', info:'ri-information-line' };
  function bmToast(type, msg) {
    const wrap = document.getElementById('bm-toast-wrap');
    const el   = document.createElement('div');
    el.className = 'bm-toast ' + type;
    el.innerHTML = `<i class="${_icons[type] || 'ri-information-line'}"></i><span>${msg}</span>`;
    wrap.appendChild(el);
    setTimeout(() => {
      el.classList.add('hide');
      setTimeout(() => el.remove(), 220);
    }, 3200);
  }
  const toastr = { success: m => bmToast('success',m), error: m => bmToast('error',m), warning: m => bmToast('warning',m), info: m => bmToast('info',m) };

  const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const ORDER_URL     = '{{ route("guest.breakfast.store") }}';
  const IS_CHECKED_IN = {{ $checkedIn ? 'true' : 'false' }};

  /* ── Char counter for description textarea ──────────────── */
  const descEl      = document.getElementById('bmDescription');
  const charCountEl = document.getElementById('bmCharCount');
  if (descEl) {
    descEl.addEventListener('input', () => {
      const len = descEl.value.length;
      charCountEl.textContent = `${len} / 255`;
      charCountEl.style.color = len >= 230 ? '#E65100' : '';
    });
  }

  /* ── Filter buttons ─────────────────────────────────────── */
  document.querySelectorAll('.bm-filter').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.bm-filter').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      const f = this.dataset.filter;
      document.querySelectorAll('.bm-card').forEach(c => {
        const avail = c.dataset.available === '1';
        c.style.display =
          f === 'all'         ? ''
          : f === 'available' ? (avail  ? '' : 'none')
          :                     (!avail ? '' : 'none');
      });
    });
  });

  /* ── Basket state ───────────────────────────────────────── */
  const basket = {};

  function bmAdd(id) {
    if (!IS_CHECKED_IN) { toastr.info('Check in to place an order.'); return; }
    const card = document.getElementById('card-' + id);
    if (!card || card.dataset.available !== '1') return;
    basket[id] = { name: card.dataset.name, price: parseFloat(card.dataset.price), qty: 1 };
    _syncCardUI(id);
    _renderBasket();
  }

  function bmInc(id) {
    if (!basket[id]) return;
    if (basket[id].qty >= 20) { toastr.warning('Maximum 20 per item.'); return; }
    basket[id].qty++;
    _syncCardUI(id);
    _renderBasket();
  }

  function bmDec(id) {
    if (!basket[id]) return;
    basket[id].qty--;
    if (basket[id].qty <= 0) {
      delete basket[id];
      _syncCardUI(id, true);
    } else {
      _syncCardUI(id);
    }
    _renderBasket();
  }

  function _syncCardUI(id, removed = false) {
    const card    = document.getElementById('card-' + id);
    const addBtn  = document.getElementById('addbtn-' + id);
    const stepper = document.getElementById('stepper-' + id);
    const qnum    = document.getElementById('qnum-' + id);
    if (!card) return;
    if (removed || !basket[id]) {
      card.classList.remove('selected');
      if (addBtn)  addBtn.style.display  = '';
      if (stepper) stepper.style.display = 'none';
    } else {
      card.classList.add('selected');
      if (addBtn)  addBtn.style.display  = 'none';
      if (stepper) stepper.style.display = '';
      if (qnum)    qnum.textContent = basket[id].qty;
    }
  }

  function _renderBasket() {
    const body        = document.getElementById('bmBasketBody');
    const footer      = document.getElementById('bmFooter');
    const empty       = document.getElementById('bmEmpty');
    const notesSection= document.getElementById('bmNotesSection');
    const countEl     = document.getElementById('bmCount');
    const totalEl     = document.getElementById('bmTotal');
    if (!body) return;

    body.querySelectorAll('.bm-line').forEach(el => el.remove());

    const keys  = Object.keys(basket);
    const total = keys.reduce((s, id) => s + basket[id].price * basket[id].qty, 0);
    const count = keys.reduce((s, id) => s + basket[id].qty, 0);

    countEl.textContent = count + ' item' + (count !== 1 ? 's' : '');

    if (!keys.length) {
      empty.style.display        = '';
      footer.style.display       = 'none';
      notesSection.style.display = 'none';
      return;
    }

    empty.style.display        = 'none';
    footer.style.display       = '';
    notesSection.style.display = '';     // ← show notes when basket has items
    totalEl.textContent        = '₱' + total.toFixed(2);

    keys.forEach(id => {
      const { name, price, qty } = basket[id];
      const div = document.createElement('div');
      div.className = 'bm-line';
      div.innerHTML = `
        <div class="bm-line-info">
          <div class="bm-line-name">${_esc(name)}</div>
          <div class="bm-line-unit">₱${price.toFixed(2)} each</div>
        </div>
        <div class="bm-line-qty">
          <button type="button" class="bm-line-qbtn minus" onclick="bmDec(${id})">−</button>
          <span class="bm-line-qval">${qty}</span>
          <button type="button" class="bm-line-qbtn plus" onclick="bmInc(${id})">+</button>
        </div>
        <span class="bm-line-sub">₱${(price * qty).toFixed(2)}</span>`;
      body.appendChild(div);
    });
  }

  /* ── Submit ──────────────────────────────────────────────── */
  async function bmSubmit() {
    const keys = Object.keys(basket);
    if (!keys.length) { toastr.warning('Please select at least one item.'); return; }

    // Grab description — fall back to generic label if blank
    const description = (descEl?.value ?? '').trim() || 'Breakfast order';

    const btn = document.getElementById('bmSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Placing order…';

    const items = keys.map(id => ({
      breakfast_id: parseInt(id),
      quantity:     basket[id].qty,
    }));

    try {
      const res  = await fetch(ORDER_URL, {
        method:  'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF,
          'Accept':       'application/json',
        },
        body: JSON.stringify({ service_type: 'food', description, items }),
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

  function _esc(s) {
    return String(s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
</script>
@endsection