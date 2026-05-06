@extends('layouts/contentNavbarLayout')
@section('title', 'Walk-in Reservation')

@section('content')
<div class="row gy-4">

  {{-- ── PAGE HEADER ──────────────────────────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <h4 class="mb-1">
              <i class="icon-base ri ri-walk-line me-2 text-primary"></i>Walk-in Reservation
            </h4>
            <p class="mb-0 text-muted">
              Register a walk-in guest directly at the front desk. All fields marked <span class="text-danger">*</span> are required.
            </p>
          </div>
          <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
            <i class="icon-base ri ri-arrow-left-line me-1"></i> Back to Reservations
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- ── GLOBAL VALIDATION ALERT ─────────────────────────────────────────── --}}
  @if ($errors->any())
    <div class="col-12">
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="icon-base ri ri-error-warning-line ri-lg"></i>
          <div>
            <strong>Please fix the following errors before submitting:</strong>
            <ul class="mb-0 mt-1 ps-3">
              {{-- @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach --}}
            </ul>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
  @endif

  {{-- ── MAIN FORM ────────────────────────────────────────────────────────── --}}
  <div class="col-12">
    <form action="{{ route('admin.reservations.walkin.store') }}" method="POST" enctype="multipart/form-data" novalidate>
      @csrf

      <div class="row gy-4">

        {{-- ╔══════════════════════════════════════╗ --}}
        {{-- ║   SECTION 1 — LEAD GUEST INFORMATION ║ --}}
        {{-- ╚══════════════════════════════════════╝ --}}
        <div class="col-12">
          <div class="card">
            <div class="card-header border-bottom">
              <h5 class="card-title m-0">
                <i class="icon-base ri ri-user-3-line me-2 text-primary"></i>Lead Guest Information
              </h5>
              <p class="card-subtitle mt-1 mb-0">Primary guest details and valid government ID.</p>
            </div>
            <div class="card-body pt-4">
              <div class="row g-4">

                {{-- First Name --}}
                <div class="col-md-4 col-sm-6">
                  <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    class="form-control @error('first_name') is-invalid @enderror"
                    value="{{ old('first_name') }}"
                    placeholder="e.g. Juan"
                    autocomplete="given-name"
                  >
                  @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Middle Name --}}
                <div class="col-md-4 col-sm-6">
                  <label class="form-label" for="middle_name">Middle Name</label>
                  <input
                    type="text"
                    id="middle_name"
                    name="middle_name"
                    class="form-control @error('middle_name') is-invalid @enderror"
                    value="{{ old('middle_name') }}"
                    placeholder="e.g. Santos"
                    autocomplete="additional-name"
                  >
                  @error('middle_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Last Name --}}
                <div class="col-md-4 col-sm-6">
                  <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    class="form-control @error('last_name') is-invalid @enderror"
                    value="{{ old('last_name') }}"
                    placeholder="e.g. Dela Cruz"
                    autocomplete="family-name"
                  >
                  @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Email Address --}}
                <div class="col-md-6 col-sm-6">
                  <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="icon-base ri ri-mail-line"></i></span>
                    <input
                      type="email"
                      id="email"
                      name="email"
                      class="form-control @error('email') is-invalid @enderror"
                      value="{{ old('email') }}"
                      placeholder="guest@email.com"
                      autocomplete="email"
                    >
                    @error('email')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="form-text">A temporary account will be created using this email.</div>
                </div>

                {{-- Phone Number --}}
                <div class="col-md-6 col-sm-6">
                  <label class="form-label" for="phone">Phone Number <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="icon-base ri ri-phone-line"></i></span>
                    <input
                      type="text"
                      id="phone"
                      name="phone"
                      class="form-control @error('phone') is-invalid @enderror"
                      value="{{ old('phone') }}"
                      placeholder="09xxxxxxxxx"
                      autocomplete="tel"
                    >
                    @error('phone')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                {{-- ID Type --}}
                <div class="col-md-4 col-sm-6">
                  <label class="form-label" for="id_type">ID Type <span class="text-danger">*</span></label>
                  <select
                    id="id_type"
                    name="id_type"
                    class="form-select @error('id_type') is-invalid @enderror"
                  >
                    <option value="" disabled {{ old('id_type') ? '' : 'selected' }}>Select ID type…</option>
                    @foreach ([
                      'Philippine Passport',
                      "Driver's License",
                      'SSS ID',
                      'GSIS ID',
                      'PhilHealth ID',
                      "Voter's ID",
                      'National ID (PhilSys)',
                      'PRC ID',
                      'Postal ID',
                      'Other Government ID',
                    ] as $idType)
                      <option value="{{ $idType }}" {{ old('id_type') === $idType ? 'selected' : '' }}>
                        {{ $idType }}
                      </option>
                    @endforeach
                  </select>
                  @error('id_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- ID Number --}}
                <div class="col-md-4 col-sm-6">
                  <label class="form-label" for="id_number">ID Number <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    id="id_number"
                    name="id_number"
                    class="form-control @error('id_number') is-invalid @enderror"
                    value="{{ old('id_number') }}"
                    placeholder="e.g. 1234-5678-9012"
                  >
                  @error('id_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Date of Birth --}}
                <div class="col-md-4 col-sm-6">
                  <label class="form-label" for="dob">Date of Birth</label>
                  <input
                    type="date"
                    id="dob"
                    name="dob"
                    class="form-control @error('dob') is-invalid @enderror"
                    value="{{ old('dob') }}"
                    max="{{ date('Y-m-d') }}"
                  >
                  @error('dob')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

              </div>{{-- /row --}}
            </div>{{-- /card-body --}}
          </div>{{-- /card --}}
        </div>

        {{-- ╔════════════════════════════════════╗ --}}
        {{-- ║   SECTION 2 — ROOM & STAY DETAILS  ║ --}}
        {{-- ╚════════════════════════════════════╝ --}}
        <div class="col-12">
          <div class="card">
            <div class="card-header border-bottom">
              <h5 class="card-title m-0">
                <i class="icon-base ri ri-hotel-bed-line me-2 text-primary"></i>Room & Stay Details
              </h5>
              <p class="card-subtitle mt-1 mb-0">Select the room and specify the guest's stay period.</p>
            </div>
            <div class="card-body pt-4">
              <div class="row g-4">

                {{-- Room Selection --}}
                <div class="col-md-6">
                  <label class="form-label" for="room_id">Room <span class="text-danger">*</span></label>
                  <select
                    id="room_id"
                    name="room_id"
                    class="form-select @error('room_id') is-invalid @enderror"
                  >
                    <option value="" disabled {{ old('room_id') ? '' : 'selected' }}>Select available room…</option>
                    @foreach ($availableRooms as $room)
                      <option
                        value="{{ $room->room_id }}"
                        data-rate="{{ $room->rate_per_night }}"
                        data-type="{{ $room->room_type_name }}"
                        data-pax="{{ $room->max_pax }}"
                        {{ old('room_id') == $room->room_id ? 'selected' : '' }}
                      >
                        Room {{ $room->room_number }} — {{ $room->room_type_name }} (₱{{ number_format($room->rate_per_night, 2) }}/night)
                      </option>
                    @endforeach
                  </select>
                  @error('room_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Number of Guests (Pax) --}}
                <div class="col-md-3 col-sm-6">
                  <label class="form-label" for="pax">Number of Guests <span class="text-danger">*</span></label>
                  <input
                    type="number"
                    id="pax"
                    name="pax"
                    class="form-control @error('pax') is-invalid @enderror"
                    value="{{ old('pax', 1) }}"
                    min="1"
                    max="10"
                  >
                  @error('pax')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text" id="paxHint">Max capacity will appear after selecting a room.</div>
                </div>

                {{-- Room info badge --}}
                <div class="col-md-3 col-sm-6 d-flex align-items-end">
                  <div id="roomInfoBadge" class="d-none w-100">
                    <div class="alert alert-primary mb-0 py-2 px-3">
                      <div class="d-flex gap-2 align-items-center flex-wrap">
                        <span id="badgeType" class="badge bg-primary"></span>
                        <small class="fw-semibold" id="badgePax"></small>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Check-in Date --}}
                <div class="col-md-4 col-sm-6">
                  <label class="form-label" for="check_in">Check-in Date <span class="text-danger">*</span></label>
                  <input
                    type="date"
                    id="check_in"
                    name="check_in"
                    class="form-control @error('check_in') is-invalid @enderror"
                    value="{{ old('check_in', date('Y-m-d')) }}"
                    min="{{ date('Y-m-d') }}"
                  >
                  @error('check_in')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Check-out Date --}}
                <div class="col-md-4 col-sm-6">
                  <label class="form-label" for="check_out">Check-out Date <span class="text-danger">*</span></label>
                  <input
                    type="date"
                    id="check_out"
                    name="check_out"
                    class="form-control @error('check_out') is-invalid @enderror"
                    value="{{ old('check_out') }}"
                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                  >
                  @error('check_out')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Stay Summary --}}
                <div class="col-md-4 col-sm-12 d-flex align-items-end">
                  <div id="staySummary" class="w-100 d-none">
                    <div class="alert alert-success mb-0 py-2 px-3">
                      <div class="small fw-semibold text-uppercase text-success mb-1">Stay Summary</div>
                      <div class="d-flex justify-content-between">
                        <span class="text-muted">Nights:</span>
                        <strong id="summaryNights">—</strong>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-muted">Total:</span>
                        <strong class="text-success" id="summaryTotal">—</strong>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Special Requests --}}
                <div class="col-12">
                  <label class="form-label" for="special_requests">Special Requests / Notes</label>
                  <textarea
                    id="special_requests"
                    name="special_requests"
                    class="form-control @error('special_requests') is-invalid @enderror"
                    rows="3"
                    placeholder="e.g. Early check-in, extra pillows, accessibility needs, anniversary setup…"
                  >{{ old('special_requests') }}</textarea>
                  @error('special_requests')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

              </div>{{-- /row --}}
            </div>{{-- /card-body --}}
          </div>{{-- /card --}}
        </div>

        {{-- ╔══════════════════════════╗ --}}
        {{-- ║   SECTION 3 — PAYMENT   ║ --}}
        {{-- ╚══════════════════════════╝ --}}
        <div class="col-lg-8">
          <div class="card h-100">
            <div class="card-header border-bottom">
              <h5 class="card-title m-0">
                <i class="icon-base ri ri-secure-payment-line me-2 text-primary"></i>Payment Details
              </h5>
              <p class="card-subtitle mt-1 mb-0">Collect at least the 30% reservation fee upon walk-in.</p>
            </div>
            <div class="card-body pt-4">
              <div class="row g-4">

                {{-- Payment Method --}}
                <div class="col-sm-6">
                  <label class="form-label" for="payment_method">Payment Method <span class="text-danger">*</span></label>
                  <select
                    id="payment_method"
                    name="payment_method"
                    class="form-select @error('payment_method') is-invalid @enderror"
                  >
                    @foreach (['Cash', 'GCash', 'Maya', 'Bank Transfer', 'Credit Card'] as $method)
                      <option value="{{ $method }}" {{ old('payment_method', 'Cash') === $method ? 'selected' : '' }}>
                        {{ $method }}
                      </option>
                    @endforeach
                  </select>
                  @error('payment_method')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Amount Paid --}}
                <div class="col-sm-6">
                  <label class="form-label" for="amount_paid">
                    Amount Paid (₱) <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input
                      type="number"
                      id="amount_paid"
                      name="amount_paid"
                      class="form-control @error('amount_paid') is-invalid @enderror"
                      value="{{ old('amount_paid') }}"
                      min="0"
                      step="0.01"
                      placeholder="0.00"
                    >
                    @error('amount_paid')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="form-text" id="minFeeHint">Minimum: 30% reservation fee.</div>
                </div>

                {{-- Proof of Payment --}}
                <div class="col-12" id="proofWrap">
                  <label class="form-label" for="payment_proof">
                    Proof of Payment
                    <span class="text-muted fw-normal">(required for non-cash)</span>
                  </label>
                  <input
                    type="file"
                    id="payment_proof"
                    name="payment_proof"
                    class="form-control @error('payment_proof') is-invalid @enderror"
                    accept=".jpg,.jpeg,.png,.pdf"
                  >
                  @error('payment_proof')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text">Accepted: JPG, PNG, PDF — max 5 MB.</div>
                </div>

              </div>{{-- /row --}}
            </div>{{-- /card-body --}}
          </div>{{-- /card --}}
        </div>

        {{-- ╔═════════════════════════════╗ --}}
        {{-- ║   SECTION 4 — BILLING BOX  ║ --}}
        {{-- ╚═════════════════════════════╝ --}}
        <div class="col-lg-4">
          <div class="card h-100">
            <div class="card-header border-bottom">
              <h5 class="card-title m-0">
                <i class="icon-base ri ri-bill-line me-2 text-primary"></i>Billing Summary
              </h5>
            </div>
            <div class="card-body pt-4">
              <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item d-flex justify-content-between px-0">
                  <span class="text-muted">Room</span>
                  <span id="billingRoom" class="fw-semibold">—</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                  <span class="text-muted">Rate / Night</span>
                  <span id="billingRate" class="fw-semibold">—</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                  <span class="text-muted">Nights</span>
                  <span id="billingNights" class="fw-semibold">—</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0 border-top pt-3">
                  <span class="fw-bold">Total Amount</span>
                  <span id="billingTotal" class="fw-bold text-primary fs-6">₱0.00</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                  <span class="text-muted">Reservation Fee (30%)</span>
                  <span id="billingFee" class="fw-semibold text-success">₱0.00</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                  <span class="text-muted">Balance Due</span>
                  <span id="billingBalance" class="fw-bold text-warning">₱0.00</span>
                </li>
              </ul>

              {{-- Hidden inputs to pass computed values --}}
              <input type="hidden" name="total_amount"    id="hiddenTotal">
              <input type="hidden" name="reservation_fee" id="hiddenFee">
              <input type="hidden" name="balance"         id="hiddenBalance">
              <input type="hidden" name="no_nights"       id="hiddenNights">

              <div class="alert alert-info mb-0 py-2 px-3">
                <small>
                  <i class="icon-base ri ri-information-line me-1"></i>
                  Check-in: <strong>2:00 PM</strong> &nbsp;|&nbsp; Check-out: <strong>12:00 PM</strong>
                </small>
              </div>
            </div>{{-- /card-body --}}
          </div>{{-- /card --}}
        </div>

        {{-- ╔═════════════════════════════════════╗ --}}
        {{-- ║   SECTION 5 — ADDITIONAL GUESTS     ║ --}}
        {{-- ╚═════════════════════════════════════╝ --}}
        <div class="col-12">
          <div class="card">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
              <div>
                <h5 class="card-title m-0">
                  <i class="icon-base ri ri-group-line me-2 text-primary"></i>Additional Guests
                  <span class="badge bg-label-secondary ms-2 fw-normal fs-6" id="guestCountBadge">0 added</span>
                </h5>
                <p class="card-subtitle mt-1 mb-0">Optional — add accompanying guests for the record.</p>
              </div>
              <button type="button" class="btn btn-outline-primary btn-sm" id="addGuestBtn">
                <i class="icon-base ri ri-add-line me-1"></i> Add Guest
              </button>
            </div>
            <div class="card-body pt-3">
              <div id="additionalGuestsContainer">
                <p class="text-muted text-center py-3 mb-0" id="noGuestsMsg">
                  <i class="icon-base ri ri-user-add-line me-1"></i>
                  No additional guests added yet.
                </p>
              </div>
            </div>
          </div>
        </div>

        {{-- ╔═════════════════════════════╗ --}}
        {{-- ║   FORM ACTIONS              ║ --}}
        {{-- ╚═════════════════════════════╝ --}}
        <div class="col-12">
          <div class="card">
            <div class="card-body py-3">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="text-muted small">
                  <i class="icon-base ri ri-information-line me-1"></i>
                  A temporary account will be automatically created for the guest using the provided email address.
                </div>
                <div class="d-flex gap-2">
                  <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
                    <i class="icon-base ri ri-close-line me-1"></i> Cancel
                  </a>
                  <button type="reset" class="btn btn-outline-warning" onclick="return confirm('Clear all form data?')">
                    <i class="icon-base ri ri-refresh-line me-1"></i> Reset
                  </button>
                  <button type="submit" class="btn btn-primary">
                    <i class="icon-base ri ri-user-add-line me-1"></i> Register Walk-in Guest
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>{{-- /row (main form) --}}
    </form>
  </div>

</div>{{-- /row (page) --}}


{{-- ─────────────────────────────────────────────────────────────── --}}
{{-- ADDITIONAL GUEST ROW TEMPLATE (hidden, cloned by JS)           --}}
{{-- ─────────────────────────────────────────────────────────────── --}}
<template id="guestRowTemplate">
  <div class="guest-row border rounded-3 p-3 mb-3 position-relative">
    <button type="button" class="btn btn-sm btn-icon btn-outline-danger position-absolute top-0 end-0 mt-2 me-2 remove-guest-btn" title="Remove guest">
      <i class="icon-base ri ri-close-line"></i>
    </button>
    <div class="row g-3 align-items-end">
      <div class="col-md-3 col-sm-6">
        <label class="form-label">First Name</label>
        <input type="text" name="guests[__INDEX__][first_name]" class="form-control" placeholder="First name">
      </div>
      <div class="col-md-3 col-sm-6">
        <label class="form-label">Last Name</label>
        <input type="text" name="guests[__INDEX__][last_name]" class="form-control" placeholder="Last name">
      </div>
      <div class="col-md-3 col-sm-6">
        <label class="form-label">Contact Number</label>
        <input type="text" name="guests[__INDEX__][contact_number]" class="form-control" placeholder="09xxxxxxxxx">
      </div>
      <div class="col-md-3 col-sm-6">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="guests[__INDEX__][dob]" class="form-control" max="{{ date('Y-m-d') }}">
      </div>
    </div>
  </div>
</template>


{{-- ─────────────────────────────────────────────────────────────── --}}
{{-- JAVASCRIPT                                                      --}}
{{-- ─────────────────────────────────────────────────────────────── --}}
@push('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {

  // ── Element refs ──────────────────────────────────────────────────────
  const roomSelect      = document.getElementById('room_id');
  const checkInInput    = document.getElementById('check_in');
  const checkOutInput   = document.getElementById('check_out');
  const amountInput     = document.getElementById('amount_paid');
  const paymentSelect   = document.getElementById('payment_method');
  const proofWrap       = document.getElementById('proofWrap');

  // Billing display
  const billingRoom     = document.getElementById('billingRoom');
  const billingRate     = document.getElementById('billingRate');
  const billingNights   = document.getElementById('billingNights');
  const billingTotal    = document.getElementById('billingTotal');
  const billingFee      = document.getElementById('billingFee');
  const billingBalance  = document.getElementById('billingBalance');

  // Hidden inputs
  const hiddenTotal     = document.getElementById('hiddenTotal');
  const hiddenFee       = document.getElementById('hiddenFee');
  const hiddenBalance   = document.getElementById('hiddenBalance');
  const hiddenNights    = document.getElementById('hiddenNights');

  // Stay summary
  const staySummary     = document.getElementById('staySummary');
  const summaryNights   = document.getElementById('summaryNights');
  const summaryTotal    = document.getElementById('summaryTotal');

  // Room badge
  const roomInfoBadge   = document.getElementById('roomInfoBadge');
  const badgeType       = document.getElementById('badgeType');
  const badgePax        = document.getElementById('badgePax');
  const paxHint         = document.getElementById('paxHint');
  const minFeeHint      = document.getElementById('minFeeHint');

  // ── Helpers ───────────────────────────────────────────────────────────
  function formatPeso(n) {
    return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function calcNights(cin, cout) {
    if (!cin || !cout) return 0;
    const ms = new Date(cout) - new Date(cin);
    const d  = ms / (1000 * 60 * 60 * 24);
    return d > 0 ? d : 0;
  }

  function getSelectedRoom() {
    const opt = roomSelect.options[roomSelect.selectedIndex];
    if (!opt || !opt.value) return null;
    return {
      id:    opt.value,
      label: opt.text,
      rate:  parseFloat(opt.dataset.rate) || 0,
      type:  opt.dataset.type  || '',
      pax:   parseInt(opt.dataset.pax)   || 0,
    };
  }

  // ── Core compute & update ─────────────────────────────────────────────
  function recompute() {
    const room   = getSelectedRoom();
    const nights = calcNights(checkInInput.value, checkOutInput.value);
    const rate   = room ? room.rate : 0;
    const total  = rate * nights;
    const fee    = Math.ceil(total * 0.30);
    const bal    = total - fee;

    // Room badge
    if (room) {
      roomInfoBadge.classList.remove('d-none');
      badgeType.textContent = room.type;
      badgePax.textContent  = 'Max ' + room.pax + ' guest' + (room.pax !== 1 ? 's' : '');
      paxHint.textContent   = 'Max capacity for selected room: ' + room.pax + ' guest' + (room.pax !== 1 ? 's' : '');
    } else {
      roomInfoBadge.classList.add('d-none');
      paxHint.textContent = 'Max capacity will appear after selecting a room.';
    }

    // Billing summary card
    billingRoom.textContent   = room ? 'Rm ' + room.id : '—';
    billingRate.textContent   = room ? formatPeso(rate) + ' / night' : '—';
    billingNights.textContent = nights > 0 ? nights + ' night' + (nights !== 1 ? 's' : '') : '—';
    billingTotal.textContent  = total > 0 ? formatPeso(total) : '₱0.00';
    billingFee.textContent    = fee   > 0 ? formatPeso(fee)   : '₱0.00';
    billingBalance.textContent= bal   > 0 ? formatPeso(bal)   : '₱0.00';

    // Hidden inputs
    hiddenTotal.value   = total;
    hiddenFee.value     = fee;
    hiddenBalance.value = bal;
    hiddenNights.value  = nights;

    // Stay summary badge (beside check-out)
    if (nights > 0 && room) {
      staySummary.classList.remove('d-none');
      summaryNights.textContent = nights + ' night' + (nights !== 1 ? 's' : '');
      summaryTotal.textContent  = formatPeso(total);
    } else {
      staySummary.classList.add('d-none');
    }

    // Min fee hint
    if (fee > 0) {
      minFeeHint.textContent = 'Minimum: ' + formatPeso(fee) + ' (30% reservation fee). Full amount: ' + formatPeso(total) + '.';
      amountInput.min = fee;
    } else {
      minFeeHint.textContent = 'Minimum: 30% reservation fee.';
      amountInput.removeAttribute('min');
    }

    // Ensure check-out min is day after check-in
    if (checkInInput.value) {
      const nextDay = new Date(checkInInput.value);
      nextDay.setDate(nextDay.getDate() + 1);
      checkOutInput.min = nextDay.toISOString().split('T')[0];
    }
  }

  // ── Proof of payment visibility ───────────────────────────────────────
  function toggleProof() {
    const isCash = paymentSelect.value === 'Cash';
    proofWrap.style.display = isCash ? 'none' : '';
    if (isCash) document.getElementById('payment_proof').value = '';
  }

  // ── Events ────────────────────────────────────────────────────────────
  roomSelect.addEventListener('change', recompute);
  checkInInput.addEventListener('change', recompute);
  checkOutInput.addEventListener('change', recompute);
  paymentSelect.addEventListener('change', toggleProof);

  // ── Additional guests ─────────────────────────────────────────────────
  let guestIndex = 0;
  const container    = document.getElementById('additionalGuestsContainer');
  const noGuestsMsg  = document.getElementById('noGuestsMsg');
  const guestBadge   = document.getElementById('guestCountBadge');
  const template     = document.getElementById('guestRowTemplate');

  function updateGuestBadge() {
    const count = container.querySelectorAll('.guest-row').length;
    guestBadge.textContent = count + (count === 1 ? ' added' : ' added');
    noGuestsMsg.style.display = count === 0 ? '' : 'none';
  }

  document.getElementById('addGuestBtn').addEventListener('click', function () {
    const html   = template.innerHTML.replaceAll('__INDEX__', guestIndex++);
    const div    = document.createElement('div');
    div.innerHTML = html;
    const row    = div.firstElementChild;
    container.appendChild(row);
    noGuestsMsg.style.display = 'none';
    updateGuestBadge();

    row.querySelector('.remove-guest-btn').addEventListener('click', function () {
      row.remove();
      updateGuestBadge();
    });
  });

  // ── Init ──────────────────────────────────────────────────────────────
  recompute();
  toggleProof();

});
</script>
@endpush

@endsection