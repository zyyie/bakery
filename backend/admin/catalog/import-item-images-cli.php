<?php
/**
 * CLI version of import-item-images.php
 * Run: php backend/admin/catalog/import-item-images-cli.php
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

// Skip admin login check for CLI
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from command line.\n");
}

function ensure_item_images_table_exists(): void {
    $sql = "CREATE TABLE IF NOT EXISTS item_images (
        imageID INT(11) NOT NULL AUTO_INCREMENT,
        itemID INT(11) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        is_primary TINYINT(1) NOT NULL DEFAULT 0,
        sort_order INT(11) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (imageID),
        KEY idx_item_images_item (itemID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    executeQuery($sql);

    $sqlFk = "SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
              WHERE CONSTRAINT_SCHEMA = DATABASE()
                AND TABLE_NAME = 'item_images'
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                AND CONSTRAINT_NAME = 'fk_item_images_item'";
    $rs = executeQuery($sqlFk);
    $hasFk = false;
    if ($rs && ($row = mysqli_fetch_assoc($rs))) {
        $hasFk = ((int)$row['c'] > 0);
    }

    if (!$hasFk) {
        @executeQuery("ALTER TABLE item_images ADD CONSTRAINT fk_item_images_item FOREIGN KEY (itemID) REFERENCES items (itemID) ON DELETE CASCADE");
    }
}

function normalize_text(string $s): string {
    $s = mb_strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/u', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

function is_image_file(string $path): bool {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
}

function score_folder_for_item(string $itemName, string $folderPath): int {
    $a = normalize_text($itemName);
    $b = normalize_text($folderPath);
    if ($a === '' || $b === '') return 0;

    if ($a === $b) return 100;
    if (strpos($b, $a) !== false) return 80;

    $aTokens = array_values(array_filter(explode(' ', $a), static fn($t) => strlen($t) >= 3));
    $bTokens = array_values(array_filter(explode(' ', $b), static fn($t) => strlen($t) >= 3));

    if (empty($aTokens) || empty($bTokens)) return 0;

    $bSet = array_fill_keys($bTokens, true);
    $score = 0;
    foreach ($aTokens as $t) {
        if (isset($bSet[$t])) {
            $score += 10;
        } else {
            foreach ($bTokens as $bt) {
                if (strpos($bt, $t) !== false || strpos($t, $bt) !== false) {
                    $score += 3;
                    break;
                }
            }
        }
    }

    return $score;
}

function image_rank(string $filenameLower): int {
    $r = 10;
    if (strpos($filenameLower, 'solo') !== false) $r -= 4;
    if (strpos($filenameLower, 'whole') !== false) $r -= 3;
    if (strpos($filenameLower, 'batch') !== false) $r -= 2;
    if (strpos($filenameLower, 'stack') !== false) $r += 1;
    if (strpos($filenameLower, 'box') !== false) $r += 5;
    return $r;
}

echo "Starting image import...\n";

try {
    ensure_item_images_table_exists();
    echo "Table ensured.\n";

    // Try multiple possible paths
    $possiblePaths = [
        __DIR__ . '/../../frontend/images',
        __DIR__ . '/../../../frontend/images',
        dirname(dirname(dirname(__DIR__))) . '/frontend/images',
    ];
    
    $baseDir = null;
    foreach ($possiblePaths as $path) {
        $realPath = realpath($path);
        if ($realPath && is_dir($realPath)) {
            $baseDir = $realPath;
            break;
        }
    }
    
    if (!$baseDir) {
        throw new RuntimeException('frontend/images directory not found. Tried: ' . implode(', ', $possiblePaths));
    }

    echo "Scanning: $baseDir\n";

    $folders = [];
    $imagesByFolder = [];

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($it as $fileInfo) {
        if ($fileInfo->isDir()) {
            continue;
        }

        $abs = $fileInfo->getPathname();
        if (!is_image_file($abs)) continue;

        $rel = str_replace('\\', '/', substr($abs, strlen($baseDir) + 1));
        $folderRel = dirname($rel);
        $folderRel = $folderRel === '.' ? '' : $folderRel;
        $folders[$folderRel] = true;
        $imagesByFolder[$folderRel][] = $rel;
    }

    $foldersList = array_keys($folders);
    echo "Found " . count($foldersList) . " folders with images.\n";

    $itemsRs = executeQuery("SELECT itemID, packageName FROM items");
    if (!$itemsRs) {
        throw new RuntimeException('Failed to load items');
    }

    $totalItems = 0;
    $matchedItems = 0;
    $inserted = 0;
    $skipped = 0;

    while ($item = mysqli_fetch_assoc($itemsRs)) {
        $totalItems++;
        $itemID = (int)$item['itemID'];
        $packageName = (string)$item['packageName'];

        $bestFolder = null;
        $bestScore = 0;
        foreach ($foldersList as $folderRel) {
            $score = score_folder_for_item($packageName, $folderRel);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestFolder = $folderRel;
            }
        }

        if ($bestFolder === null || $bestScore <= 0) {
            continue;
        }

        $imgs = $imagesByFolder[$bestFolder] ?? [];
        if (empty($imgs)) {
            continue;
        }

        $matchedItems++;

        usort($imgs, static function ($a, $b) {
            $al = strtolower(basename($a));
            $bl = strtolower(basename($b));
            $ra = image_rank($al);
            $rb = image_rank($bl);
            if ($ra === $rb) {
                return strcmp($al, $bl);
            }
            return $ra <=> $rb;
        });

        $seenPrimary = false;
        $order = 0;

        foreach ($imgs as $rel) {
            $path = 'frontend/images/' . str_replace('\\', '/', $rel);
            $existsRs = executePreparedQuery(
                "SELECT imageID FROM item_images WHERE itemID = ? AND image_path = ? LIMIT 1",
                "is",
                [$itemID, $path]
            );
            if ($existsRs && mysqli_num_rows($existsRs) > 0) {
                $skipped++;
                continue;
            }

            $isPrimary = 0;
            if (!$seenPrimary) {
                $isPrimary = 1;
                $seenPrimary = true;
            }

            $ok = executePreparedUpdate(
                "INSERT INTO item_images (itemID, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)",
                "isii",
                [$itemID, $path, $isPrimary, $order]
            );

            if ($ok) {
                $inserted++;
            }

            $order++;
        }
    }

    echo "\n=== Import Summary ===\n";
    echo "Total Items: $totalItems\n";
    echo "Matched Items: $matchedItems\n";
    echo "Inserted Images: $inserted\n";
    echo "Skipped (Already in DB): $skipped\n";
    echo "\nDone!\n";

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
