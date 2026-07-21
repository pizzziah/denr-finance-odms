<aside id="sidebar" class="sidebar d-flex flex-column p-3 vh-100 border-end">
  {{-- TOGGLE BUTTON --}}
  <div class="d-flex sidebar-brand justify-content-end">
    <button id="sidebarToggle" class="btn btn-sm w-100 text-end d-flex justify-content-center align-items-center mb-2">
      <i class="bi bi-layout-sidebar fs-4 ms-auto me-0 sidebar-text"></i>
      <i class="bi bi-layout-sidebar-inset-reverse fs-4 mx-auto d-none collapsed-icon"></i>
    </button>
  </div>

  {{-- LOGO --}}
  <div class="d-flex align-items-center gap-2 p-1 sidebar-brand">
    <div class="sdbr-logo">
      <img src="{{ asset('images/DENR-logo.png') }}" class="img-fluid">
    </div>

    <div class="sidebar-text d-flex flex-column gap-2" style="color: var(--primary)">
      <h5 class="mb-0 fw-bold lh-1">
        Obligation Disbursement Management System
      </h5>
      <p class="lh-1">
        <small><i>DENR CAR Finance Division</i></small>
      </p>
    </div>
  </div>

  <hr>

  <nav class="d-flex flex-column gap-3 mb-4">
    @if(auth()->user()->role === 'admin')
      <x-sidebar-link route="admin.dashboard" icon="bi bi-columns-gap" label="Dashboard" />
      <x-sidebar-link route="admin.unlock-requests" icon="bi bi-file-lock2" label="Unlock Requests" />
      <x-sidebar-link route="admin.users" icon="bi bi-people-fill" label="Users" />
    @endif

    @if(in_array(Auth::user()->role, ['accountant', 'bookkeeper']))
      <x-sidebar-link route="accounting.dashboard" icon="bi bi-columns-gap" label="Dashboard" />
      <x-sidebar-link route="accounting.logbook" icon="bi bi-file-earmark-spreadsheet" label="Log Book" />
      <x-sidebar-link route="accounting.cashier-status" icon="bi bi-wallet-fill" label="Cashier Status" />
      <x-sidebar-link route="accounting.quarterly-summary" icon="bi bi-pie-chart" label="Quarterly Summary" />
      <x-sidebar-link route="accounting.archives" icon="bi bi-archive" label="Archives" />
    @endif

    @if(auth()->user()->role === 'budget')
      <x-sidebar-link route="budget.dashboard" icon="bi bi-columns-gap" label="Dashboard" />
      <x-sidebar-link route="budget.logbook" icon="bi bi-file-earmark-spreadsheet" label="Log Book" />
      <x-sidebar-link route="budget.archives" icon="bi bi-archive" label="Archives" />
    @endif
  </nav>

  <div class="mt-auto p-3">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-dark logout-btn w-100">
        <i class="bi bi-box-arrow-right"></i>
        <span class="sidebar-text">Log Out</span>
      </button>
    </form>
  </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');

    if (!sidebar || !toggle) return;

    const isCollapsed = localStorage.getItem('sidebar') === 'collapsed';

    if (isCollapsed) {
        sidebar.classList.add('collapsed');
    }

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');

        localStorage.setItem(
            'sidebar',
            sidebar.classList.contains('collapsed')
                ? 'collapsed'
                : 'expanded'
        );
    });
});
</script>