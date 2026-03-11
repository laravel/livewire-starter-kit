<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/flexcon.png" type="image/png">
<link rel="apple-touch-icon" href="/flexcon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{-- ── Flatpickr ──────────────────────────────────────────────────────── --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

{{-- ── Tom Select ─────────────────────────────────────────────────────── --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

<style>
/* ═══════════════════════════════════════════════════════════════════════
   FLATPICKR — Custom theme matching Tailwind / project design
   ═══════════════════════════════════════════════════════════════════════ */
.flatpickr-input {
    width: 100%;
    cursor: pointer;
}
.flatpickr-input[readonly] {
    cursor: pointer;
}
.flatpickr-calendar {
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 4px 10px -5px rgba(0,0,0,0.1);
    border: 1px solid rgb(229 231 235);
    overflow: hidden;
}
.flatpickr-calendar.arrowTop::before,
.flatpickr-calendar.arrowTop::after { border-bottom-color: rgb(99 102 241); }
.flatpickr-months { background: rgb(99 102 241); padding: 4px 0; }
.flatpickr-months .flatpickr-month { color: white; fill: white; }
.flatpickr-current-month { color: white; font-size: 1rem; font-weight: 600; }
.flatpickr-current-month .flatpickr-monthDropdown-months { color: white; background: transparent; }
.flatpickr-current-month input.cur-year { color: white; font-weight: 600; }
.flatpickr-months .flatpickr-prev-month,
.flatpickr-months .flatpickr-next-month { color: white; fill: white; }
.flatpickr-months .flatpickr-prev-month:hover,
.flatpickr-months .flatpickr-next-month:hover { color: rgb(199 210 254); fill: rgb(199 210 254); }
.flatpickr-weekdays { background: rgb(238 242 255); }
span.flatpickr-weekday { color: rgb(99 102 241); font-weight: 600; font-size: 0.75rem; }
.flatpickr-day { border-radius: 0.5rem; font-size: 0.875rem; }
.flatpickr-day:hover { background: rgb(238 242 255); border-color: transparent; }
.flatpickr-day.selected,
.flatpickr-day.selected:hover { background: rgb(99 102 241); border-color: rgb(99 102 241); }
.flatpickr-day.today { border-color: rgb(99 102 241); color: rgb(99 102 241); font-weight: 600; }
.flatpickr-day.today:hover { background: rgb(238 242 255); }
.flatpickr-day.today.selected { color: white; }
.flatpickr-time input { font-size: 1rem; font-weight: 600; color: rgb(55 65 81); }
.flatpickr-time .flatpickr-time-separator { color: rgb(107 114 128); }
.flatpickr-time .arrowUp::after { border-bottom-color: rgb(99 102 241); }
.flatpickr-time .arrowDown::after { border-top-color: rgb(99 102 241); }

/* Dark mode — Flatpickr */
.dark .flatpickr-calendar {
    background: rgb(31 41 55);
    border-color: rgb(55 65 81);
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.4);
}
.dark .flatpickr-months { background: rgb(79 70 229); }
.dark .flatpickr-weekdays { background: rgb(17 24 39); }
.dark span.flatpickr-weekday { background: rgb(17 24 39); color: rgb(129 140 248); }
.dark .flatpickr-day { color: rgb(229 231 235); }
.dark .flatpickr-day:hover { background: rgb(55 65 81); border-color: transparent; }
.dark .flatpickr-day.selected,
.dark .flatpickr-day.selected:hover { background: rgb(99 102 241); border-color: rgb(99 102 241); color: white; }
.dark .flatpickr-day.today { border-color: rgb(99 102 241); color: rgb(165 180 252); }
.dark .flatpickr-day.flatpickr-disabled { color: rgb(75 85 99); }
.dark .flatpickr-time { background: rgb(31 41 55); border-color: rgb(55 65 81); }
.dark .flatpickr-time input { color: rgb(229 231 235); background: rgb(31 41 55); }
.dark .flatpickr-time .flatpickr-time-separator { color: rgb(156 163 175); }

/* ═══════════════════════════════════════════════════════════════════════
   TOM SELECT — Custom theme matching Tailwind / project design
   ═══════════════════════════════════════════════════════════════════════ */
.ts-wrapper {
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 0.875rem;
}
.ts-wrapper .ts-control {
    border-radius: 0.5rem;
    border: 1px solid rgb(209 213 219);
    padding: 0.5rem 0.75rem;
    min-height: 2.375rem;
    background-color: white;
    color: rgb(17 24 39);
    transition: border-color 0.15s, box-shadow 0.15s;
    cursor: pointer;
}
.ts-wrapper.single .ts-control {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.25rem;
    padding-right: 2rem;
}
.ts-wrapper.focus .ts-control {
    border-color: rgb(99 102 241);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    outline: none;
}
.ts-dropdown {
    border-radius: 0.5rem;
    border: 1px solid rgb(209 213 219);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    font-size: 0.875rem;
    background: white;
    margin-top: 4px;
}
.ts-dropdown .option {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    color: rgb(17 24 39);
}
.ts-dropdown .option:hover,
.ts-dropdown .option.active {
    background-color: rgb(238 242 255);
    color: rgb(99 102 241);
}
.ts-dropdown .option.selected {
    background-color: rgb(99 102 241);
    color: white;
}
.ts-dropdown .no-results {
    padding: 0.5rem 0.75rem;
    color: rgb(107 114 128);
    font-style: italic;
}
.ts-wrapper .ts-control .item {
    color: rgb(17 24 39);
}
/* Hide the original caret from tom-select default */
.ts-wrapper.single .ts-control::after { display: none; }

/* Dark mode — TomSelect */
.dark .ts-wrapper .ts-control {
    background-color: rgb(31 41 55);
    border-color: rgb(75 85 99);
    color: white;
}
.dark .ts-wrapper.single .ts-control {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
}
.dark .ts-wrapper.focus .ts-control {
    border-color: rgb(99 102 241);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
}
.dark .ts-wrapper .ts-control input {
    color: white;
}
.dark .ts-wrapper .ts-control input::placeholder {
    color: rgb(156 163 175);
}
.dark .ts-dropdown {
    background-color: rgb(31 41 55);
    border-color: rgb(75 85 99);
}
.dark .ts-dropdown .option {
    color: rgb(229 231 235);
}
.dark .ts-dropdown .option:hover,
.dark .ts-dropdown .option.active {
    background-color: rgb(55 65 81);
    color: white;
}
.dark .ts-dropdown .option.selected {
    background-color: rgb(79 70 229);
    color: white;
}
.dark .ts-dropdown .no-results {
    color: rgb(156 163 175);
}
.dark .ts-wrapper .ts-control .item {
    color: white;
}
.dark .ts-dropdown-content { scrollbar-color: rgb(75 85 99) transparent; }
</style>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<script>
/**
 * Auto-init Flatpickr (datepicker) & TomSelect (searchable select)
 * Compatible with Livewire 3 + Alpine.js (Flux)
 *
 * Skip inputs/selects with:
 *   data-no-picker  → skip flatpickr
 *   data-no-ts      → skip TomSelect
 */
(function () {
    'use strict';

    /* ── Flatpickr ────────────────────────────────────────────────────── */
    function initFlatpickr(el) {
        // Already initialized and instance is alive
        if (el._flatpickr) return;

        const isDatetime = el.type === 'datetime-local' ||
                           el.getAttribute('data-fp-type') === 'datetime-local';

        // Store original type so we can detect it after morph
        if (!el.getAttribute('data-fp-type')) {
            el.setAttribute('data-fp-type', el.type);
        }

        const minDate  = el.getAttribute('min') || null;
        const maxDate  = el.getAttribute('max') || null;
        const defVal   = el.value || null;

        // Flatpickr will set el.type = 'text' internally; keep the behavior
        el._flatpickr = flatpickr(el, {
            locale        : 'es',
            dateFormat    : isDatetime ? 'Y-m-d\\TH:i' : 'Y-m-d',
            enableTime    : isDatetime,
            time_24hr     : true,
            defaultDate   : defVal,
            minDate       : minDate,
            maxDate       : maxDate,
            allowInput    : true,
            disableMobile : false,
            onChange: function (selectedDates, dateStr) {
                // Livewire wire:model listens to input + change events
                el.dispatchEvent(new Event('input',  { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            },
        });
    }

    function maybeReinitFlatpickr(el) {
        if (!el._flatpickr) {
            // Instance lost (e.g. after Livewire morph replaced the element)
            initFlatpickr(el);
            return;
        }
        // Instance alive – just sync the value that Livewire may have set
        const expected = el.value;
        const current  = el._flatpickr.latestSelectedDateObj
            ? el._flatpickr.formatDate(el._flatpickr.latestSelectedDateObj, el._flatpickr.config.dateFormat)
            : '';
        if (expected && expected !== current) {
            el._flatpickr.setDate(expected, false);
        }
    }

    /* ── TomSelect ────────────────────────────────────────────────────── */
    function initTomSelect(el) {
        if (el.tomselect) {
            // Instance alive and wrapper still in DOM — just sync value
            if (el.tomselect.wrapper && document.contains(el.tomselect.wrapper)) {
                var liveVal = el.value;
                if (el.tomselect.getValue() !== liveVal) {
                    el.tomselect.setValue(liveVal, true);
                }
                return;
            }
            // Wrapper removed by Livewire morph — destroy and force-clear
            try { el.tomselect.destroy(); } catch (_) {}
            el.tomselect = null; // Must clear so new TomSelect() doesn't throw
        }

        var placeholder = el.getAttribute('placeholder') ||
                          el.dataset.placeholder         ||
                          'Seleccionar...';

        try {
            new TomSelect(el, {
                create          : false,
                allowEmptyOption: true,
                placeholder     : placeholder,
                sortField       : false,
                maxOptions      : 500,
                render: {
                    no_results: function () {
                        return '<div class="no-results">Sin resultados</div>';
                    },
                },
                onChange: function (value) {
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                    el.dispatchEvent(new Event('input',  { bubbles: true }));
                },
            });
        } catch (e) { /* skip */ }
    }

    /* ── Scan container and initialize everything ─────────────────────── */
    function initAll(container) {
        var root = (container instanceof Element) ? container : document;

        // Date / Datetime inputs
        root.querySelectorAll(
            'input[type="date"]:not([data-no-picker]), ' +
            'input[type="datetime-local"]:not([data-no-picker]), ' +
            'input[data-fp-type="date"]:not([data-no-picker]), ' +
            'input[data-fp-type="datetime-local"]:not([data-no-picker])'
        ).forEach(function (el) {
            try {
                if (el._flatpickr) {
                    maybeReinitFlatpickr(el);
                } else {
                    initFlatpickr(el);
                }
            } catch (e) { /* skip broken inputs silently */ }
        });

        // Selects — exclude: multiple, data-no-ts, flux components, and the
        // hidden original <select> that TomSelect manages (.tomselected)
        root.querySelectorAll(
            'select:not([data-no-ts]):not([multiple]):not([data-flux-select]):not(.tomselected)'
        ).forEach(function (el) {
            try {
                initTomSelect(el);
            } catch (e) { /* skip */ }
        });
    }

    /* ── Lifecycle hooks ──────────────────────────────────────────────── */

    // Initial load
    document.addEventListener('DOMContentLoaded', function () {
        initAll();
    });

    // wire:navigate SPA navigation
    document.addEventListener('livewire:navigated', function () {
        initAll();
    });

    // After each Livewire commit (server round-trip), resync pickers
    document.addEventListener('livewire:initialized', function () {
        Livewire.hook('commit', function (params) {
            // params.succeed is called after DOM morph is finished
            params.succeed(function () {
                // Short delay to let Alpine.js finish any pending DOM work
                setTimeout(function () { initAll(); }, 20);
            });
        });
    });

})();
</script>
