package com.example.jeh_store.screens

import androidx.compose.foundation.clickable
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
fun OrdersScreen(
    apiService: ApiService,
    onOrderClick: (Int) -> Unit
) {
    var orders by remember { mutableStateOf<List<Order>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }
    val scope = rememberCoroutineScope()

    LaunchedEffect(Unit) {
        scope.launch {
            orders = apiService.getOrders()
            isLoading = false
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(title = { Text("My Orders") })
        }
    ) { padding ->
        if (isLoading) {
            Box(modifier = Modifier.fillMaxSize().padding(padding), contentAlignment = Alignment.Center) {
                CircularProgressIndicator()
            }
        } else if (orders.isEmpty()) {
            Box(modifier = Modifier.fillMaxSize().padding(padding), contentAlignment = Alignment.Center) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Text("No orders yet", fontSize = 18.sp, fontWeight = FontWeight.Medium)
                    Spacer(modifier = Modifier.height(8.dp))
                    Text("Place your first order to see it here", color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
            }
        } else {
            LazyColumn(
                modifier = Modifier.fillMaxSize().padding(padding),
                contentPadding = PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                items(orders) { order ->
                    Card(
                        modifier = Modifier.fillMaxWidth().clickable { onOrderClick(order.id) },
                        elevation = CardDefaults.cardElevation(2.dp)
                    ) {
                        Column(modifier = Modifier.padding(16.dp)) {
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                horizontalArrangement = Arrangement.SpaceBetween
                            ) {
                                Text(
                                    text = "Order #${order.id}",
                                    fontWeight = FontWeight.Bold,
                                    fontSize = 16.sp
                                )
                                StatusChip(status = order.status)
                            }
                            Spacer(modifier = Modifier.height(8.dp))
                            Text(
                                text = "Total: $${String.format("%.2f", order.total_amount)}",
                                fontWeight = FontWeight.SemiBold,
                                color = MaterialTheme.colorScheme.primary,
                                fontSize = 18.sp
                            )
                            if (order.placed_at != null) {
                                Text(
                                    text = "Placed: ${order.placed_at}",
                                    style = MaterialTheme.typography.bodySmall,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant
                                )
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun StatusChip(status: String) {
    val color = when (status) {
        "pending" -> MaterialTheme.colorScheme.tertiary
        "approved" -> MaterialTheme.colorScheme.secondary
        "shipped" -> MaterialTheme.colorScheme.primary
        "delivered" -> MaterialTheme.colorScheme.secondary
        "cancelled" -> MaterialTheme.colorScheme.error
        else -> MaterialTheme.colorScheme.onSurfaceVariant
    }
    Surface(color = color.copy(alpha = 0.15f), shape = MaterialTheme.shapes.small) {
        Text(
            text = status.uppercase(),
            modifier = Modifier.padding(horizontal = 12.dp, vertical = 4.dp),
            color = color,
            fontWeight = FontWeight.SemiBold,
            fontSize = 12.sp
        )
    }
}