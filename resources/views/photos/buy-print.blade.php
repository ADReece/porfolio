<x-profile-layout :user="$photographer">
    <div class="max-w-3xl mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Buy Print</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-8">You're purchasing a print from @{{ $photographer->username }}.</p>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <img src="{{ $photo->getAwsThumbnail() }}" alt="Selected photo" class="rounded-lg w-full max-h-96 object-contain bg-gray-100 dark:bg-gray-900" />
            @if($photo->caption)
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ $photo->caption }}</p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form method="POST" action="{{ route('print-purchase.store', ['photo' => $photo->id]) }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Print product</label>
                    <select name="prodigi_product_id" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select a print product</option>
                        @foreach($enabledProducts as $product)
                            <option value="{{ $product->id }}" @selected(old('prodigi_product_id') == $product->id)>
                                {{ $product->name }} - ${{ number_format((float) ($product->pivot->retail_price ?? 29.99), 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('prodigi_product_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Quantity</label>
                    <input type="number" min="1" max="10" name="quantity" value="{{ old('quantity', 1) }}" class="w-32 rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
                    @error('quantity')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Email</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
                        @error('customer_email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Full name</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('customer_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Phone number</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
                    @error('customer_phone')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Address line 1</label>
                        <input type="text" name="shipping_address_line1" value="{{ old('shipping_address_line1') }}" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
                        @error('shipping_address_line1')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Address line 2 (optional)</label>
                        <input type="text" name="shipping_address_line2" value="{{ old('shipping_address_line2') }}" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('shipping_address_line2')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">City</label>
                        <input type="text" name="shipping_city" value="{{ old('shipping_city') }}" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
                        @error('shipping_city')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">State / County</label>
                        <input type="text" name="shipping_county" value="{{ old('shipping_county') }}" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
                        @error('shipping_county')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Postcode</label>
                        <input type="text" name="shipping_postcode" value="{{ old('shipping_postcode') }}" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
                        @error('shipping_postcode')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Country code</label>
                        <input type="text" name="shipping_country_code" value="{{ old('shipping_country_code', 'US') }}" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 uppercase" maxlength="2" required />
                        @error('shipping_country_code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-5 py-2.5 rounded bg-indigo-600 text-white hover:bg-indigo-700">Continue to secure checkout</button>
                    <a href="{{ url()->previous() }}" class="text-sm text-gray-600 dark:text-gray-300 underline">Back</a>
                </div>
            </form>
        </div>
    </div>
</x-profile-layout>
