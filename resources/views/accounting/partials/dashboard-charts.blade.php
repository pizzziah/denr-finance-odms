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
            label: 'Total Combined Amount (Debit)',
            data: payeeData,
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