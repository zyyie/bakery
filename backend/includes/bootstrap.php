<?php

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/session.php';

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
            $it = new FilesystemIterator($baseDir, FilesystemIterator::SKIP_DOTS);
            foreach ($it as $fileInfo) {
                if (!$fileInfo->isFile()) continue;
                $ext = strtolower($fileInfo->getExtension());
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) continue;
                return $prefix . 'frontend/images/' . $folder . '/' . $fileInfo->getFilename();
            }
        }
    }

    // Fallback placeholder when images folder is empty or missing
    return $prefix . 'frontend/images/placeholder.jpg';
}

function product_image_urls(array $itemRow, int $depth = 0, int $max = 3): array {
    $prefix = app_path_prefix($depth);
    $urls = [];

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
            $it = new FilesystemIterator($baseDir, FilesystemIterator::SKIP_DOTS);
            foreach ($it as $fileInfo) {
                if (!$fileInfo->isFile()) continue;
                $ext = strtolower($fileInfo->getExtension());
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) continue;
                $rel = $folder . '/' . $fileInfo->getFilename();
                $urls[] = $prefix . 'frontend/images/' . $rel;
                if (count($urls) >= $max) break;
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
