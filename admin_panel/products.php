<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products - JEH Store Admin</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="dashboard">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>JEH STORE</h2>
        <p>Administrator Panel</p>
      </div>
      <nav class="sidebar-nav">
        <a href="dashboard.php"><span class="icon">📊</span> Dashboard</a>
        <a href="products.php" class="active"><span class="icon">📦</span> Products</a>
        <a href="categories.php"><span class="icon">📁</span> Categories</a>
        <a href="orders.php"><span class="icon">📋</span> Orders</a>
        <a href="customers.php"><span class="icon">👥</span> Customers</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 20px;">
        <a href="javascript:void(0)" onclick="adminLogout()"><span class="icon">🚪</span> Logout</a>
      </nav>
    </aside>

    <main class="main-content">
      <div id="alertContainer"></div>
      <div class="page-header">
        <h1>Products</h1>
        <div class="header-actions">
          <button class="btn btn-primary" onclick="showAddProductForm()">+ Add Product</button>
          <button class="btn btn-secondary" onclick="loadProducts()">🔄 Refresh</button>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>All Products</h2>
        </div>
        <div class="card-body">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="productsTableBody">
                <tr><td colspan="6"><div class="loading"><div class="spinner"></div>Loading products...</div></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Product Modal -->
  <div id="productModal" class="modal">
    <div class="modal-content">
      <h2 id="productModalTitle">Add Product</h2>
      <form id="productForm">
        <input type="hidden" id="productId">
        <div class="form-group">
          <label>Title</label>
          <input type="text" id="productTitle" required>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea id="productDescription" rows="3"></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Price ($)</label>
            <input type="number" id="productPrice" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label>Stock</label>
            <input type="number" id="productStock" min="0">
          </div>
        </div>
        <div class="form-group">
          <label>Category ID</label>
          <input type="number" id="productCategory">
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('productModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      loadProducts();
    });
  </script>
</body>
</html>