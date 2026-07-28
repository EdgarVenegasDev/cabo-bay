/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./admin/**/*.php"],
  theme: {
    extend: {
      colors: {
        navy: { DEFAULT: '#0b2b4f', dark: '#061a30', light: '#1a4a7a' },
        coral: { DEFAULT: '#e0703f', dark: '#c85a2c' },
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'system-ui', '-apple-system', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
