package com.example.jeh_store

import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.List
import androidx.compose.material.icons.filled.Notifications
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.filled.Star
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import com.example.jeh_store.screens.*
import com.example.jeh_store.services.ApiService
import com.example.jeh_store.ui.theme.JEHSTORETheme

enum class Screen {
    LOGIN, HOME, PRODUCT_DETAIL, CART, ORDERS, ORDER_DETAIL, PROFILE, NOTIFICATIONS, ADDRESSES, ADD_ADDRESS
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun JehStoreApp() {
    val apiService = remember { ApiService() }
    var currentScreen by remember { mutableStateOf(Screen.LOGIN) }
    var isLoggedIn by remember { mutableStateOf(false) }
    var selectedProductId by remember { mutableIntStateOf(0) }
    var selectedOrderId by remember { mutableIntStateOf(0) }
    var cartItemCount by remember { mutableIntStateOf(0) }

    JEHSTORETheme {
        if (currentScreen == Screen.LOGIN && !isLoggedIn) {
            LoginScreen(
                apiService = apiService,
                onLoginSuccess = {
                    isLoggedIn = true
                    currentScreen = Screen.HOME
                }
            )
        } else if (!isLoggedIn) {
            LoginScreen(
                apiService = apiService,
                onLoginSuccess = {
                    isLoggedIn = true
                    currentScreen = Screen.HOME
                }
            )
        } else {
            Scaffold(
                bottomBar = {
                    NavigationBar {
                        NavigationBarItem(
                            icon = { Icon(Icons.Default.Home, contentDescription = "Home") },
                            label = { Text("Home") },
                            selected = currentScreen == Screen.HOME,
                            onClick = { currentScreen = Screen.HOME }
                        )
                        NavigationBarItem(
                            icon = {
                                BadgedBox(badge = {
                                    if (cartItemCount > 0) {
                                        Badge { Text("$cartItemCount") }
                                    }
                                }) {
                                    Icon(Icons.Default.ShoppingCart, contentDescription = "Cart")
                                }
                            },
                            label = { Text("Cart") },
                            selected = currentScreen == Screen.CART,
                            onClick = { currentScreen = Screen.CART }
                        )
                        NavigationBarItem(
                            icon = { Icon(Icons.Default.List, contentDescription = "Orders") },
                            label = { Text("Orders") },
                            selected = currentScreen == Screen.ORDERS,
                            onClick = { currentScreen = Screen.ORDERS }
                        )
                        NavigationBarItem(
                            icon = { Icon(Icons.Default.Notifications, contentDescription = "Notifications") },
                            label = { Text("Alerts") },
                            selected = currentScreen == Screen.NOTIFICATIONS,
                            onClick = { currentScreen = Screen.NOTIFICATIONS }
                        )
                        NavigationBarItem(
                            icon = { Icon(Icons.Default.Person, contentDescription = "Profile") },
                            label = { Text("Profile") },
                            selected = currentScreen == Screen.PROFILE,
                            onClick = { currentScreen = Screen.PROFILE }
                        )
                    }
                }
            ) { innerPadding ->
                Surface(modifier = Modifier.padding(innerPadding)) {
                    when (currentScreen) {
                        Screen.HOME -> HomeScreen(
                            apiService = apiService,
                            onProductClick = { productId ->
                                selectedProductId = productId
                                currentScreen = Screen.PRODUCT_DETAIL
                            },
                            onCartUpdated = { cartItemCount = it }
                        )
                        Screen.PRODUCT_DETAIL -> ProductDetailScreen(
                            apiService = apiService,
                            productId = selectedProductId,
                            onBack = { currentScreen = Screen.HOME },
                            onCartUpdated = { cartItemCount = it }
                        )
                        Screen.CART -> CartScreen(
                            apiService = apiService,
                            onBack = { currentScreen = Screen.HOME },
                            onCheckout = { currentScreen = Screen.ADDRESSES },
                            onCartUpdated = { cartItemCount = it }
                        )
                        Screen.ORDERS -> OrdersScreen(
                            apiService = apiService,
                            onOrderClick = { orderId ->
                                selectedOrderId = orderId
                                currentScreen = Screen.ORDER_DETAIL
                            }
                        )
                        Screen.ORDER_DETAIL -> OrderDetailScreen(
                            apiService = apiService,
                            orderId = selectedOrderId,
                            onBack = { currentScreen = Screen.ORDERS }
                        )
                        Screen.PROFILE -> ProfileScreen(
                            apiService = apiService,
                            onLogout = {
                                isLoggedIn = false
                                currentScreen = Screen.LOGIN
                            }
                        )
                        Screen.NOTIFICATIONS -> NotificationsScreen(
                            apiService = apiService
                        )
                        Screen.ADDRESSES -> AddressListScreen(
                            apiService = apiService,
                            onBack = { currentScreen = Screen.CART },
                            onAddressSelected = { addressId ->
                                // In a full app, would proceed to checkout with selected address
                                currentScreen = Screen.CART
                            },
                            onAddAddress = { currentScreen = Screen.ADD_ADDRESS }
                        )
                        Screen.ADD_ADDRESS -> AddAddressScreen(
                            apiService = apiService,
                            onBack = { currentScreen = Screen.ADDRESSES },
                            onAddressAdded = { currentScreen = Screen.ADDRESSES }
                        )
                        Screen.LOGIN -> {}
                    }
                }
            }
        }
    }
}