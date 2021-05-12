
              <div class="item_nav_side sidebar-show store-side w-100 show" id="sidebarMenu" data-simplebar>
              <div class="nav flex-column nav-pills">
                <a class="nav-link" href="{{ route('user-profile-dashboard', ['profile' => $user->username]) }}">
                  <i class="tio dashboard_outlined"></i>
                  {{ __('Dashbord') }}
                </a>
                  <a class="nav-link" href="{{ route('user-profile-dashboard-orders', ['profile' => $user->username]) }}">
                    <i class="tio inbox"></i>
                    {{ __('Orders') }}
                  </a>
                  <a class="nav-link" href="{{ route('user-store-dashboard-chat', ['profile' => $user->username]) }}">
                    <i class="tio chat_outlined"></i>
                    {{ __('Chat') }}
                  </a>
                  <a class="nav-link" href="{{ route('user-store-dashboard-settings', ['profile' => $user->username]) }}">
                    <i class="tio settings_vs_outlined"></i>
                    {{ __('Settings') }}
                  </a>
                  <a class="nav-link mt-md-8 mt-3" href="{{ route('user-store-dashboard-logout', ['profile' => $user->username]) }}">
                    <i class="tio sign_out"></i>
                    {{ __('Logout') }}
                  </a>
              </div>
             </div>