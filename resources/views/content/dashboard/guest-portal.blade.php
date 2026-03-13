@extends('layouts/contentNavbarLayout')

@section('title', 'Guest Portal - Grand Hotel')

@section('page-style')
<style>
  .status-banner {
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  .status-banner.pending    { background-color: #fff3e0; color: #e65100; }
  .status-banner.approved   { background-color: #e3f2fd; color: #1565c0; }
  .status-banner.checked-in { background-color: #e8f5e9; color: #2e7d32; }

  .balance-card {
    background: linear-gradient(135deg, #030213, rgba(3,2,19,0.82));
    color: #fff;
    border-radius: 0.5rem;
    padding: 1.5rem;
    height: 100%;
  }
  .balance-card .balance-amount { font-size: 2.25rem; font-weight: 700; line-height: 1.2; }
  .balance-card .label-muted    { color: rgba(255,255,255,0.75); font-size: 0.85rem; }
  .balance-card .divider        { border-top: 1px solid rgba(255,255,255,0.2); margin: 1rem 0; }

  .menu-carousel-wrapper { overflow: hidden; }
  .menu-carousel-track   { display: flex; gap: 1rem; transition: transform 0.3s ease-in-out; }
  .menu-card-item {
    flex: 0 0 calc(33.333% - 0.68rem);
    min-width: 260px;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 0.5rem;
    overflow: hidden;
    background: #fff;
    cursor: pointer;
    transition: box-shadow 0.2s, transform 0.2s;
  }
  .menu-card-item:hover     { box-shadow: 0 4px 16px rgba(0,0,0,0.12); transform: translateY(-2px); }
  .menu-card-item.selected  { border: 2px solid #696cff; }
  @media (max-width: 991px) { .menu-card-item { flex: 0 0 calc(50% - 0.5rem); } }
  @media (max-width: 575px) { .menu-card-item { flex: 0 0 100%; } }
  .menu-card-item img       { width: 100%; height: 160px; object-fit: cover; }
  .menu-card-item .menu-body { padding: 0.85rem; }
  .menu-price               { font-weight: 600; font-size: 1.05rem; white-space: nowrap; }

  .service-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 0.5rem;
    background: #fff;
    width: 100%;
    text-align: left;
    transition: all 0.2s;
    cursor: pointer;
  }
  .service-btn:hover {
    border-color: #696cff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
  }
  .service-icon-wrap {
    width: 48px; height: 48px;
    display: flex; align-items: center; justify-content: center;
    background: #f0f0f5;
    border-radius: 0.375rem;
    flex-shrink: 0;
  }
  .amenity-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.78rem;
    padding: 0.25rem 0.65rem;
    background: #f0f0f5;
    border-radius: 999px;
    margin: 0.2rem;
  }
  .req-row td { vertical-align: middle; }
</style>
@endsection

@section('content')

@php
  $statusMap = [
    'pending'    => ['class'=>'pending',    'icon'=>'ri-time-line',           'title'=>'Reservation Pending',  'msg'=>'Your reservation is being reviewed. We will notify you once it is approved.'],
    'approved'   => ['class'=>'approved',   'icon'=>'ri-checkbox-circle-line','title'=>'Reservation Approved', 'msg'=>'Your reservation has been confirmed! Check-in date: '.$guest['checkIn']],
    'checked-in' => ['class'=>'checked-in', 'icon'=>'ri-checkbox-circle-line','title'=>'Checked In',           'msg'=>'Welcome to Grand Hotel! Enjoy your stay.'],
  ];
  $s = $statusMap[$guest['status']];

  $services = [
    ['key'=>'room_service','icon'=>'ri-cup-line',            'title'=>'Room Service',    'sub'=>'Order food & beverages'],
    ['key'=>'housekeeping','icon'=>'ri-tools-line',          'title'=>'Housekeeping',    'sub'=>'Request cleaning service'],
    ['key'=>'wifi',        'icon'=>'ri-wifi-line',           'title'=>'WiFi Access',     'sub'=>'Network: GrandHotel_5G'],
    ['key'=>'concierge',   'icon'=>'ri-customer-service-line','title'=>'Concierge',      'sub'=>'Get assistance & recommendations'],
    ['key'=>'laundry',     'icon'=>'ri-shirt-line',          'title'=>'Laundry Service', 'sub'=>'Schedule pickup & delivery'],
    ['key'=>'spa',         'icon'=>'ri-mental-health-line',  'title'=>'Spa & Wellness',  'sub'=>'Book spa treatments'],
  ];

  $statusBadge = [
    'pending'     => 'bg-label-warning',
    'in_progress' => 'bg-label-info',
    'completed'   => 'bg-label-success',
    'cancelled'   => 'bg-label-secondary',
  ];
@endphp

<div class="row gy-6">

  {{-- Status Banner --}}
  <div class="col-12">
    <div class="status-banner {{ $s['class'] }}">
      <i class="icon-base ri {{ $s['icon'] }} icon-24px flex-shrink-0"></i>
      <div>
        <h6 class="mb-0">{{ $s['title'] }}</h6>
        <small>{{ $s['msg'] }}</small>
      </div>
    </div>
  </div>

  {{-- Room Card --}}
  <div class="col-lg-8">
    <div class="card h-100">
      @if($room)
        <img src="{{ $room['image'] }}" alt="{{ $room['type'] }}"
             class="card-img-top" style="height:280px;object-fit:cover;border-radius:0.5rem 0.5rem 0 0;">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div>
              <h5 class="card-title mb-1">{{ $room['type'] }}</h5>
              @if($room['description'])
                <p class="text-muted small mb-0">{{ $room['description'] }}</p>
              @endif
            </div>
            <div class="d-flex flex-column align-items-end gap-1">
              <span class="badge bg-primary rounded-pill px-3">Room {{ $room['number'] }}</span>
              @if($guest['status'] === 'pending')
                <span class="badge bg-label-warning rounded-pill px-3">
                  <i class="ri-time-line me-1"></i>Pending Check-in Approval
                </span>
              @elseif($guest['status'] === 'approved')
                <span class="badge bg-label-info rounded-pill px-3">
                  <i class="ri-calendar-check-line me-1"></i>Awaiting Check-in
                </span>
              @endif
            </div>
          </div>
          <hr>
          <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 d-flex align-items-center gap-2">
              <i class="ri-group-line text-muted"></i>
              <span>Up to {{ $room['max_pax'] }} guests</span>
            </div>
            <div class="col-6 col-md-4 d-flex align-items-center gap-2">
              <i class="ri-moon-line text-muted"></i>
              <span>{{ $guest['nights'] }} night{{ $guest['nights'] != 1 ? 's' : '' }}</span>
            </div>
            <div class="col-6 col-md-4 d-flex align-items-center gap-2">
              <i class="ri-money-dollar-circle-line text-muted"></i>
              <span>₱{{ $room['rate'] }} / night</span>
            </div>
          </div>
          @if(!empty($room['amenities']))
            <p class="text-muted small mb-1">Amenities</p>
            @foreach($room['amenities'] as $amenity)
              <span class="amenity-pill">
                <i class="ri-checkbox-circle-line" style="font-size:0.75rem;"></i>
                {{ $amenity }}
              </span>
            @endforeach
          @endif
        </div>
      @else
        <div class="card-body d-flex flex-column align-items-center justify-content-center" style="min-height:220px;">
          <i class="ri-hotel-bed-line mb-3" style="font-size:3rem;color:#d0d0d0;"></i>
          <p class="text-muted mb-0">No active reservation found.</p>
        </div>
      @endif
    </div>
  </div>

  {{-- Balance Card --}}
  <div class="col-lg-4">
    <div class="balance-card">
      <div class="d-flex align-items-center gap-2 mb-4">
        <i class="ri-bank-card-line icon-24px"></i>
        <h5 class="mb-0 text-white">Account Balance</h5>
      </div>
      <p class="label-muted mb-1">Remaining Balance</p>
      <div class="balance-amount mb-1">{{ $balance['remaining'] }}</div>
      @if($reservation)
        <p class="label-muted mb-3" style="font-size:0.8rem;">
          {{ $guest['nights'] }} night{{ $guest['nights'] != 1 ? 's' : '' }}
          &bull; {{ $guest['pax'] }} guest{{ $guest['pax'] != 1 ? 's' : '' }}
        </p>
      @else
        <div class="mb-3"></div>
      @endif
      <div class="divider"></div>
      <div class="row g-3">
        <div class="col-6">
          <p class="label-muted mb-1"><i class="ri-arrow-up-line me-1"></i>Total Charges</p>
          <h5 class="mb-0 text-white">{{ $balance['charges'] }}</h5>
        </div>
        <div class="col-6">
          <p class="label-muted mb-1"><i class="ri-arrow-down-line me-1"></i>Paid</p>
          <h5 class="mb-0 text-white">{{ $balance['payments'] }}</h5>
        </div>
      </div>
    </div>
  </div>

  {{-- Breakfast Menu Carousel --}}
  @if(count($menuItems))
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <h5 class="card-title m-0">Breakfast Menu</h5>
          @if($guest['status'] === 'checked-in')
            <small class="text-muted">Tap an item to add to your order</small>
          @endif
          <a href="{{ route('guest.breakfast.menu') }}" class="btn btn-outline-primary btn-sm">
    <i class="ri-restaurant-line me-1"></i> View Full Menu
</a>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-secondary" id="menuPrev">
            <i class="ri-arrow-left-s-line"></i>
          </button>
          <button class="btn btn-sm btn-outline-secondary" id="menuNext">
            <i class="ri-arrow-right-s-line"></i>
          </button>
        </div>
      </div>
      <div class="card-body pb-4">
        <div class="menu-carousel-wrapper">
          <div class="menu-carousel-track" id="menuTrack">
            @foreach($menuItems as $item)
              <div class="menu-card-item"
                   data-id="{{ $item['id'] }}"
                   data-name="{{ $item['name'] }}"
                   data-price="{{ $item['price_raw'] }}"
                   onclick="toggleMenuItem(this)">
                @if($item['img'])
                  <img src="{{ $item['img'] }}" alt="{{ $item['name'] }}">
                @else
                  <div class="bg-label-secondary d-flex align-items-center justify-content-center"
                       style="width:100%;height:160px;">
                    <div class="text-center">
                      <i class="ri ri-bowl-line icon-48px text-body-secondary"></i>
                      <p class="text-body-secondary mb-0 mt-1 small">No Image</p>
                    </div>
                  </div>
                @endif
                <div class="menu-body">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <div>
                      <h6 class="mb-0">{{ $item['name'] }}</h6>
                      <span class="badge bg-label-warning mt-1">Breakfast</span>
                    </div>
                    <span class="menu-price text-primary">{{ $item['price'] }}</span>
                  </div>
                  @if($item['desc'])
                    <p class="text-muted small mb-0 mt-2">{{ $item['desc'] }}</p>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>

        @if($guest['status'] === 'checked-in')
          <div id="orderBasket" class="mt-4 d-none">
            <hr>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">Your Order</h6>
              <button class="btn btn-sm btn-primary" onclick="submitFoodOrder()">
                <i class="ri-send-plane-line me-1"></i> Submit Order
              </button>
            </div>
            <div id="basketItems" class="d-flex flex-wrap gap-2"></div>
          </div>
        @endif
      </div>
    </div>
  </div>
  @endif

  {{-- Available Services --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0">Available Services</h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          @foreach($services as $svc)
            <div class="col-12 col-sm-6 col-lg-4">
              <button class="service-btn" onclick="handleService('{{ $svc['key'] }}')">
                <div class="service-icon-wrap">
                  <i class="icon-base ri {{ $svc['icon'] }} icon-24px"></i>
                </div>
                <div>
                  <h6 class="mb-0">{{ $svc['title'] }}</h6>
                  <small class="text-muted">{{ $svc['sub'] }}</small>
                </div>
              </button>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Service Request History --}}
  @if($guest['status'] === 'checked-in' && count($serviceRequests))
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0">My Service Requests</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Type</th>
              <th>Description</th>
              <th>Requested</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($serviceRequests as $req)
              <tr class="req-row">
                <td>{{ $req->service_request_id }}</td>
                <td>
                  <span class="badge bg-label-primary text-capitalize">
                    {{ str_replace('_', ' ', $req->service_type) }}
                  </span>
                </td>
                <td>{{ $req->description }}</td>
                <td>
                  <small class="text-muted">
                    {{ \Carbon\Carbon::parse($req->requested_at)->format('M j, g:i A') }}
                  </small>
                </td>
                <td>
                  <span class="badge {{ $statusBadge[$req->request_status] ?? 'bg-label-secondary' }} text-capitalize">
                    {{ str_replace('_', ' ', $req->request_status) }}
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif

  {{-- Stay Information --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0">Stay Information</h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-6 col-md-3">
            <p class="text-muted small mb-1">Check-in Date</p>
            <p class="fw-medium mb-0">{{ $guest['checkIn'] }}</p>
          </div>
          <div class="col-6 col-md-3">
            <p class="text-muted small mb-1">Check-out Date</p>
            <p class="fw-medium mb-0">{{ $guest['checkOut'] }}</p>
          </div>
          <div class="col-6 col-md-3">
            <p class="text-muted small mb-1">Email</p>
            <p class="fw-medium mb-0">{{ $guest['email'] }}</p>
          </div>
          <div class="col-6 col-md-3">
            <p class="text-muted small mb-1">Reservation ID</p>
            <p class="fw-medium mb-0">{{ $guest['guestId'] }}</p>
          </div>
          @if($guest['purpose'])
          <div class="col-12">
            <p class="text-muted small mb-1">Purpose of Visit</p>
            <p class="fw-medium mb-0">{{ $guest['purpose'] }}</p>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Room-Service Modal --}}
<div class="modal fade" id="roomServiceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Room Service Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Describe your request</label>
        <textarea id="roomServiceDesc" class="form-control" rows="3"
                  placeholder="e.g. Extra towels, pillow, toiletries..."></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" onclick="submitRoomService()">
          <i class="ri-send-plane-line me-1"></i> Submit
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  const CSRF         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const SERVICE_URL  = '{{ route("guest.service") }}';
  const GUEST_STATUS = '{{ $guest["status"] }}';

  // ── Carousel ─────────────────────────────────────────────────────
  (function () {
    const track = document.getElementById('menuTrack');
    if (!track) return;
    let current = 0;

    const slidesToShow = () => window.innerWidth < 576 ? 1 : window.innerWidth < 992 ? 2 : 3;

    const update = () => {
      const w = track.children[0]?.offsetWidth ?? 0;
      track.style.transform = `translateX(${-(current * (w + 16))}px)`;
    };

    document.getElementById('menuNext')?.addEventListener('click', () => {
      if (current < track.children.length - slidesToShow()) { current++; update(); }
    });
    document.getElementById('menuPrev')?.addEventListener('click', () => {
      if (current > 0) { current--; update(); }
    });
  })();

  // ── Food Basket ───────────────────────────────────────────────────
  const basket = {};

  function toggleMenuItem(el) {
    if (GUEST_STATUS !== 'checked-in') {
      toastr.info('Food ordering is only available during your stay.');
      return;
    }
    const { id, name, price } = el.dataset;
    if (basket[id]) {
      delete basket[id];
      el.classList.remove('selected');
    } else {
      basket[id] = { name, price: parseFloat(price), qty: 1 };
      el.classList.add('selected');
    }
    renderBasket();
  }

  function renderBasket() {
    const wrap = document.getElementById('orderBasket');
    const list = document.getElementById('basketItems');
    if (!wrap || !list) return;
    const keys = Object.keys(basket);
    wrap.classList.toggle('d-none', !keys.length);
    list.innerHTML = keys.map(id => `
      <div class="d-flex align-items-center gap-2 border rounded px-2 py-1">
        <span class="small">${basket[id].name}</span>
        <div class="input-group input-group-sm" style="width:90px;">
          <button class="btn btn-outline-secondary btn-sm" onclick="changeQty('${id}',-1)">−</button>
          <input type="text" class="form-control text-center" value="${basket[id].qty}" readonly>
          <button class="btn btn-outline-secondary btn-sm" onclick="changeQty('${id}',1)">+</button>
        </div>
        <span class="small text-muted">₱${(basket[id].price * basket[id].qty).toFixed(2)}</span>
      </div>`).join('');
  }

  function changeQty(id, delta) {
    if (!basket[id]) return;
    basket[id].qty = Math.max(1, basket[id].qty + delta);
    renderBasket();
  }

  async function submitFoodOrder() {
    const keys = Object.keys(basket);
    if (!keys.length) { toastr.warning('Please select at least one item.'); return; }
    const items = keys.map(id => ({ breakfast_id: parseInt(id), quantity: basket[id].qty }));
    const ok = await postService({ service_type: 'food', description: 'Breakfast order', items });
    if (ok) {
      keys.forEach(id => {
        delete basket[id];
        document.querySelector(`.menu-card-item[data-id="${id}"]`)?.classList.remove('selected');
      });
      renderBasket();
    }
  }

  // ── Service Buttons ───────────────────────────────────────────────
  function handleService(type) {
    if (type === 'wifi') { toastr.info('Password: Welcome2026', 'WiFi: GrandHotel_5G'); return; }
    if (GUEST_STATUS !== 'checked-in') { toastr.info('Services are available during your stay.'); return; }
    if (type === 'room_service') {
      new bootstrap.Modal(document.getElementById('roomServiceModal')).show();
      return;
    }
    const labels = { housekeeping:'Housekeeping', concierge:'Concierge', laundry:'Laundry Service', spa:'Spa & Wellness' };
    postService({ service_type: 'room_service', description: `${labels[type] ?? type} request` });
  }

  async function submitRoomService() {
    const desc = document.getElementById('roomServiceDesc').value.trim();
    if (!desc) { toastr.warning('Please describe your request.'); return; }
    const ok = await postService({ service_type: 'room_service', description: desc });
    if (ok) {
      bootstrap.Modal.getInstance(document.getElementById('roomServiceModal'))?.hide();
      document.getElementById('roomServiceDesc').value = '';
    }
  }

  async function postService(payload) {
    try {
      const res  = await fetch(SERVICE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (data.success) {
        toastr.success('Request submitted successfully!');
        setTimeout(() => location.reload(), 1500);
        return true;
      }
      toastr.error(data.error ?? 'Something went wrong.');
    } catch (e) {
      toastr.error('Request failed. Please try again.');
    }
    return false;
  }
</script>
@endsection