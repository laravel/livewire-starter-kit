module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
    "./resources/**/*.vue",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./vendor/livewire/flux/**/*.blade.php"
  ],
  theme: {
    extend: {
      colors: {
        navy: {
          DEFAULT: '#1a2238', // dark blue/navy
        },
        crimson: {
          DEFAULT: '#e63946', // bright red/crimson
        },
        steel: {
          DEFAULT: '#6c757d', // steel gray/industrial gray
        },
        light: {
          DEFAULT: '#f8f9fa', // white/light gray
        },
        primary: {
          DEFAULT: '#ff7f2a', // logo orange
        },
        accent: {
          DEFAULT: '#c1272d', // logo red
        },
      },
    },
  },
  plugins: [],
} 