@extends('layouts/blankLayout')
@section('title', 'Available Rooms — Hotel De SLSU')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/materio.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/front.css') }}">

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:        #1a1f5e;
      --navy-dark:   #12175a;
      --navy-deep:   #0d1147;
      --accent:      #5b67e8;
      --accent-lite: #7c86f0;
      --green:       #27ae60;
      --bg-alt:      #f0f3fb;
      --card-bg:     #f7f8fd;
      --muted:       #64748b;
      --white:       #ffffff;
      --shadow-sm:   0 2px 12px rgba(26,31,94,.07);
      --shadow-md:   0 8px 32px rgba(26,31,94,.13);
      --shadow-lg:   0 16px 56px rgba(26,31,94,.18);
    }

    html { scroll-behavior: smooth; }
    body { font-family: 'DM Sans', sans-serif; color: var(--navy-deep); background: #fff; overflow-x: hidden; }
    h1, h2, h3 { font-family: 'DM Serif Display', serif; }

    /* PAGE HEADER */
    .page-header {
      background: linear-gradient(160deg, #0d1147 0%, #1a1f5e 60%, #252d8c 100%);
      padding: 7rem 1.5rem 4rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .page-header::before {
      content: '';
      position: absolute; inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%235b67e8' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .page-header-content { position: relative; z-index: 2; }
    .page-breadcrumb {
      display: inline-flex; align-items: center; gap: 0.5rem;
      color: rgba(255,255,255,.55); font-size: 0.83rem; margin-bottom: 1.2rem;
    }
    .page-breadcrumb a { color: rgba(255,255,255,.55); text-decoration: none; transition: color .2s; }
    .page-breadcrumb a:hover { color: #fff; }
    .page-breadcrumb i { font-size: 0.75rem; }
    .page-header h1 {
      font-size: clamp(2.4rem, 5vw, 3.8rem);
      color: #fff; font-weight: 400; margin-bottom: 0.9rem; line-height: 1.08;
    }
    .page-header p { color: rgba(255,255,255,.72); font-size: 1.05rem; font-weight: 300; max-width: 480px; margin: 0 auto; }

    .btn-back {
      display: inline-flex; align-items: center; gap: 0.5rem;
      color: rgba(255,255,255,.75);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.85rem; font-weight: 600;
      text-decoration: none;
      background: rgba(255,255,255,.1);
      border: 1px solid rgba(255,255,255,.2);
      border-radius: 2rem;
      padding: 0.45rem 1.1rem;
      margin-bottom: 1.4rem;
      transition: background .2s, color .2s, border-color .2s;
      backdrop-filter: blur(8px);
    }
    .btn-back:hover {
      background: rgba(255,255,255,.2);
      color: #fff;
      border-color: rgba(255,255,255,.4);
    }
    .btn-back i { font-size: 1rem; }

    /* SEARCH SUMMARY BAR */
    .search-summary {
      background: #fff; border-bottom: 1px solid #eaecf5;
      padding: 1.2rem 1.5rem; position: sticky; top: 0; z-index: 100;
      box-shadow: 0 2px 16px rgba(26,31,94,.06);
    }
    .search-summary-inner {
      max-width: 1200px; margin: 0 auto;
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 1rem;
    }
    .search-pills { display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: center; }
    .search-pill {
      display: inline-flex; align-items: center; gap: 0.4rem;
      background: var(--bg-alt); border: 1px solid #d4d8f5;
      border-radius: 2rem; padding: 0.35rem 0.95rem;
      font-size: 0.81rem; font-weight: 500; color: var(--navy);
    }
    .search-pill i { color: var(--accent); font-size: 0.85rem; }
    .search-pill.highlight { background: var(--navy); color: #fff; border-color: var(--navy); }
    .search-pill.highlight i { color: rgba(255,255,255,.75); }
    .btn-modify {
      display: inline-flex; align-items: center; gap: 0.45rem;
      background: transparent; border: 1.5px solid var(--navy);
      color: var(--navy); border-radius: 0.6rem; padding: 0.5rem 1.2rem;
      font-family: 'DM Sans', sans-serif; font-size: 0.85rem; font-weight: 600;
      cursor: pointer; text-decoration: none; transition: background .2s, color .2s;
    }
    .btn-modify:hover { background: var(--navy); color: #fff; }

    /* MAIN LAYOUT */
    .rooms-page { padding: 3rem 1.5rem 6rem; }
    .rooms-page-inner {
      max-width: 1200px; margin: 0 auto;
      display: grid; grid-template-columns: 280px 1fr;
      gap: 2.5rem; align-items: start;
    }
    @media (max-width: 960px) { .rooms-page-inner { grid-template-columns: 1fr; } }

    /* SIDEBAR */
    .filters-sidebar { position: sticky; top: 72px; }
    .filter-card {
      background: var(--card-bg); border: 1px solid rgba(91,103,232,.1);
      border-radius: 1.2rem; padding: 1.75rem; margin-bottom: 1.25rem;
    }
    .filter-card-title {
      font-family: 'DM Sans', sans-serif; font-size: 0.78rem; font-weight: 700;
      letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted);
      margin-bottom: 1.2rem; display: flex; align-items: center; justify-content: space-between;
    }
    .filter-card-title button {
      background: none; border: none; font-size: 0.75rem; font-weight: 600;
      color: var(--accent); cursor: pointer; font-family: 'DM Sans', sans-serif;
      text-transform: none; letter-spacing: 0;
    }
    .filter-options { display: flex; flex-direction: column; gap: 0.65rem; }
    .filter-option { display: flex; align-items: center; gap: 0.7rem; cursor: pointer; }
    .filter-option input[type="checkbox"],
    .filter-option input[type="radio"] {
      width: 16px; height: 16px; accent-color: var(--navy); cursor: pointer; flex-shrink: 0;
    }
    .filter-option label {
      font-size: 0.9rem; color: var(--navy-deep); cursor: pointer;
      display: flex; align-items: center; justify-content: space-between; width: 100%;
    }
    .filter-count {
      font-size: 0.75rem; color: var(--muted);
      background: #e8eaf5; border-radius: 2rem; padding: 0.15rem 0.55rem;
    }
    .price-range-wrap { padding: 0.25rem 0; }
    .price-range { width: 100%; accent-color: var(--navy); cursor: pointer; }
    .price-range-labels {
      display: flex; justify-content: space-between;
      font-size: 0.8rem; color: var(--muted); margin-top: 0.6rem;
    }
    .btn-apply-filters {
      width: 100%; background: var(--navy); color: #fff; border: none;
      border-radius: 0.7rem; padding: 0.8rem;
      font-family: 'DM Sans', sans-serif; font-size: 0.9rem; font-weight: 600;
      cursor: pointer; transition: background .2s; margin-top: 0.5rem;
    }
    .btn-apply-filters:hover { background: var(--accent); }

    .filter-toggle-btn {
      display: none; align-items: center; gap: 0.5rem;
      background: var(--navy); color: #fff; border: none;
      border-radius: 0.7rem; padding: 0.65rem 1.4rem;
      font-family: 'DM Sans', sans-serif; font-size: 0.9rem; font-weight: 600;
      cursor: pointer; margin-bottom: 1.5rem;
    }
    @media (max-width: 960px) {
      .filter-toggle-btn { display: flex; }
      .filters-sidebar { position: static; display: none; }
      .filters-sidebar.open { display: block; }
    }

    /* RESULTS HEADER */
    .results-header {
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;
    }
    .results-count { font-size: 0.95rem; color: var(--muted); }
    .results-count strong { color: var(--navy-deep); font-weight: 700; }
    .sort-wrap { display: flex; align-items: center; gap: 0.65rem; }
    .sort-wrap label { font-size: 0.85rem; color: var(--muted); white-space: nowrap; }
    .sort-select {
      border: 1.5px solid #dde1f5; border-radius: 0.6rem; padding: 0.45rem 0.9rem;
      font-family: 'DM Sans', sans-serif; font-size: 0.88rem; color: var(--navy-deep);
      background: #fafbff; outline: none; cursor: pointer;
    }
    .sort-select:focus { border-color: var(--accent); }

    /* ROOM CARDS */
    .rooms-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
      gap: 2rem;
    }
    @media (max-width: 740px) { .rooms-grid { grid-template-columns: 1fr; } }

    .room-card {
      background: #fff; border-radius: 1.5rem; overflow: hidden;
      box-shadow: var(--shadow-sm); border: 1px solid rgba(26,31,94,.06);
      display: flex; flex-direction: column;
      transition: transform .32s, box-shadow .32s;
    }
    .room-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); }
    .room-card-img-wrap { position: relative; overflow: hidden; height: 230px; }
    .room-card-img-wrap img {
      width: 100%; height: 100%; object-fit: cover;
      transition: transform .65s cubic-bezier(.4,0,.2,1);
    }
    .room-card:hover .room-card-img-wrap img { transform: scale(1.07); }
    .room-badge {
      position: absolute; top: 1rem; left: 1rem;
      background: var(--green); color: #fff; border-radius: 2rem;
      padding: 0.3rem 0.85rem; font-size: 0.75rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.06em;
    }
    .room-rating {
      position: absolute; top: 1rem; right: 1rem;
      background: #fff; border-radius: 2rem; padding: 0.3rem 0.75rem;
      font-size: 0.82rem; font-weight: 700; color: var(--navy-deep);
      display: flex; align-items: center; gap: 0.3rem;
      box-shadow: 0 3px 12px rgba(0,0,0,.12);
    }
    .room-rating i { color: #f59e0b; }
    .room-card-body { padding: 1.5rem 1.75rem 1.75rem; flex: 1; display: flex; flex-direction: column; }
    .room-type-label {
      font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em;
      text-transform: uppercase; color: var(--accent); margin-bottom: 0.4rem;
    }
    .room-card-body h3 {
      font-family: 'DM Sans', sans-serif; font-size: 1.18rem; font-weight: 700;
      color: var(--navy-deep); margin-bottom: 0.35rem;
    }
    .room-desc {
      font-size: 0.88rem; color: var(--muted); line-height: 1.62; margin-bottom: 1.1rem;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .room-features { display: flex; flex-wrap: wrap; gap: 0.45rem; margin-bottom: 1.35rem; }
    .room-feat {
      display: inline-flex; align-items: center; gap: 0.35rem;
      font-size: 0.76rem; color: var(--navy);
      background: var(--bg-alt); border-radius: 2rem; padding: 0.28rem 0.75rem;
    }
    .room-feat i { font-size: 0.82rem; color: var(--accent); }
    .room-footer {
      margin-top: auto; display: flex; align-items: center; justify-content: space-between;
      padding-top: 1.1rem; border-top: 1px solid #eef0f8;
    }
    .room-price .amount { font-size: 1.6rem; font-weight: 800; color: var(--navy-deep); }
    .room-price .per { font-size: 0.78rem; color: var(--muted); }
    .btn-book {
      background: var(--navy); color: #fff; border: none; border-radius: 0.65rem;
      padding: 0.65rem 1.4rem; font-family: 'DM Sans', sans-serif;
      font-size: 0.88rem; font-weight: 600; cursor: pointer; text-decoration: none;
      display: inline-block; transition: background .2s, transform .15s;
      box-shadow: 0 4px 16px rgba(13,17,71,.2);
    }
    .btn-book:hover { background: var(--accent); color: #fff; transform: translateY(-2px); }

    /* EMPTY STATE */
    .empty-state { grid-column: 1 / -1; text-align: center; padding: 5rem 2rem; }
    .empty-icon {
      width: 80px; height: 80px; background: var(--bg-alt); border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem; color: var(--muted); margin: 0 auto 1.5rem;
    }
    .empty-state h3 {
      font-family: 'DM Sans', sans-serif; font-size: 1.35rem; font-weight: 700;
      color: var(--navy-deep); margin-bottom: 0.6rem;
    }
    .empty-state p { font-size: 0.95rem; color: var(--muted); margin-bottom: 2rem; }
    .btn-clear {
      background: var(--navy); color: #fff; border: none; border-radius: 0.7rem;
      padding: 0.75rem 2rem; font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem; font-weight: 600; cursor: pointer; text-decoration: none;
      display: inline-block; transition: background .2s;
    }
    .btn-clear:hover { background: var(--accent); color: #fff; }

    /* PAGINATION */
    .pagination-wrap { display: flex; justify-content: center; margin-top: 3rem; }

    /* FOOTER */
    .site-footer { background: #0d1147; color: rgba(255,255,255,.72); padding: 3rem 1.5rem 2rem; }
    .footer-bottom { max-width: 1200px; margin: 0 auto; text-align: center; font-size: 0.83rem; color: rgba(255,255,255,.35); }
  </style>
@endsection

@section('content')

  {{-- PAGE HEADER --}}
  <section class="page-header">
    <div class="page-header-content">
      <a href="{{ route('frontpage.index') }}" class="btn-back">
        <i class="ri-arrow-left-line"></i> Back to Home
      </a>
      <div class="page-breadcrumb">
        <a href="{{ route('frontpage.index') }}">Home</a>
        <i class="ri-arrow-right-s-line"></i>
        <span>Available Rooms</span>
      </div>
      <h1>Available Rooms</h1>
      <p>{{ $rooms->total() }} room{{ $rooms->total() != 1 ? 's' : '' }} ready for your stay</p>
    </div>
  </section>

  {{-- SEARCH SUMMARY BAR --}}
  <div class="search-summary">
    <div class="search-summary-inner">
      <div class="search-pills">
        @if(request('check_in'))
          <span class="search-pill">
            <i class="ri-calendar-line"></i>
            Check-in: {{ \Carbon\Carbon::parse(request('check_in'))->format('M d, Y') }}
          </span>
        @endif
        @if(request('check_out'))
          <span class="search-pill">
            <i class="ri-calendar-check-line"></i>
            Check-out: {{ \Carbon\Carbon::parse(request('check_out'))->format('M d, Y') }}
          </span>
        @endif
        @if(request('guests'))
          <span class="search-pill highlight">
            <i class="ri-user-3-line"></i>
            {{ request('guests') }} Guest{{ request('guests') > 1 ? 's' : '' }}
          </span>
        @endif
        @if(!request('check_in') && !request('check_out') && !request('guests'))
          <span class="search-pill">
            <i class="ri-information-line"></i> Showing all available rooms
          </span>
        @endif
      </div>
      <a href="{{ route('frontpage.index') }}" class="btn-modify">
        <i class="ri-edit-line"></i> Modify Search
      </a>
    </div>
  </div>

  {{-- ROOMS PAGE --}}
  <div class="rooms-page">
    <div class="rooms-page-inner">

      {{-- FILTER TOGGLE (mobile) --}}
      <button class="filter-toggle-btn" id="filterToggle" type="button">
        <i class="ri-equalizer-line"></i> Filters
      </button>

      {{-- SIDEBAR --}}
      <aside class="filters-sidebar" id="filtersSidebar">
        <form method="GET" action="{{ route('frontpage.available-rooms') }}">
          {{-- Preserve search params --}}
          @if(request('check_in'))  <input type="hidden" name="check_in"  value="{{ request('check_in') }}"> @endif
          @if(request('check_out')) <input type="hidden" name="check_out" value="{{ request('check_out') }}"> @endif
          @if(request('guests'))    <input type="hidden" name="guests"    value="{{ request('guests') }}"> @endif

          {{-- Room Type --}}
          <div class="filter-card">
            <div class="filter-card-title">
              Room Type
              <button type="button" onclick="clearGroup('type')">Clear</button>
            </div>
            <div class="filter-options" id="group-type">
              @foreach($roomTypes as $type)
                <div class="filter-option">
                  <input type="checkbox" id="type_{{ $type->room_type_id }}" name="room_types[]"
                         value="{{ $type->room_type_id }}"
                         {{ in_array($type->room_type_id, (array) request('room_types', [])) ? 'checked' : '' }}>
                  <label for="type_{{ $type->room_type_id }}">
                    {{ $type->room_type_name }}
                    <span class="filter-count">{{ $type->room_count }}</span>
                  </label>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Capacity --}}
          <div class="filter-card">
            <div class="filter-card-title">Capacity</div>
            <div class="filter-options">
              @foreach([1 => '1 Guest', 2 => '2 Guests', 3 => '3–4 Guests', 5 => '5+ Guests'] as $val => $label)
                <div class="filter-option">
                  <input type="radio" id="cap_{{ $val }}" name="min_pax" value="{{ $val }}"
                         {{ request('min_pax') == $val ? 'checked' : '' }}>
                  <label for="cap_{{ $val }}">{{ $label }}</label>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Price Range --}}
          <div class="filter-card">
            <div class="filter-card-title">Max Price / Night</div>
            <div class="price-range-wrap">
              <input type="range" class="price-range" id="maxPrice" name="max_price"
                     min="{{ $priceMin }}" max="{{ $priceMax }}"
                     value="{{ request('max_price', $priceMax) }}"
                     oninput="document.getElementById('priceVal').textContent = '₱' + Number(this.value).toLocaleString()">
              <div class="price-range-labels">
                <span>₱{{ number_format($priceMin) }}</span>
                <span id="priceVal">₱{{ number_format(request('max_price', $priceMax)) }}</span>
                <span>₱{{ number_format($priceMax) }}</span>
              </div>
            </div>
          </div>

          {{-- Sort --}}
          <div class="filter-card">
            <div class="filter-card-title">Sort By</div>
            <div class="filter-options">
              @foreach(['price_asc' => 'Price: Low to High', 'price_desc' => 'Price: High to Low', 'name_asc' => 'Name A–Z'] as $val => $label)
                <div class="filter-option">
                  <input type="radio" id="sort_{{ $val }}" name="sort" value="{{ $val }}"
                         {{ request('sort', 'price_asc') == $val ? 'checked' : '' }}>
                  <label for="sort_{{ $val }}">{{ $label }}</label>
                </div>
              @endforeach
            </div>
          </div>

          <button type="submit" class="btn-apply-filters">
            <i class="ri-search-line"></i> Apply Filters
          </button>
        </form>
      </aside>

      {{-- RESULTS --}}
      <div>
        <div class="results-header">
          <p class="results-count">
            Showing <strong>{{ $rooms->firstItem() }}–{{ $rooms->lastItem() }}</strong>
            of <strong>{{ $rooms->total() }}</strong> available rooms
          </p>
          <div class="sort-wrap">
            <label>Sort:</label>
            <select class="sort-select" onchange="applySort(this.value)">
              <option value="price_asc"  {{ request('sort', 'price_asc') == 'price_asc'  ? 'selected' : '' }}>Price: Low to High</option>
              <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
              <option value="name_asc"   {{ request('sort') == 'name_asc'   ? 'selected' : '' }}>Name A–Z</option>
            </select>
          </div>
        </div>

        <div class="rooms-grid">
          @forelse($rooms as $room)
            <div class="room-card">
              <div class="room-card-img-wrap">
                @if($room->image_path)
                  <img src="{{ asset('storage/' . $room->image_path) }}" alt="{{ $room->roomType->room_type_name }}" loading="lazy">
                @else
                  <img src="{{ asset('assets/img/frontpages/img/header-bg.jpg') }}" alt="{{ $room->roomType->room_type_name }}" loading="lazy">
                @endif
                <span class="room-badge"><i class="ri-checkbox-circle-line"></i> Available</span>
                <span class="room-rating"><i class="ri-star-fill"></i> 4.8</span>
              </div>
              <div class="room-card-body">
                <p class="room-type-label">{{ $room->roomType->room_type_name }}</p>
                <h3>Room {{ $room->room_id }}</h3>
                <p class="room-desc">{{ $room->roomType->description ?? 'A comfortable and well-appointed room designed for a relaxing stay.' }}</p>
                <div class="room-features">
                  <span class="room-feat"><i class="ri-user-line"></i> Up to {{ $room->roomType->max_pax }} guests</span>
                  <span class="room-feat"><i class="ri-wifi-line"></i> Free WiFi</span>
                  <span class="room-feat"><i class="ri-tv-line"></i> Smart TV</span>
                </div>
                <div class="room-footer">
                  <div class="room-price">
                    <div class="amount">₱{{ number_format($room->roomType->rate_per_night) }}</div>
                    <div class="per">per night</div>
                  </div>
                  <a href="{{ route('frontpage.room-details', $room->room_id) }}" class="btn-book">View Room</a>
                </div>
              </div>
            </div>
          @empty
            <div class="empty-state">
              <div class="empty-icon"><i class="ri-hotel-bed-line"></i></div>
              <h3>No Rooms Found</h3>
              <p>No rooms match your current criteria. Try adjusting your filters.</p>
              <a href="{{ route('frontpage.available-rooms') }}" class="btn-clear">Clear All Filters</a>
            </div>
          @endforelse
        </div>

        @if($rooms->hasPages())
          <div class="pagination-wrap">
            {{ $rooms->appends(request()->query())->links() }}
          </div>
        @endif
      </div>

    </div>
  </div>

  {{-- FOOTER --}}
  <footer class="site-footer">
    <div class="footer-bottom">
      <p>&copy; 2026 Hotel De SLSU. Developed by Michael &amp; Renz. All rights reserved.</p>
    </div>
  </footer>

@endsection

@push('scripts')
<script>
  document.getElementById('filterToggle')?.addEventListener('click', () => {
    document.getElementById('filtersSidebar').classList.toggle('open');
  });

  function clearGroup(id) {
    document.querySelectorAll(`#group-${id} input[type="checkbox"]`)
      .forEach(el => el.checked = false);
  }

  function applySort(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    window.location.href = url.toString();
  }
</script>
@endpush