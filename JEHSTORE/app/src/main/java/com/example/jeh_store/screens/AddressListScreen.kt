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
import com.example.jeh_store.models.Address
import com.example.jeh_store.services.ApiService
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AddressListScreen(
    apiService: ApiService,
    onBack: () -> Unit,
    onAddressSelected: (Int) -> Unit,
    onAddAddress: () -> Unit
) {
    var addresses by remember { mutableStateOf<List<Address>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }
    val scope = rememberCoroutineScope()

    LaunchedEffect(Unit) {
        scope.launch {
            addresses = apiService.getAddresses()
            isLoading = false
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Shipping Addresses") },
                navigationIcon = { TextButton(onClick = onBack) { Text("Back") } }
            )
        },
        floatingActionButton = {
            FloatingActionButton(onClick = onAddAddress) {
                Text("+", fontSize = 24.sp)
            }
        }
    ) { padding ->
        if (isLoading) {
            Box(modifier = Modifier.fillMaxSize().padding(padding), contentAlignment = Alignment.Center) {
                CircularProgressIndicator()
            }
        } else if (addresses.isEmpty()) {
            Box(modifier = Modifier.fillMaxSize().padding(padding), contentAlignment = Alignment.Center) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Text("No addresses saved", fontSize = 18.sp, fontWeight = FontWeight.Medium)
                    Spacer(modifier = Modifier.height(8.dp))
                    Text("Add an address to proceed with checkout", color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
            }
        } else {
            LazyColumn(
                modifier = Modifier.fillMaxSize().padding(padding),
                contentPadding = PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                items(addresses) { address ->
                    Card(
                        onClick = { onAddressSelected(address.id) },
                        modifier = Modifier.fillMaxWidth(),
                        elevation = CardDefaults.cardElevation(2.dp)
                    ) {
                        Column(modifier = Modifier.padding(16.dp)) {
                            if (!address.label.isNullOrBlank()) {
                                Text(
                                    text = address.label,
                                    fontWeight = FontWeight.Bold,
                                    fontSize = 16.sp
                                )
                                Spacer(modifier = Modifier.height(4.dp))
                            }
                            Text(text = address.street)
                            Text(text = "${address.city}, ${address.state ?: ""} ${address.postal_code}")
                            Text(text = address.country)
                        }
                    }
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AddAddressScreen(
    apiService: ApiService,
    onBack: () -> Unit,
    onAddressAdded: () -> Unit
) {
    var label by remember { mutableStateOf("") }
    var street by remember { mutableStateOf("") }
    var city by remember { mutableStateOf("") }
    var state by remember { mutableStateOf("") }
    var postalCode by remember { mutableStateOf("") }
    var country by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(false) }
    var errorMessage by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Add Address") },
                navigationIcon = { TextButton(onClick = onBack) { Text("Back") } }
            )
        }
    ) { padding ->
        Column(
            modifier = Modifier.fillMaxSize().padding(padding).padding(16.dp)
        ) {
            OutlinedTextField(
                value = label,
                onValueChange = { label = it },
                label = { Text("Label (e.g. Home, Work)") },
                modifier = Modifier.fillMaxWidth()
            )
            Spacer(modifier = Modifier.height(8.dp))
            OutlinedTextField(
                value = street,
                onValueChange = { street = it },
                label = { Text("Street Address *") },
                modifier = Modifier.fillMaxWidth()
            )
            Spacer(modifier = Modifier.height(8.dp))
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                OutlinedTextField(
                    value = city,
                    onValueChange = { city = it },
                    label = { Text("City *") },
                    modifier = Modifier.weight(1f)
                )
                OutlinedTextField(
                    value = state,
                    onValueChange = { state = it },
                    label = { Text("State") },
                    modifier = Modifier.weight(1f)
                )
            }
            Spacer(modifier = Modifier.height(8.dp))
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                OutlinedTextField(
                    value = postalCode,
                    onValueChange = { postalCode = it },
                    label = { Text("Postal Code *") },
                    modifier = Modifier.weight(1f)
                )
                OutlinedTextField(
                    value = country,
                    onValueChange = { country = it },
                    label = { Text("Country *") },
                    modifier = Modifier.weight(1f)
                )
            }

            if (errorMessage != null) {
                Spacer(modifier = Modifier.height(8.dp))
                Text(text = errorMessage!!, color = MaterialTheme.colorScheme.error)
            }

            Spacer(modifier = Modifier.height(24.dp))

            Button(
                onClick = {
                    if (street.isBlank() || city.isBlank() || postalCode.isBlank() || country.isBlank()) {
                        errorMessage = "Please fill all required fields"
                        return@Button
                    }
                    scope.launch {
                        isLoading = true
                        val success = apiService.createAddress(
                            street = street,
                            city = city,
                            postalCode = postalCode,
                            country = country,
                            label = label.ifBlank { null },
                            state = state.ifBlank { null }
                        )
                        isLoading = false
                        if (success) {
                            onAddressAdded()
                        } else {
                            errorMessage = "Failed to save address"
                        }
                    }
                },
                modifier = Modifier.fillMaxWidth().height(50.dp),
                enabled = !isLoading
            ) {
                if (isLoading) CircularProgressIndicator(modifier = Modifier.size(24.dp))
                else Text("Save Address", fontSize = 16.sp)
            }
        }
    }
}