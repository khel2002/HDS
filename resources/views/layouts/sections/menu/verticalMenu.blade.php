@php
use Illuminate\Support\Facades\Route;
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu">

    <!-- App Brand -->
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo me-1">@include('_partials.macros')</span>
            <span class="app-brand-text demo menu-text fw-semibold ms-2">{{ config('variables.templateName') }}</span>
        </a>

        {{--
            layout-menu-toggle: triggers expand/collapse of the whole sidebar.
            The Menu JS class listens for clicks on this element.
        --}}
          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="menu-toggle-icon d-xl-inline-block align-middle"></i>
          </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)

            {{-- ── Section headers ── --}}
            @if (isset($menu->menuHeader))
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
                </li>

            @else

                {{-- ── Resolve active/open state ── --}}
                @php
                    $activeClass      = null;
                    $currentRouteName = Route::currentRouteName();

                    if ($currentRouteName === $menu->slug) {
                        $activeClass = 'active';
                    } elseif (isset($menu->submenu)) {
                        $slugs = gettype($menu->slug) === 'array' ? $menu->slug : [$menu->slug];
                        foreach ($slugs as $slug) {
                            if (str_contains($currentRouteName, $slug) && strpos($currentRouteName, $slug) === 0) {
                                $activeClass = 'active open';
                                break;
                            }
                        }
                    }

                    $menuName = isset($menu->name) ? __($menu->name) : '';
                @endphp

                {{-- ── Menu item ── --}}
                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                       class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                       data-i18n="{{ $menuName }}"
                       @if (isset($menu->target) && !empty($menu->target)) target="_blank" @endif>

                        @isset($menu->icon)
                            <i class="{{ $menu->icon }}"></i>
                        @endisset

                        <div data-i18n="{{ $menuName }}">{{ $menuName }}</div>

                        @isset($menu->badge)
                            <div class="badge rounded-pill bg-{{ $menu->badge[0] }} ms-auto">{{ $menu->badge[1] }}</div>
                        @endisset
                    </a>

                    {{-- Submenu --}}
                    @isset($menu->submenu)
                        @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                    @endisset
                </li>

            @endif
        @endforeach
    </ul>

</aside>
