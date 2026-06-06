package com.example.jeh_store.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.jeh_store.models.Product
import com.example.jeh_store.models.Review
import com.example.jeh_store.services.ApiService
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ProductDetailScreen(
    apiService: ApiService,
    productId: Int,
    onBack: () -> Unit,
    onCartUpdated: (Int) -> Unit
) {
    var product by remember { mutableStateOf<Product?>(null) }
    var reviews by remember { mutableStateOf<List<Review>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }
    var quantity by remember { mutableIntStateOf(1) }
    var addedMessage by remember { mutableStateOf<String?>(null) }
    var showReviewDialog by remember { mutableStateOf(false) }
    var reviewRating by remember { mutableIntStateOf(5) }
    var reviewComment by remember { mutableStateOf("") }
    val scope = rememberCoroutineScope()

    LaunchedEffect(productId) {
        scope.launch {
            product = apiService.getProductDetail(productId)
            reviews = apiService.getReviews(productId)
            isLoading = false
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(title = { Text("Product Details") }, navigationIcon = {
                TextButton(onClick = onBack) { Text("Back") }
            })
        }
    ) { padding ->
        if (isLoading) {
            Box(modifier = Modifier.fillMaxSize().padding(padding), contentAlignment = Alignment.Center) {
                CircularProgressIndicator()
            }
        } else if (product != null) {
            Column(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(padding)
                    .padding(16.dp)
                    .verticalScroll(rememberScrollState())
            ) {
                Text(
                    text = product!!.title,
                    fontWeight = FontWeight.Bold,
                    fontSize = 24.sp
                )

                Spacer(modifier = Modifier.height(8.dp))

                Text(
                    text = "$${String.format("%.2f", product!!.price)}",
                    fontSize = 28.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = MaterialTheme.colorScheme.primary
                )

                Spacer(modifier = Modifier.height(8.dp))

                Text(
                    text = if (product!!.stock > 0) "In Stock: ${product!!.stock} units"
                    else "Out of Stock",
                    color = if (product!!.stock > 0) MaterialTheme.colorScheme.onSurfaceVariant
                    else MaterialTheme.colorScheme.error
                )

                Spacer(modifier = Modifier.height(16.dp))

                val productDescription = product?.description
                if (!productDescription.isNullOrBlank()) {
                    Text(
                        text = productDescription,
                        style = MaterialTheme.typography.bodyLarge
                    )
                }

                Spacer(modifier = Modifier.height(24.dp))

                // Quantity selector
                Row(
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.spacedBy(16.dp)
                ) {
                    Text("Quantity:", fontWeight = FontWeight.Medium)
                    OutlinedButton(onClick = { if (quantity > 1) quantity-- }) {
                        Text("-")
                    }
                    Text("$quantity", fontWeight = FontWeight.Bold, fontSize = 18.sp)
                    OutlinedButton(onClick = { quantity++ }) {
                        Text("+")
                    }
                }

                Spacer(modifier = Modifier.height(16.dp))

                Button(
                    onClick = {
                        scope.launch {
                            val success = apiService.addToCart(productId, quantity)
                            if (success) {
                                addedMessage = "Added to cart!"
                                onCartUpdated(apiService.getCart().size)
                            } else {
                                addedMessage = "Failed to add to cart"
                            }
                        }
                    },
                    modifier = Modifier.fillMaxWidth().height(50.dp),
                    enabled = product!!.stock > 0
                ) {
                    Text("Add to Cart", fontSize = 16.sp)
                }

                if (addedMessage != null) {
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        text = addedMessage!!,
                        color = MaterialTheme.colorScheme.primary,
                        fontWeight = FontWeight.Medium
                    )
                }

                Spacer(modifier = Modifier.height(32.dp))

                // Reviews section
                Text(
                    text = "Reviews (${reviews.size})",
                    fontWeight = FontWeight.Bold,
                    fontSize = 20.sp
                )

                Spacer(modifier = Modifier.height(8.dp))

                reviews.forEach { review ->
                    Card(
                        modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
                        elevation = CardDefaults.cardElevation(1.dp)
                    ) {
                        Column(modifier = Modifier.padding(12.dp)) {
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                horizontalArrangement = Arrangement.SpaceBetween
                            ) {
                                Text(
                                    text = review.customer_name ?: "Anonymous",
                                    fontWeight = FontWeight.Medium
                                )
                                Text(
                                    text = "${"★".repeat(review.rating)}${"☆".repeat(5 - review.rating)}",
                                    color = MaterialTheme.colorScheme.primary
                                )
                            }
                            if (!review.comment.isNullOrBlank()) {
                                Spacer(modifier = Modifier.height(4.dp))
                                Text(text = review.comment, style = MaterialTheme.typography.bodyMedium)
                            }
                        }
                    }
                }

                Spacer(modifier = Modifier.height(16.dp))

                OutlinedButton(
                    onClick = { showReviewDialog = true },
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Text("Write a Review")
                }
            }
        } else {
            Box(modifier = Modifier.fillMaxSize().padding(padding), contentAlignment = Alignment.Center) {
                Text("Product not found")
            }
        }
    }

    if (showReviewDialog) {
        AlertDialog(
            onDismissRequest = { showReviewDialog = false },
            title = { Text("Write a Review") },
            text = {
                Column {
                    Text("Rating:")
                    Row(horizontalArrangement = Arrangement.spacedBy(4.dp)) {
                        (1..5).forEach { r ->
                            TextButton(onClick = { reviewRating = r }) {
                                Text(
                                    if (r <= reviewRating) "★" else "☆",
                                    color = MaterialTheme.colorScheme.primary,
                                    fontSize = 24.sp
                                )
                            }
                        }
                    }
                    Spacer(modifier = Modifier.height(8.dp))
                    OutlinedTextField(
                        value = reviewComment,
                        onValueChange = { reviewComment = it },
                        label = { Text("Comment (optional)") },
                        modifier = Modifier.fillMaxWidth()
                    )
                }
            },
            confirmButton = {
                Button(onClick = {
                    scope.launch {
                        apiService.submitReview(productId, reviewRating, reviewComment.ifBlank { null })
                        reviews = apiService.getReviews(productId)
                        showReviewDialog = false
                    }
                }) {
                    Text("Submit")
                }
            },
            dismissButton = {
                TextButton(onClick = { showReviewDialog = false }) {
                    Text("Cancel")
                }
            }
        )
    }
}