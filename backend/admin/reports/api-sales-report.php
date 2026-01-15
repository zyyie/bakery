<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(dirname(__DIR__) . "/includes/header.php");

// Get date range from POST or use defaults
$fromDate = isset($_POST['fromDate']) ? trim($_POST['fromDate']) : date('Y-m-01');
$toDate = isset($_POST['toDate']) ? trim($_POST['toDate']) : date('Y-m-d');

// Validate date format
$validDates = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate);
// Initialize here to avoid undefined variable when $validDates is false
$hasSourceColumn = false;

// Fetch School Cafeteria API data (Live)
$canteenData = null;
$canteenError = null;
$canteen_ip = "192.168.1.100";
$canteen_url = "http://$canteen_ip/Finals_SCHOOLCANTEEN/api/get_vendor_sales.php?api_key=CARNICK-CANTEEN-2026";

// If the cafeteria API supports date filtering, pass it (safe if ignored)
if ($validDates) {
    $canteen_url .= '&from=' . rawurlencode($fromDate) . '&to=' . rawurlencode($toDate);
}

// Fetch with cURL and HTTPS fallback
$canteenHttpCode = null;
$curlErr = null;
$responseBody = null;

$tryUrls = [
    $canteen_url,
    // HTTPS fallback (ignore self-signed for connectivity testing)
    preg_replace('/^http:/i', 'https:', $canteen_url)
];

foreach ($tryUrls as $idx => $url) {
    if (!$url) continue;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_HEADER => false,
    ]);
    if (stripos($url, 'https://') === 0) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: null;
    $errNo = curl_errno($ch);
    $errStr = curl_error($ch);
    curl_close($ch);

    if ($body !== false && $code) {
        $responseBody = $body;
        $canteenHttpCode = (int)$code;
        break;
    }

    $curlErr = "cURL error #$errNo: $errStr";
}

if ($responseBody !== null && $canteenHttpCode && $canteenHttpCode >= 200 && $canteenHttpCode < 300) {
    $canteenData = json_decode($responseBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $canteenError = "Invalid JSON response from cafeteria API";
        $canteenData = null;
    }
} else {
    // Single concise diagnostic; avoid redundant duplicate probes
    $errno = 0; $errstr = '';
    $fp = @fsockopen($canteen_ip, 80, $errno, $errstr, 2.0);
    if ($fp) { fclose($fp); }
    $sockHint = $fp ? '' : (" Network/port check failed (" . (string)$errno . ": " . (string)$errstr . ")");
    $canteenError = "Can't connect to the cafeteria server." . ($sockHint !== '' ? $sockHint : '') . ($curlErr ? " Details: " . $curlErr : '');
}

$canteenConnected = (bool)($canteenData && isset($canteenData['success']) && $canteenData['success']);
$canteenVendor = $canteenConnected ? (string)($canteenData['vendor'] ?? 'Cafeteria') : '';
$canteenRevenue = $canteenConnected ? (float)($canteenData['total_revenue'] ?? 0) : 0.0;
$canteenOrders = $canteenConnected ? (int)(isset($canteenData['detailed_logs']) && is_array($canteenData['detailed_logs']) ? count($canteenData['detailed_logs']) : 0) : 0;

// Filter cafeteria logs by selected date range (so the report reflects the chosen period)
$canteenLogsAll = ($canteenConnected && isset($canteenData['detailed_logs']) && is_array($canteenData['detailed_logs'])) ? $canteenData['detailed_logs'] : [];
$canteenLogs = $canteenLogsAll;
$canteenRevenueFiltered = $canteenRevenue;
$canteenOrdersFiltered = $canteenOrders;
if ($validDates && !empty($canteenLogsAll)) {
    $fromTs = strtotime($fromDate . ' 00:00:00');
    $toTs = strtotime($toDate . ' 23:59:59');

    $filtered = [];
    $sum = 0.0;

    foreach ($canteenLogsAll as $log) {
        $createdAt = (string)($log['created_at'] ?? '');
        $ts = $createdAt !== '' ? strtotime($createdAt) : false;
        if ($ts === false) {
            continue;
        }
        if ($ts < $fromTs || $ts > $toTs) {
            continue;
        }
        $filtered[] = $log;

        // Prefer total_amount, otherwise compute quantity*price
        if (isset($log['total_amount'])) {
            $sum += (float)$log['total_amount'];
        } else {
            $qty = isset($log['quantity']) ? (float)$log['quantity'] : 0.0;
            $price = isset($log['price']) ? (float)$log['price'] : 0.0;
            $sum += ($qty * $price);
        }
    }

    $canteenLogs = $filtered;
    $canteenOrdersFiltered = count($filtered);
    $canteenRevenueFiltered = $sum;
}

// Initialize sales data
$salesData = [
    'carnick' => [
        'totalSales' => 0.00,
        'totalOrders' => 0,
        'averageOrder' => 0.00
    ],
    'karneek' => [
        'totalSales' => 0.00,
        'totalOrders' => 0,
        'averageOrder' => 0.00
    ],
    'all' => [
        'totalSales' => 0.00,
        'totalOrders' => 0,
        'averageOrder' => 0.00
    ]
];

// Use API data for school cafeteria if available
if ($canteenData && isset($canteenData['success']) && $canteenData['success']) {
    // Use filtered totals so the report reflects the selected date range
    $salesData['carnick']['totalSales'] = $validDates ? (float)$canteenRevenueFiltered : (isset($canteenData['total_revenue']) ? (float)$canteenData['total_revenue'] : 0.00);
    $salesData['carnick']['totalOrders'] = $validDates ? (int)$canteenOrdersFiltered : (isset($canteenData['detailed_logs']) ? count($canteenData['detailed_logs']) : 0);
    $salesData['carnick']['averageOrder'] = $salesData['carnick']['totalOrders'] > 0 
        ? $salesData['carnick']['totalSales'] / $salesData['carnick']['totalOrders'] 
        : 0.00;
}

if ($validDates) {
    // School Cafeteria data is already fetched from API above
    // Only query KARNEEK bakery sales from database
    
    // Check if source column exists
    $hasSourceColumn = false;
    $colCheck = executePreparedQuery("SHOW COLUMNS FROM orders LIKE 'source'", "", []);
    if ($colCheck && mysqli_num_rows($colCheck) > 0) {
        $hasSourceColumn = true;
    }
    
    if ($hasSourceColumn) {
        // Query for KARNEEK (whole bakery) sales
        $karneekQuery = "SELECT 
                            COUNT(DISTINCT o.orderID) as totalOrders,
                            COALESCE(SUM(oi.totalPrice), 0) as totalSales
                         FROM orders o
                         LEFT JOIN order_items oi ON o.orderID = oi.orderID
                         WHERE (o.source = 'KARNEEK' OR o.source IS NULL OR o.source = '')
                         AND DATE(o.orderDate) BETWEEN ? AND ?
                         AND o.orderStatus != 'Cancelled'";
        
        $karneekResult = executePreparedQuery($karneekQuery, "ss", [$fromDate, $toDate]);
        if ($karneekResult && $row = $karneekResult->fetch_assoc()) {
            $salesData['karneek']['totalOrders'] = (int)$row['totalOrders'];
            $salesData['karneek']['totalSales'] = (float)$row['totalSales'];
            $salesData['karneek']['averageOrder'] = $salesData['karneek']['totalOrders'] > 0 
                ? $salesData['karneek']['totalSales'] / $salesData['karneek']['totalOrders'] 
                : 0.00;
        }
    } else {
        // If source column doesn't exist, all orders are considered KARNEEK
        $allQuery = "SELECT 
                        COUNT(DISTINCT o.orderID) as totalOrders,
                        COALESCE(SUM(oi.totalPrice), 0) as totalSales
                     FROM orders o
                     LEFT JOIN order_items oi ON o.orderID = oi.orderID
                     WHERE DATE(o.orderDate) BETWEEN ? AND ?
                     AND o.orderStatus != 'Cancelled'";
        
        $allResult = executePreparedQuery($allQuery, "ss", [$fromDate, $toDate]);
        if ($allResult && $row = $allResult->fetch_assoc()) {
            $salesData['karneek']['totalOrders'] = (int)$row['totalOrders'];
            $salesData['karneek']['totalSales'] = (float)$row['totalSales'];
            $salesData['karneek']['averageOrder'] = $salesData['karneek']['totalOrders'] > 0 
                ? $salesData['karneek']['totalSales'] / $salesData['karneek']['totalOrders'] 
                : 0.00;
        }
    }
    
    // Calculate totals (school cafeteria already calculated from API)
    $salesData['all']['totalOrders'] = $salesData['carnick']['totalOrders'] + $salesData['karneek']['totalOrders'];
    $salesData['all']['totalSales'] = $salesData['carnick']['totalSales'] + $salesData['karneek']['totalSales'];
    $salesData['all']['averageOrder'] = $salesData['all']['totalOrders'] > 0 
        ? $salesData['all']['totalSales'] / $salesData['all']['totalOrders'] 
        : 0.00;
}
?>

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">API Sales Report</h2>
</div>

<div class="card mb-4">
  <div class="card-body">
    <?php if ($canteenConnected): ?>
      <div class="alert alert-success mb-0">
        <strong>Cafeteria API:</strong> Connected
        <?php if ($canteenHttpCode !== null): ?> (HTTP <?php echo (int)$canteenHttpCode; ?>)<?php endif; ?>
        <br>
        <strong>Vendor:</strong> <?php echo htmlspecialchars($canteenVendor); ?>
        <br>
        <strong>Total revenue:</strong> ₱<?php echo number_format($canteenRevenue, 2); ?>
        <br>
        <strong>Orders:</strong> <?php echo (int)$canteenOrders; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-danger mb-0">
        <strong>Cafeteria API:</strong> Not connected
        <?php if ($canteenHttpCode !== null): ?> (HTTP <?php echo (int)$canteenHttpCode; ?>)<?php endif; ?>
        <br>
        <?php echo htmlspecialchars((string)($canteenError ?: 'Unknown error')); ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="card mb-4">
  <div class="card-body">
    <form method="POST" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">From Date:</label>
        <input type="date" class="form-control" name="fromDate" value="<?php echo htmlspecialchars($fromDate); ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">To Date:</label>
        <input type="date" class="form-control" name="toDate" value="<?php echo htmlspecialchars($toDate); ?>" required>
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn btn-primary">Generate Report</button>
      </div>
    </form>
  </div>
</div>

<?php if ($validDates): ?>
<div class="row mb-4">
  <!-- School Cafeteria API Sales -->
  <div class="col-md-6 mb-4">
    <div class="card stat-card" style="border-left: 4px solid #28a745;">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label text-muted">School Cafeteria API</h5>
            <h5 class="mb-2" style="color: #28a745; font-weight: 600;">School Cafeteria <span class="badge bg-success">Live</span></h5>
            <?php if ($canteenData && isset($canteenData['success']) && $canteenData['success']): ?>
              <h2 class="stat-number" style="color: #28a745;">₱<?php echo number_format($salesData['carnick']['totalSales'], 2); ?></h2>
              <p class="mb-1 text-muted small">Total Orders: <strong><?php echo $salesData['carnick']['totalOrders']; ?></strong></p>
              <p class="mb-0 text-muted small">Average Order: <strong>₱<?php echo number_format($salesData['carnick']['averageOrder'], 2); ?></strong></p>
            <?php else: ?>
              <h2 class="stat-number" style="color: #28a745;">₱0.00</h2>
              <p class="mb-1 text-danger small"><i class="fas fa-exclamation-triangle"></i> API Connection Error</p>
              <p class="mb-0 text-muted small"><?php echo htmlspecialchars($canteenError ?? 'Unable to fetch data'); ?></p>
            <?php endif; ?>
          </div>
          <div class="stat-icon" style="color: #28a745;">
            <i class="fas fa-school fa-3x"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Whole Bakery (KARNEEK) Sales -->
  <div class="col-md-6 mb-4">
    <div class="card stat-card" style="border-left: 4px solid #8B4513;">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label text-muted">Whole Bakery</h5>
            <h5 class="mb-2" style="color: #8B4513; font-weight: 600;">KARNEEK Bakery</h5>
            <h2 class="stat-number" style="color: #8B4513;">₱<?php echo number_format($salesData['karneek']['totalSales'], 2); ?></h2>
            <p class="mb-1 text-muted small">Total Orders: <strong><?php echo $salesData['karneek']['totalOrders']; ?></strong></p>
            <p class="mb-0 text-muted small">Average Order: <strong>₱<?php echo number_format($salesData['karneek']['averageOrder'], 2); ?></strong></p>
          </div>
          <div class="stat-icon" style="color: #8B4513;">
            <i class="fas fa-store fa-3x"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Summary Card -->
<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title mb-4">Summary</h5>
    <div class="row">
      <div class="col-md-4 text-center">
        <div class="p-3">
          <h6 class="text-muted mb-2">Total Sales (All Sources)</h6>
          <h3 class="mb-0" style="color: #007bff; font-weight: 600;">₱<?php echo number_format($salesData['all']['totalSales'], 2); ?></h3>
        </div>
      </div>
      <div class="col-md-4 text-center">
        <div class="p-3">
          <h6 class="text-muted mb-2">Total Orders (All Sources)</h6>
          <h3 class="mb-0" style="color: #007bff; font-weight: 600;"><?php echo $salesData['all']['totalOrders']; ?></h3>
        </div>
      </div>
      <div class="col-md-4 text-center">
        <div class="p-3">
          <h6 class="text-muted mb-2">Average Order Value</h6>
          <h3 class="mb-0" style="color: #007bff; font-weight: 600;">₱<?php echo number_format($salesData['all']['averageOrder'], 2); ?></h3>
        </div>
      </div>
    </div>
    
    <?php if ($salesData['all']['totalSales'] > 0): ?>
    <div class="row mt-4">
      <div class="col-md-6">
        <h6 class="text-muted mb-3">Sales Distribution</h6>
        <div class="mb-2">
          <div class="d-flex justify-content-between mb-1">
            <span>School Cafeteria</span>
            <span><strong><?php echo number_format(($salesData['carnick']['totalSales'] / $salesData['all']['totalSales']) * 100, 1); ?>%</strong></span>
          </div>
          <div class="progress" style="height: 20px;">
            <div class="progress-bar bg-success" role="progressbar" 
                 style="width: <?php echo $salesData['all']['totalSales'] > 0 ? ($salesData['carnick']['totalSales'] / $salesData['all']['totalSales']) * 100 : 0; ?>%">
            </div>
          </div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between mb-1">
            <span>KARNEEK Bakery</span>
            <span><strong><?php echo number_format(($salesData['karneek']['totalSales'] / $salesData['all']['totalSales']) * 100, 1); ?>%</strong></span>
          </div>
          <div class="progress" style="height: 20px;">
            <div class="progress-bar" role="progressbar" style="background-color: #8B4513; width: <?php echo $salesData['all']['totalSales'] > 0 ? ($salesData['karneek']['totalSales'] / $salesData['all']['totalSales']) * 100 : 0; ?>%">
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- School Cafeteria Detailed Sales Report -->
<?php if ($validDates && $canteenData && isset($canteenData['success']) && $canteenData['success']): ?>
<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title mb-4">
      <i class="fas fa-list"></i> School Cafeteria Sales Report (Live)
    </h5>
    
    <?php if (!empty($canteenLogs) && is_array($canteenLogs) && count($canteenLogs) > 0): ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-dark">
            <tr>
              <th>Product Name</th>
              <th>Quantity</th>
              <th>Price</th>
              <th>Total</th>
              <th>Date Sold</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($canteenLogs as $log): ?>
              <tr>
                <td><?php echo htmlspecialchars($log['product_name'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($log['quantity'] ?? '0'); ?></td>
                <td>₱<?php echo number_format($log['price'] ?? 0, 2); ?></td>
                <td><strong>₱<?php echo number_format($log['total_amount'] ?? 0, 2); ?></strong></td>
                <td><?php echo isset($log['created_at']) ? date('M d, Y h:i A', strtotime($log['created_at'])) : 'N/A'; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No sales records found for the selected period.
      </div>
    <?php endif; ?>
  </div>
</div>
<?php elseif ($validDates && ($canteenError || !$canteenData)): ?>
<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title mb-4">
      <i class="fas fa-list"></i> School Cafeteria Sales Report (Live)
    </h5>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-triangle"></i> 
      <strong>Error:</strong> <?php echo htmlspecialchars($canteenError ?? 'Can\'t connect to the cafeteria server.'); ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($validDates && !$hasSourceColumn): ?>
<div class="alert alert-warning">
  <i class="fas fa-exclamation-triangle"></i> 
  <strong>Note:</strong> The 'source' column has not been added to the orders table yet. 
  Please run the migration script: <code>backend/migrations/add_order_source_column.sql</code>
  <br>Currently showing all orders as KARNEEK Bakery sales.
</div>
<?php endif; ?>

<?php else: ?>
<div class="alert alert-info">
  Please select a valid date range to generate the report.
</div>
<?php endif; ?>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

