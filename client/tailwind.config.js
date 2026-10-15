/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx,vue}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Space Grotesk', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace'],
      },
      colors: {
        carbon: '#0B0C0E',
        surface: {
          DEFAULT: '#13171F',
          dark: '#0E1117',
          light: '#1A202C',
        },
        hazard: {
          DEFAULT: '#FACC15',
          hover: '#FFE033',
          dark: '#CA8A04',
          subtle: 'rgba(250, 204, 21, 0.12)',
        },
        industrial: {
          border: '#232A36',
          grid: '#1C2028',
          line: '#475569',
        },
        brand: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#0f172a',
        },
        slate: {
          850: '#151f32',
          925: '#0b1120',
        }
      },
      borderRadius: {
        'xs': '2px',
        'sm': '3px',
        'md': '4px',
      }
    },
  },
  plugins: [],
}
