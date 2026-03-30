@php
use Illuminate\Support\Facades\Auth;

$authUser     = Auth::user();
$isGuest      = $authUser && !$authUser->isSuperAdmin() && !$authUser->isAdmin() && !$authUser->isStaff();
$isSuperAdmin = $authUser && $authUser->isSuperAdmin();

$notifUrl = match(true) {
    $isGuest      => route('guest.notifications'),
    $isSuperAdmin => route('super_admin.notifications'),
    default       => null,
};
@endphp

@if(isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-6">
    <a href="{{url('/')}}" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">@include('_partials.macros')</span>
        <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
    </a>
</div>
@endif

@if(!isset($navbarHideToggle))
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
        <i class="icon-base ri ri-menu-line icon-md"></i>
    </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <ul class="navbar-nav flex-row align-items-center ms-auto">

        @if($notifUrl)
        <li class="nav-item dropdown me-3" style="position:relative;">

            <a class="nav-link hide-arrow p-0 position-relative"
               href="javascript:void(0);"
               data-bs-toggle="dropdown"
               data-bs-auto-close="outside"
               aria-expanded="false"
               onclick="markNotifSeen()">
                <i class="icon-base ri ri-notification-3-line icon-md" id="notifBellIcon"></i>
                <span id="notifBadge"
                      class="badge rounded-pill bg-danger position-absolute"
                      style="font-size:0.6rem;min-width:16px;padding:2px 5px;top:-4px;left:10px;display:none;
                             transform-origin:center;">
                    0
                </span>
            </a>

            {{-- Floating toast strip --}}
            <div id="notifFloatStrip"
                 style="position:absolute;top:calc(100% + 10px);right:-8px;
                        width:300px;z-index:1100;
                        display:flex;flex-direction:column;gap:6px;
                        pointer-events:none;">
            </div>

            {{-- Dropdown panel --}}
            <div class="dropdown-menu dropdown-menu-end p-0 shadow-sm" style="width:320px;">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <h6 class="mb-0 fw-semibold" style="font-size:.85rem;">Notifications</h6>
                    <span class="badge bg-label-primary rounded-pill" id="notifCount" style="font-size:.7rem;">0</span>
                </div>
                <div style="max-height:340px;overflow-y:auto;">
                    <div id="notifEmpty" class="text-center py-4 px-3 text-body-secondary">
                        <i class="ri-notification-off-line d-block mb-1" style="font-size:1.6rem;opacity:.35;"></i>
                        <small>No new notifications</small>
                    </div>
                    <div id="notifList"></div>
                </div>
            </div>
        </li>
        @endif

        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0"
               href="javascript:void(0);"
               data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="" class="rounded-circle"/>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt=""
                                         class="w-px-40 h-auto rounded-circle"/>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">
                                    {{ trim(($authUser->first_name ?? '') . ' ' . ($authUser->last_name ?? '')) ?: 'User' }}
                                </h6>
                                <small class="text-body-secondary">
                                    @if($isSuperAdmin) Super Admin
                                    @elseif($authUser?->isAdmin()) Admin
                                    @elseif($authUser?->isStaff()) Staff
                                    @else Guest
                                    @endif
                                </small>
                            </div>
                        </div>
                    </a>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <i class="icon-base ri ri-user-3-line icon-md me-3"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <i class="icon-base ri ri-settings-4-line icon-md me-3"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                    <div class="d-grid px-4 pt-2 pb-1">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                <i class="ri-logout-box-line me-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </li>

    </ul>
</div>

@if($notifUrl)
<style>
@keyframes hds-bell-shake {
    0%,100% { transform:rotate(0); }
    15%     { transform:rotate(14deg); }
    30%     { transform:rotate(-11deg); }
    45%     { transform:rotate(8deg); }
    60%     { transform:rotate(-6deg); }
    75%     { transform:rotate(3deg); }
}
.hds-bell-shake { animation:hds-bell-shake .5s cubic-bezier(.36,.07,.19,.97); }

@keyframes hds-badge-pop {
    0%   { transform:scale(.4); opacity:0; }
    60%  { transform:scale(1.3); }
    100% { transform:scale(1); opacity:1; }
}
.hds-badge-pop { animation:hds-badge-pop .3s cubic-bezier(.34,1.56,.64,1); }

.hds-toast {
    background   : #fff;
    border       : 1px solid rgba(0,0,0,.09);
    border-radius: 10px;
    padding      : 10px 12px 12px;
    display      : flex;
    align-items  : flex-start;
    gap          : 10px;
    pointer-events: all;
    box-shadow   : 0 4px 18px rgba(0,0,0,.10);
    transform    : translateX(20px);
    opacity      : 0;
    transition   : transform .32s cubic-bezier(.22,1,.36,1), opacity .26s ease;
    position     : relative;
    overflow     : hidden;
}
.hds-toast.in  { transform:translateX(0); opacity:1; }
.hds-toast.out { transform:translateX(24px); opacity:0; }

.hds-toast::before {
    content:''; position:absolute; left:0;top:0;bottom:0; width:3px;
}
.hds-toast-success::before { background:#2e7d32; }
.hds-toast-warning::before { background:#f57c00; }
.hds-toast-danger ::before { background:#c62828; }
.hds-toast-info   ::before { background:#1565c0; }
.hds-toast-primary::before { background:#4527a0; }

.hds-toast-icon {
    width:30px; height:30px; border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    font-size:.9rem; flex-shrink:0;
}
.hds-toast-success .hds-toast-icon { background:#e8f5e9; color:#2e7d32; }
.hds-toast-warning .hds-toast-icon { background:#fff3e0; color:#e65100; }
.hds-toast-danger  .hds-toast-icon { background:#ffebee; color:#c62828; }
.hds-toast-info    .hds-toast-icon { background:#e3f2fd; color:#1565c0; }
.hds-toast-primary .hds-toast-icon { background:#ede7f6; color:#4527a0; }

.hds-toast-body { flex:1; min-width:0; }
.hds-toast-msg  { font-size:.8rem; color:#1a1a1a; line-height:1.4; word-break:break-word; }
.hds-toast-time { font-size:.7rem; color:#999; margin-top:2px; }

.hds-toast-close {
    background:none; border:none; cursor:pointer;
    color:#bbb; padding:2px; flex-shrink:0;
    line-height:1; transition:color .15s;
}
.hds-toast-close:hover { color:#555; }

.hds-toast-prog {
    position:absolute; bottom:0; left:0; height:2px;
    border-radius:0 0 10px 10px;
}
.hds-toast-success .hds-toast-prog { background:#2e7d32; }
.hds-toast-warning .hds-toast-prog { background:#f57c00; }
.hds-toast-danger  .hds-toast-prog { background:#c62828; }
.hds-toast-info    .hds-toast-prog { background:#1565c0; }
.hds-toast-primary .hds-toast-prog { background:#4527a0; }
</style>

<script>
(function () {
    const NOTIF_URL   = @json($notifUrl);
    const ROLE        = '{{ $isSuperAdmin ? "superadmin" : "guest" }}';
    const STORAGE_KEY = 'notif_seen_' + ROLE;
    const POLL_MS     = 5000;
    const TOAST_MS    = 6000;
    const MAX_TOASTS  = 3;

    const BADGE_CLASS = {
        success:'bg-label-success', warning:'bg-label-warning',
        danger :'bg-label-danger',  info   :'bg-label-info',
        primary:'bg-label-primary',
    };

    let currentData  = [];
    let activeToasts = [];
    let dropdownOpen = false;

    // ── Track whether this is the very first fetch on this page load ─────────
    // On first fetch we SEED seen IDs so existing notifications don't re-toast.
    // A toast only fires when an ID appears that was NOT in the previous poll.
    let firstFetch   = true;
    let prevIds      = new Set(); // IDs from the last successful poll response

    const strip   = document.getElementById('notifFloatStrip');
    const badge   = document.getElementById('notifBadge');
    const bellEl  = document.getElementById('notifBellIcon');
    const listEl  = document.getElementById('notifList');
    const emptyEl = document.getElementById('notifEmpty');
    const countEl = document.getElementById('notifCount');

    document.addEventListener('shown.bs.dropdown',  e => { if (e.target?.closest('.nav-item.dropdown')) dropdownOpen = true;  });
    document.addEventListener('hidden.bs.dropdown', e => { if (e.target?.closest('.nav-item.dropdown')) dropdownOpen = false; });

    function getSeenIds() {
        try { return new Set(JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')); }
        catch { return new Set(); }
    }
    function saveSeenIds(ids) {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify([...ids])); } catch {}
    }

    /* ══════════════════════════════════════════════════════════════════════
       POLL
       - First fetch: seed prevIds from the response, no toasts fired.
       - Subsequent fetches: toast only IDs that weren't in prevIds.
    ══════════════════════════════════════════════════════════════════════ */
    async function poll() {
        try {
            const res = await fetch(NOTIF_URL, {
                headers: { Accept:'application/json', 'X-Requested-With':'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) return;

            const incoming   = data.notifications ?? [];
            const incomingIds = new Set(incoming.map(n => n.id));

            if (firstFetch) {
                // ── Seed: treat everything visible right now as already-seen.
                // This prevents re-firing toasts for notifications that existed
                // before this page load (e.g. after navigation or refresh).
                prevIds    = incomingIds;
                firstFetch = false;

                // Also ensure localStorage is up-to-date so badge shows correctly.
                // We only seed the "seen" store if the user has never seen anything
                // yet — otherwise we preserve their existing unread state.
                const seen = getSeenIds();
                const hasAnyUnseen = incoming.some(n => !seen.has(n.id));

                // Render badge using existing seen state (don't mark as seen automatically
                // — they should still see the red dot / badge until they click the bell).
                currentData = incoming;
                renderDropdownList(incoming, seen);
                renderBadge(incoming, seen);
                return;
            }

            // ── Subsequent polls: only toast truly new IDs ────────────────────
            const brandNewItems = incoming.filter(n => !prevIds.has(n.id));

            if (brandNewItems.length > 0) {
                brandNewItems.forEach(n => fireFloatToast(n));
                shakeBell();

                // Auto-seed these new IDs as "seen" in prevIds for the next cycle
                // so they don't re-toast on the next poll either.
                brandNewItems.forEach(n => prevIds.add(n.id));
            }

            // Keep prevIds in sync (remove IDs that have disappeared from API).
            prevIds = incomingIds;

            const seen = getSeenIds();
            currentData = incoming;
            renderDropdownList(incoming, seen);
            renderBadge(incoming, seen);

            if (dropdownOpen && brandNewItems.length > 0) markNotifSeen();

        } catch { /* silent */ }
    }

    /* ── Floating toast ───────────────────────────────────────────────── */
    function fireFloatToast(n) {
        if (activeToasts.length >= MAX_TOASTS) {
            const oldest = activeToasts[0];
            dismissToast(oldest.el, oldest.tid);
        }

        const el = document.createElement('div');
        el.className = `hds-toast hds-toast-${n.color ?? 'info'}`;
        el.innerHTML = `
            <div class="hds-toast-icon"><i class="ri ${esc(n.icon ?? 'ri-notification-3-line')}"></i></div>
            <div class="hds-toast-body">
                <div class="hds-toast-msg">${esc(n.message)}</div>
                <div class="hds-toast-time">Just now</div>
            </div>
            <button class="hds-toast-close" aria-label="Dismiss">
                <i class="ri ri-close-line" style="font-size:.85rem;"></i>
            </button>
            <div class="hds-toast-prog" style="width:100%;transition:width ${TOAST_MS}ms linear;"></div>
        `;

        el.querySelector('.hds-toast-close').addEventListener('click', () => {
            const entry = activeToasts.find(t => t.el === el);
            if (entry) dismissToast(entry.el, entry.tid);
        });

        strip.appendChild(el);
        requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add('in')));

        const prog = el.querySelector('.hds-toast-prog');
        requestAnimationFrame(() => requestAnimationFrame(() => { prog.style.width = '0%'; }));

        const tid = setTimeout(() => dismissToast(el, tid), TOAST_MS);
        activeToasts.push({ el, tid });
    }

    function dismissToast(el, tid) {
        clearTimeout(tid);
        const idx = activeToasts.findIndex(t => t.el === el);
        if (idx !== -1) activeToasts.splice(idx, 1);
        el.classList.remove('in');
        el.classList.add('out');
        setTimeout(() => el.parentNode && el.parentNode.removeChild(el), 340);
    }

    /* ── Dropdown list ────────────────────────────────────────────────── */
    function renderDropdownList(items, seen) {
        if (!listEl) return;
        listEl.innerHTML = '';
        if (!items.length) { if (emptyEl) emptyEl.style.display = ''; return; }
        if (emptyEl) emptyEl.style.display = 'none';

        items.forEach(n => {
            const isNew = !seen.has(n.id);
            const div   = document.createElement('div');
            div.className = 'px-3 py-2 border-bottom' + (isNew ? ' bg-primary-subtle' : '');
            div.style.transition = 'background .4s ease';
            div.innerHTML = `
                <div class="d-flex align-items-start gap-2">
                    <div class="mt-1 flex-shrink-0">
                        <span class="badge ${BADGE_CLASS[n.color] ?? 'bg-label-secondary'} p-1 rounded">
                            <i class="ri ${esc(n.icon)}" style="font-size:.8rem;line-height:1;"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="mb-0 small lh-sm">${esc(n.message)}</p>
                        <small class="text-body-secondary">${esc(n.time)}</small>
                    </div>
                    ${isNew ? '<span class="flex-shrink-0 rounded-circle bg-danger mt-1" style="width:7px;height:7px;min-width:7px;display:block;"></span>' : ''}
                </div>`;
            listEl.appendChild(div);
        });
    }

    /* ── Badge ────────────────────────────────────────────────────────── */
    function renderBadge(items, seen) {
        if (!badge || !countEl) return;
        const unseen     = items.filter(n => !seen.has(n.id)).length;
        countEl.textContent = items.length;
        badge.textContent   = unseen > 9 ? '9+' : String(unseen);

        if (unseen > 0) {
            badge.style.display = '';
            badge.classList.remove('hds-badge-pop');
            void badge.offsetWidth;
            badge.classList.add('hds-badge-pop');
        } else {
            badge.style.display = 'none';
        }
    }

    /* ── Mark all seen (on bell click) ───────────────────────────────── */
    window.markNotifSeen = function () {
        const ids = new Set(currentData.map(n => n.id));
        saveSeenIds(ids);
        if (badge) badge.style.display = 'none';
        renderDropdownList(currentData, ids);
        activeToasts.slice().forEach(t => dismissToast(t.el, t.tid));
    };

    /* ── Bell shake ───────────────────────────────────────────────────── */
    function shakeBell() {
        if (!bellEl) return;
        bellEl.classList.remove('hds-bell-shake');
        void bellEl.offsetWidth;
        bellEl.classList.add('hds-bell-shake');
        bellEl.addEventListener('animationend', () => bellEl.classList.remove('hds-bell-shake'), { once: true });
    }

    function esc(s) {
        return String(s ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    poll();
    setInterval(poll, POLL_MS);
})();
</script>
@endif