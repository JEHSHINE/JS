// JEH STORE Admin Panel - App Logic
const API_BASE = 'http://localhost:8000/api';
let authToken = localStorage.getItem('adminToken');

// ── API helper ──────────────────────────────────────────────────
async function apiRequest(method, path, body = null) {
  const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
  if (authToken) headers['Authorization'] = `Bearer ${authToken}`;

  const options = { method, headers };
  if (body) options.body = JSON.stringify(body);

  const response = await fetch(`${API_BASE}${path}`, options);
  const data = await response.json();

  if (!response.ok) throw new Error(data.error || 'Request failed');
  return data;
}

// ── Auth ────────────────────────────────────────────────────────
async function adminLogin(username, password) {
  const data = await apiRequest('POST', '/auth/admin/login', { username, password });
  if (data.token) {
    authToken = data.token;
    localStorage.setItem('adminToken', data.token);
  }
  return data;
}

function adminLogout() {
  authToken = null;
  localStorage.removeItem('adminToken');
  window.location.href = 'login.php';
}

function checkAuth() {
  if (!authToken) {
    window.location.href = 'login.php';
    return false;
  }
  return true;
}

// ── Dashboard ───────────────────────────────────────────────────
async function loadDashboard() {
  if (!checkAuth()) return;

  try {
    const data = await apiRequest('GET', '/admin/dashboard');
    document.getElementById('totalProducts').textContent = data.totalProducts || 0;
    document.getElementById('totalOrders').textContent = data.totalOrders || 0;
    document.getElementById('totalCustomers').textContent = data.totalCustomers || 0;
    document.getElementById('pendingOrders').textContent = data.pendingOrders || 0;
    document.getElementById('revenue').textContent = `$${(data.revenue || 0).toFixed(2)}`;
  } catch (e) {
    showAlert('danger', 'Failed to load dashboard: ' + e.message);
  }
}

// ── Products ────────────────────────────────────────────────────
async function loadProducts() {
  if (!checkAuth()) return;

  const container = document.getElementById('productsTableBody');
  if (!container) return;
  container.innerHTML = '<tr><td colspan="6"><div class="loading"><div class="spinner"></div>Loading products...</div></td></tr>';

  try {
    const products = await apiRequest('GET', '/products');
    container.innerHTML = '';

    if (!products || products.length === 0) {
      container.innerHTML = '<tr><td colspan="6"><div class="empty-state"><div class="icon">📦</div>No products found</div></td></tr>';
      return;
    }

    products.forEach(p => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${p.id}</td>
        <td><strong>${p.title}</strong></td>
        <td>${p.category || 'N/A'}</td>
        <td>$${parseFloat(p.price).toFixed(2)}</td>
        <td>${p.stock}</td>
        <td>
          <button class="btn btn-warning btn-sm" onclick="editProduct(${p.id})">✏️</button>
          <button class="btn btn-danger btn-sm" onclick="deleteProduct(${p.id})">🗑️</button>
        </td>
      `;
      container.appendChild(tr);
    });
  } catch (e) {
    container.innerHTML = `<tr><td colspan="6"><div class="alert alert-danger">Error: ${e.message}</div></td></tr>`;
  }
}

async function createProduct(data) {
  if (!checkAuth()) return;
  const result = await apiRequest('POST', '/admin/products', data);
  showAlert('success', 'Product created successfully!');
  closeModal('productModal');
  loadProducts();
  return result;
}

async function updateProduct(id, data) {
  if (!checkAuth()) return;
  const result = await apiRequest('PUT', `/admin/products/${id}`, data);
  showAlert('success', 'Product updated successfully!');
  closeModal('productModal');
  loadProducts();
  return result;
}

async function deleteProduct(id) {
  if (!checkAuth()) return;
  if (!confirm('Are you sure you want to delete this product?')) return;

  try {
    await apiRequest('DELETE', `/admin/products/${id}`);
    showAlert('success', 'Product deleted successfully!');
    loadProducts();
  } catch (e) {
    showAlert('danger', 'Error: ' + e.message);
  }
}

async function editProduct(id) {
  if (!checkAuth()) return;
  try {
    const product = await apiRequest('GET', `/products/${id}`);
    document.getElementById('productId').value = product.id;
    document.getElementById('productTitle').value = product.title;
    document.getElementById('productDescription').value = product.description || '';
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productStock').value = product.stock;
    document.getElementById('productCategory').value = product.category_id || '';
    document.getElementById('productModalTitle').textContent = 'Edit Product';
    document.getElementById('productForm').onsubmit = (e) => {
      e.preventDefault();
      updateProduct(id, {
        title: document.getElementById('productTitle').value,
        description: document.getElementById('productDescription').value,
        price: parseFloat(document.getElementById('productPrice').value),
        stock: parseInt(document.getElementById('productStock').value),
        category_id: parseInt(document.getElementById('productCategory').value),
      });
    };
    openModal('productModal');
  } catch (e) {
    showAlert('danger', 'Error: ' + e.message);
  }
}

function showAddProductForm() {
  document.getElementById('productId').value = '';
  document.getElementById('productTitle').value = '';
  document.getElementById('productDescription').value = '';
  document.getElementById('productPrice').value = '';
  document.getElementById('productStock').value = '0';
  document.getElementById('productCategory').value = '';
  document.getElementById('productModalTitle').textContent = 'Add Product';
  document.getElementById('productForm').onsubmit = (e) => {
    e.preventDefault();
    createProduct({
      title: document.getElementById('productTitle').value,
      description: document.getElementById('productDescription').value,
      price: parseFloat(document.getElementById('productPrice').value),
      stock: parseInt(document.getElementById('productStock').value),
      categoryId: parseInt(document.getElementById('productCategory').value),
    });
  };
  openModal('productModal');
}

// ── Categories ──────────────────────────────────────────────────
async function loadCategories() {
  if (!checkAuth()) return;

  const container = document.getElementById('categoriesTableBody');
  if (!container) return;
  container.innerHTML = '<tr><td colspan="3"><div class="loading"><div class="spinner"></div>Loading categories...</div></td></tr>';

  try {
    const categories = await apiRequest('GET', '/categories');
    container.innerHTML = '';

    if (!categories || categories.length === 0) {
      container.innerHTML = '<tr><td colspan="3"><div class="empty-state"><div class="icon">📁</div>No categories found</div></td></tr>';
      return;
    }

    categories.forEach(c => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${c.id}</td>
        <td><strong>${c.name}</strong></td>
        <td>${c.description || 'N/A'}</td>
      `;
      container.appendChild(tr);
    });
  } catch (e) {
    container.innerHTML = `<tr><td colspan="3"><div class="alert alert-danger">Error: ${e.message}</div></td></tr>`;
  }
}

async function createCategory(data) {
  if (!checkAuth()) return;
  try {
    await apiRequest('POST', '/admin/categories', data);
    showAlert('success', 'Category created successfully!');
    closeModal('categoryModal');
    loadCategories();
  } catch (e) {
    showAlert('danger', 'Error: ' + e.message);
  }
}

function showAddCategoryForm() {
  document.getElementById('categoryName').value = '';
  document.getElementById('categoryDescription').value = '';
  document.getElementById('categoryForm').onsubmit = (e) => {
    e.preventDefault();
    createCategory({
      name: document.getElementById('categoryName').value,
      description: document.getElementById('categoryDescription').value,
    });
  };
  openModal('categoryModal');
}

// ── Orders ──────────────────────────────────────────────────────
async function loadOrders() {
  if (!checkAuth()) return;

  const container = document.getElementById('ordersTableBody');
  if (!container) return;
  container.innerHTML = '<tr><td colspan="5"><div class="loading"><div class="spinner"></div>Loading orders...</div></td></tr>';

  try {
    const orders = await apiRequest('GET', '/admin/orders');
    container.innerHTML = '';

    if (!orders || orders.length === 0) {
      container.innerHTML = '<tr><td colspan="5"><div class="empty-state"><div class="icon">📋</div>No orders found</div></td></tr>';
      return;
    }

    orders.forEach(o => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>#${o.id}</td>
        <td>${o.customer_name || 'N/A'}</td>
        <td>$${parseFloat(o.total_amount).toFixed(2)}</td>
        <td><span class="status-badge status-${o.status}">${o.status}</span></td>
        <td>
          ${o.status === 'pending' ? `<button class="btn btn-primary btn-sm" onclick="updateOrderStatus(${o.id}, 'approved')">Approve</button>` : ''}
          ${o.status === 'approved' ? `<button class="btn btn-primary btn-sm" onclick="updateOrderStatus(${o.id}, 'shipped')">Ship</button>` : ''}
          ${o.status === 'shipped' ? `<button class="btn btn-success btn-sm" onclick="updateOrderStatus(${o.id}, 'delivered')">Deliver</button>` : ''}
          ${o.status !== 'delivered' && o.status !== 'cancelled' ? `<button class="btn btn-danger btn-sm" onclick="updateOrderStatus(${o.id}, 'cancelled')">Cancel</button>` : ''}
        </td>
      `;
      container.appendChild(tr);
    });
  } catch (e) {
    container.innerHTML = `<tr><td colspan="5"><div class="alert alert-danger">Error: ${e.message}</div></td></tr>`;
  }
}

async function updateOrderStatus(orderId, status) {
  if (!checkAuth()) return;
  try {
    await apiRequest('PUT', `/admin/orders/${orderId}/status`, { status });
    showAlert('success', `Order #${orderId} status updated to ${status}!`);
    loadOrders();
  } catch (e) {
    showAlert('danger', 'Error: ' + e.message);
  }
}

// ── Customers ───────────────────────────────────────────────────
async function loadCustomers() {
  if (!checkAuth()) return;

  const container = document.getElementById('customersTableBody');
  if (!container) return;
  container.innerHTML = '<tr><td colspan="4"><div class="loading"><div class="spinner"></div>Loading customers...</div></td></tr>';

  try {
    const orders = await apiRequest('GET', '/admin/orders');
    // Extract unique customer names from orders
    const customers = [...new Set(orders.map(o => o.customer_name))].map(name => {
      const customerOrders = orders.filter(o => o.customer_name === name);
      return {
        name,
        orderCount: customerOrders.length,
        totalSpent: customerOrders.reduce((sum, o) => sum + parseFloat(o.total_amount), 0),
      };
    });

    container.innerHTML = '';
    if (customers.length === 0) {
      container.innerHTML = '<tr><td colspan="4"><div class="empty-state"><div class="icon">👥</div>No customers found</div></td></tr>';
      return;
    }

    customers.forEach((c, i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${i + 1}</td>
        <td><strong>${c.name}</strong></td>
        <td>${c.orderCount}</td>
        <td>$${c.totalSpent.toFixed(2)}</td>
      `;
      container.appendChild(tr);
    });
  } catch (e) {
    container.innerHTML = `<tr><td colspan="4"><div class="alert alert-danger">Error: ${e.message}</div></td></tr>`;
  }
}

// ── Image Upload ────────────────────────────────────────────────
async function uploadProductImage(productId) {
  if (!checkAuth()) return;

  const fileInput = document.getElementById('productImage');
  if (!fileInput || !fileInput.files[0]) {
    showAlert('danger', 'Please select an image file');
    return;
  }

  const formData = new FormData();
  formData.append('image', fileInput.files[0]);
  formData.append('is_primary', '1');

  try {
    const response = await fetch(`${API_BASE}/admin/products/${productId}/images`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${authToken}` },
      body: formData,
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Upload failed');
    showAlert('success', 'Image uploaded successfully!');
    closeModal('imageModal');
  } catch (e) {
    showAlert('danger', 'Error: ' + e.message);
  }
}

function showImageUploadForm() {
  const productId = prompt('Enter Product ID to upload image for:');
  if (!productId) return;
  document.getElementById('imageProductId').value = productId;
  openModal('imageModal');
}

// ── Modal helpers ───────────────────────────────────────────────
function openModal(id) {
  document.getElementById(id).classList.add('active');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('active');
}

// Close modal on outside click
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal')) {
    e.target.classList.remove('active');
  }
});

// ── Alert helper ────────────────────────────────────────────────
function showAlert(type, message) {
  const container = document.getElementById('alertContainer');
  if (!container) return;

  const alert = document.createElement('div');
  alert.className = `alert alert-${type}`;
  alert.textContent = message;
  container.appendChild(alert);

  setTimeout(() => alert.remove(), 5000);
}

// ── Initialize ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  const currentPage = window.location.pathname.split('/').pop();

  // Highlight active nav
  document.querySelectorAll('.sidebar-nav a').forEach(a => {
    const href = a.getAttribute('href');
    if (href === currentPage) a.classList.add('active');
    if ((currentPage === 'dashboard.php' || currentPage === '') && href === 'dashboard.php') {
      a.classList.add('active');
    }
  });

  // Load page-specific data
  if (currentPage === 'dashboard.php') {
    loadDashboard();
  }
});