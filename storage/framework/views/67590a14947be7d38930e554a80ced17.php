

<?php
extract(Flux::forwardedAttributes($attributes, [
    'name',
    'multiple',
    'size',
]));
?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'multiple' => null,
    'size' => null,
]));

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

foreach (array_filter(([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'multiple' => null,
    'size' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$classes = Flux::classes()
    ->add('w-full flex items-center gap-4')
    ->add('[[data-flux-input-group]_&]:items-stretch [[data-flux-input-group]_&]:gap-0')

    // NOTE: We need to add relative positioning here to prevent odd overflow behaviors because of
    // "sr-only": https://github.com/tailwindlabs/tailwindcss/discussions/12429
    ->add('relative')
    ;

[ $styleAttributes, $attributes ] = Flux::splitAttributes($attributes);
?>

<div
    <?php echo e($styleAttributes->class($classes)); ?>

    data-flux-input-file
    wire:ignore
    tabindex="0"
    x-data 
    x-on:click.prevent.stop="$refs.input.click()"
    x-on:keydown.enter.prevent.stop="$refs.input.click()"
    x-on:keydown.space.prevent.stop
    x-on:keyup.space.prevent.stop="$refs.input.click()"
    x-on:change="$refs.name.textContent = $event.target.files[1] ? ($event.target.files.length + ' <?php echo __('files'); ?>') : ($event.target.files[0]?.name || '<?php echo __('No file chosen'); ?>')"
>
    <input
        x-ref="input"
        x-on:click.stop 
        
        
        x-init="Object.defineProperty($el, 'value', {
          ...Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value'),
            set(value) {
            Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set.call(this, value);

            if(! value) this.dispatchEvent(new Event('change', { bubbles: true }))
          }
        })"
        type="file"
        class="sr-only"
        tabindex="-1"
        <?php echo e($attributes); ?> <?php echo e($multiple ? 'multiple' : ''); ?> <?php if($name): ?>name="<?php echo e($name); ?>"<?php endif; ?>
    >

    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['as' => 'div','class' => 'cursor-pointer','size' => $size,'ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'div','class' => 'cursor-pointer','size' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($size),'aria-hidden' => 'true']); ?>
        <?php if ($multiple) : ?>
            <?php echo __('Choose files'); ?>

        <?php else : ?>
            <?php echo __('Choose file'); ?>

        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>

    <div
        x-ref="name"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'cursor-default select-none truncate whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400 font-medium',
            '[[data-flux-input-group]_&]:flex-1 [[data-flux-input-group]_&]:border-e [[data-flux-input-group]_&]:border-y [[data-flux-input-group]_&]:shadow-xs [[data-flux-input-group]_&]:border-zinc-200 dark:[[data-flux-input-group]_&]:border-zinc-600 [[data-flux-input-group]_&]:px-4 [[data-flux-input-group]_&]:bg-white dark:[[data-flux-input-group]_&]:bg-zinc-700 [[data-flux-input-group]_&]:flex [[data-flux-input-group]_&]:items-center dark:[[data-flux-input-group]_&]:text-zinc-300',
        ]); ?>"
        aria-hidden="true"
    >
        <?php echo __('No file chosen'); ?>

    </div>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\input\file.blade.php ENDPATH**/ ?>