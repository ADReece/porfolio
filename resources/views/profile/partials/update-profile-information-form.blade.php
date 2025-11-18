<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700" :value="old('username', $user->username)" readonly />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Username cannot be changed.</p>
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <div id="bio-editor" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" style="min-height: 200px;"></div>
            <input type="hidden" name="bio" id="bio" value="{{ old('bio', $user->bio) }}">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Use the toolbar to format your bio. You can add bold, italic, lists, and links.
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        @push('styles')
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        @endpush

        @push('scripts')
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    // Check if Quill editor container exists
                    var editorContainer = document.getElementById('bio-editor');
                    var bioInput = document.getElementById('bio');

                    if (!editorContainer || !bioInput) {
                        console.warn('Quill editor elements not found');
                        return;
                    }

                    // Initialize Quill
                    var quill = new Quill('#bio-editor', {
                        theme: 'snow',
                        placeholder: 'Tell your visitors about yourself...',
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['link'],
                                ['clean']
                            ]
                        }
                    });

                    // Set initial content
                    if (bioInput.value) {
                        quill.root.innerHTML = bioInput.value;
                    }

                    // Update hidden input on text change
                    quill.on('text-change', function() {
                        bioInput.value = quill.root.innerHTML;
                    });

                    // Update hidden input before form submission
                    var form = document.querySelector('form[action="{{ route('profile.update') }}"]');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            bioInput.value = quill.root.innerHTML;
                        });
                    }
                } catch (error) {
                    console.error('Error initializing Quill editor:', error);
                    // Fallback: show the hidden input as a textarea if Quill fails
                    var bioInput = document.getElementById('bio');
                    if (bioInput) {
                        bioInput.type = 'text';
                        bioInput.classList.remove('hidden');
                        bioInput.style.display = 'block';
                    }
                }
            });
        </script>
        @endpush

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700" :value="old('email', $user->email)" readonly />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Email cannot be changed. Contact support if you need to update your email.</p>
        </div>


        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
