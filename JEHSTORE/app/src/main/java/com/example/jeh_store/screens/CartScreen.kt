package com.example.jeh_store.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.jeh_store.models.CartItem
import com.example.jeh_store.services.ApiService
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CartScreen(
    apiService: ApiService,
    onBack: () -> Unit,
    onCheckout: () -> Unit,
    onCartUpdated: (Int) -> Unit
) {
    var cartItems by remember { mutableStateOf<List<CartItem>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }
    val scope = rememberCoroutineScope()

    LaunchedEffect(Unit) {
        scope.launch {
            cartItems = apiService.getCart()
            isLoading = false
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(title = { Text("Shopping Cart") }, navigationIcon = {
                TextButton(onClick = onBack) { Text("Back") }
            })
        }
    ) { padding ->
        if (isLoading) {
            Box(modifier = Modifier.fillMaxSize().padding(padding), contentAlignment = Alignment.Center) {
                CircularProgressIndicator()
            }
        } else if (cartItems.isEmpty()) {
            Box(modifier = Modifier.fillMaxSize().padding(padding), contentAlignment = Alignment.Center) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Text("Your cart is empty", fontSize = 18.sp, fontWeight = FontWeight.Medium)
                    Spacer(modifier = Modifier.height(8.dp))
                    Text("Browse products and add items to your cart", color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
            }
        } else {
            Column(modifier = Modifier.fillMaxSize().padding(padding)) {
                LazyColumn(
                    modifier = Modifier.weight(1f),
                    contentPadding = PaddingValues(16.dp),
                    verticalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    items(cartItems) { item ->
                        CartItemCard(
                            item = item,
                            onRemove = {
                                scope.launch {
                                    apiService.removeFromCart(item.id)
                                    cartItems = apiService.getCart()
                                    onCartUpdated(cartItems.size)
                                }
                            }
                        )
                    }
                }

                // Total and checkout
                Surface(
                    modifier = Modifier.fillMaxWidth(),
                    shadowElevation = 8.dp
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        val total = cartItems.sumOf { it.price * it.quantity }
                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween
                        ) {
                            Text("Total:", fontWeight = FontWeight.Bold, fontSize = 20.sp)
                            Text(
                                "$${String.format("%.2f", total)}",
                                fontWeight = FontWeight.Bold,
                                fontSize = 20.sp,
                                color = MaterialTheme.colorScheme.primary
                            )
                        }
                        Spacer(modifier = Modifier.height(12.dp))
                        Button(
                            onClick = onCheckout,
                            modifier = Modifier.fillMaxWidth().height(50.dp)
                        ) {
                            Text("Proceed to Checkout", fontSize = 16.sp)
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun CartItemCard(item: CartItem, onRemove: () -> Unit) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        elevation = CardDefaults.cardElevation(2.dp)
    ) {
        Row(
            modifier = Modifier.padding(16.dp).fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = item.title,
                    fontWeight = FontWeight.Medium,
                    fontSize = 16.sp
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = "$${String.format("%.2f", item.price)} x ${item.quantity}",
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
                Text(
                    text = "Subtotal: $${String.format("%.2f", item.price * item.quantity)}",
                    fontWeight = FontWeight.SemiBold,
                    color = MaterialTheme.colorScheme.primary
                )
            }
            TextButton(onClick = onRemove) {
                Text("Remove", color = MaterialTheme.colorScheme.error)
            }
        }
    }
}