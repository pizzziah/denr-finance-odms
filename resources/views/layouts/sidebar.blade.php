<aside class=" d-flex flex-column p-3 vh-100 border-end" 
        style="background-color: var(--surface); width: 280px;">
    <div class="d-flex align-items-center gap-2 p-1 mt-3">
        <div class="sdbr-logo">
            <img  src="{{ asset('images/DENR-logo.png') }}" 
            class="img-fluid">
        </div>
        <div class="d-flex flex-column gap-2" style="color: var(--primary)">
            <h5 class="mb-0 fw-bold lh-1">Obligation Disbursement Monitoring System</h5>
            <p class="lh-1"><i><small>DENR CAR Finance Division</small></i></p>
        </div>
    </div>

    <hr>    

    <nav class="d-flex flex-column gap-3 mb-4">
        {{-- ADMIN --}}
        @if(Auth::user()->role === 'Admin')

            <a href="{{ route('admin.dashboard') }}"
            class="sidebar-link">
                <i class="bi bi-columns-gap"></i>
                Dashboard
            </a>

            <a href="{{ route('admin.users') }}"
            class="sidebar-link">
                <i class="bi bi-people-fill"></i>
                Users
            </a>

        @endif

        {{-- ACCOUNTING --}}
        @if(in_array(Auth::user()->role, ['Accountant', 'Book Keeper']))
            <a href="{{ route('accounting.dashboard') }}" class="sidebar-link">
                <i class="bi bi-columns-gap"></i>
                Dashboard
            </a>
            <a href="{{ route('accounting.logbook') }}"
            class="sidebar-link">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Log Book
            </a>
            <a href="{{ route('accounting.quarterly-summary') }}"
            class="sidebar-link">
                <i class="bi bi-pie-chart"></i>
                Quarterly Summary
            </a>
            <a href="{{ route('accounting.cashier-status') }}"
            class="sidebar-link">
                <i class="bi bi-wallet-fill"></i>
                Cashier Status
            </a>
        @endif

        {{-- BUDGET --}}
        @if(Auth::user()->role === 'Budget')
            <a href="{{ route('budget.dashboard') }}"
            class="sidebar-link">
                <i class="bi bi-columns-gap"></i>
                Dashboard
            </a>
            <a href="{{ route('budget.logbook') }}"
            class="sidebar-link">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Log Book
            </a>
        @endif
    </nav>

    <div class="mt-auto p-3">
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn btn-dark w-100">
                Log Out
            </button>
        </form>
    </div>
</aside>