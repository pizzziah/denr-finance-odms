<header class="d-flex w-100 justify-content-between align-items-center shadow-sm pt-4 px-4 pb-3 m-0" style="background-color: var(--background);">
  <div class="d-flex align-items-center gap-3">
    <h4 class="fw-bold" style="color: var(--primary);">
      {{ $pageTitle ?? 'Dashboard' }}
    </h4>
  </div>

  <div class="d-flex align-items-center gap-3">
    <div>
      <button class="d-flex flex-row fw-bold fs-4 gap-2 " style="background-color: transparent; color: var(--error); border:none; border-radius: 8px;">
        <i class="bi bi-app-indicator"></i>
      </button>
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
        Accounting Department
      </h6>
    @endif
        
    {{-- BUDGET --}}
    @if(auth()->user()->role === 'budget')
      <h6 class="p-3 fw-bold" style="color: var(--secondary); background-color: var(--secondary-variant); border: 1px solid var(--secondary); border-radius: 8px; ">
        Budget Department
      </h6>
    @endif        
  </div>
</header>