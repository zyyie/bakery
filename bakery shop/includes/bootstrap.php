<?php

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/session.php';

function is_https() {
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Function to resolve image path - tries to find actual file if path doesn't exist
function resolveImagePath($imagePath) {
  // If it's a URL (placeholder), return as is
  if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
    return $imagePath;
  }
  
  // Get bakery shop root directory
  $shopRoot = dirname(__DIR__);
  $normalizedPath = $imagePath;
  
  // Normalize path - convert 'bakery bread image' to 'bread image'
  $normalizedPath = str_replace('bakery bread image/', 'bread image/', $normalizedPath);
  
  // Remove '../' if present
  if (strpos($normalizedPath, '../') === 0) {
    $normalizedPath = str_replace('../', '', $normalizedPath);
  }
  
  // Try multiple path combinations with exact path first
  $pathsToTry = [
    $shopRoot . '/' . $normalizedPath,
    __DIR__ . '/../' . $normalizedPath,
    realpath($shopRoot) . '/' . $normalizedPath,
  ];
  
  foreach ($pathsToTry as $testPath) {
    if (file_exists($testPath)) {
      return $normalizedPath; // Return normalized path
    }
  }
  
  // If exact file doesn't exist, try with different extensions (jpg, jpeg, png, webp)
  $pathInfo = pathinfo($normalizedPath);
  $dirPath = isset($pathInfo['dirname']) ? $pathInfo['dirname'] : '';
  $fileName = isset($pathInfo['filename']) ? $pathInfo['filename'] : '';
  $currentExt = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';
  
  // Try different extensions
  $extensionsToTry = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
  foreach ($extensionsToTry as $ext) {
    if ($ext === $currentExt) continue; // Skip if already tried
    
    $testPath = $dirPath . '/' . $fileName . '.' . $ext;
    $fullPaths = [
      $shopRoot . '/' . $testPath,
      __DIR__ . '/../' . $testPath,
      realpath($shopRoot) . '/' . $testPath,
    ];
    
    foreach ($fullPaths as $fullPath) {
      if (file_exists($fullPath)) {
        return $testPath; // Return path with found extension
      }
    }
  }
  
  // If file doesn't exist, try to find similar file in the same directory
  if (strpos($normalizedPath, 'bread image/') !== false) {
    $pathParts = explode('/', $normalizedPath);
    if (count($pathParts) >= 3) {
      $category = $pathParts[1];
      $product = isset($pathParts[2]) ? $pathParts[2] : '';
      $expectedFile = isset($pathParts[3]) ? $pathParts[3] : '';
      
      // Try to find the product directory - check multiple possible locations
      $baseDirs = [
        $shopRoot . '/bread image',
        __DIR__ . '/../bread image',
        realpath($shopRoot) . '/bread image',
      ];
      
      foreach ($baseDirs as $baseDir) {
        if (!is_dir($baseDir)) continue;
        
        // Try exact category/product path
        $productDirs = [
          $baseDir . '/' . $category . '/' . $product,
        ];
        
        // Also try to find category folder (case-insensitive)
        $categoryDirs = glob($baseDir . '/*', GLOB_ONLYDIR);
        foreach ($categoryDirs as $catDir) {
          $catName = basename($catDir);
          // Case-insensitive category match
          if (strcasecmp($catName, $category) === 0) {
            // Try exact product name
            $productDirs[] = $catDir . '/' . $product;
            
            // Try to find product folder (case-insensitive)
            $productFolders = glob($catDir . '/*', GLOB_ONLYDIR);
            foreach ($productFolders as $prodDir) {
              $prodName = basename($prodDir);
              // Case-insensitive product match or partial match
              if (strcasecmp($prodName, $product) === 0 || 
                  stripos($prodName, $product) !== false || 
                  stripos($product, $prodName) !== false) {
                $productDirs[] = $prodDir;
              }
            }
          }
        }
        
        // Remove duplicates
        $productDirs = array_unique($productDirs);
        
        foreach ($productDirs as $productDir) {
          if (!is_dir($productDir)) continue;
          
          $files = scandir($productDir);
          $fileInfo = pathinfo($expectedFile);
          $baseName = isset($fileInfo['filename']) ? $fileInfo['filename'] : '';
          $baseName = preg_replace('/_[0-9]+$|_batch[0-9]*$|_group[0-9]*$|_stack$|_box$|_whole$|_solo$/', '', $baseName);
          $baseNameLower = strtolower($baseName);
          
          // First, try exact filename match with different extensions
          if (!empty($baseName)) {
            foreach ($extensionsToTry as $ext) {
              $testFileName = $baseName . '.' . $ext;
              if (in_array($testFileName, $files)) {
                $actualCategory = basename(dirname($productDir));
                $actualProduct = basename($productDir);
                return "bread image/$actualCategory/$actualProduct/$testFileName";
              }
            }
          }
          
          // Then, try to match by base name (without extension)
          if (!empty($baseNameLower)) {
            foreach ($files as $file) {
              if ($file === '.' || $file === '..' || is_dir($productDir . '/' . $file)) continue;
              
              $fileLower = strtolower(pathinfo($file, PATHINFO_FILENAME));
              $fileBase = preg_replace('/_[0-9]+$|_batch[0-9]*$|_group[0-9]*$|_stack$|_box$|_whole$|_solo$/', '', $fileLower);
              
              if ($fileBase === $baseNameLower || 
                  strpos($fileLower, $baseNameLower) !== false ||
                  strpos($baseNameLower, $fileLower) !== false) {
                // Reconstruct path with actual category/product names
                $actualCategory = basename(dirname($productDir));
                $actualProduct = basename($productDir);
                return "bread image/$actualCategory/$actualProduct/$file";
              }
            }
          }
          
          // If no match, just return first image file found
          foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
              // Reconstruct path with actual category/product names
              $actualCategory = basename(dirname($productDir));
              $actualProduct = basename($productDir);
              return "bread image/$actualCategory/$actualProduct/$file";
            }
          }
        }
      }
    }
  }
  
  // Return normalized path if nothing found
  return $normalizedPath;
}

// Function to URL encode image path for use in HTML
function imageUrl($path) {
  if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
    return $path;
  }
  // Split path and encode each segment
  $parts = explode('/', $path);
  $encodedParts = array_map('rawurlencode', $parts);
  return implode('/', $encodedParts);
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
