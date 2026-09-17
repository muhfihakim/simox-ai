 <!-- Topbar -->
 <style>
     @media (max-width: 768px) {
         .topbar .user-info {
             display: none !important;
         }

         .topbar .user-profile-trigger .ph-caret-down {
             display: none !important;
         }

         .topbar .user-profile-trigger {
             gap: 0 !important;
         }
     }
 </style>
 <header class="topbar">
     <div class="topbar-left">
         <button class="icon-btn menu-btn" id="toggleSidebar">
             <i class="ph ph-list"></i>
         </button>
         <div class="search-bar" id="navbarSearchBar">
             <i class="ph ph-magnifying-glass search-icon"></i>
             <input type="text" id="navbarSearchInput" placeholder="Cari Data VM, Dinas, atau Node..."
                 autocomplete="off">
             <button type="button" class="search-clear-btn" id="navbarSearchClear" style="display: none;"
                 title="Hapus pencarian">
                 <i class="ph ph-x"></i>
             </button>
             <div class="search-dropdown-results" id="navbarSearchResults">
                 <div class="search-results-loading" id="navbarSearchLoading" style="display: none;">
                     <div class="search-spinner"></div>
                     <span>Mencari data...</span>
                 </div>
                 <div class="search-results-body" id="navbarSearchResultsBody"></div>
                 <div class="search-dropdown-footer" id="navbarSearchFooter" style="display: none;">
                     <span><i class="ph ph-arrows-down-up"></i> Navigasi <strong>&uarr; &darr;</strong> &bull;
                         <strong>Enter</strong> buka</span>
                     <span><kbd>ESC</kbd> tutup</span>
                 </div>
             </div>
         </div>
     </div>

     <div class="topbar-actions">
         <button class="icon-btn ai-chat-btn" id="openAiChat" title="Tanya AI Agent">
             <i class="ph ph-sparkle"></i>
         </button>
         <div class="user-profile" style="position: relative;"
             onclick="document.getElementById('userDropdown').classList.toggle('show')">
             <div class="user-profile-trigger" style="display: flex; align-items: center; gap: 0.5rem;">
                 <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=4f46e5&color=fff"
                     alt="User">
                 <div class="user-info">
                     <span class="user-name">{{ auth()->user()->name ?? 'Admin Diskominfo' }}</span>
                     <span class="user-role">Superadmin</span>
                 </div>
                 <i class="ph ph-caret-down text-muted" style="margin-left: 4px;"></i>
             </div>

             <!-- Dropdown Menu -->
             <div id="userDropdown" class="user-dropdown-menu">
                 <div class="dropdown-header">
                     <span class="text-xs text-muted">Masuk sebagai</span>
                     <strong>{{ auth()->user()->email ?? 'admin@diskominfo.go.id' }}</strong>
                 </div>
                 <hr style="margin: 0.5rem 0; border: 0; border-top: 1px solid var(--border);">
                 <a href="{{ route('users.index') }}" class="dropdown-item"><i class="ph ph-user"></i> Profil Saya</a>
                 <hr style="margin: 0.5rem 0; border: 0; border-top: 1px solid var(--border);">
                 <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                     @csrf
                     <button type="submit" class="dropdown-item text-danger w-100 text-left"
                         style="background:none; border:none; width: 100%; text-align: left; cursor: pointer; font-family: inherit;">
                         <i class="ph ph-sign-out"></i> Keluar
                     </button>
                 </form>
             </div>
         </div>
     </div>
 </header>
