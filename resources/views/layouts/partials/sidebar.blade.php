{{--
  Sidebar partial — rendered from app.blade.php.
  Menu items built by App\Support\SidebarMenu::forUser($user).
--}}
@php
    use App\Support\SidebarMenu;
    $user      = auth()->user();
    $menuItems = $user ? SidebarMenu::forUser($user) : [];
@endphp

<div class="sidebar-wrapper">
  <div>
    <div class="logo-wrapper">
      <a href="{{ url('/') }}"><img class="img-fluid for-light" style="max-width:180px;" src="../assets/images/logo/logo-focus-group.svg" alt="Focus Group"></a>
      <div class="back-btn"><i class="fa fa-angle-left"></i></div>
      <div class="toggle-sidebar"><i class="fa fa-cog status_toggle middle sidebar-toggle"></i></div>
    </div>
    <div class="logo-icon-wrapper"><a href="{{ url('/') }}"><img class="img-fluid" style="max-width:40px;" src="../assets/images/logo/logo-focus-group.svg" alt="Focus Group"></a></div>
    <nav class="sidebar-main">
      <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
      <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
          <li class="back-btn">
            <a href="{{ url('/') }}"><img class="img-fluid" style="max-width:40px;" src="../assets/images/logo/logo-focus-group.svg" alt="Focus Group"></a>
            <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
          </li>
          <li class="sidebar-main-title">
            <h6>Menu</h6>
          </li>
          <li class="menu-box">
            <ul>
              @foreach ($menuItems as $item)

                @if (($item['type'] ?? 'link') === 'submenu')
                  {{-- ── Collapsible submenu ── --}}
                  @php
                    $patterns      = array_map('trim', explode(',', $item['active_child'] ?? ''));
                    $parentIsActive = collect($patterns)->contains(fn($p) => request()->routeIs($p));
                  @endphp
                  <li class="sidebar-list">
                    <a class="sidebar-link sidebar-title {{ $parentIsActive ? 'active' : '' }}" href="#">
                      <i data-feather="{{ $item['icon'] ?? 'circle' }}"></i>
                      <span>{{ $item['label'] }}</span>
                    </a>
                    <ul class="sidebar-submenu">
                      @foreach ($item['children'] as $child)
                        @php
                          $childActive = isset($child['active']) && request()->routeIs($child['active']);
                        @endphp
                        <li>
                          <a href="{{ route($child['route']) }}"
                             class="{{ $childActive ? 'active' : '' }}">
                            {{ $child['label'] }}
                          </a>
                        </li>
                      @endforeach
                    </ul>
                  </li>

                @else
                  {{-- ── Single link ── --}}
                  @php
                    // Determine active state
                    if (isset($item['active_url'])) {
                        // URL-based exact match (e.g. Review BP with query param)
                        $isActive = request()->fullUrlIs($item['active_url'] . '*')
                            || url()->current() === $item['active_url'];
                    } elseif (isset($item['active_url_exclude'])) {
                        // Active when routeIs matches BUT NOT the excluded URL
                        $isActive = isset($item['active']) && request()->routeIs($item['active'])
                            && request()->fullUrl() !== $item['active_url_exclude'];
                    } elseif (isset($item['active'])) {
                        $isActive = request()->routeIs($item['active']);
                    } else {
                        $isActive = false;
                    }

                    $href = isset($item['url'])
                        ? $item['url']
                        : (isset($item['route']) ? route($item['route']) : '#');
                  @endphp
                  <li class="sidebar-list">
                    <a class="sidebar-link sidebar-title link-nav {{ $isActive ? 'active' : '' }}"
                       href="{{ $href }}">
                      <i data-feather="{{ $item['icon'] ?? 'circle' }}"></i>
                      <span>{{ $item['label'] }}</span>
                    </a>
                  </li>
                @endif

              @endforeach

              @if (empty($menuItems))
                <li class="sidebar-list">
                  <span class="sidebar-link sidebar-title link-nav text-muted">
                    <i data-feather="slash"></i><span>Tidak ada menu</span>
                  </span>
                </li>
              @endif
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </div>
</div>
