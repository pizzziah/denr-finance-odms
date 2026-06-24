@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 px-4">
  <div class="row g-4">

    <!-- LEFT SIDE -->
    <div class="col-12 col-lg-9 d-flex flex-column gap-3">

      <!-- Welcome Card -->
      <div class="card glass-card-green card-a p-4" style="color: var(--background);">
        <div class="card-body">
          <h4 class="fw-bold mb-1">
            Welcome Back,
            {{ ucwords(str_replace('_', ' ', $currentUser->role ?? 'Admin')) }}!
          </h4>

          <h6 class="date mb-0 opacity-75">
            <i class="bi bi-calendar3 me-2"></i>
            {{ now()->format('F d, Y') }}
          </h6>
        </div>
      </div>

      <!-- Metric Cards -->
      <div class="row g-3 m-0 p-0">

        <!-- Total Accounts -->
        <div class="col-12 col-md-4 ps-0">
          <div class="card glass-card shadow p-2 m-0 rounded">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-uppercase mb-1 small fw-bold">
                  Total Accounts
                </h6>

                <h2 class="fw-bold fs-1 mb-0">
                  {{ $metrics['total_users'] }}
                </h2>
              </div>

              <div class="fs-1 opacity-60" style="color: var(--primary);">
                <i class="bi bi-people-fill"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Sessions -->
        <div class="col-12 col-md-4">
          <div class="card glass-card shadow p-2 m-0 rounded">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-uppercase mb-1 small fw-bold">
                  Active Sessions
                </h6>

                <h2 class="fw-bold fs-1 mb-0">
                  {{ $metrics['active_users'] }}
                </h2>
              </div>

              <div class="fs-1 opacity-60" style="color: #9D6B0B;">
                <i class="bi bi-person-check-fill"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Deactivated Accounts -->
        <div class="col-12 col-md-4 pe-0">
          <div class="card glass-card shadow p-2 m-0 rounded">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <h6 class="text-uppercase mb-1 small fw-bold">
                  Deactivated Accounts
                </h6>

                <h2 class="fw-bold fs-1 mb-0">
                  {{ $metrics['inactive_users'] }}
                </h2>
              </div>

              <div class="fs-1 opacity-60" style="color: var(--error);">
                <i class="bi bi-person-x-fill"></i>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="col-12 col-lg-3">
      <div class="card shadow glass-card p-2 m-0 rounded">

        <h4 class="fw-bold mb-0 p-3 border-0">
          <i class="bi bi-pie-chart-fill me-2"
             style="color: var(--primary);"></i>
          User Distribution
        </h4>

        <div class="card-body d-flex align-items-center justify-content-center pt-0">

          @if(count($metrics['by_department']) > 0)
            <div style="width: 100%; max-width: 240px; margin: 0 auto;">
              <canvas id="deptDistributionChart"></canvas>
            </div>
          @else
            <div class="text-center py-4 small">
              <i class="bi bi-exclamation-circle d-block mb-2 fs-4"></i>
              No data located.
            </div>
          @endif

        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const ctx = document.getElementById('deptDistributionChart');

    if (!ctx) return;

    const chartLabels =
        {!! json_encode(
            $metrics['by_department']->map(
                fn($d) => $d->department === 'Admin'
                    ? 'System Admin'
                    : $d->department
            )
        ) !!};

    const chartData =
        {!! json_encode($metrics['by_department']->pluck('total')) !!};

    new Chart(ctx, {
        type: 'doughnut',

        data: {
            labels: chartLabels,

            datasets: [{
                data: chartData,

                backgroundColor: [
                    '#4CAF50',
                    '#0B879D',
                    '#ffc107',
                    '#dc3545'
                ],

                borderWidth: 1,
                borderColor: 'transparent'
            }]
        },

        options: {
            responsive: true,

            plugins: {
                legend: {
                    position: 'bottom',

                    labels: {
                        boxWidth: 12,

                        font: {
                            size: 11,
                            family: 'Montserrat'
                        },

                        padding: 15
                    }
                },

                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw} user(s)`;
                        }
                    }
                }
            }
        }
    });

});
</script>
@endsection

@php
    $pageTitle = 'Dashboard';
@endphp