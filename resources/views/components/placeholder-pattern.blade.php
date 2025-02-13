@props([
    "id" => uniqid(),
])

<svg {{ $attributes }} fill="none">
    <defs>
        <pattern
            id="pattern-{{ $id }}"
            x="0"
            y="0"
            width="10"
            height="10"
            patternUnits="userSpaceOnUse"
        >
            <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3"></path>
        </pattern>
    </defs>
    <rect
        stroke="none"
        fill="url(#pattern-{{ $id }})"
        width="100%"
        height="100%"
    ></rect>
</svg>
