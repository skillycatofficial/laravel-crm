<x-admin::layouts>
    <x-slot:title>
        Connect Mobile Device
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Page Title -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Connect Mobile Device
            </h1>
        </div>

        <!-- Instructions Card -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start gap-4">
                <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900">
                    <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="mb-2 text-lg font-semibold text-gray-800 dark:text-white">
                        How to Connect
                    </h2>
                    <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-400">
                        <li>Click "Generate QR Code" button below</li>
                        <li>Open CRM mobile app on your device</li>
                        <li>Tap "Scan QR Code" on login screen</li>
                        <li>Point your camera at the QR code</li>
                        <li>You'll be automatically logged in!</li>
                    </ol>
                    <p class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-500">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        QR codes expire after 5 minutes for security.
                    </p>
                </div>
            </div>
        </div>

        <!-- QR Code Generator Card -->
        <div id="qr-container" class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <!-- Initial State: Show Generate Button -->
            <div id="initial-state" class="flex flex-col items-center gap-4 py-8">
                <div class="rounded-lg bg-gray-100 p-6 dark:bg-gray-800">
                    <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-.01M12 16v1m0 0h.01M12 17h4.01M17 21v-7a2 2 0 00-2-2h-2a2 2 0 00-2 2v7m0 0h6m-6 0H7m0 0v-7a2 2 0 012-2h2a2 2 0 012 2v7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Ready to Connect?
                </h3>
                <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                    Generate a QR code to quickly connect your mobile device
                </p>
                <button
                    id="generate-btn"
                    onclick="generateQRCode()"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition-all hover:bg-blue-700 disabled:opacity-50"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-.01M12 16v1m0 0h.01M12 17h4.01M17 21v-7a2 2 0 00-2-2h-2a2 2 0 00-2 2v7m0 0h6m-6 0H7m0 0v-7a2 2 0 012-2h2a2 2 0 012 2v7"></path>
                    </svg>
                    <span id="btn-text">Generate QR Code</span>
                </button>
            </div>

            <!-- QR Code Display State (Hidden Initially) -->
            <div id="qr-display" class="hidden flex-col items-center gap-4 py-8">
                <div class="rounded-lg bg-white p-4 shadow-lg dark:bg-gray-800">
                    <img id="qr-image" src="" alt="QR Code" class="h-64 w-64 object-contain">
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    QR code will expire in 5 minutes
                </p>
                <div class="flex gap-3">
                    <button
                        onclick="generateQRCode()"
                        class="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition-all hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Generate New
                    </button>
                    <button
                        onclick="resetQRCode()"
                        class="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition-all hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Close
                    </button>
                </div>
            </div>

            <!-- Error Message (Hidden Initially) -->
            <div id="error-message" class="hidden mt-4 rounded-lg bg-red-100 p-4 dark:bg-red-900">
                <div class="flex items-center gap-3">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <p id="error-text" class="text-sm text-red-800 dark:text-red-200"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let expiryTimeout = null;

        async function generateQRCode() {
            const generateBtn = document.getElementById('generate-btn');
            const btnText = document.getElementById('btn-text');
            const initialState = document.getElementById('initial-state');
            const qrDisplay = document.getElementById('qr-display');
            const errorMessage = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            
            // Clear any existing timeout
            if (expiryTimeout) {
                clearTimeout(expiryTimeout);
                expiryTimeout = null;
            }
            
            // Show loading state
            generateBtn.disabled = true;
            btnText.textContent = 'Generating...';
            errorMessage.classList.add('hidden');

            try {
                const response = await fetch('{{ route("admin.settings.connect_device.generate_qr") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    // Hide initial state, show QR display
                    initialState.classList.add('hidden');
                    qrDisplay.classList.remove('hidden');
                    qrDisplay.classList.add('flex');

                    // Set QR image
                    document.getElementById('qr-image').src = data.qr_code_image;

                    // Auto-hide after 5 minutes (300000 milliseconds)
                    expiryTimeout = setTimeout(() => {
                        resetQRCode();
                    }, 300000); // 5 minutes
                } else {
                    throw new Error(data.message || 'Failed to generate QR code');
                }
            } catch (error) {
                errorText.textContent = error.message || 'An error occurred while generating QR code';
                errorMessage.classList.remove('hidden');
            } finally {
                generateBtn.disabled = false;
                btnText.textContent = 'Generate QR Code';
            }
        }

        function resetQRCode() {
            // Clear timeout if exists
            if (expiryTimeout) {
                clearTimeout(expiryTimeout);
                expiryTimeout = null;
            }

            // Reset UI
            document.getElementById('initial-state').classList.remove('hidden');
            document.getElementById('qr-display').classList.add('hidden');
            document.getElementById('qr-display').classList.remove('flex');
            document.getElementById('error-message').classList.add('hidden');
        }
    </script>
</x-admin::layouts>
