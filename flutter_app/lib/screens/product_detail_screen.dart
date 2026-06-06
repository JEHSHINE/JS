import 'package:flutter/material.dart';
import 'package:jeh_store_flutter/models/product.dart';
import 'package:jeh_store_flutter/services/api_service.dart';

class ProductDetailScreen extends StatefulWidget {
  const ProductDetailScreen({super.key});

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  late ApiService _apiService;
  int _productId = 0;
  Product? _product;
  List<dynamic> _reviews = [];
  bool _isLoading = true;
  int _quantity = 1;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final args = ModalRoute.of(context)!.settings.arguments as Map;
    _apiService = args['apiService'] as ApiService;
    _productId = args['productId'] as int;
    _loadProduct();
  }

  Future<void> _loadProduct() async {
    setState(() => _isLoading = true);
    try {
      _product = await _apiService.getProductDetail(_productId);
      _reviews = await _apiService.fetchReviews(_productId);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString().replaceAll("Exception: ", "")}')),
        );
      }
    }
    if (mounted) setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Product Details')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _product == null
              ? const Center(child: Text('Product not found'))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _product!.title,
                        style: theme.textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        '\$${_product!.price.toStringAsFixed(2)}',
                        style: theme.textTheme.headlineMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: theme.colorScheme.primary,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        _product!.stock > 0 ? 'In Stock: ${_product!.stock} units' : 'Out of Stock',
                        style: TextStyle(
                          color: _product!.stock > 0
                              ? theme.colorScheme.onSurfaceVariant
                              : theme.colorScheme.error,
                        ),
                      ),
                      const SizedBox(height: 16),
                      if (_product!.description.isNotEmpty) ...[
                        Text(
                          _product!.description,
                          style: theme.textTheme.bodyLarge,
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Quantity selector
                      Row(
                        children: [
                          const Text('Quantity: ', style: TextStyle(fontWeight: FontWeight.w500)),
                          IconButton(
                            icon: const Icon(Icons.remove_circle_outline),
                            onPressed: _quantity > 1 ? () => setState(() => _quantity--) : null,
                          ),
                          Text('$_quantity', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                          IconButton(
                            icon: const Icon(Icons.add_circle_outline),
                            onPressed: () => setState(() => _quantity++),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      SizedBox(
                        width: double.infinity,
                        height: 50,
                        child: ElevatedButton(
                          onPressed: _product!.stock > 0
                              ? () async {
                                  try {
                                    await _apiService.addToCart(_productId, _quantity);
                                    if (!mounted) return;
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(content: Text('Added to cart!')),
                                    );
                                  } catch (e) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(content: Text('Error: $e')),
                                    );
                                  }
                                }
                              : null,
                          child: const Text('Add to Cart', style: TextStyle(fontSize: 16)),
                        ),
                      ),

                      const SizedBox(height: 32),

                      // Reviews
                      Text(
                        'Reviews (${_reviews.length})',
                        style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),

                      ..._reviews.map((review) => Card(
                        margin: const EdgeInsets.only(bottom: 8),
                        child: Padding(
                          padding: const EdgeInsets.all(12),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    review['customer_name'] ?? 'Anonymous',
                                    style: const TextStyle(fontWeight: FontWeight.w500),
                                  ),
                                  Row(
                                    children: List.generate(5, (i) {
                                      return Icon(
                                        i < (review['rating'] as int) ? Icons.star : Icons.star_border,
                                        color: Colors.amber,
                                        size: 18,
                                      );
                                    }),
                                  ),
                                ],
                              ),
                              if (review['comment'] != null) ...[
                                const SizedBox(height: 4),
                                Text(review['comment'] as String),
                              ],
                            ],
                          ),
                        ),
                      )),
                    ],
                  ),
                ),
    );
  }
}