<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categories - JEH Store Admin</title>
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
        <a href="products.php"><span class="icon">📦</span> Products</a>
        <a href="categories.php" class="active"><span class="icon">📁</span> Categories</a>
        <a href="orders.php"><span class="icon">📋</span> Orders</a>
        <a href="customers.php"><span class="icon">👥</span> Customers</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 20px;">
        <a href="javascript:void(0)" onclick="adminLogout()"><span class="icon">🚪</span> Logout</a>
      </nav>
    </aside>

    <main class="main-content">
      <div id="alertContainer"></div>
      <div class="page-header">
        <h1>Categories</h1>
        <div class="header-actions">
          <button class="btn btn-primary" onclick="showAddCategoryForm()">+ Add Category</button>
          <button class="btn btn-secondary" onclick="loadCategories()">🔄 Refresh</button>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>All Categories</h2>
        </div>
        <div class="card-body">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody id="categoriesTableBody">
                <tr><td colspan="3"><div class="loading"><div class="spinner"></div>Loading categories...</div></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Category Modal -->
  <div id="categoryModal" class="modal">
    <div class="modal-content">
      <h2>Add Category</h2>
      <form id="categoryForm">
        <div class="form-group">
          <label>Name</label>
          <input type="text" id="categoryName" required>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea id="categoryDescription" rows="3"></textarea>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('categoryModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      loadCategories();
    });
  </script>
</body>
</html>