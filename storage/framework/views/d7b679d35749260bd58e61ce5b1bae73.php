<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $message;

    public $type;

    public $visible;

    public function mount()
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function showToast($message, $type = 'info')
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('showToast'))->execute(...$arguments);
    }

    public function hideToast()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('hideToast'))->execute(...$arguments);
    }

};