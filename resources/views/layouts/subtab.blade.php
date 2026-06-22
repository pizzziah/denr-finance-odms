<div class="d-flex flex-wrap gap-3 mb-4">
  <!-- TO-DO: edit/update status -->  
    {{-- ACCOUNTING --}}
    @if(in_array(Auth::user()->role, ['accountant', 'bookkeeper']))

        <x-subtab-link status="all" label="All"/>
        <x-subtab-link status="pending" label="Pending"/>
        <x-subtab-link status="processing" label="Processing"/>
        <x-subtab-link status="returned" label="Returned"/>
        <x-subtab-link status="forwarded_to_cashier"
            label="Forwarded to Cashier"/>
        <x-subtab-link status="paid" label="Paid"/>

    @endif


    {{-- BUDGET --}}
    @if(Auth::user()->role == 'budget')

<!-- TO-DO: edit/update status -->
    <div class="d-flex flex-wrap gap-3 mb-4">
        <x-subtab-link status="all" label="All"/>
        <x-subtab-link status="pending" label="Pending"/>
        <x-subtab-link status="processing" label="Processing"/>
        <x-subtab-link status="for obligation" label="For Obligation"/>
        <x-subtab-link status="returned" label="Returned"/>
        <x-subtab-link status="forwarded to accounting"
            label="Forwarded to Accounting"/>
        <x-subtab-link status="paid" label="Paid"/>
    </div>
    @endif
</div>