class Product {
  final int id;
  final String title;
  final String description;
  final double price;
  final int stock;
  final String? category;
  final int? categoryId;
  final String? imagePath;

  Product({
    required this.id,
    required this.title,
    this.description = '',
    required this.price,
    this.stock = 0,
    this.category,
    this.categoryId,
    this.imagePath,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'] as int,
      title: json['title'] as String,
      description: json['description'] as String? ?? '',
      price: (json['price'] as num).toDouble(),
      stock: json['stock'] as int? ?? 0,
      category: json['category'] as String?,
      categoryId: json['category_id'] as int?,
      imagePath: json['image_path'] as String?,
    );
  }
}