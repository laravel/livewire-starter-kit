@props([
    'digits' => 6,
    'name' => 'code',
])

<div x-data="{
    totalDigits: @js($digits),
    init() {
        $nextTick(() => {
            const firstInput = this.$refs.input1;
            if (firstInput) {
                firstInput.focus();
            }
        });
    },
    get digitIndices() {
        return Array.from({ length: this.totalDigits }, (_, i) => i + 1);
    },
    getInput(index) {
        return this.$refs['input' + index];
    },
    getCode() {
        return this.digitIndices
            .map(i => this.getInput(i).value)
            .join('');
    },
    isComplete() {
        return this.digitIndices
            .every(ref => this.getInput(ref).value !== '');
    },
    updateHiddenField() {
        const code = this.getCode();
        this.$refs.code.value = code;
        this.$refs.code.dispatchEvent(new Event('input', { bubbles: true }));
        this.$refs.code.dispatchEvent(new Event('change', { bubbles: true }));
    },
    onComplete() {
        this.updateHiddenField();
    },
    handleNumberKey(index, key) {
        this.getInput(index).value = key;

        if (index < this.totalDigits) {
            this.getInput(index + 1).focus();
        }

        $nextTick(() => {
            this.updateHiddenField();
            if (index === this.totalDigits && this.isComplete()) {
                this.onComplete();
            }
        });
    },
    handleBackspace(index) {
        const currentInput = this.getInput(index);
        if (currentInput.value !== '') {
            currentInput.value = '';
        } else if (index > 1) {
            const previousInput = this.getInput(index - 1);
            previousInput.value = '';
            previousInput.focus();
        }
        this.updateHiddenField();
    },
    handleKeyDown(index, event) {
        const key = event.key;
        if (key >= '0' && key <= '9') {
            event.preventDefault();
            this.handleNumberKey(index, key);
            return;
        }
        if (key === 'Backspace') {
            event.preventDefault();
            this.handleBackspace(index);
            return;
        }
    },
    handlePaste(event) {
        event.preventDefault();
        const pastedText = (event.clipboardData || window.clipboardData).getData('text');
        const numericOnly = pastedText.replace(/[^0-9]/g, '');
        const digitsToFill = Math.min(numericOnly.length, this.totalDigits);
        this.digitIndices
            .slice(0, digitsToFill)
            .forEach(i => {
                this.getInput(i).value = numericOnly[i - 1];
            });
        const nextIndex = Math.min(digitsToFill + 1, this.totalDigits);
        this.getInput(nextIndex).focus();

        if (numericOnly.length >= this.totalDigits) {
           this.updateHiddenField();
           this.onComplete();
        }
    },
    clearAll() {
        this.digitIndices.forEach(i => {
            this.getInput(i).value = '';
        });
        this.$refs.code.value = '';
        this.$refs.input1.focus();
    }
}"
     @focus-auth-2fa-auth-code.window="$refs.input1 && $refs.input1.focus()"
     @clear-auth-2fa-auth-code.window="clearAll()"
     class="relative">

    <div class="flex items-center">
        @for ($x = 1; $x <= $digits; $x++)
            <input
                x-ref="input{{ $x }}"
                type="text"
                inputmode="numeric"
                pattern="[0-9]"
                maxlength="1"
                autocomplete="off"
                @paste="handlePaste"
                @keydown="handleKeyDown({{ $x }}, $event)"
                @focus="$el.select()"
                @input="$el.value = $el.value.replace(/[^0-9]/g, '').slice(0, 1)"
                class="flex h-10 w-10 items-center justify-center border border-zinc-300 bg-accent-foreground text-center text-sm font-medium text-accent-content transition-colors placeholder:text-zinc-500 focus:border-accent focus:border-2 focus:outline-none focus:relative focus:z-10 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-700 dark:focus:border-accent
                    @if($x == 1) rounded-l-md @endif
                    @if($x == $digits) rounded-r-md @endif
                    @if($x > 1) -ml-px @endif"
            />
        @endfor
    </div>

    <input
        {{ $attributes->except(['digits']) }}
        type="hidden"
        class="hidden"
        x-ref="code"
        name="{{ $name }}"
        minlength="{{ $digits }}"
        maxlength="{{ $digits }}"
    />
</div>
