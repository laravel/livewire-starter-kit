@props([
    'digits' => 6,
    'eventCallback' => null,
    'name' => 'code',
])

<div x-data="{
    total_digits: @js($digits),
    eventCallback: @js($eventCallback),
    moveCursorNext(index, digits, evt) {
        if (!isNaN(parseInt(evt.key)) && parseInt(evt.key) >= 0 && parseInt(evt.key) <= 9 && index != digits) {
            evt.preventDefault();
            evt.stopPropagation();
            this.$refs['input' + index].value = evt.key;
            this.$refs['input' + (index + 1)].focus();
        } else {
            if (evt.key === 'Backspace') {
                evt.preventDefault();
                evt.stopPropagation();
                
                // Clear current input if it has a value
                if (this.$refs['input' + index].value !== '') {
                    this.$refs['input' + index].value = '';
                } 
                // Otherwise, move to previous input if possible and clear it
                else if (index > 1) {
                    this.$refs['input' + (index - 1)].value = '';
                    this.$refs['input' + (index - 1)].focus();
                }
            }
        }
        setTimeout(() => {
            this.$refs.code.value = this.generateCode();
            // Dispatch both input and change events to ensure Livewire picks up the change
            this.$refs.code.dispatchEvent(new Event('input', { bubbles: true }));
            this.$refs.code.dispatchEvent(new Event('change', { bubbles: true }));
            
            if (index === digits && [...Array(digits).keys()].every(i => this.$refs['input' + (i + 1)].value !== '')) {
                this.submitCallback();
            }
        }, 100);
    },
    submitCallback() {
        if (this.eventCallback) {
            window.dispatchEvent(new CustomEvent(this.eventCallback, { detail: { code: this.generateCode() } }));
        }
    },
    pasteValue(event) {
        event.preventDefault();
        let paste = (event.clipboardData || window.clipboardData).getData('text');
        for (let i = 0; i < paste.length; i++) {
            if (i < this.total_digits) {
                this.$refs['input' + (i + 1)].value = paste[i];
            }
        }

        let focusLastInput = (paste.length <= this.total_digits) ? paste.length : this.total_digits;
        this.$refs['input' + focusLastInput].focus();

        if (paste.length >= this.total_digits) {
            setTimeout(() => {
                this.$refs.code.value = this.generateCode();
                // Dispatch both input and change events to ensure Livewire picks up the change
                this.$refs.code.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.code.dispatchEvent(new Event('change', { bubbles: true }));
                this.submitCallback();
            }, 100);
        }
    },
    generateCode() {
        let code = '';
        for (let i = 1; i <= this.total_digits; i++) {
            code += this.$refs['input' + i].value;
        }
        return code;
    },
}" x-init="setTimeout(() => {
    $refs.input1.focus();
}, 100);" @focus-auth-2fa-auth-code.window="$refs.input1.focus()"
    @clear-auth-2fa-auth-code.window="for (let i = 1; i <= total_digits; i++) { $refs['input' + i].value = ''; } $refs.code.value = ''; $refs.input1.focus();"
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
