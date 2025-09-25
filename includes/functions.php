<?php
/**
 * Utility Functions
 * Replace WordPress functions with custom implementations
 */

/**
 * Sanitize text input
 */
function sanitize_text($text) {
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize email
 */
function sanitize_email($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate secure random string
 */
function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash password securely
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Create SEO-friendly slug
 */
function create_slug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

/**
 * Format currency
 */
function format_currency($amount, $currency = '$') {
    return $currency . number_format($amount, 2);
}

/**
 * Format date
 */
function format_date($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Truncate text with ellipsis
 */
function truncate_text($text, $limit = 150, $ellipsis = '...') {
    if (strlen($text) <= $limit) {
        return $text;
    }
    return substr($text, 0, $limit) . $ellipsis;
}

/**
 * Get current page URL
 */
function get_current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Redirect to URL
 */
function redirect($url, $permanent = false) {
    $code = $permanent ? 301 : 302;
    header("Location: $url", true, $code);
    exit;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function get_current_user() {
    if (!is_logged_in()) {
        return null;
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Log user in
 */
function login_user($user_id) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['login_time'] = time();
}

/**
 * Log user out
 */
function logout_user() {
    session_destroy();
    session_start();
}

/**
 * Send email using SMTP
 */
function send_email($to, $subject, $message, $from = null) {
    $from = $from ?: FROM_EMAIL;

    $headers = [
        'From: ' . FROM_NAME . ' <' . $from . '>',
        'Reply-To: ' . $from,
        'Content-Type: text/html; charset=UTF-8',
        'MIME-Version: 1.0'
    ];

    return mail($to, $subject, $message, implode("\r\n", $headers));
}

/**
 * Upload file
 */
function upload_file($file, $allowed_types = null, $max_size = null) {
    $allowed_types = $allowed_types ?: ALLOWED_IMAGE_TYPES;
    $max_size = $max_size ?: MAX_UPLOAD_SIZE;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File too large'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    $filename = generate_random_string(16) . '.' . $ext;
    $destination = UPLOADS_PATH . '/images/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $filename,
            'url' => UPLOADS_URL . '/images/' . $filename
        ];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

/**
 * Log error
 */
function log_error($message, $file = 'error.log') {
    if (!LOG_ERRORS) return;

    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message" . PHP_EOL;

    file_put_contents(LOG_PATH . '/' . $file, $log_message, FILE_APPEND | LOCK_EX);
}

/**
 * Generate pagination HTML
 */
function generate_pagination($current_page, $total_pages, $base_url) {
    $html = '<div class="pagination">';

    // Previous button
    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        $html .= '<a href="' . $base_url . '?page=' . $prev_page . '" class="pagination-prev">&laquo; Previous</a>';
    }

    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $current_page) ? ' class="active"' : '';
        $html .= '<a href="' . $base_url . '?page=' . $i . '"' . $active . '>' . $i . '</a>';
    }

    // Next button
    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;
        $html .= '<a href="' . $base_url . '?page=' . $next_page . '" class="pagination-next">Next &raquo;</a>';
    }

    $html .= '</div>';
    return $html;
}

/**
 * Get meta value for object
 */
function get_meta($object_type, $object_id, $meta_key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT meta_value FROM meta_data WHERE object_type = ? AND object_id = ? AND meta_key = ?");
    $stmt->execute([$object_type, $object_id, $meta_key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['meta_value'] : $default;
}

/**
 * Update meta value for object
 */
function update_meta($object_type, $object_id, $meta_key, $meta_value) {
    global $pdo;

    // Check if meta exists
    $stmt = $pdo->prepare("SELECT id FROM meta_data WHERE object_type = ? AND object_id = ? AND meta_key = ?");
    $stmt->execute([$object_type, $object_id, $meta_key]);

    if ($stmt->fetch()) {
        // Update existing
        $stmt = $pdo->prepare("UPDATE meta_data SET meta_value = ? WHERE object_type = ? AND object_id = ? AND meta_key = ?");
        $stmt->execute([$meta_value, $object_type, $object_id, $meta_key]);
    } else {
        // Insert new
        $stmt = $pdo->prepare("INSERT INTO meta_data (object_type, object_id, meta_key, meta_value) VALUES (?, ?, ?, ?)");
        $stmt->execute([$object_type, $object_id, $meta_key, $meta_value]);
    }
}

/**
 * Include template part
 */
function include_template($template_name, $variables = []) {
    extract($variables);
    $template_path = SITE_PATH . '/templates/' . $template_name . '.php';

    if (file_exists($template_path)) {
        include $template_path;
    } else {
        echo "<!-- Template not found: $template_name -->";
    }
}
?>