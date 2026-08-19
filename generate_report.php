<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ChargingSession;
use Carbon\Carbon;

$sessions = ChargingSession::where('status', 'Completed')->get();

$dailyStats = [];
$hourlyStats = array_fill(0, 24, 0);

$totalRevenue = 0;
$totalKwh = 0;
$totalSessions = 0;

foreach ($sessions as $s) {
    if (!$s->start_time) continue;
    
    $start = Carbon::parse($s->start_time)->setTimezone('America/La_Paz');
    $date = $start->format('Y-m-d');
    $hour = (int) $start->format('G');
    
    if (!isset($dailyStats[$date])) {
        $dailyStats[$date] = ['sessions' => 0, 'users' => [], 'revenue' => 0, 'kwh' => 0];
    }
    
    $dailyStats[$date]['sessions']++;
    if ($s->user_id) $dailyStats[$date]['users'][] = $s->user_id;
    $dailyStats[$date]['revenue'] += (float) $s->total_cost;
    $dailyStats[$date]['kwh'] += (float) $s->total_energy_kwh;
    
    $hourlyStats[$hour]++;
    $totalRevenue += (float) $s->total_cost;
    $totalKwh += (float) $s->total_energy_kwh;
    $totalSessions++;
}

$daysCount = count($dailyStats) ?: 1;
$avgDailySessions = $totalSessions / $daysCount;
$avgDailyRevenue = $totalRevenue / $daysCount;
$avgDailyKwh = $totalKwh / $daysCount;

$totalUniqueCarsPerDay = 0;
$dates = array_keys($dailyStats);
sort($dates);

$chartLabels = []; $chartSessions = []; $chartRevenue = []; $chartKwh = []; $chartCars = [];

foreach ($dates as $date) {
    $stats = &$dailyStats[$date];
    $stats['unique_users_count'] = count(array_unique($stats['users']));
    $totalUniqueCarsPerDay += $stats['unique_users_count'];
    
    $chartLabels[] = $date;
    $chartSessions[] = $stats['sessions'];
    $chartRevenue[] = round($stats['revenue'], 2);
    $chartKwh[] = round($stats['kwh'], 2);
    $chartCars[] = $stats['unique_users_count'];
}

$avgDailyCars = $totalUniqueCarsPerDay / $daysCount;

$html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Reporte EVCE</title><script src="https://cdn.jsdelivr.net/npm/chart.js"></script><style>body { font-family: sans-serif; background: #f3f4f6; padding: 20px; } .container { max-width: 1200px; margin: auto; background: white; padding: 30px; border-radius: 10px; } .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; } .card { background: #eff6ff; padding: 20px; border-radius: 8px; text-align: center; } .card p { font-size: 24px; font-weight: bold; color: #1d4ed8; } .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; } .chart-container { margin-bottom: 40px; }</style></head><body><div class="container"><h1>Análisis de Afluencia e Ingresos - EVCE</h1><div class="summary-grid"><div class="card"><h3>Transacciones</h3><p>'.number_format($totalSessions).'</p></div><div class="card"><h3>Afluencia Diaria</h3><p>'.number_format($avgDailySessions,1).'</p></div><div class="card"><h3>Autos Diarios</h3><p>'.number_format($avgDailyCars,1).'</p></div><div class="card"><h3>Ingreso Diario</h3><p>BOB '.number_format($avgDailyRevenue,2).'</p></div><div class="card"><h3>Energía Diaria</h3><p>'.number_format($avgDailyKwh,1).' kWh</p></div></div><div class="chart-container"><h2>Evolución</h2><canvas id="revenueChart" height="100"></canvas></div><div class="charts-grid"><div class="chart-container"><h2>Afluencia</h2><canvas id="influxChart"></canvas></div><div class="chart-container"><h2>Horas Pico</h2><canvas id="peakChart"></canvas></div></div></div><script>const labels='.json_encode($chartLabels).'; const revenueData='.json_encode($chartRevenue).'; const kwhData='.json_encode($chartKwh).'; const sessionsData='.json_encode($chartSessions).'; const carsData='.json_encode($chartCars).'; const hourlyData='.json_encode(array_values($hourlyStats)).'; const hourLabels=["00:00","01:00","02:00","03:00","04:00","05:00","06:00","07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00","21:00","22:00","23:00"]; new Chart(document.getElementById("revenueChart"), { type: "line", data: { labels: labels, datasets: [ { label: "Ingresos (BOB)", data: revenueData, borderColor: "#10b981", yAxisID: "y" }, { label: "Energía (kWh)", data: kwhData, borderColor: "#3b82f6", yAxisID: "y1" } ] }, options: { scales: { y: { type: "linear", position: "left" }, y1: { type: "linear", position: "right" } } } }); new Chart(document.getElementById("influxChart"), { type: "bar", data: { labels: labels, datasets: [ { label: "Sesiones", data: sessionsData, backgroundColor: "#6366f1" }, { label: "Vehículos", data: carsData, backgroundColor: "#a855f7" } ] } }); new Chart(document.getElementById("peakChart"), { type: "bar", data: { labels: hourLabels, datasets: [{ label: "Frecuencia", data: hourlyData, backgroundColor: "#f59e0b" }] } });</script></body></html>';

file_put_contents(__DIR__ . '/public/Reporte_EVCE.html', $html);
echo "OK\n";
