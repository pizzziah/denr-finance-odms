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

        @if(auth()->user()->role === 'admin')
            <x-sidebar-link
                route="admin.dashboard"
                icon="bi bi-columns-gap"
                label="Dashboard" />

            <x-sidebar-link
                route="admin.users"
                icon="bi bi-people-fill"
                label="Users" />
        @endif

        {{-- ACCOUNTING --}}
        @if(in_array(Auth::user()->role, ['accountant', 'bookkeeper']))
            <x-sidebar-link
                route="accounting.dashboard"
                icon="bi bi-columns-gap"
                label="Dashboard" />

            <x-sidebar-link
                route="accounting.logbook"
                icon="bi bi-file-earmark-spreadsheet"
                label="Log Book" />

            <x-sidebar-link
                route="accounting.quarterly-summary"
                icon="bi bi-pie-chart"
                label="Quarterly Summary" />
            <x-sidebar-link
                route="accounting.cashier-status"
                icon="bi bi-wallet-fill"
                label="Cashier Status" />
        @endif

        {{-- BUDGET --}}
        @if(auth()->user()->role === 'budget')
        <x-sidebar-link
            route="budget.dashboard"
            icon="bi bi-columns-gap"
            label="Dashboard" />

        <x-sidebar-link
            route="budget.logbook"
            icon="bi bi-file-earmark-spreadsheet"
            label="Log Book" />
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