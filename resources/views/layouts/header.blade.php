<header class="d-flex justify-content-between align-items-center ">
    <div class="d-flex align-items-center gap-3">
    <!-- TO-DO: update once database is ok { $pageTitle >-->
        <h3 class="fw-bold" style="color: var(--primary);">
            Tab
        </h3>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div>
            <button class="d-flex flex-row fw-bold fs-5 gap-2 "
                style="background-color: var(--background); color: var(--text-dark); border:none; border-radius: 8px;">
                <i class="bi bi-app-indicator"></i>
                Notifications
            </button>
        </div>
        <hr style="border-right-width: 1px; border-right-style: solid; border-right-color: var(--text-dark); height: 30px;">
        
        
        {{-- ADMIN --}}
        @if(Auth::user()->role === 'admin')
            <h5 class="p-3 fw-bold" 
                style="color: var(--primary); background-color: var(--secondary-variant); border: 1px solid var(--primary); border-radius: 8px; border: 1px solid var(--primary);">
                System Admin
            </h5>
        @endif

        {{-- ACCOUNTING --}}
        @if(in_array(Auth::user()->role, ['accountant', 'bookkeeper']))
            <h5 class="p-3 fw-bold" 
                style="color: var(--primary); background-color: var(--secondary-variant); border: 1px solid var(--primary); border-radius: 8px; border: 1px solid var(--primary);">
                Accounting Department
            </h5>
        @endif
        
        {{-- BUDGET --}}
        @if(Auth::user()->role === 'budget')
            <h5 class="p-3 fw-bold" 
                style="color: var(--primary); background-color: var(--secondary-variant); border: 1px solid var(--primary); border-radius: 8px; border: 1px solid var(--primary);">
                Budget Department
            </h5>
        @endif        

        
    </div>
</header>