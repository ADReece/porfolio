<x-app-layout>
    @push('styles')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700|inter:400,500,600,700|roboto:400,500,700|open-sans:400,600,700|lato:400,700|montserrat:400,500,600,700|playfair-display:400,700|merriweather:400,700" rel="stylesheet">
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Customize Portfolio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Info Banner -->
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Preview your changes:</strong> After saving, visit your public portfolio at
                            <a href="{{ route('profile.view', ['username' => $user->username]) }}" target="_blank" class="font-semibold underline hover:text-blue-600">
                                {{ _('/@' . $user->username) }}
                            </a>
                            to see your customizations applied.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <!-- Preview Section -->
                    <div class="mb-8 p-6 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600" id="preview-area">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                            🎨 Live Preview
                        </h3>
                        <div id="portfolio-preview" class="rounded-lg overflow-hidden" style="min-height: 300px;">
                            <div class="p-8 text-center">
                                <h1 class="text-4xl font-bold mb-2">{{ $user->name ?? $user->username }}</h1>
                                <p class="text-lg mb-4">{{ _('@' . $user->username) }}</p>
                                @if($user->bio)
                                    <p class="text-base mb-6">{{ $user->bio }}</p>
                                @endif
                                <button class="px-6 py-3 rounded-lg font-semibold">Sample Button</button>
                                <div class="mt-6">
                                    <a href="#" class="underline">Sample Link</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customization Form -->
                    @include('profile.partials.customize-portfolio-form')

                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="mr-1">{{ __('Your public URL:') }}</span>
                        <code class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100">
                            {{ _('/@' . $user->username) }}
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Live preview updates
        document.addEventListener('DOMContentLoaded', function() {
            const preview = document.getElementById('portfolio-preview');
            const form = document.getElementById('customize-form');

            function updatePreview() {
                if (!form) return;

                const formData = new FormData(form);
                const font = formData.get('portfolio_font') || 'system';
                const theme = formData.get('portfolio_theme') || 'auto';
                const accentColor = formData.get('portfolio_accent_color') || '#6366F1';
                const backgroundColor = formData.get('portfolio_background_color') || '';
                const textColor = formData.get('portfolio_text_color') || '';
                const headingColor = formData.get('portfolio_heading_color') || '';

                console.log('Updating preview with:', { font, theme, accentColor, backgroundColor, textColor, headingColor });

                // Apply font
                const fontMap = {
                    'system': 'system-ui, -apple-system, sans-serif',
                    'nunito': "'Nunito', sans-serif",
                    'inter': "'Inter', sans-serif",
                    'playfair': "'Playfair Display', serif",
                    'roboto': "'Roboto', sans-serif",
                    'open-sans': "'Open Sans', sans-serif",
                    'merriweather': "'Merriweather', serif",
                    'lato': "'Lato', sans-serif",
                    'montserrat': "'Montserrat', sans-serif",
                };
                preview.style.fontFamily = fontMap[font] || fontMap['system'];

                // Apply theme colors
                if (theme === 'dark') {
                    preview.style.backgroundColor = backgroundColor || '#111827';
                    preview.style.color = textColor || '#F3F4F6';
                } else if (theme === 'light') {
                    preview.style.backgroundColor = backgroundColor || '#FFFFFF';
                    preview.style.color = textColor || '#1F2937';
                } else {
                    // Auto theme - use system preference
                    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        preview.style.backgroundColor = backgroundColor || '#111827';
                        preview.style.color = textColor || '#F3F4F6';
                    } else {
                        preview.style.backgroundColor = backgroundColor || '#FFFFFF';
                        preview.style.color = textColor || '#1F2937';
                    }
                }

                // Apply accent color to button
                const button = preview.querySelector('button');
                if (button) {
                    button.style.backgroundColor = accentColor;
                    button.style.color = '#FFFFFF';
                }

                // Apply accent color to links
                const links = preview.querySelectorAll('a');
                links.forEach(link => {
                    link.style.color = accentColor;
                });

                // Apply heading color
                const headings = preview.querySelectorAll('h1, h2, h3');
                headings.forEach(heading => {
                    if (headingColor) {
                        heading.style.color = headingColor;
                    } else {
                        heading.style.color = (theme === 'dark' ? '#F9FAFB' : '#111827');
                    }
                });
            }

            // Listen for form changes
            if (form) {
                form.addEventListener('input', updatePreview);
                form.addEventListener('change', updatePreview);

                // Initial preview with delay to ensure form is fully loaded
                setTimeout(updatePreview, 100);
            }
        });
    </script>
    @endpush
</x-app-layout>
