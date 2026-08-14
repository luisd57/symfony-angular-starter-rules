/** @type {import('tailwindcss').Config} */
// Tailwind 3 config shape. Tailwind 4 moved theming into CSS (`@theme`) and does
// not read this file - check which major you installed before editing.
export default {
  content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
  theme: {
    extend: {
      colors: {
        // {{FILL: the project's brand ramp, 50 (lightest) through 900}}
        brand: {
          50: '#f5f7fa',
          100: '#e4e9f0',
          200: '#c7d2e0',
          300: '#a1b3cb',
          400: '#7590b2',
          500: '#527099',
          600: '#41597b',
          700: '#344762',
          800: '#2b3a50',
          900: '#243043',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
