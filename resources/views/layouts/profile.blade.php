<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($user) ? ($user->name ?? $user->username) : config('app.name') }} | {{ config('app.name') }} </title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:400,600,700|inter:400,500,600,700|roboto:400,500,700|open-sans:400,600,700|lato:400,700|montserrat:400,500,600,700|playfair-display:400,700|merriweather:400,700" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        @if(isset($user))
            @php
                // Get user customization settings
                $portfolioFont = $user->portfolio_font ?? 'system';
                $portfolioTheme = $user->portfolio_theme ?? 'auto';
                $accentColor = $user->portfolio_accent_color ?? '#6366F1';
                $backgroundColor = $user->portfolio_background_color;
                $textColor = $user->portfolio_text_color;
                $headingColor = $user->portfolio_heading_color;

                // Font mapping
                $fontMap = [
                    'system' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                    'nunito' => "'Nunito', sans-serif",
                    'inter' => "'Inter', sans-serif",
                    'playfair' => "'Playfair Display', serif",
                    'roboto' => "'Roboto', sans-serif",
                    'open-sans' => "'Open Sans', sans-serif",
                    'lato' => "'Lato', sans-serif",
                    'montserrat' => "'Montserrat', sans-serif",
                    'merriweather' => "'Merriweather', serif",
                ];

                $fontFamily = $fontMap[$portfolioFont] ?? $fontMap['system'];

                // Default theme colors
                $defaultLightBg = '#F3F4F6';
                $defaultLightText = '#1F2937';
                $defaultLightHeading = '#111827';
                $defaultDarkBg = '#111827';
                $defaultDarkText = '#F3F4F6';
                $defaultDarkHeading = '#F9FAFB';

                // Convert hex to rgb components
                $hex = ltrim($accentColor, '#');
                if (strlen($hex) === 3) {
                    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                }
                [$r,$g,$b] = [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
                $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000; // YIQ contrast
                // If accent is very light, darken overlay base to ensure readability
                $overlayBase = $brightness > 180 ? '0,0,0' : "$r,$g,$b";
            @endphp

            <style>
                :root {
                    --portfolio-accent: {{ $accentColor }};
                    --portfolio-accent-rgb: {{ $r }}, {{ $g }}, {{ $b }};
                    --portfolio-accent-overlay-start: rgba({{ $overlayBase }},0.70);
                    --portfolio-accent-overlay-mid: rgba({{ $overlayBase }},0.40);
                    --portfolio-accent-overlay-end: rgba({{ $overlayBase }},0.0);
                    --portfolio-font: {{ $fontFamily }};

                    @if($portfolioTheme === 'light' || $portfolioTheme === 'auto')
                        --portfolio-bg-light: {{ $backgroundColor ?: $defaultLightBg }};
                        --portfolio-text-light: {{ $textColor ?: $defaultLightText }};
                        --portfolio-heading-light: {{ $headingColor ?: $defaultLightHeading }};
                    @endif

                    @if($portfolioTheme === 'dark' || $portfolioTheme === 'auto')
                        --portfolio-bg-dark: {{ $backgroundColor ?: $defaultDarkBg }};
                        --portfolio-text-dark: {{ $textColor ?: $defaultDarkText }};
                        --portfolio-heading-dark: {{ $headingColor ?: $defaultDarkHeading }};
                    @endif
                }

                /* Apply custom font */
                body {
                    font-family: var(--portfolio-font) !important;
                }

                /* Force theme if not auto */
                @if($portfolioTheme === 'light')
                    html {
                        color-scheme: light;
                    }
                    body {
                        background-color: var(--portfolio-bg-light) !important;
                        color: var(--portfolio-text-light) !important;
                    }
                    h1, h2, h3, h4, h5, h6 {
                        color: var(--portfolio-heading-light) !important;
                    }
                    /* Override Tailwind dark mode classes */
                    .dark\:bg-gray-900, .dark\:bg-gray-800 {
                        background-color: var(--portfolio-bg-light) !important;
                    }
                    .dark\:text-gray-100, .dark\:text-gray-200, .dark\:text-gray-300 {
                        color: var(--portfolio-text-light) !important;
                    }
                @elseif($portfolioTheme === 'dark')
                    html {
                        color-scheme: dark;
                    }
                    body {
                        background-color: var(--portfolio-bg-dark) !important;
                        color: var(--portfolio-text-dark) !important;
                    }
                    h1, h2, h3, h4, h5, h6 {
                        color: var(--portfolio-heading-dark) !important;
                    }
                    /* Override light mode Tailwind classes */
                    .bg-gray-100, .bg-white {
                        background-color: var(--portfolio-bg-dark) !important;
                    }
                    .text-gray-900, .text-gray-800, .text-gray-700 {
                        color: var(--portfolio-text-dark) !important;
                    }
                @else
                    /* Auto theme - respect system preference */
                    @media (prefers-color-scheme: light) {
                        body {
                            background-color: var(--portfolio-bg-light) !important;
                            color: var(--portfolio-text-light) !important;
                        }
                        h1, h2, h3, h4, h5, h6 {
                            color: var(--portfolio-heading-light) !important;
                        }
                    }
                    @media (prefers-color-scheme: dark) {
                        body {
                            background-color: var(--portfolio-bg-dark) !important;
                            color: var(--portfolio-text-dark) !important;
                        }
                        h1, h2, h3, h4, h5, h6 {
                            color: var(--portfolio-heading-dark) !important;
                        }
                    }
                @endif

                /* Accent color application */
                a.portfolio-link,
                a[href^="/@"]:not(.no-accent),
                .portfolio-accent {
                    color: var(--portfolio-accent) !important;
                }

                button.portfolio-button,
                .portfolio-button {
                    background-color: var(--portfolio-accent) !important;
                    border-color: var(--portfolio-accent) !important;
                }

                a.portfolio-link:hover {
                    opacity: 0.8;
                }
            </style>
        @endif

        @php($favicon = asset('favicon.ico'))
        @if(isset($user) && ($user->logo_path || $user->logo_thumb_path))
            @php($favicon = $user->logoUrl() ?? $favicon)
        @endif
        <link rel="icon" type="image/png" href="{{ $favicon }}" />
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 overflow-x-hidden">
        @if(Auth::check())
            @include('layouts.navigation')
        @endif
        <div class="bg-gray-100 dark:bg-gray-900">
            <!-- Page Heading -->
            <header class="md:fixed md:top-0 md:left-0 md:w-80 md:min-h-screen bg-white dark:bg-gray-800 shadow-lg z-10">
                @if (isset($header))
                <div class="max-w-7xl md:mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
                @endif
            </header>

            <!-- Page Content -->
            <main class="md:ml-80 bg-gray-100 dark:bg-gray-900 overflow-x-hidden" @if(Auth::check()) style="min-height: calc(100vh - 4rem);" @endif>
                {{ $slot }}
            </main>
        </div>

        <!-- Global Modals -->
        <x-modals />

        @livewireScripts
    </body>
</html>
