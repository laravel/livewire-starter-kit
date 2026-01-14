<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['signature']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['signature']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
    <div class="flex-shrink-0">
        <img src="<?php echo e($signature->signature_url); ?>" alt="Firma de <?php echo e($signature->user->name); ?>" 
            class="h-20 w-32 object-contain border border-gray-300 dark:border-gray-600 rounded bg-white">
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900 dark:text-white">
            <?php echo e($signature->user->name); ?>

        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Firmado el <?php echo e($signature->signed_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY [a las] HH:mm')); ?>

        </p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($signature->ip_address): ?>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                IP: <?php echo e($signature->ip_address); ?>

            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <div class="flex-shrink-0">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Firmado
        </span>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views\components\signature-display.blade.php ENDPATH**/ ?>