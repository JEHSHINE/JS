package com.example.jeh_store.ui.theme

import android.app.Activity
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.runtime.SideEffect
import androidx.compose.ui.graphics.toArgb
import androidx.compose.ui.platform.LocalView
import androidx.core.view.WindowCompat

// ── JEH STORE Color Schemes ───────────────────────────────────
// Primary: Orange (#F97316) for brand identity
// Dark: Navy (#0F172A) for sidebar/dark elements

private val JEHLightColorScheme = lightColorScheme(
    primary = Orange500,
    onPrimary = White,
    primaryContainer = Orange100,
    onPrimaryContainer = Orange700,
    secondary = DarkNavy,
    onSecondary = White,
    secondaryContainer = GrayWhite,
    onSecondaryContainer = DarkSlate,
    tertiary = Green600,
    onTertiary = White,
    background = LightGray,
    onBackground = DarkSlate,
    surface = White,
    onSurface = NearBlack,
    surfaceVariant = GrayWhite,
    onSurfaceVariant = GrayBlue,
    outline = GrayBlue,
    error = Red600,
    onError = White,
    errorContainer = Red100,
    onErrorContainer = Red800,
)

private val JEHDarkColorScheme = darkColorScheme(
    primary = Orange500,
    onPrimary = White,
    primaryContainer = Orange700,
    onPrimaryContainer = White,
    secondary = GrayBlue,
    onSecondary = White,
    secondaryContainer = DarkNavy,
    onSecondaryContainer = GrayWhite,
    tertiary = Green600,
    onTertiary = White,
    background = NearBlack,
    onBackground = GrayWhite,
    surface = DarkNavy,
    onSurface = GrayWhite,
    surfaceVariant = DarkSlate,
    onSurfaceVariant = GrayBlue,
    outline = GrayBlue,
    error = Red600,
    onError = White,
    errorContainer = Red800,
    onErrorContainer = Red100,
)

@Composable
fun JEHSTORETheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit
) {
    val colorScheme = if (darkTheme) JEHDarkColorScheme else JEHLightColorScheme

    val view = LocalView.current
    if (!view.isInEditMode) {
        SideEffect {
            val window = (view.context as Activity).window
            window.statusBarColor = colorScheme.surface.toArgb()
            WindowCompat.getInsetsController(window, view).isAppearanceLightStatusBars = !darkTheme
        }
    }

    MaterialTheme(
        colorScheme = colorScheme,
        typography = Typography,
        content = content
    )
}
