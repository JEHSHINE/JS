import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:jeh_store_flutter/models/product.dart';

class ApiService {
  static const baseUrl = 'http://10.0.2.2:8000/api';
  String? _authToken;

  void setToken(String? token) {
    _authToken = token;
  }

  Map<String, String> get _headers => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    if (_authToken != null) 'Authorization': 'Bearer $_authToken',
  };

  Future<Map<String, dynamic>> _request(String method, String path, {Map<String, dynamic>? body}) async {
    final uri = Uri.parse('$baseUrl$path');
    late http.Response response;

    switch (method) {
      case 'GET':
        response = await http.get(uri, headers: _headers);
        break;
      case 'POST':
        response = await http.post(uri, headers: _headers, body: body != null ? jsonEncode(body) : null);
        break;
      case 'PUT':
        response = await http.put(uri, headers: _headers, body: body != null ? jsonEncode(body) : null);
        break;
      case 'DELETE':
        response = await http.delete(uri, headers: _headers);
        break;
      default:
        throw Exception('Unsupported method: $method');
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (response.body.isEmpty) return {};
      final decoded = jsonDecode(response.body);
      if (decoded is List) return {'data': decoded};
      return decoded as Map<String, dynamic>;
    } else {
      final error = jsonDecode(response.body);
      throw Exception(error['error'] ?? 'Request failed');
    }
  }

  Future<List<dynamic>> _requestList(String method, String path, {Map<String, dynamic>? body}) async {
    final uri = Uri.parse('$baseUrl$path');
    late http.Response response;

    switch (method) {
      case 'GET':
        response = await http.get(uri, headers: _headers);
        break;
      case 'POST':
        response = await http.post(uri, headers: _headers, body: body != null ? jsonEncode(body) : null);
        break;
      default:
        throw Exception('Unsupported method: $method');
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (response.body.isEmpty) return [];
      final decoded = jsonDecode(response.body);
      if (decoded is List) return decoded;
      return [];
    } else {
      throw Exception('Request failed with status ${response.statusCode}');
    }
  }

  // ── Auth ──────────────────────────────────────────────────────

  Future<Map<String, dynamic>> login(String email, String password) async {
    final json = await _request('POST', '/auth/login', body: {
      'email': email,
      'password': password,
    });
    if (json.containsKey('token')) {
      _authToken = json['token'] as String;
    }
    return json;
  }

  Future<Map<String, dynamic>> register(String name, String email, String password) async {
    final json = await _request('POST', '/auth/register', body: {
      'name': name,
      'email': email,
      'password': password,
    });
    if (json.containsKey('token')) {
      _authToken = json['token'] as String;
    }
    return json;
  }

  Future<Map<String, dynamic>> getProfile() async {
    return await _request('GET', '/auth/profile');
  }

  // ── Products ──────────────────────────────────────────────────

  Future<List<Product>> fetchProducts() async {
    final data = await _requestList('GET', '/products');
    return data.map((json) => Product.fromJson(json as Map<String, dynamic>)).toList();
  }

  Future<Product?> getProductDetail(int id) async {
    try {
      final json = await _request('GET', '/products/$id');
      return Product.fromJson(json);
    } catch (e) {
      return null;
    }
  }

  // ── Categories ────────────────────────────────────────────────

  Future<List<dynamic>> fetchCategories() async {
    return await _requestList('GET', '/categories');
  }

  Future<List<Product>> fetchProductsByCategory(int categoryId) async {
    final data = await _requestList('GET', '/categories/$categoryId/products');
    return data.map((json) => Product.fromJson(json as Map<String, dynamic>)).toList();
  }

  // ── Cart ──────────────────────────────────────────────────────

  Future<List<dynamic>> fetchCart() async {
    return await _requestList('GET', '/cart');
  }

  Future<bool> addToCart(int productId, int quantity) async {
    await _request('POST', '/cart', body: {
      'productId': productId,
      'quantity': quantity,
    });
    return true;
  }

  Future<bool> removeFromCart(int cartItemId) async {
    await _request('POST', '/cart/remove', body: {
      'cartItemId': cartItemId,
    });
    return true;
  }

  // ── Orders ────────────────────────────────────────────────────

  Future<List<dynamic>> fetchOrders() async {
    return await _requestList('GET', '/orders');
  }

  Future<Map<String, dynamic>> getOrderDetail(int orderId) async {
    return await _request('GET', '/orders/$orderId');
  }

  Future<int> createOrder(int shippingAddressId, List<Map<String, dynamic>> items) async {
    final json = await _request('POST', '/orders', body: {
      'shippingAddressId': shippingAddressId,
      'items': items,
    });
    return json['orderId'] as int;
  }

  // ── Addresses ─────────────────────────────────────────────────

  Future<List<dynamic>> fetchAddresses() async {
    return await _requestList('GET', '/addresses');
  }

  Future<bool> createAddress(Map<String, dynamic> data) async {
    await _request('POST', '/addresses', body: data);
    return true;
  }

  Future<bool> deleteAddress(int id) async {
    await _request('DELETE', '/addresses/$id');
    return true;
  }

  // ── Reviews ───────────────────────────────────────────────────

  Future<List<dynamic>> fetchReviews(int productId) async {
    return await _requestList('GET', '/products/$productId/reviews');
  }

  Future<bool> submitReview(int productId, int rating, {String? comment}) async {
    await _request('POST', '/reviews', body: {
      'productId': productId,
      'rating': rating,
      if (comment != null) 'comment': comment,
    });
    return true;
  }

  // ── Search ────────────────────────────────────────────────────

  Future<List<Product>> search(String query, {int? categoryId}) async {
    final params = 'q=$query${categoryId != null ? '&category_id=$categoryId' : ''}';
    final data = await _requestList('GET', '/search?$params');
    return data.map((json) => Product.fromJson(json as Map<String, dynamic>)).toList();
  }

  // ── Notifications ─────────────────────────────────────────────

  Future<List<dynamic>> fetchNotifications() async {
    final json = await _request('GET', '/notifications');
    return json['notifications'] as List<dynamic>;
  }

  Future<bool> markNotificationsRead({int? notificationId}) async {
    await _request('POST', '/notifications/read', body: {
      if (notificationId != null) 'notificationId': notificationId,
    });
    return true;
  }

  // ── Payments ──────────────────────────────────────────────────

  Future<bool> processPayment(int orderId, String paymentMethod) async {
    await _request('POST', '/payments', body: {
      'orderId': orderId,
      'paymentMethod': paymentMethod,
    });
    return true;
  }
}