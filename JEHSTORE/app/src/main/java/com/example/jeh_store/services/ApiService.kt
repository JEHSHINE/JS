package com.example.jeh_store.services

import com.example.jeh_store.models.*
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import org.json.JSONArray
import org.json.JSONObject
import java.io.BufferedReader
import java.io.InputStreamReader
import java.io.OutputStreamWriter
import java.net.HttpURLConnection
import java.net.URL

class ApiService(private val baseUrl: String = "http://10.0.2.2:8000/api") {

    private var authToken: String? = null

    fun setToken(token: String?) {
        authToken = token
    }

    private suspend fun request(
        method: String,
        path: String,
        body: JSONObject? = null
    ): JSONObject = withContext(Dispatchers.IO) {
        val url = URL("$baseUrl$path")
        val conn = url.openConnection() as HttpURLConnection
        conn.requestMethod = method
        conn.setRequestProperty("Content-Type", "application/json")
        conn.setRequestProperty("Accept", "application/json")
        authToken?.let { conn.setRequestProperty("Authorization", "Bearer $it") }
        conn.connectTimeout = 15000
        conn.readTimeout = 15000

        if (body != null) {
            conn.doOutput = true
            OutputStreamWriter(conn.outputStream).use { writer ->
                writer.write(body.toString())
                writer.flush()
            }
        }

        val responseCode = conn.responseCode
        val stream = if (responseCode in 200..299) conn.inputStream else conn.errorStream
        val reader = BufferedReader(InputStreamReader(stream))
        val response = reader.readText()
        reader.close()
        conn.disconnect()

        JSONObject(response)
    }

    private suspend fun requestArray(
        method: String,
        path: String,
        body: JSONObject? = null
    ): JSONArray = withContext(Dispatchers.IO) {
        val url = URL("$baseUrl$path")
        val conn = url.openConnection() as HttpURLConnection
        conn.requestMethod = method
        conn.setRequestProperty("Content-Type", "application/json")
        conn.setRequestProperty("Accept", "application/json")
        authToken?.let { conn.setRequestProperty("Authorization", "Bearer $it") }
        conn.connectTimeout = 15000
        conn.readTimeout = 15000

        if (body != null) {
            conn.doOutput = true
            OutputStreamWriter(conn.outputStream).use { writer ->
                writer.write(body.toString())
                writer.flush()
            }
        }

        val stream = if (conn.responseCode in 200..299) conn.inputStream else conn.errorStream
        val reader = BufferedReader(InputStreamReader(stream))
        val response = reader.readText()
        reader.close()
        conn.disconnect()

        JSONArray(response)
    }

    // Auth
    suspend fun login(email: String, password: String): LoginResponse {
        val json = request("POST", "/auth/login", JSONObject().apply {
            put("email", email)
            put("password", password)
        })
        return if (json.has("token")) {
            val token = json.getString("token")
            authToken = token
            LoginResponse(token = token, customerId = json.optInt("customerId"), name = json.optString("name"))
        } else {
            LoginResponse(error = json.optString("error", "Login failed"))
        }
    }

    suspend fun register(name: String, email: String, password: String): LoginResponse {
        val json = request("POST", "/auth/register", JSONObject().apply {
            put("name", name)
            put("email", email)
            put("password", password)
        })
        return if (json.has("token")) {
            val token = json.getString("token")
            authToken = token
            LoginResponse(token = token, customerId = json.optInt("customerId"))
        } else {
            LoginResponse(error = json.optString("error", "Registration failed"))
        }
    }

    suspend fun getProfile(): User {
        val json = request("GET", "/auth/profile")
        return User(
            id = json.getInt("id"),
            name = json.getString("name"),
            email = json.getString("email"),
            phone = json.optString("phone"),
            created_at = json.optString("created_at")
        )
    }

    // Products
    suspend fun getProducts(): List<Product> {
        val arr = requestArray("GET", "/products")
        return (0 until arr.length()).map { i ->
            val obj = arr.getJSONObject(i)
            Product(
                id = obj.getInt("id"),
                title = obj.getString("title"),
                description = obj.optString("description"),
                price = obj.getDouble("price"),
                stock = obj.getInt("stock"),
                category = obj.optString("category"),
                image_path = obj.optString("image_path")
            )
        }
    }

    suspend fun getProductDetail(id: Int): Product? {
        val json = request("GET", "/products/$id")
        return if (json.has("id")) {
            Product(
                id = json.getInt("id"),
                title = json.getString("title"),
                description = json.optString("description"),
                price = json.getDouble("price"),
                stock = json.getInt("stock"),
                category_id = json.optInt("category_id")
            )
        } else null
    }

    // Categories
    suspend fun getCategories(): List<Category> {
        val arr = requestArray("GET", "/categories")
        return (0 until arr.length()).map { i ->
            val obj = arr.getJSONObject(i)
            Category(
                id = obj.getInt("id"),
                name = obj.getString("name"),
                description = obj.optString("description")
            )
        }
    }

    suspend fun getProductsByCategory(categoryId: Int): List<Product> {
        val arr = requestArray("GET", "/categories/$categoryId/products")
        return (0 until arr.length()).map { i ->
            val obj = arr.getJSONObject(i)
            Product(
                id = obj.getInt("id"),
                title = obj.getString("title"),
                description = obj.optString("description"),
                price = obj.getDouble("price"),
                stock = obj.getInt("stock"),
                category = obj.optString("category"),
                image_path = obj.optString("image_path")
            )
        }
    }

    // Cart
    suspend fun getCart(): List<CartItem> {
        val arr = requestArray("GET", "/cart")
        return (0 until arr.length()).map { i ->
            val obj = arr.getJSONObject(i)
            CartItem(
                id = obj.getInt("id"),
                product_id = obj.getInt("product_id"),
                title = obj.getString("title"),
                price = obj.getDouble("price"),
                quantity = obj.getInt("quantity"),
                image_path = obj.optString("image_path")
            )
        }
    }

    suspend fun addToCart(productId: Int, quantity: Int): Boolean {
        val json = request("POST", "/cart", JSONObject().apply {
            put("productId", productId)
            put("quantity", quantity)
        })
        return json.has("message")
    }

    suspend fun removeFromCart(cartItemId: Int): Boolean {
        val json = request("POST", "/cart/remove", JSONObject().apply {
            put("cartItemId", cartItemId)
        })
        return json.has("message")
    }

    // Orders
    suspend fun getOrders(): List<Order> {
        val arr = requestArray("GET", "/orders")
        return (0 until arr.length()).map { i ->
            val obj = arr.getJSONObject(i)
            Order(
                id = obj.getInt("id"),
                status = obj.getString("status"),
                total_amount = obj.getDouble("total_amount"),
                placed_at = obj.optString("placed_at"),
                approved_at = obj.optString("approved_at"),
                delivered_at = obj.optString("delivered_at")
            )
        }
    }

    suspend fun getOrderDetail(orderId: Int): Order? {
        val json = request("GET", "/orders/$orderId")
        return if (json.has("id")) {
            val itemsArr = json.optJSONArray("items")
            val items = if (itemsArr != null) {
                (0 until itemsArr.length()).map { j ->
                    val itemObj = itemsArr.getJSONObject(j)
                    OrderItem(
                        product_id = itemObj.getInt("product_id"),
                        title = itemObj.optString("title"),
                        quantity = itemObj.getInt("quantity"),
                        unit_price = itemObj.getDouble("unit_price"),
                        image_path = itemObj.optString("image_path")
                    )
                }
            } else null
            Order(
                id = json.getInt("id"),
                status = json.getString("status"),
                total_amount = json.getDouble("total_amount"),
                placed_at = json.optString("placed_at"),
                items = items
            )
        } else null
    }

    suspend fun createOrder(shippingAddressId: Int, items: List<Pair<Int, Pair<Int, Double>>>): Boolean {
        val itemsArr = JSONArray()
        items.forEach { (productId, qtyPrice) ->
            itemsArr.put(JSONObject().apply {
                put("productId", productId)
                put("quantity", qtyPrice.first)
                put("unitPrice", qtyPrice.second)
            })
        }
        val json = request("POST", "/orders", JSONObject().apply {
            put("shippingAddressId", shippingAddressId)
            put("items", itemsArr)
        })
        return json.has("orderId")
    }

    // Addresses
    suspend fun getAddresses(): List<Address> {
        val arr = requestArray("GET", "/addresses")
        return (0 until arr.length()).map { i ->
            val obj = arr.getJSONObject(i)
            Address(
                id = obj.getInt("id"),
                label = obj.optString("label"),
                street = obj.getString("street"),
                city = obj.getString("city"),
                state = obj.optString("state"),
                postal_code = obj.getString("postal_code"),
                country = obj.getString("country")
            )
        }
    }

    suspend fun createAddress(street: String, city: String, postalCode: String, country: String, label: String? = null, state: String? = null): Boolean {
        val json = request("POST", "/addresses", JSONObject().apply {
            put("street", street)
            put("city", city)
            put("postalCode", postalCode)
            put("country", country)
            label?.let { put("label", it) }
            state?.let { put("state", it) }
        })
        return json.has("addressId")
    }

    suspend fun deleteAddress(id: Int): Boolean {
        val json = request("DELETE", "/addresses/$id")
        return json.has("message")
    }

    // Reviews
    suspend fun getReviews(productId: Int): List<Review> {
        val arr = requestArray("GET", "/products/$productId/reviews")
        return (0 until arr.length()).map { i ->
            val obj = arr.getJSONObject(i)
            Review(
                id = obj.getInt("id"),
                rating = obj.getInt("rating"),
                comment = obj.optString("comment"),
                customer_name = obj.optString("customer_name"),
                created_at = obj.optString("created_at")
            )
        }
    }

    suspend fun submitReview(productId: Int, rating: Int, comment: String? = null): Boolean {
        val json = request("POST", "/reviews", JSONObject().apply {
            put("productId", productId)
            put("rating", rating)
            comment?.let { put("comment", it) }
        })
        return json.has("message")
    }

    // Search
    suspend fun search(query: String, categoryId: Int? = null, minPrice: Double? = null, maxPrice: Double? = null): List<Product> {
        val params = mutableListOf("q=$query")
        categoryId?.let { params.add("category_id=$it") }
        minPrice?.let { params.add("min_price=$it") }
        maxPrice?.let { params.add("max_price=$it") }
        val queryStr = params.joinToString("&")
        val arr = requestArray("GET", "/search?$queryStr")
        return (0 until arr.length()).map { i ->
            val obj = arr.getJSONObject(i)
            Product(
                id = obj.getInt("id"),
                title = obj.getString("title"),
                description = obj.optString("description"),
                price = obj.getDouble("price"),
                stock = obj.getInt("stock"),
                category = obj.optString("category"),
                image_path = obj.optString("image_path")
            )
        }
    }

    // Notifications
    suspend fun getNotifications(): List<Notification> {
        val json = request("GET", "/notifications")
        val arr = json.getJSONArray("notifications")
        return (0 until arr.length()).map { i ->
            val obj = arr.getJSONObject(i)
            Notification(
                id = obj.getInt("id"),
                title = obj.getString("title"),
                message = obj.getString("message"),
                is_read = obj.getInt("is_read") == 1,
                created_at = obj.optString("created_at")
            )
        }
    }

    // Payments
    suspend fun processPayment(orderId: Int, paymentMethod: String): Boolean {
        val json = request("POST", "/payments", JSONObject().apply {
            put("orderId", orderId)
            put("paymentMethod", paymentMethod)
        })
        return json.has("paymentId")
    }
}