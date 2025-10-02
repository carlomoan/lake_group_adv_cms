<?php
/**
 * Debug Tool: Test What Data Admin Form Sends
 * This creates a test endpoint to see exactly what's being posted
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Log file path
$logFile = __DIR__ . '/save_debug.log';

// Get the raw POST data
$rawData = file_get_contents('php://input');

// Log timestamp and data
$timestamp = date('Y-m-d H:i:s');
$logEntry = "\n\n=== SAVE REQUEST at $timestamp ===\n";
$logEntry .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$logEntry .= "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set') . "\n";
$logEntry .= "Raw Data Length: " . strlen($rawData) . " bytes\n";
$logEntry .= "Raw Data:\n" . $rawData . "\n";

// Try to decode JSON
$decoded = json_decode($rawData, true);
if ($decoded) {
    $logEntry .= "\nDecoded JSON:\n" . print_r($decoded, true) . "\n";

    // Check what's in siteSettings
    if (isset($decoded['content']['siteSettings'])) {
        $logEntry .= "\nsiteSettings structure:\n";
        $logEntry .= print_r($decoded['content']['siteSettings'], true) . "\n";

        // Check specifically for logo fields
        $logEntry .= "\nLogo Fields Check:\n";
        $logEntry .= "logoMain (flat): " . ($decoded['content']['siteSettings']['logoMain'] ?? 'NOT SET') . "\n";
        $logEntry .= "logoTransparent (flat): " . ($decoded['content']['siteSettings']['logoTransparent'] ?? 'NOT SET') . "\n";
        $logEntry .= "logo.logoMain (nested): " . ($decoded['content']['siteSettings']['logo']['logoMain'] ?? 'NOT SET') . "\n";
        $logEntry .= "logo.logoTransparent (nested): " . ($decoded['content']['siteSettings']['logo']['logoTransparent'] ?? 'NOT SET') . "\n";
    }
} else {
    $logEntry .= "\nJSON Decode Error: " . json_last_error_msg() . "\n";
}

// Write to log file
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Debug data logged',
    'logged_at' => $timestamp,
    'log_file' => 'save_debug.log',
    'data_received' => $decoded ? true : false
]);
?>
