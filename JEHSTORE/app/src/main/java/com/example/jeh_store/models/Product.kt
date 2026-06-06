package com.example.jeh_store.models

data class Product(
    val id: Int,
    val title: String,
    val description: String? = null,
    val price: Double,
    val stock: Int = 0,
    val category: String? = null,
    val category_id: Int? = null,
    val image_path: String? = null
)

data class Category(
    val id: Int,
    val name: String,
    val description: String? = null
)

data class CartItem(
    val id: Int,
    val product_id: Int,
    val title: String,
    val price: Double,
    val quantity: Int,
    val image_path: String? = null
)

data class Order(
    val id: Int,
    val status: String,
    val total_amount: Double,
    val placed_at: String? = null,
    val approved_at: String? = null,
    val delivered_at: String? = null,
    val items: List<OrderItem>? = null
)

data class OrderItem(
    val product_id: Int,
    val title: String? = null,
    val quantity: Int,
    val unit_price: Double,
    val image_path: String? = null
)

data class Address(
    val id: Int,
    val label: String? = null,
    val street: String,
    val city: String,
    val state: String? = null,
    val postal_code: String,
    val country: String
)

data class Review(
    val id: Int,
    val rating: Int,
    val comment: String? = null,
    val customer_name: String? = null,
    val created_at: String? = null
)

data class Notification(
    val id: Int,
    val title: String,
    val message: String,
    val is_read: Boolean = false,
    val created_at: String? = null
)

data class LoginResponse(
    val message: String? = null,
    val token: String? = null,
    val customerId: Int? = null,
    val name: String? = null,
    val error: String? = null
)

data class ApiError(
    val error: String
)

data class User(
    val id: Int = 0,
    val name: String = "",
    val email: String = "",
    val phone: String? = null,
    val created_at: String? = null
)