<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - JEH Store Admin</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>JEH STORE</h2>
        <p>Administrator Panel</p>
      </div>
      <nav class="sidebar-nav">
        <a href="dashboard.php" class="active"><span class="icon">📊</span> Dashboard</a>
        <a href="products.php"><span class="icon">📦</span> Products</a>
        <a href="categories.php"><span class="icon">📁</span> Categories</a>
        <a href="orders.php"><span class="icon">📋</span> Orders</a>
        <a href="customers.php"><span class="icon">👥</span> Customers</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 20px;">
        <a href="javascript:void(0)" onclick="adminLogout()"><span class="icon">🚪</span> Logout</a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div id="alertContainer"></div>

      <div class="page-header">
        <h1>Dashboard</h1>
        <div class="header-actions">
          <button class="btn btn-primary" onclick="location.reload()">🔄 Refresh</button>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">📦</div>
          <div class="stat-info">
            <h3>Total Products</h3>
            <span class="stat-number" id="totalProducts">0</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">📋</div>
          <div class="stat-info">
            <h3>Total Orders</h3>
            <span class="stat-number" id="totalOrders">0</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange">👥</div>
          <div class="stat-info">
            <h3>Total Customers</h3>
            <span class="stat-number" id="totalCustomers">0</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red">⏳</div>
          <div class="stat-info">
            <h3>Pending Orders</h3>
            <span class="stat-number" id="pendingOrders">0</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue">💰</div>
          <div class="stat-info">
            <h3>Revenue</h3>
            <span class="stat-number" id="revenue">$0.00</span>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <div class="card-header">
          <h2>Quick Actions</h2>
        </div>
        <div class="card-body">
          <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="products.php" class="btn btn-primary">📦 Manage Products</a>
            <a href="categories.php" class="btn btn-secondary">📁 Manage Categories</a>
            <a href="orders.php" class="btn btn-primary">📋 Manage Orders</a>
            <a href="customers.php" class="btn btn-secondary">👥 View Customers</a>
            <button class="btn btn-primary" onclick="showImageUploadForm()">🖼️ Upload Product Image</button>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Image Upload Modal -->
  <div id="imageModal" class="modal">
    <div class="modal-content">
      <h2>Upload Product Image</h2>
      <form id="imageForm">
        <div class="form-group">
          <label>Product ID</label>
          <input type="number" id="imageProductId" readonly>
        </div>
        <div class="form-group">
          <label>Select Image</label>
          <input type="file" id="productImage" accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('imageModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Upload</button>
        </div>
      </form>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
    document.getElementById('imageForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const productId = document.getElementById('imageProductId').value;
      uploadProductImage(productId);
    });
  </script>
</body>
</html>