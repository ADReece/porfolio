<x-guest-layout>
    <x-input-label>
        Password
    </x-input-label>
    <form method="GET">
        <x-text-input id="password" class="block mt-1 w-full"
                      type="password"
                      name="password"
                      required autocomplete="current-password" />

        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </form>
</x-guest-layout>