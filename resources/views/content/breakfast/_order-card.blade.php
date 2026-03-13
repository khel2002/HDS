{{--
  Partial: content/guest/breakfast/_order-card.blade.php
  Variables: $order (array from BreakfastController::formatOrder())
--}}
@php
  $statusMap = [
    'pending'    => ['cls' => 'pending',    'icon' => 'ri-time-line',         'label' => 'Pending'],
    'processing' => ['cls' => 'processing', 'icon' => 'ri-loader-4-line',     'label' => 'Processing'],
    'completed'  => ['cls' => 'completed',  'icon' => 'ri-check-double-line', 'label' => 'Completed'],
    'cancelled'  => ['cls' => 'cancelled',  'icon' => 'ri-close-circle-line', 'label' => 'Cancelled'],
  ];
  $s = $statusMap[$order['status']] ?? ['cls'=>'pending','icon'=>'ri-question-line','label'=>ucfirst($order['status'])];
@endphp

<div class="mo-card">
  <div class="mo-card-head">
    <div>
      <div class="mo-order-id">Order <strong>#{{ $order['service_request_id'] }}</strong></div>
      <div class="mo-order-time"><i class="ri-time-line me-1"></i>{{ $order['requested_at'] ?? '—' }}</div>
    </div>
    <span class="mo-badge {{ $s['cls'] }}">
      <i class="{{ $s['icon'] }}"></i>{{ $s['label'] }}
    </span>
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
      <tbody>
        @foreach($order['items'] as $item)
          <tr>
            <td>{{ $item['name'] ?? '—' }}</td>
            <td class="text-end">{{ $item['qty'] }}</td>
            <td class="text-end">₱{{ number_format($item['price'], 2) }}</td>
            <td class="text-end">₱{{ number_format($item['subtotal'], 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="mo-total-row">
      <span class="mo-total-label">Total</span>
      <span class="mo-total-val">₱{{ number_format($order['total'], 2) }}</span>
    </div>
  </div>
</div>