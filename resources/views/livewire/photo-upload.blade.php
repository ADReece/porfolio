<div>
    @if($setId)
        <div wire:ignore>
            <form action="{{ route('upload-files') }}" method="post" enctype="multipart/form-data" id="file-upload-{{ $setId }}" class="dropzone border-dashed border-2 border-gray-300 rounded-lg p-6 text-center" style="min-height: 200px;">
                @csrf
                <input type="hidden" name="set_id" value="{{ $setId }}">
                <div class="dz-message" data-dz-message>
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Drop photos here or click to browse</p>
                    <p class="text-xs text-gray-500 mt-1">Maximum file size: 100MB</p>
                </div>
            </form>
        </div>

        @if($uploadCount > 0)
            <div class="mt-3 text-sm text-green-600">
                {{ $uploadCount }} {{ Str::plural('file', $uploadCount) }} uploaded successfully
            </div>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const dropzoneId = "#file-upload-{{ $setId }}";
                const dropzoneElement = document.querySelector(dropzoneId);

                // Guard: Don't initialize if already has a dropzone instance
                if (!dropzoneElement) {
                    console.error('Dropzone element not found:', dropzoneId);
                    return;
                }

                if (dropzoneElement.dropzone) {
                    console.log('Dropzone already initialized for this element');
                    return;
                }

                // Ensure Dropzone is available
                if (typeof Dropzone === 'undefined') {
                    console.error('Dropzone library not loaded');
                    return;
                }

                // Create a new Dropzone instance
                const myDropzone = new Dropzone(dropzoneId, {
                    url: '{{ route("upload-files") }}',
                    autoProcessQueue: true,
                    addRemoveLinks: true,
                    dictRemoveFile: "Remove",
                    maxFiles: 100,
                    maxFilesize: 100, // in MB
                    acceptedFiles: 'image/*',
                    parallelUploads: 3,
                    timeout: 300000, // 5 minutes
                    params: {
                        set_id: '{{ $setId }}',
                        _token: '{{ csrf_token() }}'
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                // Handle file added
                myDropzone.on("addedfile", function(file) {
                    console.log("File added:", file.name);
                });

                // Automatically process the uploaded files after the upload is complete
                myDropzone.on("success", function(file, response) {
                    console.log('File uploaded successfully:', file.name, response);

                    // Update Livewire component
                    if (typeof @this !== 'undefined') {
                        @this.call('handleFileUploaded');
                    } else {
                        console.warn('Livewire component not available');
                    }
                });

                // Handle completion of all uploads
                myDropzone.on("queuecomplete", function() {
                    console.log("All files in queue processed");
                });

                // Catch any errors
                myDropzone.on("error", function(file, errorMessage, xhr) {
                    console.error("Error uploading file:", errorMessage);

                    let message = 'Upload failed: ';
                    if (typeof errorMessage === 'string') {
                        message += errorMessage;
                    } else if (errorMessage && errorMessage.message) {
                        message += errorMessage.message;
                    } else if (errorMessage && errorMessage.error) {
                        message += errorMessage.error;
                    } else {
                        message += 'Unknown error';
                    }

                    window.showErrorToast(message);
                });

                console.log('Dropzone initialized successfully for set {{ $setId }}');
            });
        </script>
    @else
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
            <p class="text-gray-500">Please create a set first before uploading photos.</p>
        </div>
    @endif
</div>
