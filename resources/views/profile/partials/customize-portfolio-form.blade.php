<form method="post" action="{{ route('profile.customize.update') }}" id="customize-form" class="space-y-8">
    @csrf
    @method('patch')

    <!-- Theme Selection -->
    <div class="border-b pb-6 dark:border-gray-700">
        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">
            🌓 Theme Settings
        </h3>

        <div>
            <x-input-label for="portfolio_theme" value="{{ __('Portfolio Theme') }}" />
            <select id="portfolio_theme" name="portfolio_theme" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                @php($themes = ['auto' => 'Auto (match visitor system)', 'light' => 'Light', 'dark' => 'Dark'])
                @foreach($themes as $value => $label)
                    <option value="{{ $value }}" {{ old('portfolio_theme', auth()->user()->portfolio_theme ?? 'auto') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Choose whether your portfolio always uses light or dark mode, or automatically matches your visitor\'s system preference.') }}
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('portfolio_theme')" />
        </div>
    </div>

    <!-- Typography -->
    <div class="border-b pb-6 dark:border-gray-700">
        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">
            ✍️ Typography
        </h3>

        <div>
            <x-input-label for="portfolio_font" value="{{ __('Font Family') }}" />
            <select id="portfolio_font" name="portfolio_font" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                @php($fonts = [
                    'system' => 'System Default',
                    'inter' => 'Inter (Modern & Clean)',
                    'nunito' => 'Nunito (Friendly & Rounded)',
                    'roboto' => 'Roboto (Professional)',
                    'open-sans' => 'Open Sans (Classic)',
                    'lato' => 'Lato (Elegant)',
                    'montserrat' => 'Montserrat (Bold & Contemporary)',
                    'playfair' => 'Playfair Display (Elegant Serif)',
                    'merriweather' => 'Merriweather (Readable Serif)',
                ])
                @foreach($fonts as $value => $label)
                    <option value="{{ $value }}" {{ old('portfolio_font', auth()->user()->portfolio_font ?? 'system') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Choose the main font for your portfolio. This affects all text on your public portfolio pages.') }}
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('portfolio_font')" />
        </div>
    </div>

    <!-- Color Customization -->
    <div class="border-b pb-6 dark:border-gray-700">
        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">
            🎨 Color Palette
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Accent Color -->
            <div>
                <x-input-label for="portfolio_accent_color" value="{{ __('Accent Color') }}" />
                <div class="flex items-center gap-3 mt-1">
                    <input type="color" id="portfolio_accent_color" name="portfolio_accent_color"
                           value="{{ old('portfolio_accent_color', auth()->user()->portfolio_accent_color ?? '#6366F1') }}"
                           class="h-12 w-20 rounded-md border border-gray-300 dark:border-gray-600 cursor-pointer">
                    <input type="text" id="portfolio_accent_color_hex"
                           value="{{ old('portfolio_accent_color', auth()->user()->portfolio_accent_color ?? '#6366F1') }}"
                           class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 uppercase"
                           pattern="^#[A-Fa-f0-9]{6}$" maxlength="7" readonly>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Used for buttons, links, and interactive elements.') }}
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('portfolio_accent_color')" />
            </div>

            <!-- Background Color -->
            <div>
                <x-input-label for="portfolio_background_color" value="{{ __('Background Color') }}" />
                <div class="flex items-center gap-3 mt-1">
                    <input type="color" id="portfolio_background_color" name="portfolio_background_color"
                           value="{{ old('portfolio_background_color', auth()->user()->portfolio_background_color ?? '') }}"
                           class="h-12 w-20 rounded-md border border-gray-300 dark:border-gray-600 cursor-pointer">
                    <input type="text" id="portfolio_background_color_hex"
                           value="{{ old('portfolio_background_color', auth()->user()->portfolio_background_color ?? '') }}"
                           placeholder="Use default"
                           class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 uppercase"
                           pattern="^#[A-Fa-f0-9]{6}$" maxlength="7">
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Main background color. Leave empty to use theme default.') }}
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('portfolio_background_color')" />
            </div>

            <!-- Text Color -->
            <div>
                <x-input-label for="portfolio_text_color" value="{{ __('Text Color') }}" />
                <div class="flex items-center gap-3 mt-1">
                    <input type="color" id="portfolio_text_color" name="portfolio_text_color"
                           value="{{ old('portfolio_text_color', auth()->user()->portfolio_text_color ?? '') }}"
                           class="h-12 w-20 rounded-md border border-gray-300 dark:border-gray-600 cursor-pointer">
                    <input type="text" id="portfolio_text_color_hex"
                           value="{{ old('portfolio_text_color', auth()->user()->portfolio_text_color ?? '') }}"
                           placeholder="Use default"
                           class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 uppercase"
                           pattern="^#[A-Fa-f0-9]{6}$" maxlength="7">
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Body text color. Leave empty to use theme default.') }}
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('portfolio_text_color')" />
            </div>

            <!-- Heading Color -->
            <div>
                <x-input-label for="portfolio_heading_color" value="{{ __('Heading Color') }}" />
                <div class="flex items-center gap-3 mt-1">
                    <input type="color" id="portfolio_heading_color" name="portfolio_heading_color"
                           value="{{ old('portfolio_heading_color', auth()->user()->portfolio_heading_color ?? '') }}"
                           class="h-12 w-20 rounded-md border border-gray-300 dark:border-gray-600 cursor-pointer">
                    <input type="text" id="portfolio_heading_color_hex"
                           value="{{ old('portfolio_heading_color', auth()->user()->portfolio_heading_color ?? '') }}"
                           placeholder="Use default"
                           class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 uppercase"
                           pattern="^#[A-Fa-f0-9]{6}$" maxlength="7">
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Color for headings (h1, h2, h3, etc.). Leave empty to use theme default.') }}
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('portfolio_heading_color')" />
            </div>
        </div>

        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-xs text-blue-800 dark:text-blue-200">
                <strong>💡 Pro Tip:</strong> Colors will override your theme defaults. For best results, test your color combinations in both light and dark themes to ensure readability.
            </p>
        </div>
    </div>

    <!-- Quick Presets -->
    <div class="border-b pb-6 dark:border-gray-700">
        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">
            🎯 Quick Presets
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <button type="button" class="preset-btn p-4 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:border-indigo-500 transition-all"
                    data-preset='{"theme":"light","accent":"#6366F1","bg":"#FFFFFF","text":"#1F2937","heading":"#111827"}'>
                <div class="w-full h-8 rounded mb-2 bg-white border"></div>
                <div class="text-xs font-medium">Classic Light</div>
            </button>

            <button type="button" class="preset-btn p-4 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:border-indigo-500 transition-all"
                    data-preset='{"theme":"dark","accent":"#818CF8","bg":"#111827","text":"#F3F4F6","heading":"#F9FAFB"}'>
                <div class="w-full h-8 rounded mb-2 bg-gray-900 border border-gray-700"></div>
                <div class="text-xs font-medium">Classic Dark</div>
            </button>

            <button type="button" class="preset-btn p-4 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:border-rose-500 transition-all"
                    data-preset='{"theme":"light","accent":"#E11D48","bg":"#FFF1F2","text":"#881337","heading":"#4C0519"}'>
                <div class="w-full h-8 rounded mb-2 bg-rose-50 border border-rose-200"></div>
                <div class="text-xs font-medium">Rose Garden</div>
            </button>

            <button type="button" class="preset-btn p-4 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:border-emerald-500 transition-all"
                    data-preset='{"theme":"dark","accent":"#10B981","bg":"#064E3B","text":"#D1FAE5","heading":"#ECFDF5"}'>
                <div class="w-full h-8 rounded mb-2 bg-emerald-900 border border-emerald-700"></div>
                <div class="text-xs font-medium">Forest Night</div>
            </button>

            <button type="button" class="preset-btn p-4 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:border-amber-500 transition-all"
                    data-preset='{"theme":"light","accent":"#F59E0B","bg":"#FFFBEB","text":"#78350F","heading":"#451A03"}'>
                <div class="w-full h-8 rounded mb-2 bg-amber-50 border border-amber-200"></div>
                <div class="text-xs font-medium">Golden Hour</div>
            </button>

            <button type="button" class="preset-btn p-4 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:border-purple-500 transition-all"
                    data-preset='{"theme":"dark","accent":"#A855F7","bg":"#1E1B4B","text":"#E9D5FF","heading":"#F3E8FF"}'>
                <div class="w-full h-8 rounded mb-2 bg-purple-950 border border-purple-800"></div>
                <div class="text-xs font-medium">Purple Dream</div>
            </button>

            <button type="button" class="preset-btn p-4 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:border-slate-500 transition-all"
                    data-preset='{"theme":"light","accent":"#0F172A","bg":"#F8FAFC","text":"#475569","heading":"#0F172A"}'>
                <div class="w-full h-8 rounded mb-2 bg-slate-50 border border-slate-200"></div>
                <div class="text-xs font-medium">Minimal Gray</div>
            </button>

            <button type="button" class="preset-btn p-4 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:border-cyan-500 transition-all"
                    data-preset='{"theme":"dark","accent":"#06B6D4","bg":"#083344","text":"#CFFAFE","heading":"#ECFEFF"}'>
                <div class="w-full h-8 rounded mb-2 bg-cyan-950 border border-cyan-800"></div>
                <div class="text-xs font-medium">Ocean Deep</div>
            </button>
        </div>
    </div>

    <!-- Reset Button -->
    <div class="flex items-center justify-between">
        <button type="button" id="reset-defaults" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline">
            Reset to Defaults
        </button>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
            @if (session('status') === 'portfolio-customized')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved!') }}</p>
            @endif
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync color inputs with hex inputs
    function syncColorInputs(colorId, hexId) {
        const colorInput = document.getElementById(colorId);
        const hexInput = document.getElementById(hexId);

        if (colorInput && hexInput) {
            colorInput.addEventListener('input', function() {
                hexInput.value = this.value.toUpperCase();
            });

            hexInput.addEventListener('input', function() {
                if (/^#[A-Fa-f0-9]{6}$/.test(this.value)) {
                    colorInput.value = this.value;
                }
            });
        }
    }

    syncColorInputs('portfolio_accent_color', 'portfolio_accent_color_hex');
    syncColorInputs('portfolio_background_color', 'portfolio_background_color_hex');
    syncColorInputs('portfolio_text_color', 'portfolio_text_color_hex');
    syncColorInputs('portfolio_heading_color', 'portfolio_heading_color_hex');

    // Preset buttons
    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const preset = JSON.parse(this.dataset.preset);

            document.getElementById('portfolio_theme').value = preset.theme;
            document.getElementById('portfolio_accent_color').value = preset.accent;
            document.getElementById('portfolio_accent_color_hex').value = preset.accent;
            document.getElementById('portfolio_background_color').value = preset.bg;
            document.getElementById('portfolio_background_color_hex').value = preset.bg;
            document.getElementById('portfolio_text_color').value = preset.text;
            document.getElementById('portfolio_text_color_hex').value = preset.text;
            document.getElementById('portfolio_heading_color').value = preset.heading;
            document.getElementById('portfolio_heading_color_hex').value = preset.heading;

            // Trigger preview update
            document.getElementById('customize-form').dispatchEvent(new Event('input'));
        });
    });

    // Reset button
    document.getElementById('reset-defaults')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to reset all customizations to default values?')) {
            document.getElementById('portfolio_theme').value = 'auto';
            document.getElementById('portfolio_font').value = 'system';
            document.getElementById('portfolio_accent_color').value = '#6366F1';
            document.getElementById('portfolio_accent_color_hex').value = '#6366F1';
            document.getElementById('portfolio_background_color').value = '';
            document.getElementById('portfolio_background_color_hex').value = '';
            document.getElementById('portfolio_text_color').value = '';
            document.getElementById('portfolio_text_color_hex').value = '';
            document.getElementById('portfolio_heading_color').value = '';
            document.getElementById('portfolio_heading_color_hex').value = '';

            // Trigger preview update
            document.getElementById('customize-form').dispatchEvent(new Event('input'));
        }
    });
});
</script>
@endpush

