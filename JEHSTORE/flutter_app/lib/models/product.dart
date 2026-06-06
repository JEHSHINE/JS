class Product {
  final int id;
  final String title;
  final String description;
  final double price;
  final int stock;
  final String category;
  final String imageUrl;

  Product({
    required this.id,
    required this.title,
    required this.description,
    required this.price,
    required this.stock,
    required this.category,
    required this.imageUrl,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'] as int,
      title: json['title'] as String,
      description: json['description'] as String? ?? '',
      price: double.parse(json['price'].toString()),
      stock: json['stock'] as int? ?? 0,
      category: json['category'] as String? ?? '',
      imageUrl: json['image_path'] as String? ?? '',
    );
  }
}
