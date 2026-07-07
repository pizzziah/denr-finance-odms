@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid mt-4 px-4">
  <div class="row">

    {{-- WELCOME CARD & MASTER CONTROL ROW --}}
    <div class="col-lg-9">
      <div class="card glass-card-green card-a p-4 mb-4 text-white">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
          <div>
            <h4 class="fw-bold mb-1">
              Welcome Back,
              @php
                $displayRole = $user->role ?? 'accounting';
                if (in_array(strtolower($displayRole), ['accountant', 'bookkeeper'])) {
                    $displayRole = ucwords(strtolower($displayRole));
                } else {
                    $displayRole = 'Accounting Team';
                }
              @endphp
              {{ $displayRole }}!
            </h4>
            <h6 class="date mb-0 opacity-75">
              <i class="bi bi-calendar3 me-2"></i>
              {{ now()->format('F d, Y') }}
            </h6>
          </div>
          
          <div class="bg-white p-2 rounded shadow-sm d-flex align-items-center gap-2 m-0" style="min-width: 160px;">
            <label class="small text-muted fw-bold text-uppercase mb-0 ps-1" style="font-size: 0.65rem; color: #044709 !important;">
              Filter Dashboard:
            </label>
            <form method="GET" class="m-0 flex-grow-1">
              <select name="year" class="form-select form-select-sm border-0 fw-bold" onchange="this.form.submit()" style="color: #044709; cursor: pointer; focus: none;">
                @for($year = now()->year; $year >= 2025; $year--)
                  <option value="{{ $year }}" {{ request('year', now()->year) == $year ? 'selected' : '' }}>
                    {{ $year }} Data
                  </option>
                @endfor
              </select>
            </form>
          </div>
        </div>
      </div>

      {{-- ROW 2/METRICS CARD --}}
      <div class="row mb-4">
        {{-- CARD C --}}
        <div class="col-md-4">
          <div class="card glass-card-hover card-c p-0 h-80 border-0 border-start border-4" style="border-color: var(--primary) !important;">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-uppercase fw-bold p-0 m-0" style="color: var(--primary)">
                  Amount in Process
                </h6>
                <p class="mb-2">
                  <small><i>{{ request('year', now()->year) }}</i></small>
                </p>
                <h2 class="fw-bold fs-2 m-0" style="color: var(--primary)">
                  ₱{{ number_format($metrics['amountInProcess'] ?? 0, 2) }}
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
          <div class="card glass-card-hover card-c p-0 h-80 border-0 border-start border-4" style="border-color: var(--secondary) !important;">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-uppercase fw-bold p-0 m-0" style="color: var(--secondary)">
                  Forwarded to Cashier
                </h6>
                <p class="mb-2">
                  <small><i>{{ request('year', now()->year) }}</i></small>
                </p>
                <h2 class="fw-bold fs-2 m-0" style="color: var(--secondary)">
                  ₱{{ number_format($metrics['amountForwarded'] ?? 0, 2) }}
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
          <div class="card glass-card-hover card-c p-0 h-80 border-0 border-start border-4" style="border-color: var(--primary-variant) !important;">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-uppercase fw-bold p-0 m-0" style="color: var(--primary-variant)">
                  Total Amount Paid
                </h6>
                <p class="mb-2">
                  <small><i>{{ request('year', now()->year) }}</i></small>
                </p>
                <h2 class="fw-bold fs-2 m-0" style="color: var(--primary-variant)">
                  ₱{{ number_format($metrics['totalAmountPaid'] ?? 0, 2) }}
                </h2>
              </div>
              <div class="fs-1 opacity-60" style="color: var(--primary-variant);">
                <i class="bi bi-database-fill-check"></i>
              </div>  
            </div>
          </div>
        </div>
      </div>

      {{-- CARD H --}}
      <div class="card glass-card card-h p-3">
        <h6 class="fw-bold mb-0 p-0 text-center text-uppercase" style="color: var(--primary)">
          Workflow Status
        </h6>
        <p class="mb-3 text-center">
          <small><i>{{ request('year', now()->year) }}</i></small>
        </p>

        <div class="row g-3 text-center">
          @php
            $statuses = [
              ['key' => 'pending', 'label' => 'Pending', 'color' => '#9D6B0B', 'bg' => '#FFFBF3'],
              ['key' => 'processing', 'label' => 'Processing', 'color' => '#fd7e14', 'bg' => '#FFF6EF'],
              ['key' => 'returned', 'label' => 'Returned', 'color' => '#6f42c1', 'bg' => '#EFDFFF'],
              ['key' => 'cancelled', 'label' => 'Cancelled', 'color' => 'var(--error)', 'bg' => '#F8E7E9'],
              ['key' => 'forwarded', 'label' => 'Forwarded to Cashier', 'color' => 'var(--primary)', 'bg' => '#E5F2D7'],
              ['key' => 'paid', 'label' => 'Paid', 'color' => 'var(--secondary)', 'bg' => '#EDFADF'],
            ];

            $totalSum = array_sum($metrics['statusCounts'] ?? []);
            $totalSum = $totalSum > 0 ? $totalSum : 1;
          @endphp

          @foreach($statuses as $status)
            @php
              $count = $metrics['statusCounts'][$status['key']] ?? 0;
              $percentage = ($count / $totalSum) * 100;
              $offset = 113 - (113 * $percentage) / 100;
            @endphp
            <div class="col-4">
              <div class="p-0 py-2 border rounded h-100 d-flex flex-column align-items-center justify-content-center" 
                  style="border-color: {{ $status['color'] }} !important; background: {{ $status['bg'] }};">
                <div class="position-relative mb-2" style="width: 60px; height: 60px;">
                  <svg class="w-100 h-100" viewBox="0 0 40 40" style="transform: rotate(-90deg);">
                    <circle cx="20" cy="20" r="18" fill="transparent" stroke="rgba(0,0,0,0.05)" stroke-width="3"></circle>
                    <circle cx="20" cy="20" r="18" fill="transparent" 
                            stroke="{{ $status['color'] }}" 
                            stroke-width="3" 
                            stroke-dasharray="113" 
                            stroke-dashoffset="{{ $offset }}"
                            stroke-linecap="round"
                            style="transition: stroke-dashoffset 0.5s ease-in-out;">
                    </circle>
                  </svg>
                  <div class="position-absolute top-50 start-50 translate-middle fw-bold" style="color: {{ $status['color'] }}; font-size: 1.1rem;">
                    {{ $count }}
                  </div>
                </div>
                 <span class="small text-muted d-block fw-semibold text-center px-1" style="font-size: 0.70rem; color: {{ $status['color'] }} !important; line-height: 1.1;">
                  {{ $status['label'] }}
                </span>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- RIGHT-SIDE COLUMN --}}
    <div class="col-lg-3">
      {{-- CARD B --}}
      <div class="card glass-card-hover card-b p-3 border-0 text-center mb-4">
        <h6 class="fw-bold mb-0 text-uppercase" style="color: var(--primary)">
          Total Transactions
        </h6>
        <p class="mb-0">
          <small><i>{{ request('year', now()->year) }}</i></small>
        </p>
        <h2 class="display-4 fw-bold p-0 m-0" style="color: var(--primary)">
          {{ $metrics['totalTransactions'] ?? 0 }}
        </h2>
      </div>

      {{-- ROW 3/VISUALIZATION CARD --}}
      <div class="card glass-card card-f p-3 m-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold m-0 text-uppercase" style="color: var(--primary)">
            Top 10 Payees Breakdown
          </h6>
        </div>

        <div class="p-1" style="height: 350px; position: relative;">
          <canvas id="payeeChart"></canvas>
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

  const payeeCtx = document.getElementById('payeeChart');
  if (payeeCtx) {
    const payeeAmountsData = {!! json_encode($metrics['payeeAmounts'] ?? json_decode('{}')) !!};
    
    const payeeLabels = Object.keys(payeeAmountsData);
    const payeeData = Object.values(payeeAmountsData);

    if (payeeLabels.length === 0) {
      payeeCtx.style.display = 'none';
      const noDataDiv = document.createElement('div');
      noDataDiv.className = 'text-center py-5 text-muted';
      noDataDiv.innerHTML = '<i class="bi bi-graph-down display-6 d-block mb-2"></i> No data recorded for this filtered timeline.';
      payeeCtx.parentNode.appendChild(noDataDiv);
    } else {
      new Chart(payeeCtx, {
        type: 'bar',
        data: {
          labels: payeeLabels,
          datasets: [{
            label: 'Total Combined Amount (Debit + Credit)',
            data: payeeData,
            backgroundColor: 'rgb(240, 255, 230)',
            borderColor: '#044709',
            borderWidth: 2
          }]
        },
        
        options: {
            indexAxis: 'y',
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
            },

            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + Number(value).toLocaleString();
                        }
                    }
                },
                y: {
                    ticks: {
                        autoSkip: false
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