 <!-- Topbar -->
 <header class="topbar">
     <div class="topbar-left">
         <button class="icon-btn menu-btn" id="toggleSidebar">
             <i class="ph ph-list"></i>
         </button>
         <div class="search-bar">
             <i class="ph ph-magnifying-glass"></i>
             <input type="text" placeholder="Cari Data VM, Dinas, atau Node...">
         </div>
     </div>

     <div class="topbar-actions">
         <button class="icon-btn ai-chat-btn" id="openAiChat" title="Tanya AI Agent">
             <i class="ph ph-sparkle"></i>
         </button>
         <button class="icon-btn position-relative" onclick="showToast('Tidak ada notifikasi baru', 'info')">
             <i class="ph ph-bell"></i>
             <span class="notification-badge">3</span>
         </button>
         <div class="user-profile" style="position: relative;" onclick="document.getElementById('userDropdown').classList.toggle('show')">
             <div class="user-profile-trigger" style="display: flex; align-items: center; gap: 0.5rem;">
                 <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=4f46e5&color=fff" alt="User">
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
                 <a href="#" class="dropdown-item"><i class="ph ph-user"></i> Profil Saya</a>
                 <a href="#" class="dropdown-item"><i class="ph ph-gear"></i> Pengaturan</a>
                 <hr style="margin: 0.5rem 0; border: 0; border-top: 1px solid var(--border);">
                 <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                     @csrf
                     <button type="submit" class="dropdown-item text-danger w-100 text-left" style="background:none; border:none; width: 100%; text-align: left; cursor: pointer; font-family: inherit;">
                         <i class="ph ph-sign-out"></i> Keluar
                     </button>
                 </form>
             </div>
         </div>
     </div>
 </header>
