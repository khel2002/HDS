<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('/assets') . '/' }}" dir="ltr"
  data-skin="default" data-base-url="{{ url('/') }}" data-framework="laravel" data-bs-theme="light"
  data-template="vertical-menu-template">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">  {{-- ← ADD THIS LINE --}}
  <title>@yield('title', 'Hotel De SLSU')</title>
  <meta name="description" content="Hotel De SLSU" />
  <meta name="keywords" content="Hotel De SLSU, Hotel, SLSU, Southern Leyte State University" />
  <!-- Include Styles -->
  @include('layouts/sections/styles')

  <!-- Include Scripts for customizer, helper, analytics, config -->
  @include('layouts/sections/scriptsIncludes')
</head>

<body>
  <!-- Layout Content -->
  @yield('layoutContent')
  <!--/ Layout Content -->

  <!-- Include Scripts -->
  @include('layouts/sections/scripts')
</body>

</html>