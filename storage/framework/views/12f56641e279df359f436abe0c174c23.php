<div class="p-6">
    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Wizard de Capacidad</h1>
        <p class="text-gray-600 dark:text-gray-400">Calcula la capacidad de producción en 3 pasos</p>
    </div>

    
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [1 => 'Disponibilidad', 2 => 'Cálculo', 3 => 'Cierre']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center">
                    <button 
                        wire:click="goToStep(<?php echo e($step); ?>)"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'flex items-center justify-center w-10 h-10 rounded-full border-2 font-semibold transition',
                            'bg-blue-600 border-blue-600 text-white' => $currentStep >= $step,
                            'border-gray-300 text-gray-400 dark:border-gray-600' => $currentStep < $step,
                            'cursor-pointer hover:bg-blue-700' => $currentStep >= $step,
                            'cursor-not-allowed' => $currentStep < $step,
                        ]); ?>"
                        <?php if($currentStep < $step): echo 'disabled'; endif; ?>
                    >
                        <?php echo e($step); ?>

                    </button>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'ml-2 text-sm font-medium',
                        'text-blue-600 dark:text-blue-400' => $currentStep >= $step,
                        'text-gray-400' => $currentStep < $step,
                    ]); ?>"><?php echo e($label); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step < 3): ?>
                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'w-16 h-1 mx-4 rounded',
                        'bg-blue-600' => $currentStep > $step,
                        'bg-gray-200 dark:bg-gray-700' => $currentStep <= $step,
                    ]); ?>"></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMessage): ?>
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 p-4">
            <p class="text-sm text-red-700 dark:text-red-300"><?php echo e($errorMessage); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($successMessage): ?>
        <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 p-4">
            <p class="text-sm text-green-700 dark:text-green-300"><?php echo e($successMessage); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($warnings)): ?>
        <div class="mb-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-medium text-yellow-800 dark:text-yellow-200 mb-1">Advertencias de Capacidad</p>
                    <ul class="list-disc list-inside space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $warnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="text-sm text-yellow-700 dark:text-yellow-300"><?php echo e($warning); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentStep === 1): ?>
            <?php echo $__env->make('livewire.admin.capacity-wizard.step1', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($currentStep === 2): ?>
            <?php echo $__env->make('livewire.admin.capacity-wizard.step2', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($currentStep === 3): ?>
            <?php echo $__env->make('livewire.admin.capacity-wizard.step3', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\resources\views/livewire/admin/capacity-wizard.blade.php ENDPATH**/ ?>