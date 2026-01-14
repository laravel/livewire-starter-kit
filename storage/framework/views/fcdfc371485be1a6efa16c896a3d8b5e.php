<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModal"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modal-title">
                            Firmar Documento
                        </h3>
                        <button wire:click="closeModal" type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Saved Signature Option -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($savedSignatureUrl): ?>
                    <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    Tienes una firma guardada. Puedes usarla o dibujar una nueva.
                                </p>
                                <div class="mt-2">
                                    <img src="<?php echo e($savedSignatureUrl); ?>" alt="Firma guardada" class="h-16 border border-gray-300 dark:border-gray-600 rounded bg-white">
                                </div>
                                <button wire:click="useSaved" type="button" 
                                    class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-200 dark:hover:bg-blue-700">
                                    Usar esta firma
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- Signature Canvas -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Dibuja tu firma
                        </label>
                        <div class="border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white" wire:ignore>
                            <canvas id="signature-canvas" class="w-full" style="touch-action: none;"></canvas>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Dibuja tu firma usando el mouse o pantalla táctil
                        </p>
                    </div>

                    <!-- Save/Update signature checkbox -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$useSavedSignature): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$savedSignatureUrl): ?>
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input wire:model="saveForFuture" type="checkbox" 
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Guardar esta firma para uso futuro
                                </span>
                            </label>
                        </div>
                        <?php else: ?>
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input wire:model="updateSavedSignature" type="checkbox" 
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Actualizar mi firma guardada con esta nueva firma
                                </span>
                            </label>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($useSavedSignature): ?>
                    <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <p class="text-sm text-green-700 dark:text-green-300">
                            ✓ Usando firma guardada
                        </p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="confirmSignature" type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Confirmar Firma
                    </button>
                    <button wire:click="clearSignature" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Limpiar
                    </button>
                    <button wire:click="closeModal" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>

    <?php
        $__scriptKey = '3369681931-1';
        ob_start();
    ?>
<script>
let signaturePad = null;
let signatureData = null;

$wire.on('signature-modal-opened', () => {
    setTimeout(() => {
        const canvas = document.getElementById('signature-canvas');
        if (!canvas) {
            console.error('Canvas element not found');
            return;
        }
        
        // Set canvas dimensions
        const container = canvas.parentElement;
        canvas.width = container.offsetWidth;
        canvas.height = 200;
        
        // Check if SignaturePad is available
        if (typeof SignaturePad === 'undefined') {
            console.error('SignaturePad library not loaded');
            return;
        }
        
        // Initialize SignaturePad
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 1,
            maxWidth: 2.5
        });
        
        // Store signature data locally (don't update Livewire on every stroke)
        signaturePad.addEventListener('endStroke', () => {
            if (!signaturePad.isEmpty()) {
                signatureData = signaturePad.toDataURL('image/png');
            }
        });
        
        console.log('SignaturePad initialized successfully');
    }, 200);
});

$wire.on('clear-signature-pad', () => {
    if (signaturePad) {
        signaturePad.clear();
        signatureData = null;
    }
});

$wire.on('use-saved-signature', () => {
    if (signaturePad) {
        signaturePad.clear();
        signatureData = null;
    }
});

// Intercept confirm button to send signature data
document.addEventListener('click', (e) => {
    if (e.target.closest('[wire\\:click="confirmSignature"]')) {
        if (signatureData) {
            $wire.set('signatureData', signatureData, false);
        }
    }
});
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?><?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views\livewire\admin\signature-modal.blade.php ENDPATH**/ ?>