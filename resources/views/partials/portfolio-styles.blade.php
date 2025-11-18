@php
    /** @var \App\Models\User|null $user */
    $can = isset($user) && method_exists($user,'canUseCustomizations') && $user->canUseCustomizations();
@endphp
@if($can)
    @php
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
        $lightBg = $bg ?: '#F3F4F6';
        $lightText = $text ?: '#1F2937';
        $lightHeading = $heading ?: '#111827';
        $darkBg = $bg ?: '#111827';
        $darkText = $text ?: '#F3F4F6';
        $darkHeading = $heading ?: '#F9FAFB';
    @endphp
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
    </style>
@endif

