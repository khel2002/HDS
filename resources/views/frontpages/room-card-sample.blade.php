{{-- Room Card Component --}}
@php
  $title = $title ?? 'Room Title';
  $description = $description ?? 'Spacious room with modern comfort and amenities.';
  $image = $image ?? 'assets/img/frontpages/img/test.jpg';
@endphp

<style>
  .room-card-wrapper {
    position: relative;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .room-card-wrapper:hover {
    transform: translateY(-8px);
  }

  .room-card-image-container {
    position: relative;
    overflow: hidden;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(10, 36, 99, 0.15);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .room-card-wrapper:hover .room-card-image-container {
    box-shadow: 0 12px 40px rgba(36, 123, 160, 0.3);
  }

  .room-card-image-container::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
      180deg,
      transparent 0%,
      rgba(0, 18, 51, 0.3) 50%,
      rgba(10, 36, 99, 0.85) 100%
    );
    z-index: 1;
    transition: opacity 0.4s;
  }

  .room-card-wrapper:hover .room-card-image-container::before {
    background: linear-gradient(
      180deg,
      transparent 0%,
      rgba(0, 18, 51, 0.4) 50%,
      rgba(10, 36, 99, 0.9) 100%
    );
  }

  .room-card-image-container::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
      135deg,
      rgba(36, 123, 160, 0.1) 0%,
      transparent 50%,
      rgba(56, 145, 166, 0.1) 100%
    );
    opacity: 0;
    z-index: 2;
    transition: opacity 0.4s;
  }

  .room-card-wrapper:hover .room-card-image-container::after {
    opacity: 1;
  }

  .room-card-img {
    height: 220px;
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
  }

  .room-card-wrapper:hover .room-card-title {
    transform: translateX(-50%) translateY(-4px);
    text-shadow:
      0 6px 16px rgba(0, 0, 0, 0.6),
      0 0 20px rgba(56, 145, 166, 0.4);
  }

  .room-card-description {
    color: #64748b;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-top: 1.5rem;
    text-align: center;
    min-height: 3rem;
    transition: color 0.3s;
  }

  .room-card-wrapper:hover .room-card-description {
    color: #475569;
  }

  /* Badge/Tag (Optional) */
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

  /* Responsive adjustments */
  @media (max-width: 991.98px) {
    .room-card-img {
      height: 200px;
    }

    .room-card-title {
      font-size: 1.375rem;
    }
  }

  @media (max-width: 767.98px) {
    .room-card-img {
      height: 240px;
    }

    .room-card-title {
      bottom: 1.25rem;
    }

    .room-card-description {
      margin-top: 1.25rem;
      font-size: 0.9rem;
    }
  }

  @media (max-width: 575.98px) {
    .room-card-img {
      height: 220px;
    }

    .room-card-title {
      font-size: 1.25rem;
      width: 85%;
    }

    .room-card-description {
      font-size: 0.875rem;
    }
  }

  /* Animation for page load */
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
    height: 100%;
  }

  .room-card-wrapper .card-body {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  /* Stagger animation for multiple cards */
  .carousel-item .room-card-wrapper:nth-child(1) {
    animation-delay: 0.1s;
  }

  .carousel-item .room-card-wrapper:nth-child(2) {
    animation-delay: 0.2s;
  }

  .carousel-item .room-card-wrapper:nth-child(3) {
    animation-delay: 0.3s;
  }

  /* Fixed Carousel Height - Prevents jumping */
  .carousel-inner {
    min-height: 420px;
  }

  #roomsCarouselDesktop .carousel-inner {
    min-height: 420px;
  }

  #roomsCarouselMobile .carousel-inner {
    min-height: 450px;
  }

  .carousel-item {
    min-height: 420px;
  }

  #roomsCarouselMobile .carousel-item {
    min-height: 450px;
  }

  /* Ensure rows maintain consistent height */
  .carousel-item .row {
    min-height: 100%;
  }

  /* Make columns fill height */
  .carousel-item [class*="col-"] {
    display: flex;
  }

  /* Responsive carousel heights */
  @media (max-width: 991.98px) {
    .carousel-inner {
      min-height: 400px;
    }

    .carousel-item {
      min-height: 400px;
    }
  }

  @media (max-width: 767.98px) {
    #roomsCarouselMobile .carousel-inner {
      min-height: 480px;
    }

    #roomsCarouselMobile .carousel-item {
      min-height: 480px;
    }
  }

  @media (max-width: 575.98px) {
    #roomsCarouselMobile .carousel-inner {
      min-height: 460px;
    }

    #roomsCarouselMobile .carousel-item {
      min-height: 460px;
    }
  }
</style>

<div class="card bg-transparent shadow-none room-card-wrapper">
  <div class="card-body text-center p-0">
    <div class="room-card-image-container">
      <img
        src="{{ asset($image) }}"
        class="card-img room-card-img"
        alt="{{ $title }}"
        loading="lazy"
      >

      {{-- Optional badge - remove comment to show --}}
      {{-- <div class="room-card-badge">Featured</div> --}}

      <h5 class="room-card-title">{{ $title }}</h5>
    </div>

    <p class="card-text room-card-description">
      {{ $description }}
    </p>
  </div>
</div>
