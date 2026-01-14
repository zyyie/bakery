<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();

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

$summary = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        ensure_item_images_table_exists();

        $baseDir = realpath(__DIR__ . '/../../frontend/images');
        if (!$baseDir || !is_dir($baseDir)) {
            throw new RuntimeException('frontend/images directory not found');
        }

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

        $summary = [
            'total_items' => $totalItems,
            'matched_items' => $matchedItems,
            'inserted' => $inserted,
            'skipped_existing' => $skipped,
        ];
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Item Images</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="m-0">Import Item Images</h3>
        <a class="btn btn-outline-secondary" href="dashboard.php">Back</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($summary): ?>
        <div class="alert alert-success">
            <div><strong>Total Items:</strong> <?php echo (int)$summary['total_items']; ?></div>
            <div><strong>Matched Items:</strong> <?php echo (int)$summary['matched_items']; ?></div>
            <div><strong>Inserted Images:</strong> <?php echo (int)$summary['inserted']; ?></div>
            <div><strong>Skipped (Already in DB):</strong> <?php echo (int)$summary['skipped_existing']; ?></div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <button type="submit" class="btn btn-primary">Import All Images from frontend/images</button>
            </form>
            <div class="mt-3 text-muted">
                This scans <code>frontend/images</code> and inserts discovered image file paths into <code>item_images</code> for matching items.
            </div>
        </div>
    </div>
</div>
</body>
</html>
