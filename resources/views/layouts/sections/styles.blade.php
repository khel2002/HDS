<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link rel="stylesheet"href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" />
<!-- Fonts Icons -->
@vite(['resources/assets/vendor/fonts/iconify/iconify.css'])

<!-- BEGIN: Vendor CSS-->
@vite(['resources/assets/vendor/libs/node-waves/node-waves.scss'])


<!-- Core CSS -->
@vite(['resources/assets/vendor/scss/core.scss', 'resources/assets/css/demo.css'])

<!-- Vendor Styles -->
@vite('resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss')
@yield('vendor-style')

<!-- Page Styles -->
@yield('page-style')

<!-- app CSS -->
@vite(['resources/css/app.css'])
<!-- END: app CSS-->
