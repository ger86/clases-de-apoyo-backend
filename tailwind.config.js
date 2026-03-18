const colors = require('tailwindcss/colors')

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    fontFamily: {
      sans: ['"Open Sans"', 'sans-serif'],
      heading: ['"Lato"', 'sans-serif']
    },
    extend: {
      colors: {
        primary: '#107896',
        "primary-dark": '#0c5c73',
        "primary-light": colors.sky[500],
        "body": "#515151",
        "gray-600": "#515151",
        "gray-700": "#343a40",
        "heading": "#343a40"
      }
    },
  },
  plugins: [],
}

