<div>
    @if($setId)
        <div wire:ignore>
            <div class="border-dashed border-2 border-gray-300 rounded-lg p-6 text-center bg-white" id="file-upload-wrapper-{{ $setId }}">
                @csrf
                <input type="file" id="file-upload-{{ $setId }}" name="file" multiple accept="image/*">
                <div class="mt-3 text-sm text-gray-600">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="mt-2">Drop photos here or click to browse</p>
                    <p class="text-xs text-gray-500 mt-1">Maximum file size: 100MB</p>
                </div>
            </div>
        </div>

        @if($uploadCount > 0)
            <div class="mt-3 text-sm text-green-600">
                {{ $uploadCount }} {{ Str::plural('file', $uploadCount) }} uploaded successfully
            </div>
        @endif

        <script>
            (function () {
                const inputId = 'file-upload-{{ $setId }}';

                function initializeUploader() {
                    const inputElement = document.getElementById(inputId);

                    if (!inputElement) {
                        console.error('FilePond input not found:', inputId);
                        return;
                    }

                    if (inputElement.dataset.filepondInitialized === 'true') {
                        return;
                    }

                    if (typeof window.FilePond === 'undefined') {
                        console.error('FilePond library not loaded');
                        return;
                    }

                    inputElement.dataset.filepondInitialized = 'true';

                    const pond = window.FilePond.create(inputElement, {
                        allowMultiple: true,
                        acceptedFileTypes: ['image/*'],
                        maxFiles: 100,
                        maxFileSize: '100MB',
                        credits: false,
                        labelIdle: 'Drag & Drop your photos or <span class="filepond--label-action">Browse</span>',
                        server: {
                            process: {
                                url: '{{ route("upload-files") }}',
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                ondata: (formData) => {
                                    formData.append('set_id', '{{ $setId }}');
                                    return formData;
                                },
                                onload: (responseText) => {
                                    try {
                                        const response = JSON.parse(responseText);
                                        if (typeof @this !== 'undefined') {
                                            @this.call('handleFileUploaded');
                                        }
                                        return response.fileName || responseText;
                                    } catch (error) {
                                        return responseText;
                                    }
                                },
                                onerror: (responseText) => {
                                    let message = 'Upload failed.';

                                    try {
                                        const response = JSON.parse(responseText);
                                        message = response.message || response.error || message;
                                    } catch (error) {
                                        if (responseText) {
                                            message = responseText;
                                        }
                                    }

                                    window.showErrorToast(message);
                                    return message;
                                },
                            },
                        },
                    });

                    pond.on('processfile', (error, file) => {
                        if (error) {
                            console.error('Error uploading file:', error);
                            return;
                        }

                        console.log('File uploaded successfully:', file.filename || file.file?.name);
                    });

                    pond.on('processfiles', () => {
                        console.log('All files in queue processed');
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initializeUploader, { once: true });
                } else {
                    initializeUploader();
                }

                document.addEventListener('livewire:load', initializeUploader);
                document.addEventListener('livewire:update', initializeUploader);
            })();
        </script>
    @else
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
            <p class="text-gray-500">Please create a set first before uploading photos.</p>
        </div>
    @endif
</div>
