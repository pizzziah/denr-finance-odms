<div class="d-flex flex-wrap gap-3 mb-1 mt-1">
  {{-- ACCOUNTING --}}
  @if(in_array(strtolower(Auth::user()->role), ['accountant', 'bookkeeper']))
    <x-subtab-link status="all" label="All"/>
    <x-subtab-link status="Pending" label="Pending"/>
    <x-subtab-link status="Processing" label="Processing"/>
    <x-subtab-link status="Returned to End User" label="Returned to End User"/>
    <x-subtab-link status="Returned to Budget" label="Returned to Budget"/>
    <x-subtab-link status="Forwarded to Cashier" label="Forwarded to Cashier"/>
    <x-subtab-link status="Paid" label="Paid"/>
    <x-subtab-link status="Cancelled" label="Cancelled"/>
  @endif

  {{-- BUDGET --}}
  @if(strtolower(Auth::user()->role) == 'budget')
      <x-subtab-link status="all" label="All"/>
      <x-subtab-link status="pending" label="Pending"/>
      <x-subtab-link status="processing" label="Processing"/>
      <x-subtab-link status="for_obligation" label="For Obligation"/>
      <x-subtab-link status="returned_to_end_user" label="Returned to End User"/>
      <x-subtab-link status="returned_by_accounting" label="Returned by Accounting"/>
      <x-subtab-link status="forwarded_to_accounting" label="Forwarded to Accounting"/>
      <x-subtab-link status="paid" label="Paid"/>
      <x-subtab-link status="Cancelled" label="Cancelled"/>
  @endif
</div>