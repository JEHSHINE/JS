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
import com.example.jeh_store.models.Order
import com.example.jeh_store.services.ApiService
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun OrderDetailScreen(
    apiService: ApiService,
    orderId: Int,
    onBack: () -> Unit
) {
    var order by remember { mutableStateOf<Order?>(null) }
    var isLoading by remember { mutableStateOf(true) }
    var isPaying by remember { mutableStateOf(false) }
    var paymentMessage by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    LaunchedEffect(orderId) {
        scope.launch {
            order = apiService.getOrderDetail(orderId)
            isLoading = false
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Order #$orderId") },
                navigationIcon = { TextButton(onClick = onBack) { Text("Back") } }
            )
        }
    ) { padding ->
        if (isLoading) {
            Box(modifier = Modifier.fillMaxSize().padding(padding), contentAlignment = Alignment.Center) {
                CircularProgressIndicator()
            }
        } else if (order != null) {
            Column(
                modifier = Modifier.fillMaxSize().padding(padding).padding(16.dp)
            ) {
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween
                ) {
                    Text("Status:", fontWeight = FontWeight.Medium)
                    StatusChip(status = order!!.status)
                }

                Spacer(modifier = Modifier.height(12.dp))

                Text(
                    text = "Total: $${String.format("%.2f", order!!.total_amount)}",
                    fontWeight = FontWeight.Bold,
                    fontSize = 24.sp,
                    color = MaterialTheme.colorScheme.primary
                )

                if (order!!.placed_at != null) {
                    Text(
                        text = "Placed at: ${order!!.placed_at}",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }

                Spacer(modifier = Modifier.height(24.dp))

                Text(
                    text = "Items (${order!!.items?.size ?: 0})",
                    fontWeight = FontWeight.Bold,
                    fontSize = 18.sp
                )

                Spacer(modifier = Modifier.height(8.dp))

                order!!.items?.forEach { item ->
                    Card(
                        modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
                        elevation = CardDefaults.cardElevation(1.dp)
                    ) {
                        Row(
                            modifier = Modifier.padding(12.dp).fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween,
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Column(modifier = Modifier.weight(1f)) {
                                Text(
                                    text = item.title ?: "Product #${item.product_id}",
                                    fontWeight = FontWeight.Medium
                                )
                                Text(
                                    text = "$${String.format("%.2f", item.unit_price)} x ${item.quantity}",
                                    style = MaterialTheme.typography.bodySmall
                                )
                            }
                            Text(
                                text = "$${String.format("%.2f", item.unit_price * item.quantity)}",
                                fontWeight = FontWeight.SemiBold
                            )
                        }
                    }
                }

                Spacer(modifier = Modifier.height(24.dp))

                // Payment section for pending orders
                if (order!!.status == "pending" && paymentMessage == null) {
                    Text("Payment", fontWeight = FontWeight.Bold, fontSize = 18.sp)
                    Spacer(modifier = Modifier.height(8.dp))
                    Button(
                        onClick = {
                            scope.launch {
                                isPaying = true
                                val success = apiService.processPayment(orderId, "credit_card")
                                isPaying = false
                                paymentMessage = if (success) "Payment successful!" else "Payment failed"
                                if (success) {
                                    order = apiService.getOrderDetail(orderId)
                                }
                            }
                        },
                        modifier = Modifier.fillMaxWidth().height(50.dp),
                        enabled = !isPaying
                    ) {
                        if (isPaying) CircularProgressIndicator(modifier = Modifier.size(24.dp))
                        else Text("Pay Now - $${String.format("%.2f", order!!.total_amount)}", fontSize = 16.sp)
                    }

                    OutlinedButton(
                        onClick = {
                            scope.launch {
                                isPaying = true
                                val success = apiService.processPayment(orderId, "cod")
                                isPaying = false
                                paymentMessage = if (success) "Order placed with Cash on Delivery" else "Failed"
                                if (success) {
                                    order = apiService.getOrderDetail(orderId)
                                }
                            }
                        },
                        modifier = Modifier.fillMaxWidth().height(50.dp),
                        enabled = !isPaying
                    ) {
                        Text("Cash on Delivery", fontSize = 16.sp)
                    }
                }

                if (paymentMessage != null) {
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        text = paymentMessage!!,
                        color = if (paymentMessage!!.contains("successful", ignoreCase = true) || paymentMessage!!.contains("placed", ignoreCase = true))
                            MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.error,
                        fontWeight = FontWeight.Medium
                    )
                }
            }
        } else {
            Box(modifier = Modifier.fillMaxSize().padding(padding), contentAlignment = Alignment.Center) {
                Text("Order not found")
            }
        }
    }
}