@props([
    'digits' => 6,
    'eventCallback' => null,
    'name' => 'code',
])

<div x-data="{
    total_digits: @js($digits),
    eventCallback: @js($eventCallback),
    UPDATE_DELAY: 100,
    getInputRef(index) {
        return this.$refs['input' + index];
    },
    updateHiddenInput() {
        this.$refs.code.value = this.generateCode();
        this.$refs.code.dispatchEvent(new Event('input', { bubbles: true }));
        this.$refs.code.dispatchEvent(new Event('change', { bubbles: true }));
    },
    isAllInputsFilled() {
        return [...Array(this.total_digits).keys()].every(i =>
            this.getInputRef(i + 1).value !== ''
        );
    },
    isNumericKey(key) {
        return !isNaN(parseInt(key)) && parseInt(key) >= 0 && parseInt(key) <= 9;
    },
    moveCursorNext(index, digits, evt) {
        if (this.isNumericKey(evt.key) && index !== digits) {
            evt.preventDefault();
            evt.stopPropagation();
            this.getInputRef(index).value = evt.key;
            this.getInputRef(index + 1).focus();
        } else if (evt.key === 'Backspace') {
            evt.preventDefault();
            evt.stopPropagation();

            if (this.getInputRef(index).value !== '') {
                this.getInputRef(index).value = '';
            } else if (index > 1) {
                this.getInputRef(index - 1).value = '';
                this.getInputRef(index - 1).focus();
            }
        }
        setTimeout(() => {
            this.updateHiddenInput();

            if (index === digits && this.isAllInputsFilled()) {
                this.submitCallback();
            }
        }, this.UPDATE_DELAY);
    },
    submitCallback() {
        if (this.eventCallback) {
            window.dispatchEvent(new CustomEvent(this.eventCallback, {
                detail: { code: this.generateCode() }
            }));
        }
    },
    pasteValue(event) {
        event.preventDefault();
        const clipboardData = (event.clipboardData || window.clipboardData).getData('text');
        for (let i = 0; i < clipboardData.length; i++) {
            if (i < this.total_digits) {
                this.getInputRef(i + 1).value = clipboardData[i];
            }
        }
        const focusIndex = Math.min(clipboardData.length, this.total_digits);
        this.getInputRef(focusIndex).focus();
        if (clipboardData.length >= this.total_digits) {
            setTimeout(() => {
                this.updateHiddenInput();
                this.submitCallback();
            }, this.UPDATE_DELAY);
        }
    },
    generateCode() {
        let code = '';
        for (let i = 1; i <= this.total_digits; i++) {
            code += this.getInputRef(i).value;
        }
        return code;
    },

    clearAllInputs() {
        for (let i = 1; i <= this.total_digits; i++) {
            this.getInputRef(i).value = '';
        }
        this.$refs.code.value = '';
        this.$refs.input1.focus();
    }
}"
x-init="setTimeout(() => { $refs.input1.focus(); }, 100);"
@focus-auth-2fa-auth-code.window="$refs.input1.focus()"
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
                   @paste="pasteValue"
                   @keydown="moveCursorNext({{ $x }}, {{ $digits }}, $event)"
                   @focus="$el.select()"
                   @input="$el.value = $el.value.replace(/[^0-9]/g, '').slice(0, 1)"
                   class="flex h-10 w-10 items-center justify-center border border-zinc-300 bg-accent-foreground text-center text-sm font-medium text-accent-content transition-colors placeholder:text-zinc-500 focus:border-accent focus:border-2 focus:outline-none focus:relative focus:z-10 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-700 dark:focus:border-accent @if($x == 1) rounded-l-md @endif @if($x == $digits) rounded-r-md @endif @if($x > 1) -ml-px @endif" />
        @endfor
    </div>

    <input {{ $attributes->except(['eventCallback', 'digits']) }}
           type="hidden"
           class="hidden"
           x-ref="code"
           name="{{ $name }}"
           minlength="{{ $digits }}"
           maxlength="{{ $digits }}" />
</div>
