import 'package:flutter/material.dart';
import 'package:jeh_store_flutter/models/product.dart';
import 'package:jeh_store_flutter/services/api_service.dart';
import 'package:jeh_store_flutter/widgets/product_card.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late ApiService _apiService;
  List<Product> _products = [];
  List<dynamic> _categories = [];
  bool _isLoading = true;
  final _searchController = TextEditingController();
  int? _selectedCategoryId;
  int _cartItemCount = 0;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _apiService = ModalRoute.of(context)!.settings.arguments as ApiService;
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      _products = await _apiService.fetchProducts();
      _categories = await _apiService.fetchCategories();
      final cart = await _apiService.fetchCart();
      _cartItemCount = cart.length;
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString().replaceAll("Exception: ", "")}')),
        );
      }
    }
    if (mounted) setState(() => _isLoading = false);
  }

  Future<void> _search(String query) async {
    if (query.length < 2) {
      _loadData();
      return;
    }
    setState(() => _isLoading = true);
    try {
      _products = await _apiService.search(query, categoryId: _selectedCategoryId);
    } catch (e) {
      // ignore search errors
    }
    if (mounted) setState(() => _isLoading = false);
  }

  Future<void> _filterByCategory(int? categoryId) async {
    setState(() {
      _selectedCategoryId = categoryId;
      _isLoading = true;
    });
    try {
      if (categoryId == null) {
        _products = await _apiService.fetchProducts();
      } else {
        _products = await _apiService.fetchProductsByCategory(categoryId);
      }
    } catch (e) {
      // ignore
    }
    if (mounted) setState(() => _isLoading = false);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('JEH STORE'),
        centerTitle: true,
      ),
      body: Column(
        children: [
          // Search bar
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search products...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                          _loadData();
                        },
                      )
                    : null,
              ),
              onChanged: (value) {
                setState(() {});
                _search(value);
              },
            ),
          ),

          // Categories
          SizedBox(
            height: 50,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              children: [
                _buildCategoryChip(null, 'All'),
                ..._categories.map((cat) => _buildCategoryChip(
                      cat['id'] as int,
                      cat['name'] as String,
                    )),
              ],
            ),
          ),

          const SizedBox(height: 8),

          // Products
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _products.isEmpty
                    ? const Center(child: Text('No products found'))
                    : RefreshIndicator(
                        onRefresh: _loadData,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _products.length,
                          itemBuilder: (context, index) {
                            return ProductCard(
                              product: _products[index],
                              onTap: () => Navigator.pushNamed(
                                context,
                                '/product-detail',
                                arguments: {'apiService': _apiService, 'productId': _products[index].id},
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: 0,
        destinations: [
          const NavigationDestination(icon: Icon(Icons.home), label: 'Home'),
          NavigationDestination(
            icon: Badge(
              isLabelVisible: _cartItemCount > 0,
              label: Text('$_cartItemCount'),
              child: const Icon(Icons.shopping_cart),
            ),
            label: 'Cart',
          ),
          const NavigationDestination(icon: Icon(Icons.list_alt), label: 'Orders'),
          const NavigationDestination(icon: Icon(Icons.person), label: 'Profile'),
        ],
        onDestinationSelected: (index) {
          switch (index) {
            case 1:
              Navigator.pushNamed(context, '/cart', arguments: _apiService);
              break;
            case 2:
              Navigator.pushNamed(context, '/orders', arguments: _apiService);
              break;
            case 3:
              Navigator.pushNamed(context, '/profile', arguments: _apiService);
              break;
          }
        },
      ),
    );
  }

  Widget _buildCategoryChip(int? id, String label) {
    final isSelected = _selectedCategoryId == id;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (_) => _filterByCategory(id),
      ),
    );
  }
}