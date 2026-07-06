
<header class="d-flex w-100 justify-content-between align-items-center shadow-sm pt-4 px-4 pb-3 m-0" style="background-color: transparent;">
  <div class="d-flex align-items-center gap-3">
    <h4 class="fw-bold" style="color: var(--primary);">
      {{ $pageTitle ?? 'Dashboard' }}
    </h4>
  </div>

  <div class="d-flex align-items-center gap-3">
    <div>
      <div class="dropdown">
        <button class="btn position-relative" id="notificationBell" data-bs-toggle="dropdown" style="background:transparent;border:none;">
          <i class="bi bi-app-indicator fs-4 text-danger"></i>
          <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"> 0</span>
        </button>

        <div class="dropdown-menu dropdown-menu-end shadow p-0" style="width:500px;max-height:500px;overflow-y:auto;">
          <div class="p-3 border-bottom d-flex justify-content-between">
            <strong>Notifications</strong>
            <button class="btn btn-sm btn-link"id="readAllBtn"> Mark all as read </button>
          </div>
          
          <div id="notificationList">
            <div class="text-center p-4 text-muted">
            Loading...
          </div>
        </div>
      </div>
    </div>
  </div>
    
  <hr style="border-right-width: 1px; border-right-style: solid; border-right-color: var(--text-dark); height: 30px;">
        
    {{-- ADMIN --}}
    @if(auth()->user()->role === 'admin')
      <h6 class="p-3 fw-bold" style="color: #0B879D;; background-color: #EFF9FA; border: 1px solid #0B879D; border-radius: 8px;">
        System Administrator
      </h6>
    @endif

    {{-- ACCOUNTING --}}
    @if(in_array(Auth::user()->role, ['accountant', 'bookkeeper']))
      <h6 class="p-3 fw-bold" style="color: var(--primary); background-color: var(--secondary-variant); border: 1px solid var(--primary); border-radius: 8px;">
        Accounting Section
      </h6>
    @endif
        
    {{-- BUDGET --}}
    @if(auth()->user()->role === 'budget')
      <h6 class="p-3 fw-bold" style="color: var(--secondary); background-color: var(--secondary-variant); border: 1px solid var(--secondary); border-radius: 8px; ">
        Budget Section
      </h6>
    @endif        
  </div>
</header>