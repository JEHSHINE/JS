import 'package:flutter/material.dart';
import 'package:jeh_store_flutter/screens/cart_screen.dart';
import 'package:jeh_store_flutter/screens/home_screen.dart';
import 'package:jeh_store_flutter/screens/login_screen.dart';
import 'package:jeh_store_flutter/screens/order_detail_screen.dart';
import 'package:jeh_store_flutter/screens/orders_screen.dart';
import 'package:jeh_store_flutter/screens/product_detail_screen.dart';
import 'package:jeh_store_flutter/screens/profile_screen.dart';

void main() {
  runApp(const JEHStoreApp());
}

class JEHStoreApp extends StatelessWidget {
  const JEHStoreApp({super.key});

  @override
  Widget build(BuildContext context) {
    // JEH STORE Brand Colors
    const Color brandOrange = Color(0xFFF97316);
    const Color brandOrangeDark = Color(0xFFEA580C);
    const Color darkNavy = Color(0xFF0F172A);
    const Color darkText = Color(0xFF111827);
    const Color mutedText = Color(0xFF64748B);
    const Color successGreen = Color(0xFF16A34A);
    const Color dangerRed = Color(0xFFDC2626);
    const Color warningAmber = Color(0xFFF59E0B);

    return MaterialApp(
      title: 'JEH Store',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.light(
          primary: brandOrange,
          onPrimary: Colors.white,
          primaryContainer: brandOrange.withOpacity(0.1),
          onPrimaryContainer: brandOrangeDark,
          secondary: darkNavy,
          onSecondary: Colors.white,
          secondaryContainer: const Color(0xFFF8FAFC),
          onSecondaryContainer: darkText,
          tertiary: successGreen,
          onTertiary: Colors.white,
          background: const Color(0xFFF5F7FB),
          onBackground: darkText,
          surface: Colors.white,
          onSurface: darkText,
          surfaceVariant: const Color(0xFFF8FAFC),
          onSurfaceVariant: mutedText,
          error: dangerRed,
          onError: Colors.white,
          errorContainer: const Color(0xFFFEE2E2),
          onErrorContainer: const Color(0xFF991B1B),
          outline: mutedText,
        ),
        useMaterial3: true,
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.white,
          foregroundColor: darkText,
          elevation: 0,
          centerTitle: true,
        ),
        navigationBarTheme: NavigationBarThemeData(
          backgroundColor: Colors.white,
          indicatorColor: brandOrange.withOpacity(0.15),
          labelTextStyle: WidgetStateProperty.resolveWith((states) {
            if (states.contains(WidgetState.selected)) {
              return const TextStyle(color: brandOrange, fontWeight: FontWeight.w600, fontSize: 12);
            }
            return const TextStyle(color: mutedText, fontSize: 12);
          }),
          iconTheme: WidgetStateProperty.resolveWith((states) {
            if (states.contains(WidgetState.selected)) {
              return const IconThemeData(color: brandOrange, size: 24);
            }
            return const IconThemeData(color: mutedText, size: 24);
          }),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: brandOrange,
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            elevation: 0,
          ),
        ),
        outlinedButtonTheme: OutlinedButtonThemeData(
          style: OutlinedButton.styleFrom(
            foregroundColor: darkNavy,
            side: const BorderSide(color: mutedText),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          ),
        ),
        cardTheme: CardThemeData(
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          color: Colors.white,
        ),
        chipTheme: ChipThemeData(
          selectedColor: brandOrange.withOpacity(0.15),
          labelStyle: const TextStyle(fontSize: 13),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          side: BorderSide.none,
        ),
        floatingActionButtonTheme: const FloatingActionButtonThemeData(
          backgroundColor: brandOrange,
          foregroundColor: Colors.white,
        ),
        badgeTheme: const BadgeThemeData(
          backgroundColor: dangerRed,
          textColor: Colors.white,
        ),
      ),
      initialRoute: '/login',
      routes: {
        '/login': (context) => const LoginScreen(),
        '/home': (context) => const HomeScreen(),
        '/cart': (context) => const CartScreen(),
        '/product-detail': (context) => const ProductDetailScreen(),
        '/orders': (context) => const OrdersScreen(),
        '/order-detail': (context) => const OrderDetailScreen(),
        '/profile': (context) => const ProfileScreen(),
      },
    );
  }
}