@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 px-4">
  <div class="row">

  {{-- WELCOME CARD --}}
    <div class="col-lg-9">
      <div class="card glass-card-green card-a p-4 mb-4 text-white">
        <h4 class="fw-bold mb-1">
          Welcome Back,
          {{ ucwords(str_replace('_', ' ', $user->role ?? 'Budget')) }}!
        </h4>
        <h6 class="date mb-0 opacity-75">
          <i class="bi bi-calendar3 me-2"></i>
          {{ now()->format('F d, Y') }}
        </h6>
      </div>

      {{-- ROW 2/METRICS CARD --}}
      <div class="row mb-4">
        {{-- CARD C --}}
        <div class="col-md-4">
          <div class="card glass-card card-c p-0 h-80 border-0 border-start border-4" style="border-color: var(--primary) !important;">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-uppercase fw-bold p-0 m-0" style="color: var(--primary)">
                  Amount in Process
                </h6>
                <p class="mb-2">
                  <small><i>{{ now()->year }}</i></small>
                </p>
                <h2 class="fw-bold fs-2 m-0" style="color: var(--primary)">
                  ₱{{ number_format($metrics['amountInProcess'], 2) }}
                </h2>
              </div>
              <div class="fs-1 opacity-60" style="color: var(--primary);">
                <i class="bi bi-database-exclamation"></i>
              </div>  
            </div>
          </div>
        </div>

        {{-- CARD D --}}
        <div class="col-md-4">
          <div class="card glass-card card-c p-0 h-80 border-0 border-start border-4" style="border-color: var(--secondary) !important;">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-uppercase fw-bold p-0 m-0" style="color: var(--secondary)">
                  Forwarded to Accounting
                </h6>
                <p class="mb-2">
                  <small><i>{{ now()->year }}</i></small>
                </p>
                <h2 class="fw-bold fs-2 m-0" style="color: var(--secondary)">
                  ₱{{ number_format($metrics['amountForwarded'], 2) }}
                </h2>
              </div>
              <div class="fs-1 opacity-60" style="color: var(--secondary);">
                <i class="bi bi-database-fill-up"></i>
              </div>  
            </div>
          </div>
        </div>

        {{-- CARD E --}}
        <div class="col-md-4">
          <div class="card glass-card card-c p-0 h-80 border-0 border-start border-4" style="border-color: var(--primary-variant) !important;">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-uppercase fw-bold p-0 m-0" style="color: var(--primary-variant)">
                  Total Amount Paid
                </h6>
                <p class="mb-2">
                  <small><i>{{ now()->year }}</i></small>
                </p>
                <h2 class="fw-bold fs-2 m-0" style="color: var(--primary-variant)">
                  ₱{{ number_format($metrics['totalAmountPaid'], 2) }}
                </h2>
              </div>
              <div class="fs-1 opacity-60" style="color: var(--primary-variant);">
                <i class="bi bi-database-fill-check"></i>
              </div>  
            </div>
          </div>
        </div>
      </div>

      {{-- ROW 3/VISUALIZATION CARD --}}
      <div class="card glass-card card-f p-3 m-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold m-0 text-uppercase" style="color: var(--primary)">
            Amount Per Office
          </h6>
          <form method="GET" class="m-0" style="min-width: 120px;">
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
              @for($year = now()->year; $year >= 2025; $year--)
                <option value="{{ $year }}" {{ request('year', now()->year) == $year ? 'selected' : '' }}>
                  {{ $year }}
                </option>
              @endfor
            </select>
          </form>
        </div>

        <div class="p-1" style="height: 350px; position: relative;">
          <canvas id="officeChart"></canvas>
        </div>
      </div>
    </div>

    {{-- RIGHT-SIDE COLUMN --}}
    <div class="col-lg-3">
      {{-- CARD B --}}
      <div class="card glass-card card-b p-3 border-0 text-center mb-4">
        <h6 class="fw-bold mb-0 text-uppercase" style="color: var(--primary)">
          Total Transactions
        </h6>
        <p class="mb-0">
          <small><i>{{ now()->year }}</i></small>
        </p>
        <h2 class="display-4 fw-bold p-0 m-0" style="color: var(--primary)">
          {{ $metrics['totalTransactions'] }}
        </h2>
      </div>
      
      {{-- CARD H --}}
      <div class="card glass-card card-h p-3">
        <h6 class="fw-bold mb-0 text-center text-uppercase" style="color: var(--primary)">
          Workflow Status
        </h6>
        <p class="mb-2 text-center">
          <small><i>{{ now()->year }}</i></small>
        </p>
        <div class="row g-3 text-center">
          
          <div class="col-6">
            <div class="p-2 border rounded h-100 d-flex flex-column align-items-center justify-content-center" style="border-color: #17a2b8 !important;">
              <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 border-info fw-bold mb-1 text-info" style="width: 45px; height: 45px; font-size: 1.1rem;">
                {{ $metrics['statusCounts']['for_review'] }}
              </div>
              <span class="small text-muted d-block" style="font-size: 0.75rem;">
                For Review
              </span>
            </div>
          </div>
          
          <div class="col-6">
            <div class="p-2 border rounded  h-100 d-flex flex-column align-items-center justify-content-center" style="border-color: #ffc107 !important;">
              <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 border-warning fw-bold mb-1 text-warning" style="width: 45px; height: 45px; font-size: 1.1rem;">
                {{ $metrics['statusCounts']['pending'] }}
              </div>
              <span class="small text-muted d-block" style="font-size: 0.75rem;">
                Pending
              </span>
            </div>
          </div>
          
          <div class="col-6">
            <div class="p-2 border rounded h-100 d-flex flex-column align-items-center justify-content-center" style="border-color: #fd7e14 !important;">
              <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 fw-bold mb-1" style="width: 45px; height: 45px; font-size: 1.1rem; color: #fd7e14; border-color: #fd7e14 !important;">
                {{ $metrics['statusCounts']['processing'] }}
              </div>
              <span class="small text-muted d-block" style="font-size: 0.75rem;">
                Processing
              </span>
            </div>
          </div>
          
          <div class="col-6">
            <div class="p-2 border rounded h-100 d-flex flex-column align-items-center justify-content-center" style="border-color: #0d6efd !important;">
              <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 border-primary fw-bold mb-1 text-primary" style="width: 45px; height: 45px; font-size: 1.1rem;">
                {{ $metrics['statusCounts']['for_obligation'] }}
              </div>
              <span class="small text-muted d-block" style="font-size: 0.75rem;">
                For Obligation
              </span>
            </div>
          </div>
          
          <div class="col-6">
            <div class="p-2 border rounded h-100 d-flex flex-column align-items-center justify-content-center" style="border-color: #6f42c1 !important;">
              <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 fw-bold mb-1" style="width: 45px; height: 45px; font-size: 1.1rem; color: #6f42c1; border-color: #6f42c1 !important;">
                {{ $metrics['statusCounts']['returned'] }}
              </div>
              <span class="small text-muted d-block" style="font-size: 0.75rem;">
                Returned
              </span>
            </div>
          </div>
          
          <div class="col-6">
            <div class="p-2 border rounded h-100 d-flex flex-column align-items-center justify-content-center" style="border-color: #dc3545 !important;">
              <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 border-danger fw-bold mb-1 text-danger" style="width: 45px; height: 45px; font-size: 1.1rem;">
                {{ $metrics['statusCounts']['cancelled'] }}
              </div>
              <span class="small text-muted d-block" style="font-size: 0.75rem;">
                Cancelled
              </span>
            </div>
          </div>
          
          <div class="col-6">
            <div class="p-2 border rounded h-100 d-flex flex-column align-items-center justify-content-center" style="border-color: #198754 !important;">
              <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 border-success fw-bold mb-1 text-success" style="width: 45px; height: 45px; font-size: 1.1rem;">
                {{ $metrics['statusCounts']['forwarded'] }}
              </div>
              <span class="small text-muted d-block" style="font-size: 0.75rem;">
                Forwarded
              </span>
            </div>
          </div>
          
          <div class="col-6">
            <div class="p-2 border rounded h-100 d-flex flex-column align-items-center justify-content-center" style="border-color: #198754 !important;">
              <div class="rounded-circle d-flex align-items-center justify-content-center border border-3 border-success fw-bold mb-1 text-success" style="width: 45px; height: 45px; font-size: 1.1rem;">
                {{ $metrics['statusCounts']['paid'] }}
              </div>
              <span class="small text-muted d-block" style="font-size: 0.75rem;">
                Paid
              </span>
            </div>
          </div>
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
  const ctx = document.getElementById('officeChart');
  
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: {!! json_encode(array_keys($metrics['officeAmounts'])) !!},
        datasets: [{
          label: 'Forwarded to Accounting Amount',
          data: {!! json_encode(array_values($metrics['officeAmounts'])) !!},
          backgroundColor: 'rgba(54, 162, 235, 0.6)', // added subtle default color
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
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
        }, // Fixed missing structural brace here
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
});
</script>
@endsection

@php
    $pageTitle = 'Dashboard';
@endphp