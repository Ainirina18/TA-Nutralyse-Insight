let dailyChart;
let currentType = 'energy';

function renderDailyChart(type = 'energy') {

    const labels = dailyData.map(item => item.date);
    const values = dailyData.map(item => item[type]);

    // warna tiap nutrisi
    const colors = {

        energy: {
            border: '#FFD83D',
            bg: '#FEF2C0'
        },

        protein: {
            border: '#9FB608',
            bg: '#E0EB99'
        },

        fat: {
            border: '#AA2B1D',
            bg:'#FFB8B0'
        }
    };

    // destroy chart lama
    if (dailyChart) {
        dailyChart.destroy();
    }

    const ctx = document.getElementById('dailyChart');

    dailyChart = new Chart(ctx, {

        type: 'line',

        data: {
            labels: labels,

            datasets: [{
                label: type,
                data: values,

                fill: true,
                tension: 0.4,
                cubicInterpolationMode: 'monotone',

                borderWidth: 2,

                borderColor: colors[type].border,
                backgroundColor: colors[type].bg,

                pointRadius: 2,
                pointHoverRadius: 3,

                pointBackgroundColor: colors[type].border,
                pointBorderColor: '#fff',
                pointHoverBorderWidth: 2
            }]
        },

        options: {

            responsive: true,

            plugins: {
                legend: {
                    display: false
                }
            },

            scales: {

                x: {
                    grid: {
                        display: false
                    },

                    ticks: {
                        maxRotation: 0
                    }
                },

                y: {
                    beginAtZero: true,

                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            }
        }
    });
}

document.querySelectorAll('.filter-btn').forEach(btn => {

    btn.addEventListener('click', function () {

        // active toggle
        document.querySelectorAll('.filter-btn')
            .forEach(b => b.classList.remove('active'));

        this.classList.add('active');

        const type = this.dataset.type;

        renderDailyChart(type);
    });
});

document.addEventListener("DOMContentLoaded", function () {
    renderDailyChart('energy');
});

// weekly grafik batang

let proteinChart;
let fatChart;
let energyChart;

function createBarChart(canvasId, label, labels, data, color) {

    const ctx = document.getElementById(canvasId);

    const chartCtx = ctx.getContext('2d');

    const gradient = chartCtx.createLinearGradient(0, 0, 0, 220);

    gradient.addColorStop(0, color.top);
    gradient.addColorStop(1, color.bottom);

    return new Chart(ctx, {

        type: 'bar',

        data: {
            labels: labels,

            datasets: [{
                label: label,

                data: data,

                backgroundColor: gradient,

                borderRadius: 4,
                borderSkipped: false,

                maxBarThickness: 20,

                categoryPercentage: 0.9,
                barPercentage: 0.95,
            }]
        },

        options: {

            responsive: true,
            maintainAspectRatio: false,

            layout: {
                padding: {
                    left: 10,
                    right: 10
                }
            },

            plugins: {

                legend: {
                    display: false
                },

                title: {
                    display: true,
                    text: label
                }
            },

            scales: {

                x: {

                    offset: true,

                    grid: {
                        display: false
                    }
                },

                y: {

                    beginAtZero: true,

                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            }
        }
    });
}
function renderWeeklyCharts(weekIndex = 0) {

    if (!weeklyData.length) return;

    const week = weeklyData[weekIndex];

    if (!week) return;

    const labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

    // destroy chart lama
    if (proteinChart) proteinChart.destroy();
    if (fatChart) fatChart.destroy();
    if (energyChart) energyChart.destroy();

    proteinChart = createBarChart(
        'proteinChart',
        `Protein Mingguan - ${week.week}`,
        labels,
        week.protein_daily,
        {
            top: '#9FB608',
            bottom: '#F1FF9A'
        }
    );

   fatChart = createBarChart(
        'fatChart',
        `Lemak Mingguan - ${week.week}`,
        labels,
        week.fat_daily,
        {
            top: '#AA2B1D',
            bottom: '#FFB8B0'
        }
    );

    energyChart = createBarChart(
        'energyChart',
        `Energi Mingguan - ${week.week}`,
        labels,
        week.energy_daily,
        {
            top: '#FFD83D',
            bottom: '#FEF2C0'
        }
    );
}

// dropdown week
document.getElementById('weekFilter')
    .addEventListener('change', function () {

        renderWeeklyCharts(this.selectedIndex);
});

//init

document.addEventListener("DOMContentLoaded", function () {

    renderDailyChart('energy');

    renderWeeklyCharts(0);
});

document.getElementById('pdfForm')
.addEventListener('submit', function () {

    document.getElementById('energy_chart').value =
        energyChart.toBase64Image();

    document.getElementById('protein_chart').value =
        proteinChart.toBase64Image();

    document.getElementById('fat_chart').value =
        fatChart.toBase64Image();

    // ⚠️ INI FIX PENTING
    const weeklyImages = [];

    document.querySelectorAll('.weekly-chart-card canvas').forEach((canvas) => {
        weeklyImages.push(canvas.toDataURL('image/png'));
    });

    console.log("weeklyImages:", weeklyImages); // DEBUG WAJIB

    document.getElementById('weekly_charts').value =
        JSON.stringify(weeklyImages);

});


/*weekly chart PDF*/
async function generateWeeklyChartsForPdf() {

    const weeklyImages = [];

    for (let i = 0; i < weeklyData.length; i++) {

        renderWeeklyCharts(i);

        // tunggu chart render
        await new Promise(resolve => setTimeout(resolve, 300));

        weeklyImages.push({
            energy: energyChart.toBase64Image(),
            protein: proteinChart.toBase64Image(),
            fat: fatChart.toBase64Image(),
        });
    }

    return weeklyImages;
}


/*daily chart PDF*/
async function generateDailyChartsForPdf() {

    const charts = {};

    const types = ['energy', 'protein', 'fat'];

    for (const type of types) {

        renderDailyChart(type);

        await new Promise(resolve => setTimeout(resolve, 300));

        charts[type] = dailyChart.toBase64Image();
    }

    return charts;
}

/* ===========================================
   MOBILE ACCORDION
=========================================== */

document.addEventListener("DOMContentLoaded", () => {

    if(window.innerWidth > 768) return;

    const items = document.querySelectorAll(".evaluation-section");

    items.forEach((item,index)=>{

        const title=item.querySelector("h3");

        if(index===0){

            item.classList.add("active");

        }

        title.addEventListener("click",()=>{

            if(item.classList.contains("active")){

                item.classList.remove("active");

                return;

            }

            items.forEach(i=>i.classList.remove("active"));

            item.classList.add("active");

        });

    });

});

