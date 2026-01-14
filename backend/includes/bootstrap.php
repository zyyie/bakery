<?php

require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/validation.php';

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$__appLogDir = __DIR__ . '/../logs';
if (!is_dir($__appLogDir)) {
    @mkdir($__appLogDir, 0775, true);
}
ini_set('error_log', $__appLogDir . '/php_errors.log');

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e) {
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
    $accept = (string)($_SERVER['HTTP_ACCEPT'] ?? '');
    $contentType = (string)($_SERVER['CONTENT_TYPE'] ?? '');
    $isJson = stripos($accept, 'application/json') !== false || stripos($contentType, 'application/json') !== false || stripos($uri, '/api/') !== false;

    $msg = "Unhandled exception: " . get_class($e) . ": " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString();
    error_log($msg);

    if (!headers_sent()) {
        http_response_code(500);
        if ($isJson) {
            header('Content-Type: application/json');
        } else {
            header('Content-Type: text/html; charset=UTF-8');
        }
    }

    if ($isJson) {
        echo json_encode(['ok' => false, 'error' => 'Server error']);
    } else {
        echo 'An unexpected error occurred. Please try again later.';
    }
    exit;
});

register_shutdown_function(function () {
    $err = error_get_last();
    if (!$err) {
        return;
    }
    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($err['type'] ?? 0, $fatalTypes, true)) {
        return;
    }

    $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
    $accept = (string)($_SERVER['HTTP_ACCEPT'] ?? '');
    $contentType = (string)($_SERVER['CONTENT_TYPE'] ?? '');
    $isJson = stripos($accept, 'application/json') !== false || stripos($contentType, 'application/json') !== false || stripos($uri, '/api/') !== false;

    error_log('Fatal error: ' . ($err['message'] ?? '') . ' in ' . ($err['file'] ?? '') . ':' . ($err['line'] ?? ''));

    if (!headers_sent()) {
        http_response_code(500);
        if ($isJson) {
            header('Content-Type: application/json');
        } else {
            header('Content-Type: text/html; charset=UTF-8');
        }
    }

    if ($isJson) {
        echo json_encode(['ok' => false, 'error' => 'Server error']);
    } else {
        echo 'An unexpected error occurred. Please try again later.';
    }
});

function is_https() {
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . $path);
    exit();
}

function current_base_url() {
    $scheme = is_https() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(dirname($_SERVER['PHP_SELF'] ?? '/'), '/\\');
    return $scheme . '://' . $host . ($dir ? '/' . $dir : '');
}

function get_server_ip() {
    // Try to get the local IP address for mobile device access
    $possibleIPs = [];
    
    // Method 1: Get server IP from SERVER_ADDR
    if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1' && $_SERVER['SERVER_ADDR'] !== '::1') {
        $ip = $_SERVER['SERVER_ADDR'];
        // Only use private IPs (192.168.x.x, 10.x.x.x, 172.16-31.x.x)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            $possibleIPs[] = $ip;
        }
    }
    
    // Method 2: Try to get IP from network interfaces (Windows)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = [];
        @exec('ipconfig', $output, $returnVar);
        if ($returnVar === 0) {
            foreach ($output as $line) {
                if (preg_match('/IPv4 Address[^:]*:\s*(\d+\.\d+\.\d+\.\d+)/i', $line, $matches)) {
                    $ip = trim($matches[1]);
                    if ($ip !== '127.0.0.1' && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                        // It's a private IP (192.168.x.x, 10.x.x.x, etc.)
                        if (!in_array($ip, $possibleIPs)) {
                            $possibleIPs[] = $ip;
                        }
                    }
                }
            }
        }
    }
    
    // Method 3: Try socket connection to get local IP
    if (empty($possibleIPs)) {
        $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socket !== false) {
            @socket_connect($socket, '8.8.8.8', 53);
            $localIP = '';
            @socket_getsockname($socket, $localIP);
            @socket_close($socket);
            if (!empty($localIP) && $localIP !== '127.0.0.1') {
                $possibleIPs[] = $localIP;
            }
        }
    }
    
    // Return first valid IP or fallback to localhost
    return !empty($possibleIPs) ? $possibleIPs[0] : 'localhost';
}

function get_reset_link_base_url() {
    // Check if base URL is configured in email config (PRIORITY - use this first)
    $emailConfig = require __DIR__ . '/../config/email.php';
    if (!empty($emailConfig['base_url'])) {
        return rtrim($emailConfig['base_url'], '/');
    }
    
    // For email links, use IP address if available so mobile devices can access
    $scheme = is_https() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // If host is localhost, try to get the actual IP address
    if ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost') !== false) {
        $serverIP = get_server_ip();
        if ($serverIP !== 'localhost' && $serverIP !== '127.0.0.1') {
            $host = $serverIP;
            error_log("Using detected IP address for reset link: $host");
        } else {
            error_log("Warning: Could not detect server IP, using localhost (may not work on mobile devices)");
        }
    }
    
    // Get base path
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $backendPos = strpos($scriptName, '/backend/');
    
    if ($backendPos !== false) {
        $basePath = rtrim(substr($scriptName, 0, $backendPos), '/');
        $url = $scheme . '://' . $host . $basePath;
        error_log("Generated reset link base URL: $url");
        return $url;
    }
    
    $url = $scheme . '://' . $host . '/bakery';
    error_log("Generated reset link base URL (fallback): $url");
    return $url;
}

function get_sms_base_url() {
    // Get SMS base URL from config (PRIORITY - use this first)
    $smsConfig = require __DIR__ . '/../config/sms.php';
    if (!empty($smsConfig['base_url'])) {
        return rtrim($smsConfig['base_url'], '/');
    }
    
    // Fallback: use email config base_url if SMS config doesn't have one
    $emailConfig = require __DIR__ . '/../config/email.php';
    if (!empty($emailConfig['base_url'])) {
        return rtrim($emailConfig['base_url'], '/');
    }
    
    // Final fallback: auto-detect
    $scheme = is_https() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // If host is localhost, try to get the actual IP address
    if ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost') !== false) {
        $serverIP = get_server_ip();
        if ($serverIP !== 'localhost' && $serverIP !== '127.0.0.1') {
            $host = $serverIP;
        }
    }
    
    return $scheme . '://' . $host . '/bakery';
}

function set_app_cookie($name, $value, $expires) {
    $options = [
        'expires' => $expires,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Strict'
    ];

    setcookie($name, $value, $options);
}

// Secure Remember Me (cookie stores raw token, DB stores SHA-256 hash)
if (!isset($_SESSION['userID']) && isset($_COOKIE['remember_token'])) {
    $rawToken = trim((string)$_COOKIE['remember_token']);

    if ($rawToken !== '') {
        $tokenHash = hash('sha256', $rawToken);

        // Support legacy tokens (DB may still store raw token) and upgrade on use
        $query = "SELECT userID, fullName, email, remember_token FROM users WHERE token_expires > NOW() AND (remember_token = ? OR remember_token = ?) LIMIT 1";
        $result = executePreparedQuery($query, "ss", [$rawToken, $tokenHash]);

        if ($result && ($user = $result->fetch_assoc())) {
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['fullName'] = $user['fullName'];
            $_SESSION['email'] = $user['email'];

            // Rotate token to reduce replay risk
            $newRawToken = bin2hex(random_bytes(32));
            $newTokenHash = hash('sha256', $newRawToken);
            $expiresTs = time() + (86400 * 30);
            $expiryDate = date('Y-m-d H:i:s', $expiresTs);

            set_app_cookie('remember_token', $newRawToken, $expiresTs);

            $update = "UPDATE users SET remember_token = ?, token_expires = ? WHERE userID = ?";
            executePreparedUpdate($update, "ssi", [$newTokenHash, $expiryDate, $user['userID']]);
        } else {
            // Invalid/expired token: clear cookie
            set_app_cookie('remember_token', '', time() - 3600);
        }
    }
}

function app_path_prefix(int $depth = 0): string {
    if ($depth <= 0) {
        return '';
    }
    return str_repeat('../', $depth);
}

function product_image_url(array $itemRow, int $depth = 0): string {
    $prefix = app_path_prefix($depth);

    if (!empty($itemRow['itemImage'])) {
        return $prefix . 'uploads/' . e($itemRow['itemImage']);
    }

    $name = (string)($itemRow['packageName'] ?? '');
    $folder = find_frontend_image_folder_for_product_name($name);
    if ($folder !== null) {
        // Try to find any image in the detected folder
        $baseDir = realpath(__DIR__ . '/../../frontend/images/' . $folder);
        if ($baseDir && is_dir($baseDir)) {
            // Products that should avoid box images
            $avoidBoxProducts = ['Wheat pandesal', 'Ube cheese bread'];
            $shouldAvoidBox = in_array($name, $avoidBoxProducts, true);
            
            $preferredImages = [];
            $otherImages = [];
            
            $it = new FilesystemIterator($baseDir, FilesystemIterator::SKIP_DOTS);
            foreach ($it as $fileInfo) {
                if (!$fileInfo->isFile()) continue;
                $ext = strtolower($fileInfo->getExtension());
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) continue;
                
                $fileName = strtolower($fileInfo->getFilename());
                
                // For products that should avoid box images, skip files with "box" in name
                if ($shouldAvoidBox && strpos($fileName, 'box') !== false) {
                    $otherImages[] = $fileInfo->getFilename();
                    continue;
                }
                
                // Prefer images without "box" in the name
                $preferredImages[] = $fileInfo->getFilename();
            }
            
            // Return preferred image (non-box) first, fallback to others if needed
            if (!empty($preferredImages)) {
                return $prefix . 'frontend/images/' . $folder . '/' . $preferredImages[0];
            } elseif (!empty($otherImages)) {
                // Only use box images if no other images available
                return $prefix . 'frontend/images/' . $folder . '/' . $otherImages[0];
            }
        }
    }

    // Fallback placeholder when images folder is empty or missing
    return $prefix . 'frontend/images/placeholder.jpg';
}

function product_image_urls(array $itemRow, int $depth = 0, int $max = 3): array {
    $prefix = app_path_prefix($depth);
    $urls = [];

    // Prefer images from item_images table when available
    $itemID = isset($itemRow['itemID']) ? intval($itemRow['itemID']) : 0;
    if ($itemID > 0) {
        try {
            $rs = executePreparedQuery(
                "SELECT image_path FROM item_images WHERE itemID = ? ORDER BY is_primary DESC, sort_order ASC, imageID ASC LIMIT ?",
                "ii",
                [$itemID, max(1, $max)]
            );
            if ($rs && mysqli_num_rows($rs) > 0) {
                while ($r = mysqli_fetch_assoc($rs)) {
                    $p = trim((string)($r['image_path'] ?? ''));
                    if ($p === '') continue;

                    // Absolute URLs
                    if (preg_match('~^https?://~i', $p)) {
                        $urls[] = $p;
                        if (count($urls) >= $max) break;
                        continue;
                    }

                    // Normalize slashes and trim
                    $p2 = str_replace('\\', '/', $p);
                    $p2 = ltrim($p2, '/');

                    // If path already targets frontend images, serve it directly
                    if (stripos($p2, 'frontend/images/') === 0) {
                        $urls[] = $prefix . $p2;
                    } elseif (stripos($p2, 'images/') === 0) {
                        $urls[] = $prefix . 'frontend/' . $p2;
                    } else {
                        // Default: uploads-relative
                        $urls[] = $prefix . 'uploads/' . $p2;
                    }

                    if (count($urls) >= $max) break;
                }

                if (!empty($urls)) {
                    return $urls;
                }
            }
        } catch (Throwable $e) {
            // Ignore DB errors here and fall back gracefully
        }
    }

    if (!empty($itemRow['itemImage'])) {
        // If itemImage is set, return it as a single image
        $urls[] = $prefix . 'uploads/' . e($itemRow['itemImage']);
        return $urls;
    }

    $name = (string)($itemRow['packageName'] ?? '');
    $folder = find_frontend_image_folder_for_product_name($name);
    if ($folder !== null) {
        $baseDir = realpath(__DIR__ . '/../../frontend/images/' . $folder);
        if ($baseDir && is_dir($baseDir)) {
            // Products that should avoid box images
            $avoidBoxProducts = ['Wheat pandesal', 'Ube cheese bread'];
            $shouldAvoidBox = in_array($name, $avoidBoxProducts, true);
            
            $preferredImages = [];
            $otherImages = [];
            
            $it = new FilesystemIterator($baseDir, FilesystemIterator::SKIP_DOTS);
            foreach ($it as $fileInfo) {
                if (!$fileInfo->isFile()) continue;
                $ext = strtolower($fileInfo->getExtension());
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) continue;
                
                $fileName = strtolower($fileInfo->getFilename());
                $rel = $folder . '/' . $fileInfo->getFilename();
                
                // For products that should avoid box images, prioritize non-box images
                if ($shouldAvoidBox && strpos($fileName, 'box') !== false) {
                    $otherImages[] = $prefix . 'frontend/images/' . $rel;
                } else {
                    $preferredImages[] = $prefix . 'frontend/images/' . $rel;
                }
            }
            
            // Add preferred images first, then others if needed
            foreach ($preferredImages as $img) {
                $urls[] = $img;
                if (count($urls) >= $max) break;
            }
            
            // Add other images if we haven't reached max yet
            if (count($urls) < $max) {
                foreach ($otherImages as $img) {
                    $urls[] = $img;
                    if (count($urls) >= $max) break;
                }
            }
        }
    }

    // Fallback to single image if none found
    if (empty($urls)) {
        $fallback = product_image_url($itemRow, $depth);
        $urls[] = $fallback;
    }

    return $urls;
}

function find_frontend_image_for_product_name(string $productName): ?string {
    static $indexed = false;
    static $files = [];
    static $folderEmpty = false;

    if (!$indexed) {
        $baseDir = realpath(__DIR__ . '/../../frontend/images');
        if ($baseDir === false) {
            $indexed = true;
            $folderEmpty = true;
            return null;
        }

        // Quick check: if folder is empty, skip scanning
        $iterator = new FilesystemIterator($baseDir, FilesystemIterator::SKIP_DOTS);
        if (!$iterator->valid()) {
            $indexed = true;
            $folderEmpty = true;
            return null;
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $ext = strtolower($fileInfo->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                continue;
            }
            $abs = $fileInfo->getPathname();
            $rel = str_replace('\\', '/', substr($abs, strlen($baseDir) + 1));
            $files[] = [
                'rel' => $rel,
                'name' => strtolower($fileInfo->getBasename('.' . $fileInfo->getExtension())),
                'path' => strtolower($rel),
            ];
        }
        $indexed = true;
    }

    if ($folderEmpty) {
        return null;
    }

    $q = strtolower(trim($productName));
    if ($q === '') {
        return null;
    }

    $qNorm = preg_replace('/[^a-z0-9]+/i', ' ', $q);
    $tokens = array_values(array_filter(explode(' ', (string)$qNorm), static function ($t) {
        return strlen($t) >= 3;
    }));

    $bestRel = null;
    $bestScore = 0;

    foreach ($files as $f) {
        $hay = $f['name'] . ' ' . $f['path'];
        $score = 0;
        foreach ($tokens as $t) {
            if (strpos($hay, $t) !== false) {
                $score++;
            }
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestRel = $f['rel'];
        }
    }

    if ($bestScore <= 0) {
        return null;
    }

    return $bestRel;
}

function find_frontend_image_folder_for_product_name(string $productName): ?string {
    $baseDir = realpath(__DIR__ . '/../../frontend/images');
    if (!$baseDir || !is_dir($baseDir)) return null;

    $folders = [];
    $it = new FilesystemIterator($baseDir, FilesystemIterator::SKIP_DOTS);
    foreach ($it as $fileInfo) {
        if ($fileInfo->isDir()) {
            $folders[] = $fileInfo->getFilename();
        }
    }

    $q = strtolower(trim($productName));
    if ($q === '') return null;

    // Bread heuristic: prefer any folder under 'bread image'
    foreach ($folders as $folder) {
        $folderLower = strtolower($folder);
        if (strpos($folderLower, 'bread') !== false) {
            // Try to find a matching subfolder under this bread folder
            $subfolder = find_bread_subfolder_for_product($productName, $folder);
            if ($subfolder !== null) {
                return $folder . '/' . $subfolder;
            }
        }
    }

    // If we detected bread keywords but didn't find a subfolder, do not fall back to generic matching
    if (strpos($q, 'bread') !== false || strpos($q, 'bun') !== false || strpos($q, 'roll') !== false || strpos($q, 'pandesal') !== false) {
        return null;
    }

    // Fallback: try any folder that contains a matching file name (for non-bread items)
    foreach ($folders as $folder) {
        $match = find_frontend_image_for_product_name_in_folder($productName, $folder);
        if ($match !== null) {
            return $folder;
        }
    }

    return null;
}

function find_bread_subfolder_for_product(string $productName, string $breadFolder): ?string {
    $baseDir = realpath(__DIR__ . "/../../frontend/images/{$breadFolder}");
    if (!$baseDir || !is_dir($baseDir)) return null;

    $q = strtolower(trim($productName));
    $qTokens = preg_split('/[\s\(\)\–-]+/', $q, -1, PREG_SPLIT_NO_EMPTY);

    $bestMatch = null;
    $bestScore = 0;

    // Manual recursive search with scoring
    $search = function($dir, $relative = '') use (&$search, $q, $qTokens, &$bestMatch, &$bestScore) {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $subfolderName = $item;
                $subLower = strtolower($subfolderName);
                $subTokens = preg_split('/[\s\(\)\–-]+/', $subLower, -1, PREG_SPLIT_NO_EMPTY);
                $score = 0;
                // Exact match gets highest score
                if ($subLower === $q) {
                    $score = 100;
                }
                // Token matching
                foreach ($qTokens as $qt) {
                    if (strlen($qt) < 3) continue;
                    foreach ($subTokens as $st) {
                        if (strlen($st) < 3) continue;
                        if ($st === $qt) {
                            $score += 10;
                        } elseif (strpos($st, $qt) !== false || strpos($qt, $st) !== false) {
                            $score += 3;
                        }
                    }
                }
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = ($relative ? $relative . '/' : '') . $subfolderName;
                }
                // Recurse deeper
                $search($path, ($relative ? $relative . '/' : '') . $subfolderName);
            }
        }
    };

    $search($baseDir);
    return $bestMatch;
}

function find_frontend_image_for_product_name_in_folder(string $productName, string $folder): ?string {
    $baseDir = realpath(__DIR__ . "/../../frontend/images/{$folder}");
    if (!$baseDir || !is_dir($baseDir)) return null;

    $it = new FilesystemIterator($baseDir, FilesystemIterator::SKIP_DOTS);
    foreach ($it as $fileInfo) {
        if (!$fileInfo->isFile()) continue;
        $ext = strtolower($fileInfo->getExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) continue;
        $name = strtolower($fileInfo->getBasename('.' . $ext));
        $q = strtolower(trim($productName));
        if (strpos($name, $q) !== false || strpos($q, $name) !== false) {
            return $fileInfo->getFilename();
        }
    }
    return null;
}
