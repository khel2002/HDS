@extends('layouts/contentNavbarLayout')

@section('title', 'Check-out')

@section('page-style')
<style>
  /* ── Layout ─────────────────────────────────────────────────── */
  .co-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.5rem;
    align-items: start;
  }
  @media (max-width: 991px) { .co-layout { grid-template-columns: 1fr; } }

  /* ── Status banner ──────────────────────────────────────────── */
  .co-status-bar {
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem 1.25rem; border-radius: 12px;
    border: 1.5px solid; margin-bottom: 1.5rem;
  }
  .co-status-bar.info     { background: rgba(105,108,255,.07); border-color: rgba(105,108,255,.25); }
  .co-status-bar.warning  { background: rgba(255,171,0,.08);   border-color: rgba(255,171,0,.3);   }
  .co-status-bar.success  { background: rgba(40,199,111,.08);  border-color: rgba(40,199,111,.3);  }
  .co-status-bar.danger   { background: rgba(234,84,85,.08);   border-color: rgba(234,84,85,.3);   }
  .co-status-icon { font-size: 1.4rem; flex-shrink: 0; }
  .co-status-bar.info     .co-status-icon { color: #696cff; }
  .co-status-bar.warning  .co-status-icon { color: #ffab00; }
  .co-status-bar.success  .co-status-icon { color: #28c76f; }
  .co-status-bar.danger   .co-status-icon { color: #ea5455; }
  .co-status-bar h6 { margin: 0 0 .1rem; font-size: .88rem; font-weight: 700; }
  .co-status-bar p  { margin: 0; font-size: .78rem; color: var(--bs-secondary-color); }

  /* ── Section card ───────────────────────────────────────────── */
  .co-card {
    background: var(--bs-body-bg);
    border: 1.5px solid var(--bs-border-color);
    border-radius: 14px; padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
  }
  .co-card-title {
    display: flex; align-items: center; gap: .55rem;
    font-size: .78rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--bs-secondary-color);
    margin-bottom: 1rem; padding-bottom: .65rem;
    border-bottom: 1.5px solid var(--bs-border-color);
  }

  /* ── Stay summary grid ──────────────────────────────────────── */
  .co-info-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem;
  }
  @media (max-width: 575px) { .co-info-grid { grid-template-columns: repeat(2, 1fr); } }
  .co-info-item p  { margin: 0 0 .15rem; font-size: .72rem; color: var(--bs-secondary-color); }
  .co-info-item span { font-size: .88rem; font-weight: 600; }

  /* ── Timeline (inspection steps) ───────────────────────────── */
  .co-timeline { display: flex; align-items: center; }
  .co-timeline .step { display: flex; flex-direction: column; align-items: center; flex: 1; }
  .co-timeline .step .dot {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; margin-bottom: 6px; flex-shrink: 0;
  }
  .co-timeline .step small { font-size: .7rem; text-align: center; white-space: nowrap; }
  .co-timeline .line { flex: 1; height: 2px; margin-bottom: 24px; }
  .co-timeline .line.done { background: #28c76f; }
  .co-timeline .line.idle { background: var(--bs-border-color); }

  /* ── Charges table ──────────────────────────────────────────── */
  .co-charge-row td { vertical-align: middle; font-size: .82rem; }
  .co-charge-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .18rem .6rem; border-radius: 999px;
    font-size: .67rem; font-weight: 700; text-transform: uppercase;
  }

  /* ── Balance sidebar ────────────────────────────────────────── */
  .co-balance-card {
    background: linear-gradient(135deg, #030213, rgba(3,2,19,.85));
    color: #fff; border-radius: 14px; padding: 1.4rem 1.5rem;
    margin-bottom: 1.25rem;
  }
  .co-balance-card .label-muted { color: rgba(255,255,255,.65); font-size: .78rem; }
  .co-balance-card .balance-amt { font-size: 2rem; font-weight: 700; line-height: 1.2; }
  .co-balance-card .divider     { border-top: 1px solid rgba(255,255,255,.15); margin: 1rem 0; }
  .co-balance-row { display: flex; justify-content: space-between; font-size: .82rem; margin-bottom: .4rem; }
  .co-balance-row .lbl { color: rgba(255,255,255,.65); }
  .co-balance-row .val { font-weight: 600; }

  /* ── Damage alert ───────────────────────────────────────────── */
  .co-damage-alert {
    background: rgba(234,84,85,.06); border: 1.5px solid rgba(234,84,85,.3);
    border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1rem;
  }
  .co-damage-alert h6 { color: #ea5455; margin: 0 0 .35rem; font-size: .85rem; }
  .co-damage-alert p  { margin: 0; font-size: .78rem; color: var(--bs-secondary-color); }

  /* ── Notes textarea ─────────────────────────────────────────── */
  .co-notes {
    width: 100%; padding: .6rem .85rem; font-size: .82rem;
    background: var(--bs-tertiary-bg); color: var(--bs-body-color);
    border: 1.5px solid var(--bs-border-color); border-radius: 10px;
    resize: none; font-family: inherit; line-height: 1.5;
    transition: border-color .15s, box-shadow .15s;
  }
  .co-notes:focus {
    outline: none; border-color: #696cff;
    box-shadow: 0 0 0 3px rgba(105,108,255,.12);
    background: var(--bs-body-bg);
  }
  .co-notes::placeholder { color: var(--bs-secondary-color); opacity: .65; }

  /* ── Toast ──────────────────────────────────────────────────── */
  #co-toast-wrap {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    display: flex; flex-direction: column; gap: .5rem;
    z-index: 9999; pointer-events: none;
  }
  .co-toast {
    display: flex; align-items: center; gap: .7rem;
    padding: .75rem 1.1rem; border-radius: 10px;
    font-size: .84rem; font-weight: 500; min-width: 240px; max-width: 320px;
    box-shadow: 0 8px 24px rgba(0,0,0,.15); pointer-events: auto; color: #fff;
    animation: coToastIn .22s ease forwards;
  }
  .co-toast.success { background: #2e7d32; }
  .co-toast.error   { background: #c62828; }
  .co-toast.warning { background: #e65100; }
  .co-toast.info    { background: #1565c0; }
  .co-toast.hide    { animation: coToastOut .2s ease forwards; }
  @keyframes coToastIn  { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:none} }
  @keyframes coToastOut { from{opacity:1;transform:none} to{opacity:0;transform:translateX(20px)} }
</style>
@endsection

@section('content')

@php
  $status   = $checkoutRequest->status ?? null;
  $hasIssues       = $status === 'has_issues';
  $isCleared       = $status === 'cleared';
  $isInspecting    = $status === 'inspecting';
  $isPending       = $status === 'pending';
  $noRequest       = is_null($status);
  $hasUnacknowledged = $damageCharges->where('is_acknowledged', 0)->count() > 0;

  // Timeline indices: 0=requested, 1=inspecting, 2=cleared or has_issues
  $timelineIdx = match($status) {
    'pending'    => 0,
    'inspecting' => 1,
    'cleared'    => 2,
    'has_issues' => 2,
    default      => -1,
  };

  $checkInCarbon  = \Carbon\Carbon::parse($reservation->check_in_date);
  $checkOutCarbon = \Carbon\Carbon::parse($reservation->check_out_date);
  $today          = \Carbon\Carbon::today();
  $daysLeft       = $today->diffInDays($checkOutCarbon, false);
@endphp

{{-- Page header --}}
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h4 class="mb-1">Check-out</h4>
        <p class="mb-0 text-body-secondary small">
          Room <strong>{{ $reservation->room_number }}</strong>
          &mdash; {{ $reservation->room_type_name }}
          &mdash; Expected check-out: <strong>{{ $checkOutCarbon->format('F j, Y') }}</strong>
        </p>
      </div>
      <a href="{{ route('guest.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ri ri-arrow-left-line me-1"></i>Back to Dashboard
      </a>
    </div>
  </div>
</div>

{{-- ── Contextual status banner ─────────────────────────────── --}}
@if($isCleared)
  <div class="co-status-bar success">
    <span class="co-status-icon"><i class="ri ri-checkbox-circle-line"></i></span>
    <div>
      <h6>Room Inspection Cleared!</h6>
      <p>Your room has been inspected and is in good condition. You are cleared to check out. Please settle your remaining balance at the front desk.</p>
    </div>
  </div>
@elseif($hasIssues)
  <div class="co-status-bar danger">
    <span class="co-status-icon"><i class="ri ri-alert-line"></i></span>
    <div>
      <h6>Issues Found During Inspection</h6>
      <p>Our staff found damage or missing items in your room. Additional charges have been added to your balance. Please review and acknowledge below.</p>
    </div>
  </div>
@elseif($isInspecting)
  <div class="co-status-bar warning">
    <span class="co-status-icon"><i class="ri ri-loader-4-line"></i></span>
    <div>
      <h6>Room Inspection In Progress</h6>
      <p>Our staff is currently inspecting your room. This page will update automatically — please wait.</p>
    </div>
  </div>
@elseif($isPending)
  <div class="co-status-bar info">
    <span class="co-status-icon"><i class="ri ri-time-line"></i></span>
    <div>
      <h6>Checkout Request Received</h6>
      <p>Your request has been sent to our staff. They will begin the room inspection shortly.</p>
    </div>
  </div>
@elseif($daysLeft <= 1)
  <div class="co-status-bar warning">
    <span class="co-status-icon"><i class="ri ri-calendar-event-line"></i></span>
    <div>
      <h6>{{ $daysLeft === 0 ? 'Check-out is Today' : 'Check-out is Tomorrow' }}</h6>
      <p>When you are ready to leave, tap <strong>Request Check-out</strong> below to notify our staff for room inspection.</p>
    </div>
  </div>
@else
  <div class="co-status-bar info">
    <span class="co-status-icon"><i class="ri ri-information-line"></i></span>
    <div>
      <h6>{{ $daysLeft }} Day{{ $daysLeft != 1 ? 's' : '' }} Until Check-out</h6>
      <p>Your balance summary is shown below. You can request check-out on or before your scheduled check-out date.</p>
    </div>
  </div>
@endif

<div class="co-layout">

  {{-- ════════════════════ LEFT COLUMN ════════════════════ --}}
  <div>

    {{-- Stay summary --}}
    <div class="co-card">
      <div class="co-card-title">
        <i class="ri ri-hotel-line"></i> Stay Summary
      </div>
      <div class="co-info-grid">
        <div class="co-info-item">
          <p>Room</p>
          <span>{{ $reservation->room_number }}</span>
        </div>
        <div class="co-info-item">
          <p>Type</p>
          <span>{{ $reservation->room_type_name }}</span>
        </div>
        <div class="co-info-item">
          <p>Nights</p>
          <span>{{ $reservation->no_nights }}</span>
        </div>
        <div class="co-info-item">
          <p>Check-in</p>
          <span>{{ $checkInCarbon->format('M d, Y') }}</span>
        </div>
        <div class="co-info-item">
          <p>Check-out</p>
          <span>{{ $checkOutCarbon->format('M d, Y') }}</span>
        </div>
        <div class="co-info-item">
          <p>Guests</p>
          <span>{{ ($reservation->adults + $reservation->children) }}</span>
        </div>
      </div>
    </div>

    {{-- Inspection timeline --}}
    @if(! $noRequest)
    <div class="co-card">
      <div class="co-card-title">
        <i class="ri ri-map-pin-time-line"></i> Inspection Progress
      </div>
      @php
        $steps = [
          ['label' => 'Requested',   'icon' => 'ri-send-plane-line'],
          ['label' => 'Inspecting',  'icon' => 'ri-search-eye-line'],
          ['label' => $hasIssues ? 'Issues Found' : 'Cleared', 'icon' => $hasIssues ? 'ri-alert-line' : 'ri-checkbox-circle-line'],
        ];
      @endphp
      <div class="co-timeline">
        @foreach($steps as $i => $step)
          @php
            $isDone   = $timelineIdx > $i;
            $isActive = $timelineIdx === $i;
            $dotCls   = $isDone   ? 'bg-success text-white'
                      : ($isActive
                          ? ($hasIssues && $i === 2 ? 'bg-danger text-white' : 'bg-primary text-white')
                          : 'bg-label-secondary text-body-secondary');
            $lblCls   = $isDone   ? 'text-success fw-semibold'
                      : ($isActive
                          ? ($hasIssues && $i === 2 ? 'text-danger fw-semibold' : 'text-primary fw-semibold')
                          : 'text-body-secondary');
            $ico      = $isDone ? 'ri-check-line' : $step['icon'];
          @endphp
          <div class="step">
            <div class="dot {{ $dotCls }}">
              <i class="ri {{ $ico }}"></i>
            </div>
            <small class="{{ $lblCls }}">{{ $step['label'] }}</small>
          </div>
          @if(! $loop->last)
            <div class="line {{ $isDone ? 'done' : 'idle' }}"></div>
          @endif
        @endforeach
      </div>

      @if($checkoutRequest->staff_notes ?? false)
        <div class="mt-3 p-3 rounded" style="background: var(--bs-tertiary-bg); font-size: .81rem;">
          <span class="fw-semibold text-body-secondary text-uppercase" style="font-size:.68rem; letter-spacing:.06em;">
            <i class="ri ri-chat-3-line me-1"></i>Staff Note
          </span>
          <p class="mb-0 mt-1">{{ $checkoutRequest->staff_notes }}</p>
        </div>
      @endif
    </div>
    @endif

    {{-- Damage / missing charges --}}
    @if($damageCharges->isNotEmpty())
    <div class="co-card">
      <div class="co-card-title">
        <i class="ri ri-alert-line text-danger"></i>
        <span class="text-danger">Additional Charges</span>
      </div>

      @if($hasUnacknowledged)
        <div class="co-damage-alert">
          <h6><i class="ri ri-error-warning-line me-1"></i>Action Required</h6>
          <p>The following charges have been added to your bill. Please review and tap <strong>Acknowledge Charges</strong> to proceed with check-out.</p>
        </div>
      @endif

      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>Item / Description</th>
              <th class="text-end">Amount</th>
              <th class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($damageCharges as $charge)
            <tr class="co-charge-row">
              <td>{{ $charge->description }}</td>
              <td class="text-end fw-semibold text-danger">₱{{ number_format($charge->amount, 2) }}</td>
              <td class="text-center">
                @if($charge->is_acknowledged)
                  <span class="co-charge-badge bg-label-success">
                    <i class="ri ri-checkbox-circle-line"></i> Acknowledged
                  </span>
                @else
                  <span class="co-charge-badge bg-label-danger">
                    <i class="ri ri-time-line"></i> Pending
                  </span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="table-light">
            <tr>
              <td class="fw-semibold">Total Additional Charges</td>
              <td class="text-end fw-bold text-danger">₱{{ number_format($totalDamageCharges, 2) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      @if($hasUnacknowledged)
        <div class="mt-3 text-end">
          <button class="btn btn-danger btn-sm" id="acknowledgeBtn" onclick="acknowledgeDamage()">
            <i class="ri ri-checkbox-circle-line me-1"></i>Acknowledge Charges
          </button>
        </div>
      @endif
    </div>
    @endif

    {{-- Food / breakfast charges --}}
    @if($serviceCharges->isNotEmpty())
    <div class="co-card">
      <div class="co-card-title">
        <i class="ri ri-restaurant-line"></i> Food &amp; Breakfast Orders
      </div>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>Item</th>
              <th class="text-center">Qty</th>
              <th class="text-end">Unit Price</th>
              <th class="text-end">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($serviceCharges as $sc)
            <tr class="co-charge-row">
              <td>{{ $sc->meal_name }}</td>
              <td class="text-center">{{ $sc->quantity }}</td>
              <td class="text-end">₱{{ number_format($sc->price_at_order, 2) }}</td>
              <td class="text-end fw-semibold">₱{{ number_format($sc->line_total, 2) }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="table-light">
            <tr>
              <td colspan="3" class="fw-semibold">Total Food Charges</td>
              <td class="text-end fw-bold">₱{{ number_format($totalServiceCharges, 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    @endif

    {{-- Request checkout CTA --}}
    @if($noRequest || ($hasIssues && ! $hasUnacknowledged))
    <div class="co-card">
      <div class="co-card-title">
        <i class="ri ri-logout-box-line"></i> Request Check-out
      </div>
      <p class="small text-body-secondary mb-3">
        When you are ready to leave, tap the button below.
        Our staff will inspect your room before finalizing your check-out.
        @if($hasIssues)
          <br><span class="text-success fw-semibold">
            <i class="ri ri-checkbox-circle-line me-1"></i>
            All charges acknowledged — you may now re-submit your checkout request.
          </span>
        @endif
      </p>
      <div class="mb-3">
        <label class="form-label small fw-semibold text-body-secondary text-uppercase" style="font-size: .7rem; letter-spacing: .06em;">
          <i class="ri ri-chat-3-line me-1"></i>Optional Note for Staff
        </label>
        <textarea id="checkoutNotes" class="co-notes" rows="2"
          placeholder="e.g. We will be leaving at 10 AM, please check early…"></textarea>
      </div>
      <button class="btn btn-primary w-100" id="requestCheckoutBtn" onclick="requestCheckout()">
        <i class="ri ri-send-plane-line me-1"></i>
        {{ $hasIssues ? 'Re-submit Checkout Request' : 'Request Check-out' }}
      </button>
    </div>
    @endif

    {{-- Cleared: front desk instruction --}}
    @if($isCleared)
    <div class="co-card" style="border-color: rgba(40,199,111,.4); background: rgba(40,199,111,.04);">
      <div class="co-card-title" style="border-color: rgba(40,199,111,.25);">
        <i class="ri ri-checkbox-circle-line text-success"></i>
        <span class="text-success">Cleared for Check-out</span>
      </div>
      <p class="small mb-2">Your room inspection has been completed successfully. Please proceed to the <strong>front desk</strong> to:</p>
      <ul class="small mb-0" style="line-height: 2;">
        <li>Settle your remaining balance of <strong>₱{{ number_format($updatedBalance, 2) }}</strong></li>
        <li>Return your room key / key card</li>
        <li>Collect your receipt</li>
      </ul>
    </div>
    @endif

  </div>

  {{-- ════════════════════ RIGHT SIDEBAR ════════════════════ --}}
  <div>

    {{-- Balance card --}}
    <div class="co-balance-card">
      <div class="d-flex align-items-center gap-2 mb-3">
        <i class="ri ri-bank-card-line" style="font-size: 1.2rem;"></i>
        <h6 class="mb-0 text-white" style="font-size: .9rem;">Account Balance</h6>
      </div>

      <p class="label-muted mb-1">Remaining Balance</p>
      <div class="balance-amt mb-1">₱{{ number_format($updatedBalance, 2) }}</div>
      <p class="label-muted mb-0" style="font-size: .75rem;">
        {{ $reservation->no_nights }} night{{ $reservation->no_nights != 1 ? 's' : '' }}
        &bull; Room {{ $reservation->room_number }}
      </p>

      <div class="divider"></div>

      <div class="co-balance-row">
        <span class="lbl">Room Charges</span>
        <span class="val">₱{{ number_format($reservation->total_amount, 2) }}</span>
      </div>
      @if($totalServiceCharges > 0)
      <div class="co-balance-row">
        <span class="lbl">Food &amp; Breakfast</span>
        <span class="val">₱{{ number_format($totalServiceCharges, 2) }}</span>
      </div>
      @endif
      @if($totalDamageCharges > 0)
      <div class="co-balance-row">
        <span class="lbl" style="color: rgba(234,84,85,.8);">Damage / Missing</span>
        <span class="val" style="color: #ea5455;">+₱{{ number_format($totalDamageCharges, 2) }}</span>
      </div>
      @endif
      <div class="co-balance-row" style="margin-top: .5rem; padding-top: .5rem; border-top: 1px solid rgba(255,255,255,.1);">
        <span class="lbl">Already Paid</span>
        <span class="val text-success">₱{{ number_format($reservation->total_amount - $reservation->balance, 2) }}</span>
      </div>
    </div>

    {{-- Checkout status card --}}
    <div class="co-card">
      <div class="co-card-title">
        <i class="ri ri-logout-box-line"></i> Checkout Status
      </div>

      @if($noRequest)
        <div class="text-center py-3">
          <i class="ri ri-logout-box-line" style="font-size: 2.5rem; opacity: .3; display: block; margin-bottom: .5rem;"></i>
          <p class="small text-body-secondary mb-0">No checkout request yet.</p>
        </div>
      @else
        @php
          $statusCfg = [
            'pending'    => ['label' => 'Awaiting Staff',  'badge' => 'bg-label-warning', 'icon' => 'ri-time-line'],
            'inspecting' => ['label' => 'Being Inspected', 'badge' => 'bg-label-info',    'icon' => 'ri-search-eye-line'],
            'cleared'    => ['label' => 'Cleared',         'badge' => 'bg-label-success', 'icon' => 'ri-checkbox-circle-line'],
            'has_issues' => ['label' => 'Issues Found',    'badge' => 'bg-label-danger',  'icon' => 'ri-alert-line'],
          ];
          $sc = $statusCfg[$status] ?? $statusCfg['pending'];
        @endphp
        <div class="text-center py-2">
          <span class="badge {{ $sc['badge'] }} px-3 py-2 mb-2" style="font-size: .8rem;">
            <i class="ri {{ $sc['icon'] }} me-1"></i>{{ $sc['label'] }}
          </span>
          <p class="small text-body-secondary mb-0 mt-1">
            Requested {{ \Carbon\Carbon::parse($checkoutRequest->created_at)->diffForHumans() }}
          </p>
        </div>
      @endif
    </div>

    {{-- Important reminders --}}
    <div class="co-card">
      <div class="co-card-title">
        <i class="ri ri-information-line"></i> Reminders
      </div>
      <ul class="small text-body-secondary mb-0" style="line-height: 2.1; padding-left: 1.2rem;">
        <li>Check-out time is <strong>12:00 PM</strong></li>
        <li>Return room key / key card at the front desk</li>
        <li>Ensure all personal belongings are packed</li>
        <li>Settle any outstanding balance before leaving</li>
        <li>Damage charges must be acknowledged before checkout is approved</li>
      </ul>
    </div>

  </div>

</div>

<div id="co-toast-wrap"></div>

@endsection

@section('page-script')
<script>
  const CSRF               = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const REQUEST_URL        = '{{ route("guest.checkout.request") }}';
  const ACKNOWLEDGE_URL    = '{{ route("guest.checkout.acknowledge") }}';
  const STATUS_URL         = '{{ route("guest.checkout.status") }}';

  // ── Toast helper ──────────────────────────────────────────────
  const _icons = {
    success: 'ri-checkbox-circle-line',
    error:   'ri-error-warning-line',
    warning: 'ri-alert-line',
    info:    'ri-information-line',
  };
  function coToast(type, msg) {
    const wrap = document.getElementById('co-toast-wrap');
    const el   = document.createElement('div');
    el.className = 'co-toast ' + type;
    el.innerHTML = `<i class="ri ${_icons[type]}"></i><span>${msg}</span>`;
    wrap.appendChild(el);
    setTimeout(() => {
      el.classList.add('hide');
      setTimeout(() => el.remove(), 220);
    }, 3600);
  }

  // ── Request checkout ──────────────────────────────────────────
  async function requestCheckout() {
    const btn   = document.getElementById('requestCheckoutBtn');
    const notes = (document.getElementById('checkoutNotes')?.value ?? '').trim();

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting…';

    try {
      const res  = await fetch(REQUEST_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body:    JSON.stringify({ notes }),
      });
      const data = await res.json();
      if (data.success) {
        coToast('success', data.message);
        setTimeout(() => location.reload(), 1800);
      } else {
        coToast('error', data.error ?? 'Something went wrong.');
        btn.disabled = false;
        btn.innerHTML = '<i class="ri ri-send-plane-line me-1"></i>Request Check-out';
      }
    } catch (err) {
      console.error(err);
      coToast('error', 'Request failed. Please check your connection.');
      btn.disabled = false;
      btn.innerHTML = '<i class="ri ri-send-plane-line me-1"></i>Request Check-out';
    }
  }

  // ── Acknowledge damage charges ────────────────────────────────
  async function acknowledgeDamage() {
    const btn = document.getElementById('acknowledgeBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…';

    try {
      const res  = await fetch(ACKNOWLEDGE_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body:    JSON.stringify({}),
      });
      const data = await res.json();
      if (data.success) {
        coToast('success', data.message);
        setTimeout(() => location.reload(), 1800);
      } else {
        coToast('error', data.error ?? 'Something went wrong.');
        btn.disabled = false;
        btn.innerHTML = '<i class="ri ri-checkbox-circle-line me-1"></i>Acknowledge Charges';
      }
    } catch (err) {
      console.error(err);
      coToast('error', 'Request failed. Please try again.');
      btn.disabled = false;
      btn.innerHTML = '<i class="ri ri-checkbox-circle-line me-1"></i>Acknowledge Charges';
    }
  }

  // ── Auto-poll while pending or inspecting ─────────────────────
  @if(in_array($status ?? '', ['pending', 'inspecting']))
  (function poll() {
    setTimeout(async () => {
      try {
        const res  = await fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (! data.success) return poll();

        const newStatus = data.checkout_request?.status ?? null;

        if (newStatus === 'inspecting' && '{{ $status }}' === 'pending') {
          coToast('info', 'Staff has started inspecting your room.');
          setTimeout(() => location.reload(), 1200);
          return;
        }
        if (newStatus === 'cleared') {
          coToast('success', 'Room cleared! You are good to check out.');
          setTimeout(() => location.reload(), 1200);
          return;
        }
        if (newStatus === 'has_issues') {
          coToast('warning', 'Issues found during inspection. Please review the charges.');
          setTimeout(() => location.reload(), 1200);
          return;
        }
        if (data.has_new_charges) {
          coToast('warning', 'New charges have been added to your account.');
          setTimeout(() => location.reload(), 1200);
          return;
        }
      } catch (_) {}
      poll();
    }, 8000); // poll every 8 seconds
  })();
  @endif
</script>
@endsection