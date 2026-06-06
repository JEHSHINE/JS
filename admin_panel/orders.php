<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders - JEH Store Admin</title>
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
        <a href="categories.php"><span class="icon">📁</span> Categories</a>
        <a href="orders.php" class="active"><span class="icon">📋</span> Orders</a>
        <a href="customers.php"><span class="icon">👥</span> Customers</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 20px;">
        <a href="javascript:void(0)" onclick="adminLogout()"><span class="icon">🚪</span> Logout</a>
      </nav>
    </aside>

    <main class="main-content">
      <div id="alertContainer"></div>
      <div class="page-header">
        <h1>Orders</h1>
        <div class="header-actions">
          <button class="btn btn-secondary" onclick="loadOrders()">🔄 Refresh</button>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>All Orders</h2>
        </div>
        <div class="card-body">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Customer</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="ordersTableBody">
                <tr><td colspan="5"><div class="loading"><div class="spinner"></div>Loading orders...</div></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      loadOrders();
    });
  </script>
</body>
</html>