window.createDonutChart = function(id, value, max, color, title) {
    const ctx = document.getElementById(id);

    if (!ctx) {
        console.error(id + " tidak ditemukan!");
        return;
    }

    const safeMax = Math.max(max, 1); // 🔥 cegah 0
    const safeValue = Math.min(value, safeMax);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [safeValue, safeMax - safeValue],
                backgroundColor: [color, '#eee'],
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '60%',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            }
        },

        plugins: [{
            beforeDraw: function(chart) {
                const { ctx } = chart;

                const { chartArea: { left, right, top, bottom } } = chart;
                const x = (left + right) / 2;
                const y = (top + bottom) / 2;

                const meta = chart.getDatasetMeta(0);
                const innerRadius = meta.data[0].innerRadius;

                ctx.save();

                // 🔥 BACKGROUND TENGAH
                ctx.beginPath();
                ctx.arc(x, y, innerRadius, 0, 2 * Math.PI);
                ctx.fillStyle = "#F3CF7A";
                ctx.fill();

                // 🔥 TEXT STYLE (WAJIB DI ATAS)
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                // 🔥 VALUE (atas)
                ctx.font = '600 16px Arial';
                ctx.fillStyle = '#000';
                ctx.fillText(value, x, y - 8);

                // 🔥 UNIT (bawah)
                ctx.font = '600 13px Arial';
                ctx.fillStyle = '#000';
                ctx.fillText(title, x, y + 10);

                ctx.font = '600 18px Arial'; // angka lebih dominan
                ctx.font = '500 12px Arial'; // unit lebih kecil
                ctx.fillStyle = 'rgba(0,0,0,0.65)'; // unit lebih soft

                ctx.restore();
            }
        }]
    });
}

function initLMHIChart() {
    const ctx = document.getElementById("lmhiPieChart");

    if (!ctx) return;

    const energy = ctx.dataset.energy;
    const protein = ctx.dataset.protein;
    const fat = ctx.dataset.fat;

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Energi', 'Protein', 'Lemak'],
            datasets: [{
                data: [energy, protein, fat],
                backgroundColor: [
                    '#CC561E',
                    '#08448E',
                    '#D6A15E'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
    
        }
    });
    
}

let weeklyChartInstance = null;

// ================= WEEKLY CHART =================

function initWeeklyChart() {
    console.log("DATA:", weeklyData);

    if (!weeklyData || weeklyData.length === 0) {
        console.warn("Data kosong");
        return;
    }

    renderWeeklyChart('energy');
}

function updateChart(type) {
    renderWeeklyChart(type);
}

function renderWeeklyChart(type) {
    const canvas = document.getElementById("weeklyChart");

    if (!canvas) {
        console.error("Canvas weeklyChart ga ketemu!");
        return;
    }

    const ctx = canvas.getContext("2d");

    const sortedData = [...weeklyData].sort((a, b) => {
        return new Date(a.date) - new Date(b.date);
    });

    const labels = sortedData.map(item => item.date);
    const values = sortedData.map(item => item[type]);

    if (weeklyChartInstance) {
        weeklyChartInstance.destroy();
    }

    // ================= WARNA =================
    let color = '#CC561E'; // energy
    if (type === 'protein') color = '#08448E';
    if (type === 'fat') color = '#D6A15E';

    // ================= GRADIENT =================
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);

    gradient.addColorStop(0, hexToRgba(color, 0.5)); // atas
    gradient.addColorStop(1, hexToRgba(color, 0));   // bawah fade

    // ================= CHART =================
    weeklyChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                borderColor: color,
                backgroundColor: gradient, // 🔥 gradient masuk sini
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: color
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

// ================= HELPER =================
function hexToRgba(hex, alpha) {
    const r = parseInt(hex.substring(1, 3), 16);
    const g = parseInt(hex.substring(3, 5), 16);
    const b = parseInt(hex.substring(5, 7), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

// ================= EVENT =================

document.addEventListener("DOMContentLoaded", function () {

    const buttons = document.querySelectorAll('.filter-btn');

    buttons.forEach(button => {
        button.addEventListener('click', function() {

            buttons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const type = this.dataset.type;

            updateChart(type); // 🔥 FIX DISINI
        });
    });

    initWeeklyChart(); // 🔥 WAJIB
});

