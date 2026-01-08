<?php
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
$timestamp = date("Y-m-d H:i:s");
$logFile = __DIR__ . "/sms_log.txt";

file_put_contents($logFile, "[$timestamp] $rawData" . PHP_EOL, FILE_APPEND);

http_response_code(200);
echo json_encode(["status" => "ok", "received" => true]);
?>
