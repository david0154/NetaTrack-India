<?php
/**
 * NetaTrack India — API Ping
 * Used by Python admin to verify API connectivity
 */
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'service' => 'NetaTrack India API', 'time' => time()]);
