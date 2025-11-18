<x-guest-layout :user="$user ?? null">
    @isset($user)
        <h1 class="text-center text-2xl font-semibold mb-4">{{ $user->name ?? $user->username }}</h1>
    @endisset
    <x-input-label>
        Password
    </x-input-label>
    <form method="GET" class="space-y-4">
        <x-text-input id="password" class="block mt-1 w-full"
                      type="password"
                      name="password"
                      required autocomplete="current-password" />

        <x-input-error :messages="$errors->get('password')" class="mt-2" />
        <x-primary-button>{{ __('Enter') }}</x-primary-button>
    </form>
</x-guest-layout>