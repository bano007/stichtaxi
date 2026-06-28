<?php

if (!isset($conn)) {
    require_once __DIR__ . '/../config.php';
}

$selected_date = $_GET['dashboard_date'] ?? date('Y-m-d');

if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)){
    $selected_date = date('Y-m-d');
}


/*
|--------------------------------------------------------------------------
| Kontrolli i login
|--------------------------------------------------------------------------
*/
if (
    !isset($_SESSION['user_id']) &&
    !isset($_SESSION['logged_in']) &&
    !isset($_SESSION['user'])
) {
    header("Location: login.php");
    exit;
}

$page_title = "Dashboard";

date_default_timezone_set('Europe/Tirane');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($conn) && $conn instanceof mysqli) {
    $conn->set_charset("utf8mb4");
}

/* --------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/
function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function numf($v) {
    return number_format((float)$v, 2);
}

function table_exists(mysqli $conn, string $table): bool {
    $table = $conn->real_escape_string($table);
    $sql = "SHOW TABLES LIKE '{$table}'";
    $res = $conn->query($sql);
    return ($res && $res->num_rows > 0);
}

function table_has_column(mysqli $conn, string $table, string $column): bool {
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $sql = "SHOW COLUMNS FROM `{$table}` LIKE '{$column}'";
    $res = $conn->query($sql);
    return ($res && $res->num_rows > 0);
}

$today       = $selected_date;
$tomorrow    = date('Y-m-d', strtotime('+1 day'));
$weekEnd     = date('Y-m-d', strtotime('+6 day'));
$today_label = date('d.m.Y');

/* --------------------------------------------------------------------------
| Zbulim kolonash reale sipas databazës
|--------------------------------------------------------------------------
*/
$hasHotelsTable  = table_exists($conn, 'hotels');
$hasDriversTable = table_exists($conn, 'drivers');

$hotelDisplayExpr = "''";
if ($hasHotelsTable) {
    if (table_has_column($conn, 'hotels', 'hotel_name')) {
        $hotelDisplayExpr = "COALESCE(h.hotel_name, '')";
    } elseif (table_has_column($conn, 'hotels', 'name')) {
        $hotelDisplayExpr = "COALESCE(h.name, '')";
    } elseif (table_has_column($conn, 'hotels', 'hotel')) {
        $hotelDisplayExpr = "COALESCE(h.hotel, '')";
    } elseif (table_has_column($conn, 'hotels', 'title')) {
        $hotelDisplayExpr = "COALESCE(h.title, '')";
    }
}

$driverDisplayExpr = "CONCAT('Shofer #', t.driver_id)";
if ($hasDriversTable) {
    if (table_has_column($conn, 'drivers', 'name')) {
        $driverDisplayExpr = "COALESCE(d.name, CONCAT('Shofer #', t.driver_id))";
    } elseif (table_has_column($conn, 'drivers', 'full_name')) {
        $driverDisplayExpr = "COALESCE(d.full_name, CONCAT('Shofer #', t.driver_id))";
    } elseif (table_has_column($conn, 'drivers', 'driver_name')) {
        $driverDisplayExpr = "COALESCE(d.driver_name, CONCAT('Shofer #', t.driver_id))";
    } elseif (table_has_column($conn, 'drivers', 'username')) {
        $driverDisplayExpr = "COALESCE(d.username, CONCAT('Shofer #', t.driver_id))";
    }
}

$hasBookingsHotelId = table_has_column($conn, 'bookings', 'hotel_id');
$hasBookingsSource  = table_has_column($conn, 'bookings', 'source');

$bookingSourceExpr = $hasBookingsSource ? "COALESCE(b.source, '')" : "''";

/* --------------------------------------------------------------------------
| KPI Sot
|--------------------------------------------------------------------------
*/
$kpi_trips = [
    'cnt'       => 0,
    'sum_price' => 0,
    'sum_comm'  => 0,
    'sum_total' => 0
];

$sqlTrips = "
    SELECT
        COUNT(*) AS cnt,
        COALESCE(SUM(price), 0) AS sum_price,
        COALESCE(SUM(commission), 0) AS sum_comm,
        COALESCE(SUM(total), 0) AS sum_total
    FROM trips
    WHERE trip_date = '{$today}'
";
$q = $conn->query($sqlTrips);
if ($q && $row = $q->fetch_assoc()) {
    $kpi_trips = $row;
}

$kpi_expenses = 0.0;
$sqlExpenses = "
    SELECT COALESCE(SUM(amount), 0) AS s
    FROM expenses
    WHERE expense_date = '{$today}'
";
$q = $conn->query($sqlExpenses);
if ($q && $row = $q->fetch_assoc()) {
    $kpi_expenses = (float)($row['s'] ?? 0);
}

$to_deliver = ((float)$kpi_trips['sum_price'] + (float)$kpi_trips['sum_comm']) - (float)$kpi_expenses;

/* --------------------------------------------------------------------------
| Prenotime në pritje
|--------------------------------------------------------------------------
*/
$pending_today = 0;
$pending_tomorrow = 0;
$pending_week = 0;

$sqlPendingCounts = "
    SELECT booking_date, COUNT(*) AS c
    FROM bookings
    WHERE status = 'pending'
      AND booking_date BETWEEN '{$today}' AND '{$weekEnd}'
    GROUP BY booking_date
";
$q = $conn->query($sqlPendingCounts);
if ($q) {
    while ($r = $q->fetch_assoc()) {
        $d = (string)($r['booking_date'] ?? '');
        $c = (int)($r['c'] ?? 0);

        if ($d === $today) {
            $pending_today = $c;
        }
        if ($d === $tomorrow) {
            $pending_tomorrow = $c;
        }
        $pending_week += $c;
    }
}

if ($hasHotelsTable && $hasBookingsHotelId) {
    $sqlBookings = "
        SELECT
            b.id,
            b.booking_date,
            b.booking_time,
            b.customer_name,
            b.phone,
            b.pickup,
            b.destination,
            COALESCE(b.expected_price, 0) AS expected_price,
            {$bookingSourceExpr} AS source,
            {$hotelDisplayExpr} AS hotel_name
        FROM bookings b
        LEFT JOIN hotels h ON h.id = b.hotel_id
        WHERE b.status = 'pending'
          AND b.booking_date IN ('{$today}', '{$tomorrow}')
        ORDER BY b.booking_date ASC, b.booking_time ASC, b.id ASC
        LIMIT 12
    ";
} else {
    $sqlBookings = "
        SELECT
            b.id,
            b.booking_date,
            b.booking_time,
            b.customer_name,
            b.phone,
            b.pickup,
            b.destination,
            COALESCE(b.expected_price, 0) AS expected_price,
            {$bookingSourceExpr} AS source,
            '' AS hotel_name
        FROM bookings b
        WHERE b.status = 'pending'
          AND b.booking_date IN ('{$today}', '{$tomorrow}')
        ORDER BY b.booking_date ASC, b.booking_time ASC, b.id ASC
        LIMIT 12
    ";
}
$bookings = $conn->query($sqlBookings);

/* --------------------------------------------------------------------------
| Shoferë aktiv / jo aktiv
|--------------------------------------------------------------------------
*/
$drivers_active = 0;
$drivers_inactive = 0;

if ($hasDriversTable && table_has_column($conn, 'drivers', 'is_active')) {
    $sqlDrivers = "SELECT SUM(is_active = 1) AS a, SUM(is_active = 0) AS i FROM drivers";
    $q = $conn->query($sqlDrivers);
    if ($q && $r = $q->fetch_assoc()) {
        $drivers_active   = (int)($r['a'] ?? 0);
        $drivers_inactive = (int)($r['i'] ?? 0);
    }
}

/* --------------------------------------------------------------------------
| Grafik 7 ditë
|--------------------------------------------------------------------------
*/
$days = [];
$vals = [];
$map  = [];

$sqlChart = "
    SELECT trip_date AS d, COALESCE(SUM(total), 0) AS s
    FROM trips
    WHERE trip_date >= DATE_SUB('{$today}', INTERVAL 6 DAY)
      AND trip_date <= '{$today}'
    GROUP BY trip_date
    ORDER BY trip_date ASC
";
$q = $conn->query($sqlChart);
if ($q) {
    while ($r = $q->fetch_assoc()) {
        $map[$r['d']] = (float)$r['s'];
    }
}

for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime($today . " -{$i} day"));
    $days[] = $d;
    $vals[] = $map[$d] ?? 0;
}

/* --------------------------------------------------------------------------
| Top shoferi sot
|--------------------------------------------------------------------------
*/
$top_driver_name = '—';
$top_driver_total = 0.0;

if ($hasDriversTable && table_has_column($conn, 'drivers', 'id') && table_has_column($conn, 'trips', 'driver_id')) {
    $sqlTopDriver = "
        SELECT
            {$driverDisplayExpr} AS driver_name,
            COALESCE(SUM(t.total), 0) AS total_sum
        FROM trips t
        LEFT JOIN drivers d ON d.id = t.driver_id
        WHERE t.trip_date = '{$today}'
        GROUP BY t.driver_id, driver_name
        ORDER BY total_sum DESC
        LIMIT 1
    ";
} else {
    $sqlTopDriver = "
        SELECT
            '—' AS driver_name,
            0 AS total_sum
        LIMIT 1
    ";
}
$q = $conn->query($sqlTopDriver);
if ($q && $r = $q->fetch_assoc()) {
    $top_driver_name  = (string)($r['driver_name'] ?? '—');
    $top_driver_total = (float)($r['total_sum'] ?? 0);
}

/* --------------------------------------------------------------------------
| Rruga më e përdorur sot
|--------------------------------------------------------------------------
*/
$top_route = '—';

$hasTripsPickup      = table_has_column($conn, 'trips', 'pickup');
$hasTripsDestination = table_has_column($conn, 'trips', 'destination');

if ($hasTripsPickup && $hasTripsDestination) {
    $sqlTopRoute = "
        SELECT
            CONCAT(COALESCE(pickup, ''), ' → ', COALESCE(destination, '')) AS route_name,
            COUNT(*) AS c
        FROM trips
        WHERE trip_date = '{$today}'
          AND COALESCE(pickup, '') <> ''
          AND COALESCE(destination, '') <> ''
        GROUP BY pickup, destination
        ORDER BY c DESC, route_name ASC
        LIMIT 1
    ";
    $q = $conn->query($sqlTopRoute);
    if ($q && $r = $q->fetch_assoc()) {
        $top_route = (string)($r['route_name'] ?? '—');
    }
}

/* --------------------------------------------------------------------------
| Top hotel (nga bookings në javë)
|--------------------------------------------------------------------------
*/
$top_hotel = '—';
$top_hotel_count = 0;

if ($hasHotelsTable && $hasBookingsHotelId) {
    $sqlTopHotel = "
        SELECT
            {$hotelDisplayExpr} AS hotel_name,
            COUNT(*) AS c
        FROM bookings b
        LEFT JOIN hotels h ON h.id = b.hotel_id
        WHERE b.booking_date BETWEEN '{$today}' AND '{$weekEnd}'
          AND COALESCE(b.hotel_id, 0) > 0
        GROUP BY b.hotel_id, hotel_name
        ORDER BY c DESC, hotel_name ASC
        LIMIT 1
    ";
    $q = $conn->query($sqlTopHotel);
    if ($q && $r = $q->fetch_assoc()) {
        $top_hotel = (string)($r['hotel_name'] ?? '—');
        $top_hotel_count = (int)($r['c'] ?? 0);
    }
}
