import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:jeh_store_flutter/models/product.dart';

class ApiService {
  static const baseUrl = 'http://localhost:8000/api';

  Future<List<Product>> fetchProducts() async {
    final response = await http.get(Uri.parse('\$baseUrl/products'));
    if (response.statusCode != 200) {
      throw Exception('Failed to load products');
    }
    final data = jsonDecode(response.body) as List<dynamic>;
    return data.map((json) => Product.fromJson(json as Map<String, dynamic>)).toList();
  }

  Future<void> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('\$baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );
    if (response.statusCode != 200) {
      throw Exception('Login failed');
    }
  }
}
