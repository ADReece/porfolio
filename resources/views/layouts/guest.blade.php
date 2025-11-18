<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ isset($user) ? ($user->name ?? $user->username) . ' | ' . config('app.name') : config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700|inter:400,500,600,700|roboto:400,500,700|open-sans:400,600,700|lato:400,700|montserrat:400,500,600,700|playfair-display:400,700|merriweather:400,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php($favicon = asset('favicon.ico'))
    @if(isset($user) && ($user->logo_path || $user->logo_thumb_path))
        @php($favicon = $user->logoUrl() ?? $favicon)
    @endif
    <link rel="icon" type="image/png" href="{{ $favicon }}" />

    @if(isset($user) && method_exists($user,'canUseCustomizations') && $user->canUseCustomizations())
        @php
            $portfolioFont = $user->portfolio_font ?? 'system';
            $portfolioTheme = $user->portfolio_theme ?? 'auto';
            $accentColor = $user->portfolio_accent_color ?? '#6366F1';
            $backgroundColor = $user->portfolio_background_color;
            $textColor = $user->portfolio_text_color;
            $headingColor = $user->portfolio_heading_color;
            $fontMap = [
                'system' => 'system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif',
                'nunito' => '"Nunito",sans-serif',
                'inter' => '"Inter",sans-serif',
                'playfair' => '"Playfair Display",serif',
                'roboto' => '"Roboto",sans-serif',
                'open-sans' => '"Open Sans",sans-serif',
                'lato' => '"Lato",sans-serif',
                'montserrat' => '"Montserrat",sans-serif',
                'merriweather' => '"Merriweather",serif',
            ];
            $fontFamily = $fontMap[$portfolioFont] ?? $fontMap['system'];
            $defaultLightBg = '#F3F4F6';
            $defaultLightText = '#1F2937';
            $defaultLightHeading = '#111827';
            $defaultDarkBg = '#111827';
            $defaultDarkText = '#F3F4F6';
            $defaultDarkHeading = '#F9FAFB';
        @endphp
        <style>
            :root {
                --portfolio-font: {{ $fontFamily }};
                --portfolio-accent: {{ $accentColor }};
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
            body, body * { font-family: var(--portfolio-font) !important; }
            @if($portfolioTheme === 'light')
            html { color-scheme: light; }
            body { background: var(--portfolio-bg-light) !important; color: var(--portfolio-text-light) !important; }
            h1,h2,h3,h4,h5,h6 { color: var(--portfolio-heading-light) !important; }
            @elseif($portfolioTheme === 'dark')
            html { color-scheme: dark; }
            body { background: var(--portfolio-bg-dark) !important; color: var(--portfolio-text-dark) !important; }
            h1,h2,h3,h4,h5,h6 { color: var(--portfolio-heading-dark) !important; }
            @else
            @@media (prefers-color-scheme: light){body{background:var(--portfolio-bg-light)!important;color:var(--portfolio-text-light)!important;}h1,h2,h3,h4,h5,h6{color:var(--portfolio-heading-light)!important;}}
            @@media (prefers-color-scheme: dark){body{background:var(--portfolio-bg-dark)!important;color:var(--portfolio-text-dark)!important;}h1,h2,h3,h4,h5,h6{color:var(--portfolio-heading-dark)!important;}}
            @endif
            .guest-card a.button,.guest-card button,.guest-card .accent-bg { background: var(--portfolio-accent); color:#fff; }
            .guest-card a.link { color: var(--portfolio-accent)!important; }
        </style>
    @endif
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div>
            <a href="/">
                @if(isset($user) && ($user->logo_path || $user->logo_thumb_path))
                    <img src="{{ $user->logoUrl() }}" alt="Logo" class="w-20 h-20 object-contain" />
                @else
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                @endif
            </a>
        </div>
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg guest-card">
            {{ $slot }}
        </div>
    </div>
    @if(app()->environment('local') && isset($user))
        <div class="fixed left-4 bottom-4 z-50 text-xs px-3 py-2 rounded bg-black/70 text-white">
            font={{ $user->portfolio_font ?? 'system' }} theme={{ $user->portfolio_theme ?? 'auto' }} accent={{ $user->portfolio_accent_color ?? '#6366F1' }} override={{ $user->isFeatureOverrideActive() ? 'yes':'no' }} customizations={{ $user->canUseCustomizations() ? 'on':'off' }}
        </div>
    @endif
</body>
</html>
