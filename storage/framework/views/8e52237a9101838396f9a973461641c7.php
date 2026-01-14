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

    
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentStep === 1): ?>
            <?php echo $__env->make('livewire.admin.capacity-wizard.step1', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($currentStep === 2): ?>
            <?php echo $__env->make('livewire.admin.capacity-wizard.step2', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($currentStep === 3): ?>
            <?php echo $__env->make('livewire.admin.capacity-wizard.step3', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views\livewire\admin\capacity-wizard.blade.php ENDPATH**/ ?>