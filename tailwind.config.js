import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                // Primary: Industrial Blue (The Lila Rule - single accent lock)
                "primary": "#1D4ED8",
                "primary-container": "#2563EB",
                "on-primary": "#FFFFFF",
                "on-primary-container": "#FFFFFF",
                "primary-fixed": "#DBEAFE",
                "primary-fixed-dim": "#BFDBFE",
                "on-primary-fixed": "#1E3A8A",
                "on-primary-fixed-variant": "#1E40AF",
                "inverse-primary": "#93C5FD",
                "surface-tint": "#1D4ED8",

                // Neutrals: Slate base (crisp engineering canvas)
                "background": "#F8FAFC",
                "on-background": "#0F172A",
                "surface": "#F8FAFC",
                "surface-dim": "#E2E8F0",
                "surface-bright": "#FFFFFF",
                "surface-container-lowest": "#FFFFFF",
                "surface-container-low": "#F8FAFC",
                "surface-container": "#F1F5F9",
                "surface-container-high": "#E2E8F0",
                "surface-container-highest": "#E2E8F0",
                "surface-variant": "#E2E8F0",
                "on-surface": "#0F172A",
                "on-surface-variant": "#475569",
                "outline": "#94A3B8",
                "outline-variant": "#CBD5E1",

                // Secondary: Slate interactive neutrals
                "secondary": "#334155",
                "on-secondary": "#FFFFFF",
                "secondary-container": "#E2E8F0",
                "on-secondary-container": "#334155",
                "secondary-fixed": "#E2E8F0",
                "secondary-fixed-dim": "#CBD5E1",
                "on-secondary-fixed": "#0F172A",
                "on-secondary-fixed-variant": "#475569",

                // Error: destructive red (WCAG AA on white)
                "error": "#DC2626",
                "on-error": "#FFFFFF",
                "error-container": "#FEE2E2",
                "on-error-container": "#991B1B",

                // Inverse
                "inverse-surface": "#1E293B",
                "inverse-on-surface": "#F8FAFC",

                // Tertiary: burnt orange accents
                "tertiary": "#9A3412",
                "on-tertiary": "#FFFFFF",
                "tertiary-container": "#C2410C",
                "on-tertiary-container": "#FFEDD5",
                "tertiary-fixed": "#FFEDD5",
                "tertiary-fixed-dim": "#FDBA74",
                "on-tertiary-fixed": "#431407",
                "on-tertiary-fixed-variant": "#7C2D12",

                // Semantic status tokens (WCAG AA verified)
                "status-success-bg": "#D1FAE5",
                "status-success-text": "#065F46",
                "status-approved-bg": "#E0F2FE",
                "status-approved-text": "#075985",
                "status-pending-bg": "#FEF3C7",
                "status-pending-text": "#92400E",
                "status-partial-bg": "#FFEDD5",
                "status-partial-text": "#9A3412",
                "status-loan-bg": "#E0E7FF",
                "status-loan-text": "#3730A3",
                "status-overdue-bg": "#E11D48",
                "status-overdue-text": "#FFFFFF",
                "status-rejected-bg": "#FFE4E6",
                "status-rejected-text": "#9F1239",

                // Stock indicators
                "stock-available": "#065F46",
                "stock-limited": "#92400E",
                "stock-maintenance": "#1E293B",
                "stock-none": "#64748B",

                // Sidebar
                "sidebar-dark": "#0F172A",
            },
            boxShadow: {
                "xs": "0 1px 2px 0 rgb(15 23 42 / 0.05)",
            },
            spacing: {
                "gutter-md": "1.5rem",
                "padding-card": "1.5rem",
                "container-max": "80rem",
                "margin-page": "1rem",
                "gutter-sm": "1rem",
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                'headline-lg': ['Inter', 'sans-serif'],
                'headline-md': ['Inter', 'sans-serif'],
                'headline-sm': ['Inter', 'sans-serif'],
                'title-sm': ['Inter', 'sans-serif'],
                'body-md': ['Inter', 'sans-serif'],
                'label-md': ['Inter', 'sans-serif'],
                'caption-xs': ['Inter', 'sans-serif'],
                'badge-xs': ['Inter', 'sans-serif'],
            },
            fontSize: {
                "badge-xs": ["12px", { "lineHeight": "16px", "fontWeight": "500" }],
                "label-md": ["14px", { "lineHeight": "20px", "fontWeight": "500" }],
                "caption-xs": ["12px", { "lineHeight": "16px", "fontWeight": "400" }],
                "headline-lg": ["30px", { "lineHeight": "36px", "fontWeight": "700" }],
                "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                "title-sm": ["16px", { "lineHeight": "24px", "fontWeight": "600" }],
                "headline-sm": ["18px", { "lineHeight": "28px", "fontWeight": "600" }],
            },
        },
    },

    plugins: [forms],
};
