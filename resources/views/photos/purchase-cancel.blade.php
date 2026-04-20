<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 py-14">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Checkout canceled</h1>
            <p class="text-gray-600 dark:text-gray-300">No payment was captured. You can return and try again any time.</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">Order reference: {{ $order->id }}</p>
        </div>
    </div>
</x-app-layout>
