@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

@extends('layouts/commonMaster')

@section('layoutContent')

  {{-- ══════ STICKY NAVBAR ══════ --}}
  <nav class="landing-nav" id="landingNav">
    <div class="landing-nav-inner">

      {{-- Brand --}}
      <a href="{{ route('frontpage.index') }}" class="nav-brand">
        <div class="nav-brand-icon"><i class="ri-building-line"></i></div>
        <span>Hotel De <strong>SLSU</strong></span>
      </a>

      {{-- Desktop links --}}
      <ul class="nav-links" id="navLinks">
        <li><a href="{{ route('frontpage.index') }}#home" class="nav-link">Home</a></li>
        <li><a href="{{ route('frontpage.index') }}#rooms"     class="nav-link">Rooms</a></li>
        <li><a href="{{ route('frontpage.index') }}#amenities" class="nav-link">Amenities</a></li>
        <li><a href="{{ route('frontpage.index') }}#contact"   class="nav-link">Contact</a></li>
      </ul>

      {{-- CTA --}}
      <div class="nav-actions">
        @auth
          <a href="{{ url('/') }}" class="nav-btn-outline">Dashboard</a>
        @else
          <a href="{{ route('login') }}"    class="nav-btn-outline">Sign In</a>
        @endauth
      </div>

      {{-- Hamburger --}}
      <button class="nav-hamburger" id="navHamburger" aria-label="Toggle menu" type="button">
        <span></span><span></span><span></span>
      </button>
    </div>

    {{-- Mobile drawer --}}
    <div class="nav-drawer" id="navDrawer">
      <ul class="nav-drawer-links">
        <li><a href="{{ route('frontpage.index') }}">Home</a></li>
        <li><a href="{{ route('frontpage.index') }}#rooms">Rooms</a></li>
        <li><a href="{{ route('frontpage.index') }}#amenities">Amenities</a></li>
        <li><a href="{{ route('frontpage.index') }}#contact">Contact</a></li>
      </ul>
      <div class="nav-drawer-actions">
        @auth
          <a href="{{ url('/') }}" class="nav-btn-outline w-full">Dashboard</a>
        @else
          <a href="{{ route('login') }}"    class="nav-btn-outline w-full">Sign In</a>
        @endauth
      </div>
    </div>
  </nav>

  <style>
    /* ── NAV RESET ── */
    .landing-nav *, .landing-nav *::before, .landing-nav *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── NAV BASE ── */
    .landing-nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 9999;
      height: 68px;
      display: flex;
      flex-direction: column;
      /* starts transparent over hero */
      background: transparent;
      transition: background .35s ease, box-shadow .35s ease, height .3s ease;
    }

    /* scrolled state — applied via JS */
    .landing-nav.scrolled {
      background: rgba(13, 17, 71, 0.97);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      box-shadow: 0 4px 32px rgba(0,0,0,.22);
    }

    /* ── INNER ── */
    .landing-nav-inner {
      max-width: 1280px;
      width: 100%;
      margin: 0 auto;
      padding: 0 2rem;
      height: 68px;
      display: flex;
      align-items: center;
      gap: 2rem;
    }

    /* ── BRAND ── */
    .nav-brand {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      text-decoration: none;
      flex-shrink: 0;
    }
    .nav-brand-icon {
      width: 36px; height: 36px;
      background: linear-gradient(135deg, #5b67e8, #3d4fd6);
      border-radius: 0.6rem;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.05rem; color: #fff;
      box-shadow: 0 3px 12px rgba(91,103,232,.4);
    }
    .nav-brand span {
      font-family: 'DM Sans', sans-serif;
      font-size: 1.1rem;
      font-weight: 500;
      color: rgba(255,255,255,.9);
      letter-spacing: 0.01em;
    }
    .nav-brand span strong { font-weight: 800; color: #fff; }

    /* ── DESKTOP LINKS ── */
    .nav-links {
      list-style: none;
      display: flex;
      align-items: center;
      gap: 0.25rem;
      margin-left: auto;
    }
    .nav-link {
      font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem;
      font-weight: 500;
      color: rgba(255,255,255,.78);
      text-decoration: none;
      padding: 0.45rem 0.9rem;
      border-radius: 0.5rem;
      transition: color .2s, background .2s;
      position: relative;
    }
    .nav-link::after {
      content: '';
      position: absolute;
      bottom: 2px; left: 50%; right: 50%;
      height: 2px;
      background: #7c86f0;
      border-radius: 2px;
      transition: left .25s, right .25s;
    }
    .nav-link:hover,
    .nav-link.active {
      color: #fff;
    }
    .nav-link:hover::after,
    .nav-link.active::after {
      left: 0.9rem;
      right: 0.9rem;
    }

    /* ── CTA BUTTONS ── */
    .nav-actions {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      flex-shrink: 0;
    }
    .nav-btn-outline {
      font-family: 'DM Sans', sans-serif;
      font-size: 0.875rem; font-weight: 600;
      color: rgba(255,255,255,.88);
      text-decoration: none;
      border: 1.5px solid rgba(255,255,255,.35);
      border-radius: 0.6rem;
      padding: 0.5rem 1.2rem;
      transition: border-color .2s, color .2s, background .2s;
    }
    .nav-btn-outline:hover {
      border-color: rgba(255,255,255,.75);
      color: #fff;
      background: rgba(255,255,255,.08);
    }
    .nav-btn-solid {
      font-family: 'DM Sans', sans-serif;
      font-size: 0.875rem; font-weight: 700;
      color: #fff;
      text-decoration: none;
      background: #5b67e8;
      border: 1.5px solid #5b67e8;
      border-radius: 0.6rem;
      padding: 0.5rem 1.35rem;
      transition: background .2s, transform .15s, box-shadow .2s;
      box-shadow: 0 4px 16px rgba(91,103,232,.35);
    }
    .nav-btn-solid:hover {
      background: #4a56d4;
      border-color: #4a56d4;
      transform: translateY(-1px);
      box-shadow: 0 6px 22px rgba(91,103,232,.45);
      color: #fff;
    }
    .nav-btn-solid.w-full,
    .nav-btn-outline.w-full { width: 100%; text-align: center; }

    /* ── HAMBURGER ── */
    .nav-hamburger {
      display: none;
      flex-direction: column;
      justify-content: center;
      gap: 5px;
      width: 36px; height: 36px;
      background: rgba(255,255,255,.1);
      border: 1px solid rgba(255,255,255,.2);
      border-radius: 0.5rem;
      cursor: pointer;
      padding: 0 8px;
      flex-shrink: 0;
      transition: background .2s;
    }
    .nav-hamburger:hover { background: rgba(255,255,255,.18); }
    .nav-hamburger span {
      display: block;
      height: 2px;
      background: #fff;
      border-radius: 2px;
      transition: transform .3s, opacity .3s, width .3s;
    }
    /* open state */
    .nav-hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .nav-hamburger.open span:nth-child(2) { opacity: 0; width: 0; }
    .nav-hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

    /* ── MOBILE DRAWER ── */
    .nav-drawer {
      display: none; /* only block on mobile */
      background: rgba(10, 14, 60, 0.98);
      backdrop-filter: blur(20px);
      padding: 1.25rem 1.75rem 1.75rem;
      border-top: 1px solid rgba(255,255,255,.08);
    }
    .nav-drawer-links {
      list-style: none;
      display: flex; flex-direction: column; gap: 0.25rem;
      margin-bottom: 1.25rem;
    }
    .nav-drawer-links a {
      display: block;
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem; font-weight: 500;
      color: rgba(255,255,255,.78);
      text-decoration: none;
      padding: 0.7rem 0.5rem;
      border-bottom: 1px solid rgba(255,255,255,.07);
      transition: color .2s;
    }
    .nav-drawer-links a:hover { color: #fff; }
    .nav-drawer-links li:last-child a { border-bottom: none; }
    .nav-drawer-actions {
      display: flex; flex-direction: column; gap: 0.65rem;
      padding-top: 0.5rem;
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
      .nav-links   { display: none; }
      .nav-actions { display: none; }
      .nav-hamburger { display: flex; margin-left: auto; }
      .nav-drawer    { display: block; }  /* always in DOM, open class toggles visibility */
      .nav-drawer:not(.open) { display: none; }
    }
    @media (max-width: 480px) {
      .landing-nav-inner { padding: 0 1.25rem; }
    }

    /* ── PUSH PAGE CONTENT DOWN ── */
    /* Hero already has top padding — nav is overlaid on hero so no push needed */
  </style>

  <script>
    (function () {
      const nav       = document.getElementById('landingNav');
      const hamburger = document.getElementById('navHamburger');
      const drawer    = document.getElementById('navDrawer');

      /* Scroll: add .scrolled after 40px */
      function onScroll() {
        nav.classList.toggle('scrolled', window.scrollY > 40);
      }
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll(); // run on load

      /* Mobile toggle */
      hamburger.addEventListener('click', function () {
        const open = drawer.classList.toggle('open');
        hamburger.classList.toggle('open', open);
      });

      /* Close drawer on link click */
      drawer.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
          drawer.classList.remove('open');
          hamburger.classList.remove('open');
        });
      });
    })();
  </script>

  {{-- ══════ PAGE CONTENT ══════ --}}
  @yield('content')

@endsection