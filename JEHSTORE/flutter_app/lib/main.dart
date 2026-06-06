import 'package:flutter/material.dart';
import 'package:jeh_store_flutter/screens/cart_screen.dart';
import 'package:jeh_store_flutter/screens/home_screen.dart';
import 'package:jeh_store_flutter/screens/login_screen.dart';

void main() {
  runApp(const JEHStoreApp());
}

class JEHStoreApp extends StatelessWidget {
  const JEHStoreApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'JEH Store',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blueAccent),
        useMaterial3: true,
      ),
      initialRoute: '/login',
      routes: {
        '/login': (context) => const LoginScreen(),
        '/home': (context) => const HomeScreen(),
        '/cart': (context) => const CartScreen(),
      },
    );
  }
}
