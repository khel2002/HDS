{{-- Room Card Component --}}
@php
  $room_id = $room_id ?? null;
  $title = $title ?? ($room_type_name ?? 'Room Title');
  $description = $description ?? 'Spacious room with modern comfort and amenities.';
  $image = $image ?? 'assets/img/frontpages/img/test.jpg';
  $price = $price ?? null;
  $rating = $rating ?? null;
  $size = $size ?? null;
  $bed = $bed ?? null;
  $capacity = $capacity ?? null;
  $tags = $tags ?? [];
@endphp

<style>
  /* ==================== ROOM CARD STYLES ==================== */
  .room-card-wrapper {
    position: relative;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
  }

  .room-card-wrapper:hover {
    transform: translateY(-8px);
  }

  .room-card-wrapper .card-body {
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 0;
  }

  .room-card-image-container {
    position: relative;
    overflow: hidden;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(10, 36, 99, 0.15);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
  }

  .room-card-wrapper:hover .room-card-image-container {
    box-shadow: 0 12px 40px rgba(36, 123, 160, 0.3);
  }

  .room-card-image-container::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg,
        transparent 0%,
        rgba(0, 18, 51, 0.3) 50%,
        rgba(10, 36, 99, 0.85) 100%);
    z-index: 1;
    transition: opacity 0.4s;
  }

  .room-card-wrapper:hover .room-card-image-container::before {
    background: linear-gradient(180deg,
        transparent 0%,
        rgba(0, 18, 51, 0.4) 50%,
        rgba(10, 36, 99, 0.9) 100%);
  }

  .room-card-image-container::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg,
        rgba(36, 123, 160, 0.1) 0%,
        transparent 50%,
        rgba(56, 145, 166, 0.1) 100%);
    opacity: 0;
    z-index: 2;
    transition: opacity 0.4s;
  }

  .room-card-wrapper:hover .room-card-image-container::after {
    opacity: 1;
  }

  .room-card-img {
    height: 280px;
    object-fit: cover;
    border-radius: 1rem;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    display: block;
    width: 100%;
  }

  .room-card-wrapper:hover .room-card-img {
    transform: scale(1.08);
  }

  .room-card-title {
    position: absolute;
    bottom: 1.5rem;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.25rem, 3vw, 1.75rem);
    font-weight: 700;
    text-align: center;
    width: 90%;
    z-index: 3;
    margin: 0;
    text-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    letter-spacing: 0.5px;
    transition: all 0.3s;
    line-height: 1.3;
  }

  .room-card-wrapper:hover .room-card-title {
    transform: translateX(-50%) translateY(-4px);
    text-shadow:
      0 6px 16px rgba(0, 0, 0, 0.6),
      0 0 20px rgba(56, 145, 166, 0.4);
  }

  .room-card-description {
    color: #64748b;
    font-size: clamp(0.9375rem, 1.5vw, 1rem);
    line-height: 1.6;
    margin-top: 1.5rem;
    text-align: center;
    min-height: 3rem;
    transition: color 0.3s;
    padding: 0 0.5rem;
  }

  .room-card-wrapper:hover .room-card-description {
    color: #475569;
  }

  .room-card-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, rgba(36, 123, 160, 0.95), rgba(56, 145, 166, 0.95));
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 12px rgba(36, 123, 160, 0.3);
    z-index: 3;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .room-card-wrapper:hover .room-card-badge {
    opacity: 1;
    transform: translateY(0);
  }

  #roomsCarouselDesktop .carousel-inner {
    min-height: 480px;
  }

  #roomsCarouselDesktop .carousel-item {
    min-height: 480px;
  }

  #roomsCarouselMobile .carousel-inner {
    min-height: 520px;
  }

  #roomsCarouselMobile .carousel-item {
    min-height: 520px;
  }

  .carousel-item .row {
    min-height: 100%;
    display: flex;
    align-items: stretch;
  }

  .carousel-item [class*="col-"] {
    display: flex;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .room-card-wrapper {
    animation: fadeInUp 0.6s ease-out backwards;
  }

  .carousel-item .room-card-wrapper:nth-child(1) { animation-delay: 0.1s; }
  .carousel-item .room-card-wrapper:nth-child(2) { animation-delay: 0.2s; }
  .carousel-item .room-card-wrapper:nth-child(3) { animation-delay: 0.3s; }

  @media (min-width: 1400px) {
    .room-card-img { height: 300px; }
    #roomsCarouselDesktop .carousel-inner,
    #roomsCarouselDesktop .carousel-item { min-height: 500px; }
  }

  @media (min-width: 1200px) and (max-width: 1399.98px) {
    .room-card-img { height: 280px; }
  }

  @media (max-width: 1199.98px) {
    .room-card-img { height: 260px; }
    #roomsCarouselDesktop .carousel-inner,
    #roomsCarouselDesktop .carousel-item { min-height: 460px; }
  }

  @media (max-width: 991.98px) {
    .room-card-img { height: 240px; }
    .room-card-title { font-size: 1.375rem; bottom: 1.25rem; }
    .room-card-description { margin-top: 1.25rem; }
    #roomsCarouselMobile .carousel-inner,
    #roomsCarouselMobile .carousel-item { min-height: 480px; }
  }

  @media (max-width: 767.98px) {
    .room-card-img { height: 260px; }
    .room-card-title { font-size: 1.25rem; width: 85%; }
    .room-card-description { font-size: 0.9375rem; margin-top: 1rem; }
    #roomsCarouselMobile .carousel-inner,
    #roomsCarouselMobile .carousel-item { min-height: 500px; }
  }

  @media (max-width: 575.98px) {
    .room-card-img { height: 240px; }
    .room-card-title { font-size: 1.125rem; width: 80%; bottom: 1rem; }
    .room-card-description { font-size: 0.875rem; margin-top: 1rem; min-height: 2.5rem; }
    #roomsCarouselMobile .carousel-inner,
    #roomsCarouselMobile .carousel-item { min-height: 480px; }
    .room-card-badge { font-size: 0.6875rem; padding: 0.375rem 0.75rem; }
  }

  @media (max-width: 374.98px) {
    .room-card-img { height: 220px; }
    .room-card-title { font-size: 1rem; }
    .room-card-description { font-size: 0.8125rem; }
    #roomsCarouselMobile .carousel-inner,
    #roomsCarouselMobile .carousel-item { min-height: 460px; }
  }

  @media (prefers-reduced-motion: reduce) {
    .room-card-wrapper,
    .room-card-img,
    .room-card-title,
    .room-card-badge {
      animation: none !important;
      transition: none !important;
    }
    .room-card-wrapper:hover { transform: none; }
    .room-card-wrapper:hover .room-card-img { transform: none; }
  }

  @media print {
    .room-card-badge { opacity: 1 !important; }
    .room-card-image-container::before,
    .room-card-image-container::after { display: none; }
  }
</style>

<a href="{{ $room_id ? route('frontpage.room-details', ['room_id' => $room_id]) : '#' }}" class="text-decoration-none" style="display:flex; flex-direction:column; height:100%;">
  <div class="room-card" style="flex:1;">
    <div class="room-card-img-wrap">
      <img src="{{ asset('storage/' . $image) }}" alt="{{ $title }}" loading="lazy">
      @if($rating)
      <div class="room-rating">
        <i class="ri-star-fill"></i> {{ $rating }}
      </div>
      @endif
    </div>
    <div class="room-card-body">
      <h3>{{ $title }}</h3>
      <div class="room-meta">
        @if($size)<span>{{ $size }}</span>@endif
        @if($bed)<span>{{ $bed }}</span>@endif
        @if($capacity)<span>{{ $capacity }}</span>@endif
      </div>
      @if(count($tags))
      <div class="room-tags">
        @foreach($tags as $tag)
          <span class="room-tag">{{ $tag }}</span>
        @endforeach
      </div>
      @else
      <p style="font-size:0.88rem;color:#6b7280;margin-bottom:1rem;line-height:1.5;">{{ Str::limit($description, 80) }}</p>
      @endif
      <div class="room-footer">
        <div class="room-price">
          @if($price)
            <span class="amount">${{ $price }}</span>
            <span class="per"> / night</span>
          @endif
        </div>
        <span class="btn-book">Book Now</span>
      </div>
    </div>
  </div>
</a>