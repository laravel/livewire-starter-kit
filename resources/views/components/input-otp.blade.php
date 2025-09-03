@props([
    'digits' => 6,
    'eventCallback' => null,
    'name' => 'code',
])

<div x-data="{
        totalInputDigits: @js($digits),
        callbackEventName: @js($eventCallback),

        init() {
            this.$nextTick(() => {
                this.focusFirstInputField();
                this.updateHiddenInputValue();
            });
        },

        focusFirstInputField() {
            this.getInputElementByIndex(1)?.focus();
        },

        getInputElementByIndex(inputIndex) {
            return this.$refs[`input${inputIndex}`];
        },

        handleKeydown(currentInputIndex, keyboardEvent) {
            const pressedKey = keyboardEvent.key;

            if (this.isValidDigitKey(pressedKey)) {
                this.processDigitInput(currentInputIndex, keyboardEvent);
            } else if (pressedKey === 'Backspace') {
                this.processBackspaceInput(currentInputIndex, keyboardEvent);
            }
        },

        isValidDigitKey(keyValue) {
            const parsedNumber = parseInt(keyValue);
            return !isNaN(parsedNumber) && parsedNumber >= 0 && parsedNumber <= 9;
        },

        processDigitInput(currentInputIndex, keyboardEvent) {
            if (currentInputIndex === this.totalInputDigits) return;

            keyboardEvent.preventDefault();
            keyboardEvent.stopPropagation();

            const currentInputElement = this.getInputElementByIndex(currentInputIndex);
            const nextInputElement = this.getInputElementByIndex(currentInputIndex + 1);

            currentInputElement.value = keyboardEvent.key;
            nextInputElement?.focus();

            this.scheduleInputUpdate(currentInputIndex);
        },

        processBackspaceInput(currentInputIndex, keyboardEvent) {
            keyboardEvent.preventDefault();
            keyboardEvent.stopPropagation();

            const currentInputElement = this.getInputElementByIndex(currentInputIndex);
            const previousInputElement = this.getInputElementByIndex(currentInputIndex - 1);

            if (currentInputElement.value !== '') {
                currentInputElement.value = '';
            } else if (currentInputIndex > 1) {
                previousInputElement.value = '';
                previousInputElement.focus();
            }

            this.scheduleInputUpdate(currentInputIndex);
        },

        handlePaste(pasteEvent) {
            pasteEvent.preventDefault();

            const clipboardData = (pasteEvent.clipboardData || window.clipboardData).getData('text');
            const extractedDigits = clipboardData.split('').slice(0, this.totalInputDigits);

            extractedDigits.forEach((digitValue, digitIndex) => {
                const inputElement = this.getInputElementByIndex(digitIndex + 1);
                if (inputElement && this.isValidDigitKey(digitValue)) {
                    inputElement.value = digitValue;
                }
            });

            const nextFocusIndex = Math.min(extractedDigits.length, this.totalInputDigits);
            this.getInputElementByIndex(nextFocusIndex)?.focus();

            this.updateHiddenInputValue();
        },

        sanitizeInput(inputElement) {
            inputElement.value = inputElement.value.replace(/[^0-9]/g, '').slice(0, 1);
        },

        scheduleInputUpdate(currentInputIndex) {
            setTimeout(() => {
                this.updateHiddenInputValue();
            }, 100);
        },

        generateCompleteCode() {
            return Array.from({ length: this.totalInputDigits }, (_, digitIndex) =>
                this.getInputElementByIndex(digitIndex + 1)?.value || ''
            ).join('');
        },

        updateHiddenInputValue() {
            const completeCode = this.generateCompleteCode();
            const hiddenInputElement = this.$refs.code;

            if (hiddenInputElement) {
                hiddenInputElement.value = completeCode;
                hiddenInputElement.dispatchEvent(new Event('input', { bubbles: true }));
            }
        },

        clearAllInputs() {
            for (let i = 1; i <= this.totalInputDigits; i++) {
                const input = this.getInputElementByIndex(i);
                if (input) input.value = '';
            }
            this.updateHiddenInputValue();
            this.focusFirstInputField();
        },
    }"
     x-init="init()"
     x-intersect.once="setTimeout(() => focusFirstInputField(), 100)"
     @focus-auth-2fa-auth-code.window="focusFirstInputField()"
     @clear-auth-2fa-auth-code.window="clearAllInputs()"
     class="relative">

    <div class="flex items-center">
        @for ($x = 1; $x <= $digits; $x++)
            <input x-ref="input{{ $x }}"
                   type="text"
                   inputmode="numeric"
                   pattern="[0-9]"
                   maxlength="1"
                   autocomplete="off"
                   @paste="handlePaste"
                   @keydown="handleKeydown({{ $x }}, $event)"
                   @focus="$el.select()"
                   @input="sanitizeInput($el)"
                   class="flex h-10 w-10 items-center justify-center border border-zinc-300 bg-accent-foreground text-center text-sm font-medium text-accent-content transition-colors placeholder:text-zinc-500 focus:border-accent focus:border-2 focus:outline-none focus:relative focus:z-10 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-700 dark:focus:border-accent @if($x == 1) rounded-l-md @endif @if($x == $digits) rounded-r-md @endif @if($x > 1) -ml-px @endif" />
        @endfor
    </div>

    <input {{ $attributes->except(['eventCallback', 'digits', 'name']) }}
           type="hidden"
           class="hidden"
           x-ref="code"
           name="{{ $name }}"
           minlength="{{ $digits }}"
           maxlength="{{ $digits }}" />
</div>
