@props([
    // total number of boxes to display
    'digits' => 4,
    'eventCallback' => null,
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
            this.$refs.pin.value = this.generateCode();
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

            let focusLastInput = (paste.length <= this.total_digits) ? paste.length : this.total_digits;
            this.$refs['input' + focusLastInput].focus();

            if (paste.length >= this.total_digits) {
                setTimeout(() => {
                    this.$refs.pin.value = this.generateCode();
                    this.submitCallback();
                }, 100);
            }
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
    class="relative input-otp">
    <div class="flex">
        <div class="flex mx-auto space-x-2">
            @for ($x = 1; $x <= $digits; $x++)
                <input x-ref="input{{ $x }}" type="text" inputmode="numeric" pattern="[0-9]"
                    x-on:paste="pasteValue"
                    x-on:keydown="moveCursorNext({{ $x }}, {{ $digits }}, event)"
                    x-on:focus="$el.select()" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').slice(0, 1)"
                    class="w-12 h-12 font-light text-center text-black dark:text-stone-100 rounded-md border shadow-sm appearance-none auth-component-code-input dark:text-dark-400 border-stone-200 dark:border-stone-700 focus:border-2"
                    maxlength="1" />
            @endfor
        </div>
    </div>
    <input {{ $attributes->whereStartsWith('id') }} type="hidden" x-ref="pin" name="pin" />
</div>
