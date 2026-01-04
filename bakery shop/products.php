<?php
require_once __DIR__ . '/includes/bootstrap.php';
include("includes/header.php");

// Function to get all related images for a product
function getAllProductImages($mainImage, $packageName) {
  // Resolve the main image path first
  $mainImage = resolveImagePath($mainImage);
  $allImages = [$mainImage];
  
  // Only process if image is from bread image folder
  if (strpos($mainImage, 'bread image/') === false && strpos($mainImage, 'bakery bread image/') === false) {
    return $allImages;
  }
  
  // Normalize path - convert 'bakery bread image' to 'bread image'
  $mainImage = str_replace('bakery bread image/', 'bread image/', $mainImage);
  
  // Get directory path
  $imagePath = __DIR__ . '/' . $mainImage;
  $imageDir = dirname($imagePath);
  $baseFileName = basename($mainImage);
  $fileInfo = pathinfo($baseFileName);
  $baseName = $fileInfo['filename'];
  $extension = isset($fileInfo['extension']) ? '.' . $fileInfo['extension'] : '';
  
  // Remove common suffixes to get base name
  $baseName = preg_replace('/_[0-9]+$|_batch[0-9]*$|_group[0-9]*$|_stack$|_box$|_whole$/', '', $baseName);
  
  // Common image patterns to look for
  $patterns = [
    $baseName . '_1' . $extension,
    $baseName . '_2' . $extension,
    $baseName . '_3' . $extension,
    $baseName . '_batch' . $extension,
    $baseName . '_batch1' . $extension,
    $baseName . '_batch2' . $extension,
    $baseName . '_batch3' . $extension,
    $baseName . '_group' . $extension,
    $baseName . '_group1' . $extension,
    $baseName . '_group2' . $extension,
    $baseName . '_stack' . $extension,
    $baseName . '_box' . $extension,
    $baseName . '_whole' . $extension,
  ];
  
  // Also try with different extensions
  $extensions = ['.jpg', '.jpeg', '.png', '.JPG', '.JPEG', '.PNG'];
  foreach ($extensions as $ext) {
    if ($ext !== $extension) {
      $patterns[] = $baseName . '_1' . $ext;
      $patterns[] = $baseName . '_2' . $ext;
      $patterns[] = $baseName . '_batch' . $ext;
      $patterns[] = $baseName . '_group' . $ext;
      $patterns[] = $baseName . '_stack' . $ext;
      $patterns[] = $baseName . '_box' . $ext;
    }
  }
  
  // Check if files exist and get ALL images in the folder
  if (is_dir($imageDir)) {
    $files = scandir($imageDir);
    $foundImages = [];
    
    foreach ($files as $file) {
      if ($file === '.' || $file === '..') continue;
      
      $filePath = $imageDir . '/' . $file;
      if (is_file($filePath)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
          // Get relative path
          $relativePath = str_replace(__DIR__ . '/', '', $filePath);
          // Normalize path
          $relativePath = str_replace('bakery bread image/', 'bread image/', $relativePath);
          
          // Add all images from the folder
          if (!in_array($relativePath, $allImages)) {
            $foundImages[] = [
              'path' => $relativePath,
              'filename' => $file,
              'time' => filemtime($filePath)
            ];
          }
        }
      }
    }
    
    // Sort by filename to ensure consistent order
    usort($foundImages, function($a, $b) {
      return strcmp($a['filename'], $b['filename']);
    });
    
    // Add all found images (excluding the main image which is already first)
    foreach ($foundImages as $img) {
      if ($img['path'] !== $mainImage && !in_array($img['path'], $allImages)) {
        $allImages[] = $img['path'];
      }
    }
  }
  
  return $allImages;
}

// Check if a specific product should be auto-opened in modal
$autoOpenProduct = null;
if (isset($_GET['product'])) {
  $productId = intval($_GET['product']);
  if ($productId > 0) {
    // Load product images mapping
    $imagesMap = [];
    $imagesJsonPath = __DIR__ . '/product-images.json';
    if (file_exists($imagesJsonPath)) {
      $imagesJson = file_get_contents($imagesJsonPath);
      $imagesMap = json_decode($imagesJson, true) ?: [];
    }
    
    // Fetch product data
    $productQuery = "SELECT items.*, categories.categoryName FROM items 
                     LEFT JOIN categories ON items.categoryID = categories.categoryID 
                     WHERE items.itemID = ? AND items.status = 'Active'";
    $productResult = executePreparedQuery($productQuery, "i", [$productId]);
    if ($productResult && ($product = mysqli_fetch_assoc($productResult))) {
      // Get image from JSON mapping, fallback to database, then placeholder
      $productImage = 'https://via.placeholder.com/300x200';
      $packageName = $product['packageName'];
      
      if (isset($imagesMap[$packageName])) {
        $productImage = $imagesMap[$packageName];
      } elseif (!empty($product['itemImage'])) {
        $productImage = 'bread image/' . $product['itemImage'];
      }
      
      // Resolve the actual image path
      $productImage = resolveImagePath($productImage);
      
      // Get all related images
      $allProductImages = getAllProductImages($productImage, $packageName);
      // URL-encode all image paths for use in HTML/JavaScript
      $allProductImagesEncoded = array_map('imageUrl', $allProductImages);
      
      $autoOpenProduct = [
        'itemID' => $product['itemID'],
        'packageName' => $product['packageName'],
        'productImage' => imageUrl($productImage),
        'allImages' => $allProductImagesEncoded,
        'price' => $product['price'],
        'foodDescription' => $product['foodDescription'],
        'itemContains' => $product['itemContains'] ?? '',
        'categoryName' => $product['categoryName']
      ];
    }
  }
}
?>


<div class="container products-page my-5">
  <div class="row">
    <!-- Sidebar Categories -->
    <div class="col-md-3">
      <div class="card sidebar-card mb-4">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-bars"></i> Shop By Category</h5>
        </div>
        <div class="list-group list-group-flush">
          <a href="products.php" class="list-group-item list-group-item-action">All Categories</a>
          <?php
          // Define exact category order as specified
          $categoryOrder = [
            'Bread–Cake Combo',
            'Brownies',
            'Buns & Rolls',
            'Classic & Basic Bread',
            'Cookies',
            'Crinkles',
            'Filled / Stuffed Bread',
            'Special (Budget-Friendly)',
            'Sweet Bread'
          ];
          
          // Get all categories from database
          $catResult = executePreparedQuery("SELECT * FROM categories", "", []);
          $categoriesMap = [];
          while($catResult && ($cat = mysqli_fetch_assoc($catResult))):
            $categoriesMap[$cat['categoryName']] = $cat;
          endwhile;
          
          // Display categories in the specified order
          foreach ($categoryOrder as $categoryName):
            if (isset($categoriesMap[$categoryName])):
              $cat = $categoriesMap[$categoryName];
          ?>
          <a href="products.php?category=<?php echo $cat['categoryID']; ?>" 
             class="list-group-item list-group-item-action">
            <?php echo e($cat['categoryName']); ?>
          </a>
          <?php 
            endif;
          endforeach; 
          ?>
        </div>
      </div>
    </div>

    <!-- Products -->
    <div class="col-md-9">
      <div class="row">
        <?php
        
        // Load product images mapping from JSON file
        $imagesMap = [];
        $imagesJsonPath = __DIR__ . '/product-images.json';
        if (file_exists($imagesJsonPath)) {
          $imagesJson = file_get_contents($imagesJsonPath);
          $imagesMap = json_decode($imagesJson, true) ?: [];
        }
        
        $categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
        if ($categoryId > 0) {
          $query = "SELECT items.*, categories.categoryName FROM items 
                    LEFT JOIN categories ON items.categoryID = categories.categoryID 
                    WHERE items.status = 'Active' AND items.categoryID = ?";
          $result = executePreparedQuery($query, "i", [$categoryId]);
        } else {
          $query = "SELECT items.*, categories.categoryName FROM items 
                    LEFT JOIN categories ON items.categoryID = categories.categoryID 
                    WHERE items.status = 'Active'";
          $result = executePreparedQuery($query, "", []);
        }
        
        if($result && mysqli_num_rows($result) > 0):
          while($row = mysqli_fetch_assoc($result)):
            // Get image from JSON mapping, fallback to database, then placeholder
            $productImage = 'https://via.placeholder.com/300x200';
            $packageName = $row['packageName'];
            
            if (isset($imagesMap[$packageName])) {
              $productImage = $imagesMap[$packageName];
              // Normalize path - convert 'bakery bread image' to 'bread image'
              $productImage = str_replace('bakery bread image/', 'bread image/', $productImage);
            } elseif (!empty($row['itemImage'])) {
              $productImage = 'bread image/' . $row['itemImage'];
            }
            
            // Resolve the actual image path
            $productImage = resolveImagePath($productImage);
            
            // Get all related images
            $allProductImages = getAllProductImages($productImage, $packageName);
            // URL-encode all image paths for use in HTML/JavaScript
            $allProductImagesEncoded = array_map('imageUrl', $allProductImages);
            // Ensure the first image matches the main product image (URL-encoded)
            if (!empty($allProductImagesEncoded) && $allProductImagesEncoded[0] !== imageUrl($productImage)) {
              // If first image doesn't match, prepend the main image
              array_unshift($allProductImagesEncoded, imageUrl($productImage));
            }
            $allImagesJson = json_encode($allProductImagesEncoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        ?>
        <div class="col-md-4 mb-4">
          <div class="card product-card" 
               style="cursor: pointer; height: 100%;" 
               onclick="if(!event.target.closest('button')) { const img = this.querySelector('img'); if(img) openProductModal(img); }">
            <div class="position-relative">
              <img src="<?php echo imageUrl($productImage); ?>" 
                   class="card-img-top" 
                   alt="<?php echo e($row['packageName']); ?>"
                   style="transition: transform 0.3s; width: 100%; height: 250px; object-fit: cover;"
                   onerror="this.onerror=null; this.src='https://via.placeholder.com/300x200?text=<?php echo urlencode($row['packageName']); ?>';"
                   data-bs-toggle="modal" 
                   data-bs-target="#productModal"
                   data-item-id="<?php echo (int)$row['itemID']; ?>"
                   data-product-name="<?php echo e($row['packageName']); ?>"
                   data-product-image="<?php echo e(imageUrl($productImage)); ?>"
                   data-product-images="<?php echo e($allImagesJson); ?>"
                   data-product-price="<?php echo e($row['price']); ?>"
                   data-product-description="<?php echo e($row['foodDescription']); ?>"
                   data-product-contains="<?php echo e($row['itemContains'] ?? ''); ?>"
                   data-category-name="<?php echo e($row['categoryName']); ?>"
                   onmouseover="this.style.transform='scale(1.05)'"
                   onmouseout="this.style.transform='scale(1)'">
              <button class="btn btn-favorite" style="z-index: 10;" onclick="toggleFavorite(this, <?php echo (int)$row['itemID']; ?>); event.stopPropagation();">
                <i class="far fa-heart"></i>
              </button>
            </div>
            <div class="card-body">
              <span class="category-badge"><?php echo e($row['categoryName']); ?></span>
              <h5 class="card-title mt-2"><?php echo e($row['packageName']); ?></h5>
              <p class="card-text text-muted"><?php echo e(substr($row['foodDescription'], 0, 60)); ?>...</p>
              <p class="price">₱<?php echo e($row['price']); ?></p>
            </div>
          </div>
        </div>
        <?php 
          endwhile;
        else:
        ?>
        <div class="col-12">
          <div class="alert alert-info">No products found in this category.</div>
        </div>
        <?php endif; ?>
      </div>
      
      <!-- Debug: Image Paths Section -->
      <?php if (isset($_GET['debug']) && $_GET['debug'] === 'images'): ?>
      <div class="col-12 mt-5">
        <div class="card">
          <div class="card-header bg-warning">
            <h5 class="mb-0">🔍 Complete Image Paths Debug Information</h5>
            <small class="text-muted">Showing all image paths for each product</small>
          </div>
          <div class="card-body">
            <h6>Product Images from JSON Mapping:</h6>
            <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;"><?php 
              $imagesMap = [];
              $imagesJsonPath = __DIR__ . '/product-images.json';
              if (file_exists($imagesJsonPath)) {
                $imagesJson = file_get_contents($imagesJsonPath);
                $imagesMap = json_decode($imagesJson, true) ?: [];
              }
              echo json_encode($imagesMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            ?></pre>
            
            <h6 class="mt-4">All Products with Complete Image Paths:</h6>
            <table class="table table-sm table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Product Name</th>
                  <th>Category</th>
                  <th>Main Image Path (JSON)</th>
                  <th>Resolved Main Path</th>
                  <th>File Exists</th>
                  <th>All Related Images</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $allProductsQuery = "SELECT items.*, categories.categoryName FROM items 
                                   LEFT JOIN categories ON items.categoryID = categories.categoryID 
                                   WHERE items.status = 'Active' 
                                   ORDER BY categories.categoryName, items.packageName";
                $allProductsResult = executePreparedQuery($allProductsQuery, "", []);
                
                if ($allProductsResult):
                  $prodNum = 1;
                  while ($prod = mysqli_fetch_assoc($allProductsResult)):
                    $prodName = $prod['packageName'];
                    $jsonImage = isset($imagesMap[$prodName]) ? $imagesMap[$prodName] : 'Not in JSON';
                    $resolvedImage = resolveImagePath($jsonImage !== 'Not in JSON' ? $jsonImage : ('bread image/' . $prod['itemImage']));
                    $fullPath = __DIR__ . '/' . $resolvedImage;
                    $fileExists = file_exists($fullPath) ? '✅ Yes' : '❌ No';
                    
                    // Get all related images
                    $allRelatedImages = getAllProductImages($resolvedImage, $prodName);
                ?>
                <tr>
                  <td><?php echo $prodNum++; ?></td>
                  <td><strong><?php echo e($prodName); ?></strong></td>
                  <td><?php echo e($prod['categoryName']); ?></td>
                  <td><small style="word-break: break-all;"><?php echo e($jsonImage); ?></small></td>
                  <td><small style="word-break: break-all; color: #0066cc;"><?php echo e($resolvedImage); ?></small></td>
                  <td><?php echo $fileExists; ?></td>
                  <td>
                    <div style="max-width: 400px;">
                      <?php if (count($allRelatedImages) > 0): ?>
                        <ul style="margin: 0; padding-left: 20px; font-size: 0.85em;">
                          <?php foreach ($allRelatedImages as $idx => $imgPath): ?>
                            <?php 
                              $imgFullPath = __DIR__ . '/' . $imgPath;
                              $imgExists = file_exists($imgFullPath);
                              $imgUrl = imageUrl($imgPath);
                            ?>
                            <li style="margin-bottom: 5px;">
                              <span style="color: <?php echo $imgExists ? '#28a745' : '#dc3545'; ?>;">
                                <?php echo $imgExists ? '✅' : '❌'; ?>
                              </span>
                              <a href="<?php echo htmlspecialchars($imgUrl); ?>" target="_blank" style="color: #0066cc; text-decoration: none;">
                                <?php echo e($imgPath); ?>
                              </a>
                              <?php if ($idx === 0): ?>
                                <span class="badge bg-primary">Main</span>
                              <?php endif; ?>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      <?php else: ?>
                        <span class="text-muted">No images found</span>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <?php
                  endwhile;
                endif;
                ?>
              </tbody>
            </table>
            
            <div class="mt-4 p-3 bg-light rounded">
              <h6>Summary:</h6>
              <ul>
                <li><strong>Total Products:</strong> <?php echo $prodNum - 1; ?></li>
                <li><strong>Products with images in JSON:</strong> <?php 
                  $count = 0;
                  if ($allProductsResult) {
                    mysqli_data_seek($allProductsResult, 0);
                    while ($p = mysqli_fetch_assoc($allProductsResult)) {
                      if (isset($imagesMap[$p['packageName']])) $count++;
                    }
                  }
                  echo $count;
                ?></li>
                <li><strong>Products with existing image files:</strong> <?php 
                  $count = 0;
                  if ($allProductsResult) {
                    mysqli_data_seek($allProductsResult, 0);
                    while ($p = mysqli_fetch_assoc($allProductsResult)) {
                      $img = isset($imagesMap[$p['packageName']]) ? $imagesMap[$p['packageName']] : ('bread image/' . $p['itemImage']);
                      $resolved = resolveImagePath($img);
                      if (file_exists(__DIR__ . '/' . $resolved)) $count++;
                    }
                  }
                  echo $count;
                ?></li>
              </ul>
            </div>
            
            <p class="text-muted mt-3">
              <small>To hide this debug section, remove <code>?debug=images</code> from the URL</small>
            </p>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
/* Image Path Display Styles */
.product-card .card-body details {
  margin-top: 5px;
}

.product-card .card-body details summary {
  list-style: none;
  outline: none;
}

.product-card .card-body details summary::-webkit-details-marker {
  display: none;
}

.product-card .card-body details summary:hover {
  text-decoration: underline;
}

.product-card .card-body .border-top {
  border-color: #e9ecef !important;
}

/* Product Modal Bottom Sheet Style */
#productModal .modal-dialog {
  margin: 0;
  max-width: 100%;
  height: 100%;
  display: flex;
  align-items: flex-end;
}

@media (max-width: 575.98px) {
  #productModal .modal-dialog {
    margin: 0;
  }
  
  #productModal .modal-content {
    border-radius: 20px 20px 0 0;
    margin: 0;
    max-height: 90vh;
  }
}

#productModal .modal-content {
  border-radius: 20px 20px 0 0;
  border: none;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.product-modal-image-wrapper {
  position: relative;
  width: 100%;
  overflow: hidden;
  background-color: #f8f9fa;
}

.product-modal-image-wrapper {
  position: relative;
}

.product-modal-image-wrapper img {
  width: 100%;
  display: block;
  object-fit: cover;
}

/* Image Gallery Carousel */
.product-image-gallery {
  position: relative;
  background-color: #f8f9fa;
  padding: 15px 50px;
  margin-top: 10px;
  border-radius: 10px;
}

.gallery-images-container {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  scroll-behavior: smooth;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE and Edge */
}

.gallery-images-container::-webkit-scrollbar {
  display: none; /* Chrome, Safari, Opera */
}

.gallery-image-thumb {
  flex: 0 0 auto;
  width: 80px;
  height: 80px;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.gallery-image-thumb:hover {
  border-color: #ffc107;
  transform: scale(1.05);
}

.gallery-image-thumb.active {
  border-color: #ffc107;
  box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.3);
}

.gallery-image-thumb {
  flex: 0 0 auto;
  width: 80px;
  height: 80px;
  border-radius: 8px;
  overflow: visible;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.3s ease;
  background-color: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3px;
  box-sizing: border-box;
}

.gallery-image-thumb:hover {
  border-color: #ffc107;
  transform: scale(1.05);
}

.gallery-image-thumb.active {
  border-color: #ffc107;
  box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.3);
}

.gallery-image-thumb img {
  max-width: 100%;
  max-height: 100%;
  width: auto;
  height: auto;
  object-fit: contain;
  display: block;
  border-radius: 4px;
}

.gallery-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background-color: #fff;
  border: 1px solid #dee2e6;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  transition: all 0.3s ease;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.gallery-arrow:hover {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #000;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Image Lightbox/Popup */
.product-modal-image-wrapper img {
  cursor: pointer;
  transition: opacity 0.3s ease;
}

.product-modal-image-wrapper img:hover {
  opacity: 0.9;
}

.image-lightbox {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.95);
  animation: fadeIn 0.3s ease;
}

.image-lightbox.active {
  display: flex;
  align-items: center;
  justify-content: center;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.lightbox-content {
  position: relative;
  max-width: 90%;
  max-height: 90%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.lightbox-image {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.lightbox-close {
  position: absolute;
  top: 20px;
  right: 30px;
  color: #fff;
  font-size: 40px;
  font-weight: bold;
  cursor: pointer;
  z-index: 10000;
  transition: all 0.3s ease;
  background: rgba(0, 0, 0, 0.5);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}

.lightbox-close:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: rotate(90deg);
}

.lightbox-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  font-size: 30px;
  padding: 15px 20px;
  cursor: pointer;
  border: none;
  border-radius: 5px;
  transition: all 0.3s ease;
  z-index: 10000;
}

.lightbox-nav:hover {
  background: rgba(255, 255, 255, 0.2);
}

.lightbox-nav.prev {
  left: 20px;
}

.lightbox-nav.next {
  right: 20px;
}

.lightbox-counter {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  color: #fff;
  background: rgba(0, 0, 0, 0.5);
  padding: 10px 20px;
  border-radius: 20px;
  font-size: 14px;
  z-index: 10000;
}

.gallery-arrow-left {
  left: 5px;
}

.gallery-arrow-right {
  right: 5px;
}

.gallery-arrow i {
  font-size: 1rem;
  color: #333;
}

.gallery-arrow:hover i {
  color: #000;
}

@media (min-width: 576px) {
  #productModal .modal-dialog {
    max-width: 600px;
    margin: 1.75rem auto;
  }
  
  #productModal .modal-content {
    border-radius: 20px;
    min-height: auto;
    max-height: 85vh;
  }
  
  .product-modal-image-wrapper img {
    height: 400px;
  }
}

/* Package Type Buttons */
.btn-group .btn-check:checked + .btn-outline-warning {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #000;
  font-weight: 600;
}

.btn-group .btn-outline-warning {
  border-color: #ffc107;
  color: #ffc107;
  font-weight: 500;
}

.btn-group .btn-outline-warning:hover {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #000;
}

/* Quantity Input Group */
.input-group .btn-outline-secondary {
  border-color: #dee2e6;
  color: #6c757d;
  width: 40px;
}

.input-group .btn-outline-secondary:hover {
  background-color: #e9ecef;
  border-color: #dee2e6;
  color: #495057;
}

#modalQuantity {
  font-weight: 600;
  font-size: 1.1rem;
  border-left: none;
  border-right: none;
}

/* Add to Cart Button */
.modal-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
  padding: 1rem;
}

.modal-footer .btn-warning {
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0.75rem 1.5rem;
}

.modal-footer .btn-outline-warning {
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0.75rem 1.5rem;
  border-color: #ffc107;
  color: #ffc107;
}

.modal-footer .btn-outline-warning:hover {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #000;
}

.modal-footer .btn-outline-secondary {
  font-weight: 500;
}

/* Quantity Buttons */
.quantity-buttons .btn {
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  transition: all 0.3s ease;
}

.quantity-buttons .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.quantity-buttons .btn.btn-warning {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #000;
  font-weight: 600;
}

/* Category Badge */
#modalCategoryBadge {
  font-size: 0.85rem;
  padding: 0.35rem 0.75rem;
  font-weight: 500;
}

/* Favorite Button */
.modal-body .btn-link {
  text-decoration: none;
}

.modal-body .btn-link i.fas {
  color: #dc3545 !important;
}

/* Reviews Section */
.review-form {
  background-color: #f8f9fa;
  border-radius: 8px;
}

.star-rating {
  display: flex;
  align-items: center;
  gap: 5px;
}

.star-rating input[type="radio"] {
  display: none;
}

.star-rating label {
  font-size: 1.5rem;
  color: #ddd;
  cursor: pointer;
  transition: all 0.2s;
  margin: 0;
}

.star-rating label i {
  transition: all 0.2s;
}

.star-rating label:hover i,
.star-rating label.active i {
  color: #ffc107;
}

.star-rating label.active i {
  font-weight: 900;
}

.rating-text {
  font-size: 0.9rem;
  color: #666;
  font-weight: 500;
}

.reviews-list {
  max-height: 400px;
  overflow-y: auto;
}

.review-item {
  padding: 1rem;
  border-bottom: 1px solid #e9ecef;
  margin-bottom: 1rem;
}

.review-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.reviewer-name {
  font-weight: 600;
  color: #333;
  margin-right: 0.5rem;
}

.review-date {
  font-size: 0.85rem;
  color: #6c757d;
}

.review-stars {
  color: #ffc107;
  margin-bottom: 0.5rem;
}

.review-comment {
  color: #555;
  line-height: 1.6;
  margin: 0;
}

.review-item .badge {
  font-size: 0.75rem;
}

.average-rating {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background-color: #fff3cd;
  border-radius: 5px;
}

.average-rating-number {
  font-size: 1.5rem;
  font-weight: bold;
  color: #856404;
}

.average-rating-stars {
  color: #ffc107;
  font-size: 1.2rem;
}
</style>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true" data-bs-backdrop="true">
  <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content product-modal-content">
      <div class="modal-header border-0 pb-2">
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <form method="POST" action="cart.php" id="modalCartForm">
          <input type="hidden" name="itemID" id="modalItemID">
          <input type="hidden" name="quantity" id="modalQuantityHidden">
          <input type="hidden" name="packageType" id="modalPackageType">
          <input type="hidden" name="action" id="modalAction" value="add">
          
          <!-- Product Image -->
          <div class="product-modal-image-wrapper">
            <img id="modalProductImage" src="" class="img-fluid w-100" alt="Product Image" style="height: 350px; object-fit: cover; cursor: pointer;" onclick="openImageLightbox(this.src)">
            
            <!-- Image Gallery Carousel -->
            <div class="product-image-gallery" id="productImageGallery" style="display: none;">
              <button type="button" class="gallery-arrow gallery-arrow-left" onclick="event.preventDefault(); event.stopPropagation(); navigateProductImages('left', event); return false;">
                <i class="fas fa-chevron-left"></i>
              </button>
              <div class="gallery-images-container" id="galleryImagesContainer">
                <!-- Images will be populated by JavaScript -->
              </div>
              <button type="button" class="gallery-arrow gallery-arrow-right" onclick="event.preventDefault(); event.stopPropagation(); navigateProductImages('right', event); return false;">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
          
          <!-- Product Details -->
          <div class="p-4">
            <!-- Category Badge -->
            <span class="badge bg-secondary mb-2" id="modalCategoryBadge"></span>
            
            <!-- Product Name and Favorite -->
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h4 id="modalProductName" class="mb-0 flex-grow-1"></h4>
              <button type="button" class="btn btn-link p-0 ms-2" onclick="toggleModalFavorite(this)" style="font-size: 1.5rem; color: #ccc;">
                <i class="far fa-heart"></i>
              </button>
            </div>
            
            <!-- Price -->
            <p class="h5 text-warning fw-bold mb-4" id="modalProductPrice"></p>
            
            <!-- Description -->
            <div class="mb-4">
              <h6 class="fw-bold mb-2">Description</h6>
              <p class="text-muted mb-0" id="modalProductDescription"></p>
            </div>
            
            <!-- Item Contains -->
            <div class="mb-4" id="modalContainsSection">
              <h6 class="fw-bold mb-2">Item Contains</h6>
              <p class="text-muted mb-0" id="modalProductContains"></p>
            </div>
            
            <!-- Quantity Selection Buttons -->
            <div class="mb-4">
              <label class="fw-bold mb-2 d-block">Quantity:</label>
              <div class="quantity-buttons d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary quantity-btn" data-quantity="1" onclick="setQuantity(1)">1 pcs</button>
                <button type="button" class="btn btn-outline-secondary quantity-btn" data-quantity="4" onclick="setQuantity(4)">4 pcs</button>
                <button type="button" class="btn btn-outline-secondary quantity-btn" data-quantity="6" onclick="setQuantity(6)">6 pcs</button>
                <button type="button" class="btn btn-outline-secondary quantity-btn" data-quantity="8" onclick="setQuantity(8)">8 pcs</button>
                <button type="button" class="btn btn-outline-secondary quantity-btn" data-quantity="12" onclick="setQuantity(12)">12 pcs</button>
                <button type="button" class="btn btn-outline-secondary quantity-btn" data-quantity="16" onclick="setQuantity(16)">16 pcs</button>
                <button type="button" class="btn btn-outline-secondary quantity-btn" data-quantity="20" onclick="setQuantity(20)">20 pcs</button>
                <button type="button" class="btn btn-outline-secondary quantity-btn" data-quantity="24" onclick="setQuantity(24)">24 pcs</button>
                <button type="button" class="btn btn-outline-secondary quantity-btn" data-quantity="32" onclick="setQuantity(32)">32 pcs</button>
              </div>
              <div class="input-group mt-3" style="max-width: 150px;">
                <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity()">-</button>
                <input type="number" class="form-control text-center" id="modalQuantity" name="quantity" value="1" min="1" style="border-left: 0; border-right: 0;" onchange="updateQuantityButtons()">
                <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity()">+</button>
              </div>
            </div>
            
            <!-- Package Type Selection -->
            <div class="mb-4">
              <label class="fw-bold mb-2 d-block">Package Type:</label>
              <div class="btn-group w-100" role="group" aria-label="Package type">
                <input type="radio" class="btn-check" name="packageType" id="packageBox" value="Per Box" checked>
                <label class="btn btn-outline-warning" for="packageBox">Per Box</label>
                
                <input type="radio" class="btn-check" name="packageType" id="packagePack" value="Per Pack">
                <label class="btn btn-outline-warning" for="packagePack">Per Pack</label>
              </div>
            </div>
            
            <!-- Customer Reviews Section -->
            <div class="mb-4 border-top pt-4" id="reviewsSection">
              <h6 class="fw-bold mb-3">
                <i class="fas fa-star text-warning me-2"></i>Customer Reviews
                <span class="badge bg-secondary ms-2" id="reviewsCount">0</span>
              </h6>
              
              <!-- Review Form (only if logged in) -->
              <?php if (isset($_SESSION['userID'])): ?>
              <div id="reviewFormContainer" style="display: none;">
                <div class="review-form mb-4 p-3 bg-light rounded">
                  <h6 class="fw-bold mb-3">Write a Review</h6>
                  <form id="reviewForm">
                  <input type="hidden" name="itemID" id="reviewItemID">
                  
                  <!-- Star Rating -->
                  <div class="mb-3">
                    <label class="form-label fw-bold">Rating:</label>
                    <div class="star-rating" id="starRatingContainer">
                      <label for="star1" class="star-label" data-rating="1">
                        <i class="far fa-star"></i>
                      </label>
                      <input type="radio" name="rating" id="star1" value="1" required>
                      
                      <label for="star2" class="star-label" data-rating="2">
                        <i class="far fa-star"></i>
                      </label>
                      <input type="radio" name="rating" id="star2" value="2">
                      
                      <label for="star3" class="star-label" data-rating="3">
                        <i class="far fa-star"></i>
                      </label>
                      <input type="radio" name="rating" id="star3" value="3">
                      
                      <label for="star4" class="star-label" data-rating="4">
                        <i class="far fa-star"></i>
                      </label>
                      <input type="radio" name="rating" id="star4" value="4">
                      
                      <label for="star5" class="star-label" data-rating="5">
                        <i class="far fa-star"></i>
                      </label>
                      <input type="radio" name="rating" id="star5" value="5">
                      
                      <span class="rating-text ms-2" id="ratingText">Select rating</span>
                    </div>
                  </div>
                  
                  <!-- Comment -->
                  <div class="mb-3">
                    <label for="reviewComment" class="form-label fw-bold">Your Review:</label>
                    <textarea class="form-control" id="reviewComment" name="comment" rows="3" 
                              placeholder="Share your experience... Is it delicious? How was the quality?" required></textarea>
                  </div>
                  
                  <!-- Submit Button -->
                  <button type="submit" class="btn btn-warning">
                    <i class="fas fa-paper-plane me-2"></i>Submit Review
                  </button>
                </form>
                </div>
              </div>
              <div id="reviewNotAllowedContainer" style="display: none;">
                <div class="alert alert-warning mb-4">
                  <i class="fas fa-info-circle me-2"></i>
                  <span id="reviewNotAllowedMessage">You can only review products you have received. Please wait until your order is delivered.</span>
                </div>
              </div>
              <?php else: ?>
              <div class="alert alert-info">
                <a href="login.php" class="alert-link">Login</a> to write a review
              </div>
              <?php endif; ?>
              
              <!-- Reviews List -->
              <div id="reviewsList" class="reviews-list">
                <!-- Reviews will be loaded here via AJAX -->
                <div class="text-center text-muted py-3">
                  <i class="fas fa-spinner fa-spin"></i> Loading reviews...
                </div>
              </div>
            </div>
          </div>
          
          <!-- Footer with Chat, Add to Cart and Buy Buttons -->
          <div class="modal-footer border-top p-3 bg-light">
            <div class="product-modal-actions">
              <button type="button" class="btn btn-chat-now btn-sm flex-grow-1" onclick="openChatWithSeller()">
                <i class="fas fa-comments"></i>Chat Now
              </button>
              <button type="button" class="btn btn-outline-warning btn-sm flex-grow-1" id="addToCartBtn" onclick="handleAddToCart()">
                <i class="fas fa-shopping-cart me-1"></i>ADD TO CART
              </button>
              <button type="button" class="btn btn-warning btn-sm flex-grow-1" id="buyNowBtn" onclick="handleBuyNow()">
                <i class="fas fa-bolt me-1"></i>BUY NOW
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Image Lightbox/Popup -->
<div class="image-lightbox" id="imageLightbox" onclick="closeImageLightbox(event)">
  <span class="lightbox-close" onclick="closeImageLightbox(event)">&times;</span>
  <button class="lightbox-nav prev" onclick="navigateLightbox('prev', event)">
    <i class="fas fa-chevron-left"></i>
  </button>
  <div class="lightbox-content" onclick="event.stopPropagation()">
    <img id="lightboxImage" class="lightbox-image" src="" alt="Product Image">
  </div>
  <button class="lightbox-nav next" onclick="navigateLightbox('next', event)">
    <i class="fas fa-chevron-right"></i>
  </button>
  <div class="lightbox-counter" id="lightboxCounter"></div>
</div>

<script>
function openProductModal(button) {
  // Get product data from data attributes
  const itemID = button.getAttribute('data-item-id');
  const productName = button.getAttribute('data-product-name');
  const productImage = button.getAttribute('data-product-image');
  const productImagesJson = button.getAttribute('data-product-images');
  const productPrice = button.getAttribute('data-product-price');
  const productDescription = button.getAttribute('data-product-description');
  const productContains = button.getAttribute('data-product-contains');
  const categoryName = button.getAttribute('data-category-name');
  
  // Get all images for this product (from PHP)
  let allImages = [productImage];
  try {
    if (productImagesJson) {
      const parsed = JSON.parse(productImagesJson);
      if (Array.isArray(parsed) && parsed.length > 0) {
        allImages = parsed;
        // Ensure productImage is in the array and is first
        const mainImageIndex = allImages.indexOf(productImage);
        if (mainImageIndex > 0) {
          // Move main image to first position
          allImages.splice(mainImageIndex, 1);
          allImages.unshift(productImage);
        } else if (mainImageIndex === -1) {
          // Main image not in array, add it first
          allImages.unshift(productImage);
        }
      }
    }
  } catch (e) {
    console.error('Error parsing product images:', e, 'JSON:', productImagesJson);
  }
  
  console.log('Product images loaded:', allImages.length, allImages);
  
  // Store images globally for lightbox and modal navigation
  window.currentProductImages = allImages;
  window.currentProductImageIndex = 0; // Track current image index for arrow key navigation
  
  // Function to update main image and active thumbnail
  function updateProductImage(index) {
    if (index < 0) index = allImages.length - 1;
    if (index >= allImages.length) index = 0;
    
    window.currentProductImageIndex = index;
    const img = allImages[index];
    document.getElementById('modalProductImage').src = img;
    
    // Update active thumbnail
    document.querySelectorAll('.gallery-image-thumb').forEach((t, i) => {
      if (i === index) {
        t.classList.add('active');
        // Scroll thumbnail into view
        t.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      } else {
        t.classList.remove('active');
      }
    });
  }
  
  // Set product image
  document.getElementById('modalProductImage').src = productImage;
  
  // Setup image gallery
  const gallery = document.getElementById('productImageGallery');
  const galleryContainer = document.getElementById('galleryImagesContainer');
  
  if (allImages.length > 1) {
    gallery.style.display = 'block';
    galleryContainer.innerHTML = '';
    
    allImages.forEach((img, index) => {
      const thumb = document.createElement('div');
      thumb.className = 'gallery-image-thumb' + (index === 0 ? ' active' : '');
      thumb.onclick = (e) => {
        if (e) {
          e.preventDefault();
          e.stopPropagation();
        }
        updateProductImage(index);
        // Open lightbox with clicked image
        openImageLightbox(img);
        return false;
      };
      
      const imgEl = document.createElement('img');
      imgEl.src = img;
      imgEl.alt = productName;
      imgEl.onerror = function() {
        console.error('Failed to load image:', img);
        this.style.display = 'none';
      };
      thumb.appendChild(imgEl);
      galleryContainer.appendChild(thumb);
    });
    
    // Store update function globally for arrow key navigation
    window.updateProductImage = updateProductImage;
  } else {
    gallery.style.display = 'none';
  }
  
  // Set category badge
  const categoryBadge = document.getElementById('modalCategoryBadge');
  if (categoryName) {
    categoryBadge.textContent = categoryName;
    categoryBadge.style.display = 'inline-block';
  } else {
    categoryBadge.style.display = 'none';
  }
  
  // Set product name
  document.getElementById('modalProductName').textContent = productName;
  
  // Set product price
  document.getElementById('modalProductPrice').textContent = '₱' + parseFloat(productPrice).toFixed(2);
  
  // Set description
  document.getElementById('modalProductDescription').textContent = productDescription || '';
  
  // Set item contains (specifications)
  const containsSection = document.getElementById('modalContainsSection');
  const containsDiv = document.getElementById('modalProductContains');
  if (productContains && productContains.trim()) {
    containsDiv.textContent = productContains;
    containsSection.style.display = 'block';
  } else {
    containsSection.style.display = 'none';
  }
  
  // Set item ID
  document.getElementById('modalItemID').value = itemID;
  
  // Reset quantity
  document.getElementById('modalQuantity').value = 1;
  document.getElementById('modalQuantityHidden').value = 1;
  updateQuantityButtons();
  
  // Reset package type to default (Per Box)
  document.getElementById('packageBox').checked = true;
  document.getElementById('modalPackageType').value = 'Per Box';
  
  // Reset buttons
  const addBtn = document.getElementById('addToCartBtn');
  const buyBtn = document.getElementById('buyNowBtn');
  if (addBtn) {
    addBtn.disabled = false;
    addBtn.innerHTML = '<i class="fas fa-shopping-cart me-1"></i>ADD TO CART';
  }
  if (buyBtn) {
    buyBtn.disabled = false;
    buyBtn.innerHTML = '<i class="fas fa-bolt me-1"></i>BUY NOW';
  }
  
  // Reset action
  document.getElementById('modalAction').value = 'add';
  
  // Set review item ID and load reviews
  document.getElementById('reviewItemID').value = itemID;
  loadReviews(itemID);
  
  // Check if user can review this product
  checkCanReview(itemID);
}

function openChatWithSeller() {
  // Simple redirect to contact page with product context
  const productName = document.getElementById('modalProductName') ? document.getElementById('modalProductName').textContent : '';
  const url = 'contact.php' + (productName ? ('?product=' + encodeURIComponent(productName)) : '');
  window.open(url, '_blank');
}

function toggleModalFavorite(btn) {
  // Check if user is logged in
  if (!isUserLoggedIn) {
    // Close product modal first
    const productModal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
    if (productModal) {
      productModal.hide();
    }
    // Show login required modal
    setTimeout(() => {
      const loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
      loginModal.show();
    }, 300);
    return;
  }
  
  const icon = btn.querySelector('i');
  if (icon.classList.contains('far')) {
    icon.classList.remove('far');
    icon.classList.add('fas');
    btn.style.color = '#dc3545';
    // Get itemID from modal
    const itemID = document.getElementById('modalItemID')?.value;
    if (itemID) {
      let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
      if (!favorites.includes(parseInt(itemID))) {
        favorites.push(parseInt(itemID));
      }
      localStorage.setItem('favorites', JSON.stringify(favorites));
    }
  } else {
    icon.classList.remove('fas');
    icon.classList.add('far');
    btn.style.color = '#ccc';
    // Get itemID from modal
    const itemID = document.getElementById('modalItemID')?.value;
    if (itemID) {
      let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
      favorites = favorites.filter(id => id !== parseInt(itemID));
      localStorage.setItem('favorites', JSON.stringify(favorites));
    }
  }
}

function setQuantity(quantity) {
  const quantityInput = document.getElementById('modalQuantity');
  quantityInput.value = quantity;
  document.getElementById('modalQuantityHidden').value = quantity;
  updateQuantityButtons();
}

function updateQuantityButtons() {
  const quantityInput = document.getElementById('modalQuantity');
  const currentQuantity = parseInt(quantityInput.value) || 1;
  
  // Update all quantity buttons
  document.querySelectorAll('.quantity-btn').forEach(btn => {
    const btnQuantity = parseInt(btn.getAttribute('data-quantity'));
    if (btnQuantity === currentQuantity) {
      btn.classList.remove('btn-outline-secondary');
      btn.classList.add('btn-warning');
    } else {
      btn.classList.remove('btn-warning');
      btn.classList.add('btn-outline-secondary');
    }
  });
}

function increaseQuantity() {
  const quantityInput = document.getElementById('modalQuantity');
  const currentValue = parseInt(quantityInput.value) || 1;
  quantityInput.value = currentValue + 1;
  document.getElementById('modalQuantityHidden').value = quantityInput.value;
  updateQuantityButtons();
}

function decreaseQuantity() {
  const quantityInput = document.getElementById('modalQuantity');
  const currentValue = parseInt(quantityInput.value) || 1;
  if (currentValue > 1) {
    quantityInput.value = currentValue - 1;
    document.getElementById('modalQuantityHidden').value = quantityInput.value;
    updateQuantityButtons();
  }
}

// Update quantity when manually changed
document.addEventListener('DOMContentLoaded', function() {
  const quantityInput = document.getElementById('modalQuantity');
  if (quantityInput) {
    quantityInput.addEventListener('change', function() {
      const value = parseInt(this.value) || 1;
      this.value = Math.max(1, value);
      document.getElementById('modalQuantityHidden').value = this.value;
      updateQuantityButtons();
    });
    
    // Initialize quantity buttons on page load
    updateQuantityButtons();
  }
  
});

// Function to set action (add to cart or buy now)
function setAction(action) {
  document.getElementById('modalAction').value = action;
}

// Handle Add to Cart button click
function handleAddToCart() {
  const form = document.getElementById('modalCartForm');
  const itemID = document.getElementById('modalItemID').value;
  const quantity = document.getElementById('modalQuantity').value;
  
  if (!itemID) {
    alert('Please select a product');
    return;
  }
  
  if (!quantity || quantity < 1) {
    alert('Please enter a valid quantity');
    return;
  }
  
  // Set action to add
  setAction('add');
  
  // Sync form values
  syncFormValues();
  
  // Disable buttons to prevent double submission
  const addBtn = document.getElementById('addToCartBtn');
  const buyBtn = document.getElementById('buyNowBtn');
  const originalAddText = addBtn.innerHTML;
  
  addBtn.disabled = true;
  buyBtn.disabled = true;
  addBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding...';
  
  // Submit form
  form.submit();
}

// Handle Buy Now button click
function handleBuyNow() {
  const form = document.getElementById('modalCartForm');
  const itemID = document.getElementById('modalItemID').value;
  const quantity = document.getElementById('modalQuantity').value;
  
  if (!itemID) {
    alert('Please select a product');
    return;
  }
  
  if (!quantity || quantity < 1) {
    alert('Please enter a valid quantity');
    return;
  }
  
  // Check if user is logged in
  <?php if (!isset($_SESSION['userID'])): ?>
    alert('Please log in to proceed with checkout');
    window.location.href = 'login.php';
    return;
  <?php endif; ?>
  
  // Set action to buy
  setAction('buy');
  
  // Sync form values
  syncFormValues();
  
  // Disable buttons to prevent double submission
  const addBtn = document.getElementById('addToCartBtn');
  const buyBtn = document.getElementById('buyNowBtn');
  const originalBuyText = buyBtn.innerHTML;
  
  addBtn.disabled = true;
  buyBtn.disabled = true;
  buyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
  
  // Submit form
  form.submit();
}

// Sync form values before submission
function syncFormValues() {
  const quantity = document.getElementById('modalQuantity').value;
  document.getElementById('modalQuantityHidden').value = quantity;
  
  const packageType = document.querySelector('input[name="packageType"]:checked');
  if (packageType) {
    document.getElementById('modalPackageType').value = packageType.value;
  }
}

// Update form before submission and sync values
document.addEventListener('DOMContentLoaded', function() {
  const cartForm = document.getElementById('modalCartForm');
  if (cartForm) {
    cartForm.addEventListener('submit', function(e) {
      // Prevent default submission if buttons are handling it
      // The buttons now handle submission directly
      
      // Sync quantity before submit
      syncFormValues();
      
      // If buy now action, add buy_now parameter
      const action = document.getElementById('modalAction').value;
      if (action === 'buy') {
        // Remove existing buy_now input if any
        const existingBuyNow = this.querySelector('input[name="buy_now"]');
        if (existingBuyNow) {
          existingBuyNow.remove();
        }
        
        const buyNowInput = document.createElement('input');
        buyNowInput.type = 'hidden';
        buyNowInput.name = 'buy_now';
        buyNowInput.value = '1';
        this.appendChild(buyNowInput);
      }
    });
  }
  
  // Sync package type when radio button changes
  const packageTypes = document.querySelectorAll('input[name="packageType"]');
  packageTypes.forEach(function(radio) {
    radio.addEventListener('change', function() {
      document.getElementById('modalPackageType').value = this.value;
    });
  });
});

// Check if user is logged in (passed from PHP)
const isUserLoggedIn = <?php echo isset($_SESSION['userID']) ? 'true' : 'false'; ?>;

function toggleFavorite(btn, itemID) {
  // Check if user is logged in
  if (!isUserLoggedIn) {
    // Show login required modal
    const loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
    loginModal.show();
    return;
  }
  
  const icon = btn.querySelector('i');
  if (icon.classList.contains('far')) {
    icon.classList.remove('far');
    icon.classList.add('fas');
    btn.classList.add('favorite-active');
    // Save to localStorage or send to server
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    if (!favorites.includes(itemID)) {
      favorites.push(itemID);
    }
    localStorage.setItem('favorites', JSON.stringify(favorites));
  } else {
    icon.classList.remove('fas');
    icon.classList.add('far');
    btn.classList.remove('favorite-active');
    // Remove from localStorage
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    favorites = favorites.filter(id => id !== itemID);
    localStorage.setItem('favorites', JSON.stringify(favorites));
  }
}

// Load favorites on page load and setup modal keyboard handlers
document.addEventListener('DOMContentLoaded', function() {
  // Load favorites
  let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
  favorites.forEach(itemID => {
    const btn = document.querySelector(`button[onclick*="${itemID}"]`);
    if (btn) {
      const icon = btn.querySelector('i');
      icon.classList.remove('far');
      icon.classList.add('fas');
      btn.classList.add('favorite-active');
    }
  });
  
  // Setup product modal keyboard handlers
  const productModal = document.getElementById('productModal');
  let productModalKeyboardHandler = null;
  
  if (productModal) {
    // Add keyboard handler when modal is shown
    productModal.addEventListener('shown.bs.modal', function() {
      productModalKeyboardHandler = function(event) {
        // Only handle if modal is visible and lightbox is not active
        const lightbox = document.getElementById('imageLightbox');
        if (lightbox && lightbox.classList.contains('active')) {
          return; // Let lightbox handle keyboard events
        }
        
        // Check if we're in an input field (don't interfere with typing)
        const activeElement = document.activeElement;
        if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
          return; // Don't handle arrow keys when typing
        }
        
        // Handle arrow keys for image navigation
        if (event.key === 'ArrowLeft') {
          navigateProductImages('left', event);
        } else if (event.key === 'ArrowRight') {
          navigateProductImages('right', event);
        }
      };
      
      document.addEventListener('keydown', productModalKeyboardHandler);
    });
    
    // Remove keyboard handler when modal is hidden
    productModal.addEventListener('hidden.bs.modal', function() {
      if (productModalKeyboardHandler) {
        document.removeEventListener('keydown', productModalKeyboardHandler);
        productModalKeyboardHandler = null;
      }
    });
  }
});

// Gallery scroll function
function scrollGallery(direction) {
  const container = document.getElementById('galleryImagesContainer');
  if (!container) return;
  const scrollAmount = 200;
  
  if (direction === 'left') {
    container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
  } else {
    container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
  }
}

// Navigate product images with arrow keys
function navigateProductImages(direction, event) {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  
  const images = window.currentProductImages || [];
  if (images.length <= 1) return;
  
  let newIndex = window.currentProductImageIndex || 0;
  
  if (direction === 'left' || direction === 'prev') {
    newIndex = (newIndex - 1 + images.length) % images.length;
  } else if (direction === 'right' || direction === 'next') {
    newIndex = (newIndex + 1) % images.length;
  }
  
  if (window.updateProductImage) {
    window.updateProductImage(newIndex);
  }
}

// Image Lightbox Functions
let currentLightboxIndex = 0;

function openImageLightbox(imageSrc) {
  const lightbox = document.getElementById('imageLightbox');
  const lightboxImage = document.getElementById('lightboxImage');
  const lightboxCounter = document.getElementById('lightboxCounter');
  
  if (!lightbox || !lightboxImage) return;
  
  // Find the index of the clicked image
  const images = window.currentProductImages || [];
  currentLightboxIndex = images.findIndex(img => img === imageSrc);
  if (currentLightboxIndex === -1) currentLightboxIndex = 0;
  
  // Set the image
  lightboxImage.src = imageSrc;
  
  // Update counter
  if (images.length > 1) {
    lightboxCounter.textContent = `${currentLightboxIndex + 1} / ${images.length}`;
    lightboxCounter.style.display = 'block';
  } else {
    lightboxCounter.style.display = 'none';
  }
  
  // Show lightbox
  lightbox.classList.add('active');
  document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeImageLightbox(event) {
  // Only close if clicking on the background or close button, not on the image itself
  if (event && event.target.id !== 'imageLightbox' && event.target.className !== 'lightbox-close') {
    return;
  }
  
  const lightbox = document.getElementById('imageLightbox');
  if (lightbox) {
    lightbox.classList.remove('active');
    document.body.style.overflow = ''; // Restore scrolling
  }
}

function navigateLightbox(direction, event) {
  event.stopPropagation(); // Prevent closing lightbox
  
  const images = window.currentProductImages || [];
  if (images.length <= 1) return;
  
  if (direction === 'prev') {
    currentLightboxIndex = (currentLightboxIndex - 1 + images.length) % images.length;
  } else {
    currentLightboxIndex = (currentLightboxIndex + 1) % images.length;
  }
  
  const lightboxImage = document.getElementById('lightboxImage');
  const lightboxCounter = document.getElementById('lightboxCounter');
  
  if (lightboxImage) {
    lightboxImage.src = images[currentLightboxIndex];
  }
  
  if (lightboxCounter && images.length > 1) {
    lightboxCounter.textContent = `${currentLightboxIndex + 1} / ${images.length}`;
  }
}

// Close lightbox on Escape key and navigate with arrow keys
document.addEventListener('keydown', function(event) {
  const lightbox = document.getElementById('imageLightbox');
  if (!lightbox || !lightbox.classList.contains('active')) return;
  
  if (event.key === 'Escape') {
    closeImageLightbox(event);
  } else if (event.key === 'ArrowLeft') {
    navigateLightbox('prev', event);
  } else if (event.key === 'ArrowRight') {
    navigateLightbox('next', event);
  }
});

// Review Functions
function loadReviews(itemID) {
  const reviewsList = document.getElementById('reviewsList');
  const reviewsCount = document.getElementById('reviewsCount');
  
  if (!reviewsList || !itemID) return Promise.resolve();
  
  reviewsList.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Loading reviews...</div>';
  
  return fetch(`get-reviews.php?itemID=${itemID}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        displayReviews(data.reviews, data.averageRating, data.totalReviews);
        if (reviewsCount) reviewsCount.textContent = data.totalReviews;
      } else {
        reviewsList.innerHTML = '<div class="text-center text-muted py-3">No reviews yet. Be the first to review!</div>';
        if (reviewsCount) reviewsCount.textContent = '0';
      }
    })
    .catch(error => {
      console.error('Error loading reviews:', error);
      reviewsList.innerHTML = '<div class="text-center text-danger py-3">Error loading reviews</div>';
      if (reviewsCount) reviewsCount.textContent = '0';
    });
}

// Check if user can review this product
function checkCanReview(itemID) {
  const reviewFormContainer = document.getElementById('reviewFormContainer');
  const reviewNotAllowedContainer = document.getElementById('reviewNotAllowedContainer');
  const reviewNotAllowedMessage = document.getElementById('reviewNotAllowedMessage');
  
  if (!reviewFormContainer || !reviewNotAllowedContainer) return;
  
  // Hide both containers first
  reviewFormContainer.style.display = 'none';
  reviewNotAllowedContainer.style.display = 'none';
  
  // Check if user is logged in (only show check for logged in users)
  fetch(`check-can-review.php?itemID=${itemID}`)
    .then(response => response.json())
    .then(data => {
      if (data.canReview) {
        reviewFormContainer.style.display = 'block';
        reviewNotAllowedContainer.style.display = 'none';
      } else {
        reviewFormContainer.style.display = 'none';
        reviewNotAllowedContainer.style.display = 'block';
        if (reviewNotAllowedMessage) {
          reviewNotAllowedMessage.textContent = data.message;
        }
      }
    })
    .catch(error => {
      console.error('Error checking review eligibility:', error);
      // If error, hide both containers
      reviewFormContainer.style.display = 'none';
      reviewNotAllowedContainer.style.display = 'none';
    });
}

function displayReviews(reviews, averageRating, totalReviews) {
  const reviewsList = document.getElementById('reviewsList');
  
  if (!reviewsList) return;
  
  if (reviews.length === 0) {
    reviewsList.innerHTML = '<div class="text-center text-muted py-3">No reviews yet. Be the first to review!</div>';
    return;
  }
  
  let html = '';
  
  // Average rating display
  if (averageRating > 0) {
    html += `<div class="average-rating mb-3">
      <span class="average-rating-number">${averageRating.toFixed(1)}</span>
      <div class="average-rating-stars">${getStarRating(averageRating)}</div>
      <span class="text-muted ms-2">(${totalReviews} ${totalReviews === 1 ? 'review' : 'reviews'})</span>
    </div>`;
  }
  
  // Reviews list
  reviews.forEach(review => {
    const reviewDate = new Date(review.reviewDate).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
    
    html += `<div class="review-item">
      <div class="review-header">
        <div>
          <span class="reviewer-name">${escapeHtml(review.fullName)}</span>
          ${review.isCurrentUser ? '<span class="badge bg-primary">You</span>' : ''}
        </div>
        <span class="review-date">${reviewDate}</span>
      </div>
      <div class="review-stars">${getStarRating(review.rating)}</div>
      <p class="review-comment">${escapeHtml(review.comment)}</p>
    </div>`;
  });
  
  reviewsList.innerHTML = html;
}

function getStarRating(rating) {
  const fullStars = Math.floor(rating);
  const hasHalfStar = rating % 1 >= 0.5;
  let html = '';
  
  for (let i = 1; i <= 5; i++) {
    if (i <= fullStars) {
      html += '<i class="fas fa-star"></i>';
    } else if (i === fullStars + 1 && hasHalfStar) {
      html += '<i class="fas fa-star-half-alt"></i>';
    } else {
      html += '<i class="far fa-star"></i>';
    }
  }
  
  return html;
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Star rating interaction
document.addEventListener('DOMContentLoaded', function() {
  const starRatingContainer = document.getElementById('starRatingContainer');
  if (!starRatingContainer) return;
  
  const starLabels = starRatingContainer.querySelectorAll('.star-label');
  const starInputs = starRatingContainer.querySelectorAll('input[name="rating"]');
  const ratingText = document.getElementById('ratingText');
  
  function updateStarDisplay(rating) {
    starLabels.forEach((label, index) => {
      const labelRating = parseInt(label.getAttribute('data-rating'));
      if (labelRating <= rating) {
        label.classList.add('active');
        label.querySelector('i').classList.remove('far');
        label.querySelector('i').classList.add('fas');
      } else {
        label.classList.remove('active');
        label.querySelector('i').classList.remove('fas');
        label.querySelector('i').classList.add('far');
      }
    });
    
    if (ratingText) {
      const texts = {
        '5': 'Excellent',
        '4': 'Very Good',
        '3': 'Good',
        '2': 'Fair',
        '1': 'Poor'
      };
      ratingText.textContent = texts[rating] || 'Select rating';
    }
  }
  
  starLabels.forEach(label => {
    label.addEventListener('mouseenter', function() {
      const rating = parseInt(this.getAttribute('data-rating'));
      updateStarDisplay(rating);
    });
    
    label.addEventListener('click', function() {
      const rating = parseInt(this.getAttribute('data-rating'));
      const radio = document.getElementById('star' + rating);
      if (radio) {
        radio.checked = true;
        updateStarDisplay(rating);
      }
    });
  });
  
  // Reset on mouse leave
  starRatingContainer.addEventListener('mouseleave', function() {
    const checked = starRatingContainer.querySelector('input[name="rating"]:checked');
    if (checked) {
      updateStarDisplay(parseInt(checked.value));
    } else {
      updateStarDisplay(0);
      if (ratingText) ratingText.textContent = 'Select rating';
    }
  });
  
  // Handle radio change
  starInputs.forEach(input => {
    input.addEventListener('change', function() {
      updateStarDisplay(parseInt(this.value));
    });
  });
  
  function updateRatingText(rating) {
    if (!ratingText) return;
    const texts = {
      '5': 'Excellent',
      '4': 'Very Good',
      '3': 'Good',
      '2': 'Fair',
      '1': 'Poor'
    };
    ratingText.textContent = texts[rating] || 'Select rating';
  }
  
  // Review form submission
  const reviewForm = document.getElementById('reviewForm');
  if (reviewForm) {
    reviewForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      const itemID = document.getElementById('reviewItemID').value;
      
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
      
      fetch('submit-review.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Reset form and star rating
            reviewForm.reset();
            const ratingText = document.getElementById('ratingText');
            if (ratingText) ratingText.textContent = 'Select rating';
            
            // Reset star display
            const starContainer = document.getElementById('starRatingContainer');
            if (starContainer) {
              const starInputs = starContainer.querySelectorAll('input[name="rating"]');
              starInputs.forEach(input => input.checked = false);
              const starLabels = starContainer.querySelectorAll('.star-label');
              starLabels.forEach(label => {
                label.classList.remove('active');
                const icon = label.querySelector('i');
                if (icon) {
                  icon.classList.remove('fas');
                  icon.classList.add('far');
                }
              });
            }
            
            // Reload reviews immediately
            if (itemID) {
              loadReviews(itemID).then(() => {
                // Scroll to reviews section to show the new review
                const reviewsSection = document.getElementById('reviewsSection');
                if (reviewsSection) {
                  setTimeout(() => {
                    reviewsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                  }, 300);
                }
              });
            }
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred. Please try again.');
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        });
    });
  }
});
</script>

<?php if ($autoOpenProduct): ?>
<script>
// Auto-open product modal when product ID is in URL
document.addEventListener('DOMContentLoaded', function() {
  const productData = <?php echo json_encode($autoOpenProduct); ?>;
  
  // Create a temporary button element with product data
  const tempButton = document.createElement('button');
  tempButton.setAttribute('data-item-id', productData.itemID);
  tempButton.setAttribute('data-product-name', productData.packageName);
  tempButton.setAttribute('data-product-image', productData.productImage);
  tempButton.setAttribute('data-product-images', JSON.stringify(productData.allImages));
  tempButton.setAttribute('data-product-price', productData.price);
  tempButton.setAttribute('data-product-description', productData.foodDescription);
  tempButton.setAttribute('data-product-contains', productData.itemContains || '');
  tempButton.setAttribute('data-category-name', productData.categoryName);
  
  // Open the modal with this product
  setTimeout(function() {
    openProductModal(tempButton);
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();
    
    // Clean up URL without reloading
    if (window.history && window.history.replaceState) {
      const newUrl = window.location.pathname + (window.location.search.replace(/[?&]product=\d+/, '').replace(/^&/, '?') || '');
      window.history.replaceState({}, document.title, newUrl);
    }
  }, 100);
});
</script>
<?php endif; ?>

<!-- Login Required Modal -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-2">
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
        </div>
        <h5 class="modal-title mb-3" id="loginRequiredModalLabel">Login Required</h5>
        <div class="mb-3">
          <i class="fas fa-heart fa-4x text-danger"></i>
        </div>
        <p class="text-muted mb-4">Please log in or sign up first before adding this to your favorites.</p>
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
          <a href="login.php" class="btn btn-warning btn-lg px-4">LOG IN</a>
          <a href="signup.php" class="btn btn-outline-secondary btn-lg px-4">SIGN UP</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

