/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#8b5cf6",
        "primary-glow": "#a78bfa",
        accent: "#c084fc",
        "bg-dark": "#0a0a0b",
        secondary: "rgba(255, 255, 255, 0.6)",
      },
    },
  },
  plugins: [],
}
