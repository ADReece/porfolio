<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ isset($user) ? ($user->name ?? $user->username).' | '.config('app.name') : config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700|inter:400,500,600,700|roboto:400,500,700|open-sans:400,600,700|lato:400,700|montserrat:400,500,600,700|playfair-display:400,700|merriweather:400,700" rel="stylesheet">

    <!-- App Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $favicon = asset('favicon.ico');
        $apply = isset($user) && method_exists($user,'canUseCustomizations') && $user->canUseCustomizations();
        if(isset($user) && ($user->logo_path || $user->logo_thumb_path)) {
            $logoUrl = $user->logoUrl();
            if($logoUrl) { $favicon = $logoUrl; }
        }

        if($apply) {
            $fontKey = $user->portfolio_font ?? 'system';
            $theme   = $user->portfolio_theme ?? 'auto';
            $accent  = $user->portfolio_accent_color ?? '#6366F1';
            $bg      = $user->portfolio_background_color ?: null;
            $text    = $user->portfolio_text_color ?: null;
            $heading = $user->portfolio_heading_color ?: null;
            $fontMap = [
                'system' => 'system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif',
                'nunito' => '"Nunito",sans-serif',
                'inter' => '"Inter",sans-serif',
                'roboto' => '"Roboto",sans-serif',
                'open-sans' => '"Open Sans",sans-serif',
                'lato' => '"Lato",sans-serif',
                'montserrat' => '"Montserrat",sans-serif',
                'playfair' => '"Playfair Display",serif',
                'merriweather' => '"Merriweather",serif',
            ];
            $fontFamily = $fontMap[$fontKey] ?? $fontMap['system'];
            // Defaults
            $lightBg = $bg ?: '#F3F4F6';
            $lightText = $text ?: '#1F2937';
            $lightHeading = $heading ?: '#111827';
            $darkBg = $bg ?: '#111827';
            $darkText = $text ?: '#F3F4F6';
            $darkHeading = $heading ?: '#F9FAFB';
        }
    @endphp

    <link rel="icon" type="image/png" href="{{ $favicon }}" />

    @if($apply)
        <style>
            :root {
                --portfolio-font: {{ $fontFamily }};
                --portfolio-accent: {{ $accent }};
                --portfolio-bg-light: {{ $lightBg }};
                --portfolio-text-light: {{ $lightText }};
                --portfolio-heading-light: {{ $lightHeading }};
                --portfolio-bg-dark: {{ $darkBg }};
                --portfolio-text-dark: {{ $darkText }};
                --portfolio-heading-dark: {{ $darkHeading }};
            }
            body, body * { font-family: var(--portfolio-font) !important; }
            @if($theme === 'light')
            html { color-scheme: light; }
            body { background: var(--portfolio-bg-light) !important; color: var(--portfolio-text-light) !important; }
            h1,h2,h3,h4,h5,h6 { color: var(--portfolio-heading-light) !important; }
            @elseif($theme === 'dark')
            html { color-scheme: dark; }
            body { background: var(--portfolio-bg-dark) !important; color: var(--portfolio-text-dark) !important; }
            h1,h2,h3,h4,h5,h6 { color: var(--portfolio-heading-dark) !important; }
            @else
            @media (prefers-color-scheme: light){body{background:var(--portfolio-bg-light)!important;color:var(--portfolio-text-light)!important;}h1,h2,h3,h4,h5,h6{color:var(--portfolio-heading-light)!important;}}
            @media (prefers-color-scheme: dark){body{background:var(--portfolio-bg-dark)!important;color:var(--portfolio-text-dark)!important;}h1,h2,h3,h4,h5,h6{color:var(--portfolio-heading-dark)!important;}}
            @endif
            .guest-card .accent-bg, .guest-card button, .guest-card a.button { background: var(--portfolio-accent); color:#fff; }
            .guest-card a.link, .guest-card a.accent { color: var(--portfolio-accent)!important; }
        </style>
    @endif
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div>
            <a href="/">
                @if(isset($user) && ($user->logo_path || $user->logo_thumb_path) && isset($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Logo" class="w-20 h-20 object-contain" />
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
        <div class="fixed left-4 bottom-4 z-50 text-[10px] px-2 py-1 rounded bg-black/70 text-white shadow">
            font={{ $fontKey ?? 'system' }} theme={{ $theme ?? 'auto' }} accent={{ $accent ?? '#6366F1' }} override={{ $user->isFeatureOverrideActive() ? 'yes':'no' }} customizations={{ $apply ? 'on':'off' }}
        </div>
    @endif
</body>
</html>
