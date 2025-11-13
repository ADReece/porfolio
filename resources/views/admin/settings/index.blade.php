<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Currency Settings -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Currency Settings</h3>
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Default Currency
                                </label>
                                <select name="settings[default_currency]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="GBP" {{ optional($settings->get('currency')->firstWhere('key', 'default_currency'))->value === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                    <option value="USD" {{ optional($settings->get('currency')->firstWhere('key', 'default_currency'))->value === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="EUR" {{ optional($settings->get('currency')->firstWhere('key', 'default_currency'))->value === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                    <option value="AUD" {{ optional($settings->get('currency')->firstWhere('key', 'default_currency'))->value === 'AUD' ? 'selected' : '' }}>AUD ($)</option>
                                    <option value="CAD" {{ optional($settings->get('currency')->firstWhere('key', 'default_currency'))->value === 'CAD' ? 'selected' : '' }}>CAD ($)</option>
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This currency will be used for all pricing</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Currency Symbol
                                </label>
                                <input type="text" name="settings[currency_symbol]"
                                       value="{{ optional($settings->get('currency')->firstWhere('key', 'currency_symbol'))->value ?? '£' }}"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                       maxlength="5">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Symbol displayed before prices</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Save Currency Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stripe Configuration -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Stripe Product & Price Configuration</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Configure your Stripe Product IDs and Price IDs for each subscription plan.
                        Get these from your <a href="https://dashboard.stripe.com/test/products" target="_blank" class="text-indigo-600 hover:underline">Stripe Dashboard</a>.
                    </p>

                    <form action="{{ route('admin.settings.plans.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        @foreach($plans as $index => $plan)
                            @if(!$plan->isFree())
                            <div class="mb-8 pb-8 {{ !$loop->last ? 'border-b border-gray-200 dark:border-gray-700' : '' }}">
                                <input type="hidden" name="plans[{{ $index }}][id]" value="{{ $plan->id }}">

                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                    {{ $plan->name }} Plan - £{{ number_format($plan->price, 2) }}/month
                                </h4>

                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Stripe Product ID
                                        </label>
                                        <input type="text"
                                               name="plans[{{ $index }}][stripe_product_id]"
                                               value="{{ $plan->stripe_product_id }}"
                                               placeholder="prod_xxxxxxxxxxxxx"
                                               class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 font-mono text-sm">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Found in Stripe Dashboard → Products</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Monthly Price ID (£{{ number_format($plan->price, 2) }}/month)
                                        </label>
                                        <input type="text"
                                               name="plans[{{ $index }}][stripe_price_id]"
                                               value="{{ $plan->stripe_price_id }}"
                                               placeholder="price_xxxxxxxxxxxxx"
                                               class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 font-mono text-sm">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Recurring monthly price</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Annual Price ID (£{{ number_format($plan->annual_price, 2) }}/year)
                                        </label>
                                        <input type="text"
                                               name="plans[{{ $index }}][annual_stripe_price_id]"
                                               value="{{ $plan->annual_stripe_price_id }}"
                                               placeholder="price_xxxxxxxxxxxxx"
                                               class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 font-mono text-sm">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Recurring yearly price (Save {{ $plan->annual_discount_percent }}%)</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach

                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Save Stripe Configuration
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                        <h5 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">💡 How to get Stripe IDs:</h5>
                        <ol class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-decimal list-inside">
                            <li>Go to <a href="https://dashboard.stripe.com/test/products" target="_blank" class="underline">Stripe Dashboard → Products</a></li>
                            <li>Create a new product for each plan (Photographer, Videographer)</li>
                            <li>Add two prices to each product: monthly and yearly recurring</li>
                            <li>Copy the Product ID (prod_xxx) and Price IDs (price_xxx) here</li>
                            <li>Make sure to create prices in {{ optional($settings->get('currency')->firstWhere('key', 'default_currency'))->value ?? 'GBP' }}</li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

