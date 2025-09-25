<?php
/**
 * Security Functions
 */

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF hidden input field
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Check user permission
 */
function check_permission($required_role) {
    $user = get_current_user();

    if (!$user) {
        return false;
    }

    $roles_hierarchy = [
        'customer' => 1,
        'editor' => 2,
        'admin' => 3
    ];

    $user_level = $roles_hierarchy[$user['role']] ?? 0;
    $required_level = $roles_hierarchy[$required_role] ?? 999;

    return $user_level >= $required_level;
}

/**
 * Require user to be logged in
 */
function require_login($redirect_url = '/login') {
    if (!is_logged_in()) {
        redirect($redirect_url);
    }
}

/**
 * Require specific user role
 */
function require_role($role, $redirect_url = '/') {
    if (!check_permission($role)) {
        redirect($redirect_url);
    }
}

/**
 * Rate limiting
 */
function check_rate_limit($action, $max_attempts = 5, $time_window = 300) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = "rate_limit:{$action}:{$ip}";

    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }

    $now = time();
    $rate_limits = $_SESSION['rate_limits'];

    // Clean old entries
    foreach ($rate_limits as $k => $data) {
        if ($data['expires'] < $now) {
            unset($rate_limits[$k]);
        }
    }

    if (!isset($rate_limits[$key])) {
        $rate_limits[$key] = [
            'count' => 1,
            'expires' => $now + $time_window
        ];
    } else {
        $rate_limits[$key]['count']++;
    }

    $_SESSION['rate_limits'] = $rate_limits;

    return $rate_limits[$key]['count'] <= $max_attempts;
}

/**
 * Validate and sanitize input
 */
function validate_input($data, $rules) {
    $errors = [];
    $sanitized = [];

    foreach ($rules as $field => $rule_set) {
        $value = $data[$field] ?? null;
        $field_errors = [];

        foreach ($rule_set as $rule => $param) {
            switch ($rule) {
                case 'required':
                    if ($param && empty($value)) {
                        $field_errors[] = ucfirst($field) . ' is required';
                    }
                    break;

                case 'email':
                    if ($param && $value && !is_valid_email($value)) {
                        $field_errors[] = ucfirst($field) . ' must be a valid email';
                    }
                    break;

                case 'min_length':
                    if ($value && strlen($value) < $param) {
                        $field_errors[] = ucfirst($field) . " must be at least {$param} characters";
                    }
                    break;

                case 'max_length':
                    if ($value && strlen($value) > $param) {
                        $field_errors[] = ucfirst($field) . " must not exceed {$param} characters";
                    }
                    break;

                case 'numeric':
                    if ($param && $value && !is_numeric($value)) {
                        $field_errors[] = ucfirst($field) . ' must be numeric';
                    }
                    break;

                case 'match':
                    if ($param && $value !== ($data[$param] ?? null)) {
                        $field_errors[] = ucfirst($field) . ' does not match';
                    }
                    break;
            }
        }

        if (empty($field_errors)) {
            // Sanitize the value
            if ($value !== null) {
                $sanitized[$field] = sanitize_text($value);
            }
        } else {
            $errors[$field] = $field_errors;
        }
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $sanitized
    ];
}

/**
 * Check for SQL injection patterns
 */
function check_sql_injection($input) {
    $patterns = [
        '/(\'\s*(or|and)\s*\')/i',
        '/(\'\s*(or|and)\s*\d+\s*=\s*\d+)/i',
        '/(union\s+select)/i',
        '/(drop\s+table)/i',
        '/(insert\s+into)/i',
        '/(delete\s+from)/i',
        '/(update\s+.*\s+set)/i'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }

    return false;
}

/**
 * Check for XSS patterns
 */
function check_xss($input) {
    $patterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/<iframe[^>]*>.*?<\/iframe>/is',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<object[^>]*>.*?<\/object>/is',
        '/<embed[^>]*>.*?<\/embed>/is'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }

    return false;
}

/**
 * Log security event
 */
function log_security_event($event, $details = []) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $user_id = $_SESSION['user_id'] ?? 'Anonymous';

    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $ip,
        'user_agent' => $user_agent,
        'user_id' => $user_id,
        'details' => $details
    ];

    log_error(json_encode($log_data), 'security.log');
}

/**
 * Block suspicious requests
 */
function security_check() {
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Check for common attack patterns
    $suspicious_patterns = [
        '/\.\.\//i',                    // Directory traversal
        '/etc\/passwd/i',               // System file access
        '/proc\/self\/environ/i',       // Environment variables
        '/eval\s*\(/i',                 // Code execution
        '/base64_decode/i',             // Encoded payloads
        '/shell_exec/i',                // Command execution
        '/system\s*\(/i',               // System commands
        '/wget\s+/i',                   // File downloads
        '/curl\s+/i',                   // HTTP requests
    ];

    $request_data = $request_uri . ' ' . $query_string . ' ' . $user_agent;

    foreach ($suspicious_patterns as $pattern) {
        if (preg_match($pattern, $request_data)) {
            log_security_event('suspicious_request', [
                'pattern' => $pattern,
                'request_uri' => $request_uri,
                'query_string' => $query_string,
                'user_agent' => $user_agent
            ]);

            http_response_code(403);
            die('Access denied');
        }
    }

    // Check for SQL injection in request data
    if (check_sql_injection($request_data)) {
        log_security_event('sql_injection_attempt', [
            'request_uri' => $request_uri,
            'query_string' => $query_string
        ]);

        http_response_code(403);
        die('Access denied');
    }

    // Check for XSS in request data
    if (check_xss($request_data)) {
        log_security_event('xss_attempt', [
            'request_uri' => $request_uri,
            'query_string' => $query_string
        ]);

        http_response_code(403);
        die('Access denied');
    }
}

// Run security check on every request
security_check();
?>