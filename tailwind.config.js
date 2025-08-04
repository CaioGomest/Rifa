/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./admin/**/*.php",
    "./assets/**/*.{js,css}",
    "./functions/**/*.php"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: '#22C55A',
        'primary-dark': '#16A34A'
      }
    },
  },
  plugins: [],
} 