@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
  $selectedYear = request('year', now()->year);
  $selectedMonth = request('month');
    
  $monthName = $selectedMonth ? DateTime::createFromFormat('!m', $selectedMonth)->format('F') : '';
  $timelineLabel = $selectedMonth ? "$monthName $selectedYear" : "$selectedYear";
@endphp

<div class="container-fluid mt-4 px-4">
  <div class="row">
    <div class="col-lg-9">
      {{-- WELCOME CARD --}}
      <x-db-main-card :user="$user">
        <x-db-main-card-filter :selected-year="$selectedYear" :selected-month="$selectedMonth" />
      </x-db-main-card>

      {{-- METRICS CARD --}}
      <div class="row mb-4">
        <x-db-amount-card title="Amount in Process" :value="$metrics['amountInProcess'] ?? 0" icon="bi-database-exclamation" :timeline-label="$timelineLabel"color-var="primary" />
        <x-db-amount-card title="Forwarded to Accounting" :value="$metrics['amountForwarded'] ?? 0" icon="bi-database-fill-up" :timeline-label="$timelineLabel" color-var="secondary" :cancelled-amount="$metrics['totalAmountCancelled'] ?? 0" />
        <x-db-amount-card title="Total Amount Paid" :value="$metrics['totalAmountPaid'] ?? 0" icon="bi-database-fill-check" :timeline-label="$timelineLabel" color-var="primary-variant" />
      </div>

      {{-- WORKFLOW STATUS --}}
      @php
      $budgetStatuses = [
        ['key' => 'pending', 'label' => 'Pending', 'color' => '#9D6B0B', 'bg' => '#FFFBF3'],
        ['key' => 'processing', 'label' => 'Processing', 'color' => '#fd7e14', 'bg' => '#FFF6EF'],
        ['key' => 'for_obligation', 'label' => 'For Obligation', 'color' => '#271ECE', 'bg' => '#BCC3F6'],
        ['key' => 'returned', 'label' => 'Returned to End User', 'color' => '#6f42c1', 'bg' => '#EFDFFF'],
        ['key' => 'cancelled', 'label' => 'Cancelled', 'color' => '#C61919', 'bg' => '#FFC2C2'],
        ['key' => 'forwarded', 'label' => 'Forwarded to Accounting', 'color' => 'var(--primary)', 'bg' => '#E5F2D7'],
        ['key' => 'paid', 'label' => 'Paid', 'color' => 'var(--secondary)', 'bg' => '#EDFADF'],
      ];
      @endphp

      <x-db-workflow-status :statuses="$budgetStatuses" :metrics="$metrics" :timeline-label="$timelineLabel" row-class="row g-3" col-class="col-3" />
    </div>

    {{-- RIGHT-SIDE COLUMN --}}
    <div class="col-lg-3">
      {{-- TOTAL COUNT CARD --}}
      <div class="card glass-card-hover card-b p-3 border-0 text-center mb-4">
        <h6 class="fw-bold mb-0 text-uppercase" style="color: var(--primary)">
          Total Transactions
        </h6>
        <p class="mb-0">
          <small><i>{{ $timelineLabel }}</i></small>
        </p>
        <h2 class="display-4 fw-bold p-0 m-0" style="color: var(--primary)">
          {{ $metrics['totalTransactions'] ?? 0 }}
        </h2>
      </div>

      {{-- VISUALIZATION CARD --}}
      <div class="card glass-card card-f p-3 m-0">
        <div class="text-center mb-3">
          <h6 class="fw-bold m-0 text-uppercase" style="color: var(--primary)">
            Amount Per Office
          </h6>
          <p class="m-0 mt-1">
            <small class="text-muted"><i>{{ $timelineLabel }}</i></small>
          </p>
        </div>

        <div class="p-1" style="height: 350px; position: relative;">
          <canvas id="officeChart"></canvas>
        </div>
      </div>
      
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  Chart.defaults.font.family = "'Montserrat', 'Inter', sans-serif";

  const officeCtx = document.getElementById('officeChart');
  if (officeCtx) {
    const officeAmountsData = {!! json_encode($metrics['officeAmounts'] ?? json_decode('{}')) !!};
    
    const officeLabels = Object.keys(officeAmountsData);
    const officeData = Object.values(officeAmountsData);

    if (officeLabels.length === 0) {
      officeCtx.style.display = 'none';
      const noDataDiv = document.createElement('div');
      noDataDiv.className = 'text-center py-5 text-muted';
      noDataDiv.innerHTML = '<i class="bi bi-graph-down display-6 d-block mb-2"></i> No data recorded for this filtered timeline.';
      officeCtx.parentNode.appendChild(noDataDiv);
    } else {
      new Chart(officeCtx, {
        type: 'bar',
        data: {
          labels: officeLabels,
          datasets: [{
            label: 'Forwarded Amount',
            data: officeData,
            backgroundColor: 'rgb(240, 255, 230)',
            borderColor: '#044709',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return '₱' + Number(context.raw).toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                  });
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return '₱' + Number(value).toLocaleString();
                }
              }
            }
          }
        }
      });
    }
  }
});
</script>
@endsection
@php
    $pageTitle = 'Dashboard';
@endphp