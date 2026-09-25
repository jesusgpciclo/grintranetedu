<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'GR Intranet EDU')</title>

    <!-- Immediate Theme & Sidebar Initialization Script (Prevents FOUC & Layout Shifts) -->
    <script>
        (function() {
            // Theme initialization
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme ? savedTheme : (prefersDark ? 'dark' : 'light');
            
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(theme);
            document.documentElement.setAttribute('data-theme', theme);

            // Early desktop sidebar collapse initialization
            try {
                if (localStorage.getItem('sidebar_collapsed') === 'true' && window.innerWidth >= 1024) {
                    document.documentElement.classList.add('sidebar-collapsed-init');
                }
            } catch(e) {}
        })();
    </script>

    <!-- PWA Manifest & Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0284c7">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Guardia">
    <link rel="apple-touch-icon" href="{{ asset('avatars/predefined/teacher_1.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js Core & Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <style>
        /* ==========================================================================
           CSS DESIGN TOKENS FOR LIGHT & DARK MODES
           ========================================================================== */
        
        /* DARK MODE (Default, html.dark, [data-theme="dark"]) */
        :root,
        html.dark,
        [data-theme="dark"],
        body.dark {
            --bg-color: #0b1120;
            --bg-surface: #0f172a;
            --bg-card: rgba(15, 23, 42, 0.85);
            --bg-card-solid: #0f172a;
            --bg-card-hover: rgba(30, 41, 59, 0.85);
            --bg-sidebar: rgba(15, 23, 42, 0.98);
            --bg-header: rgba(15, 23, 42, 0.95);
            --bg-input: rgba(15, 23, 42, 0.7);
            --bg-input-focus: rgba(15, 23, 42, 0.95);
            --bg-hover: rgba(255, 255, 255, 0.05);
            --bg-active: rgba(56, 189, 248, 0.12);
            
            --text-color: #f8fafc;
            --text-heading: #ffffff;
            --text-muted: #94a3b8;
            --text-secondary: #cbd5e1;
            
            --primary: #38bdf8;
            --primary-hover: #0ea5e9;
            --primary-light: rgba(56, 189, 248, 0.15);
            --primary-border: rgba(56, 189, 248, 0.3);
            
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --danger-light: rgba(239, 68, 68, 0.15);
            --danger-border: rgba(239, 68, 68, 0.3);
            
            --success: #22c55e;
            --success-hover: #16a34a;
            --success-light: rgba(34, 197, 94, 0.15);
            --success-border: rgba(34, 197, 94, 0.3);
            
            --warning: #f59e0b;
            --warning-hover: #d97706;
            --warning-light: rgba(245, 158, 11, 0.15);
            --warning-border: rgba(245, 158, 11, 0.3);
            
            --border: rgba(255, 255, 255, 0.1);
            --border-light: rgba(255, 255, 255, 0.05);
            --border-focus: #38bdf8;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
            
            --font-main: 'Outfit', sans-serif;
            color-scheme: dark;
        }

        /* LIGHT MODE (html.light, [data-theme="light"]) */
        html.light,
        [data-theme="light"],
        body.light {
            --bg-color: #f1f5f9;
            --bg-surface: #ffffff;
            --bg-card: #ffffff;
            --bg-card-solid: #ffffff;
            --bg-card-hover: #f8fafc;
            --bg-sidebar: #ffffff;
            --bg-header: rgba(255, 255, 255, 0.98);
            --bg-input: #f8fafc;
            --bg-input-focus: #ffffff;
            --bg-hover: rgba(0, 0, 0, 0.04);
            --bg-active: rgba(2, 132, 199, 0.08);
            
            --text-color: #1e293b;
            --text-heading: #0f172a;
            --text-muted: #64748b;
            --text-secondary: #475569;
            
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --primary-light: rgba(2, 132, 199, 0.1);
            --primary-border: rgba(2, 132, 199, 0.25);
            
            --danger: #dc2626;
            --danger-hover: #b91c1c;
            --danger-light: rgba(220, 38, 38, 0.1);
            --danger-border: rgba(220, 38, 38, 0.25);
            
            --success: #16a34a;
            --success-hover: #15803d;
            --success-light: rgba(22, 163, 74, 0.1);
            --success-border: rgba(22, 163, 74, 0.25);
            
            --warning: #d97706;
            --warning-hover: #b45309;
            --warning-light: rgba(217, 119, 6, 0.1);
            --warning-border: rgba(217, 119, 6, 0.25);
            
            --border: #cbd5e1;
            --border-light: #e2e8f0;
            --border-focus: #0284c7;
            
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
            
            --font-main: 'Outfit', sans-serif;
            color-scheme: light;
        }

        /* ==========================================================================
           LEGACY HARDCODED INLINE COLOR OVERRIDES FOR LIGHT MODE
           ========================================================================== */
        /* Targets elements with white text inline styles and replaces with semantic theme colors */
        html.light [style*="color: #fff"],
        html.light [style*="color:#fff"],
        html.light [style*="color: #ffffff"],
        html.light [style*="color:#ffffff"],
        html.light [style*="color: white"],
        html.light [style*="color:white"],
        html.light [style*="color: rgb(255, 255, 255)"],
        html.light [style*="color:rgb(255,255,255)"],
        html.light [style*="color:rgba(255,255,255"] {
            color: var(--text-color) !important;
        }

        /* Enforce heading colors to be var(--text-heading) even if overridden with white inline */
        html.light h1[style*="color"],
        html.light h2[style*="color"],
        html.light h3[style*="color"],
        html.light h4[style*="color"],
        html.light h5[style*="color"],
        html.light h6[style*="color"],
        html.light strong[style*="color"],
        html.light th[style*="color"],
        html.light .page-title[style*="color"] {
            color: var(--text-heading) !important;
        }

        /* Fix link styles with hardcoded white */
        html.light a[style*="color: #fff"],
        html.light a[style*="color:#fff"],
        html.light a[style*="color: white"],
        html.light a[style*="color:white"] {
            color: var(--primary) !important;
        }
        html.light a[style*="color: #fff"]:hover,
        html.light a[style*="color:#fff"]:hover,
        html.light a[style*="color: white"]:hover,
        html.light a[style*="color:white"]:hover {
            color: var(--primary-hover) !important;
        }

        /* Targets form fields, inputs, selects, textareas that have hardcoded white text */
        html.light input,
        html.light select,
        html.light textarea,
        html.light .form-control {
            color: var(--text-color) !important;
            background-color: var(--bg-input) !important;
            border-color: var(--border) !important;
        }

        /* Override dark inline backgrounds with theme card backgrounds */
        html.light [style*="background: rgba(0,0,0"],
        html.light [style*="background:rgba(0,0,0"],
        html.light [style*="background-color: rgba(0,0,0"],
        html.light [style*="background-color:rgba(0,0,0"],
        html.light [style*="background: #1"],
        html.light [style*="background:#1"],
        html.light [style*="background-color: #1"],
        html.light [style*="background-color:#1"],
        html.light [style*="background: #0"],
        html.light [style*="background:#0"],
        html.light [style*="background-color: #0"],
        html.light [style*="background-color:#0"] {
            background-color: var(--bg-input) !important;
            color: var(--text-color) !important;
        }

        html.light [style*="background: #1f2937"],
        html.light [style*="background:#1f2937"],
        html.light [style*="background-color: #1f2937"],
        html.light [style*="background-color:#1f2937"] {
            background-color: var(--bg-card) !important;
            border-color: var(--border) !important;
        }

        /* General elements override to ensure default text colors in Light Mode */
        html.light td, 
        html.light p, 
        html.light span:not(.badge):not(.nav-icon):not([class*="text-"]), 
        html.light label {
            color: var(--text-color) !important;
        }

        /* Override Tailwind utility classes in Light Mode to keep text readable */
        html.light .text-white {
            color: var(--text-heading) !important;
        }
        html.light .text-gray-100,
        html.light .text-slate-100 {
            color: var(--text-heading) !important;
        }
        html.light .text-gray-200,
        html.light .text-slate-200 {
            color: var(--text-heading) !important;
        }
        html.light .text-gray-300,
        html.light .text-slate-300 {
            color: var(--text-color) !important;
        }
        html.light .text-gray-400,
        html.light .text-slate-400 {
            color: var(--text-muted) !important;
        }
        html.light .text-slate-500,
        html.light .text-gray-500 {
            color: var(--text-muted) !important;
        }

        /* ==========================================================================
           CORE STYLES
           ========================================================================== */
        html, body {
            font-family: var(--font-main);
            background-color: var(--bg-color) !important;
            color: var(--text-color) !important;
            margin: 0;
            line-height: 1.5;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--text-heading) !important;
            font-weight: 700;
        }

        a {
            color: var(--primary);
            transition: color 0.2s ease;
        }
        a:hover {
            color: var(--primary-hover);
        }

        /* Layout Structure */
        .app-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            background-color: var(--bg-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-x: hidden;
            width: 100%;
        }

        /* Topbar Header (Unified for Desktop & Mobile) */
        .app-topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            background: var(--bg-header) !important;
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border) !important;
            padding: 0.65rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            transition: background 0.25s ease, border-color 0.25s ease;
        }

        .topbar-btn {
            min-height: 44px;
            min-width: 44px;
            padding: 0.5rem 0.85rem;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: var(--bg-hover);
            border: 1px solid var(--border);
            color: var(--text-color);
            cursor: pointer;
            font-size: 0.825rem;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            box-sizing: border-box;
        }
        .topbar-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary-border);
            color: var(--primary);
        }

        .mobile-header {
            display: none;
        }

        /* Sidebar Backdrop */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(4px);
            z-index: 45;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-backdrop.active {
            display: block;
            opacity: 1;
        }

        /* Sidebar */
        .sidebar {
            width: 270px;
            min-width: 270px;
            background: var(--bg-sidebar) !important;
            backdrop-filter: blur(16px);
            border-right: 1px solid var(--border) !important;
            padding: 1.25rem 1rem 1rem 1rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: sticky;
            top: 0;
            height: 100vh;
            max-height: 100vh;
            z-index: 50;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                        min-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                        padding 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                        margin 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                        opacity 0.25s ease, 
                        transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                        background 0.25s ease, 
                        border-color 0.25s ease;
        }

        .sidebar-scroll-area {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            margin-top: 0.25rem;
            margin-bottom: 0.5rem;
            padding-right: 0.2rem;
            scrollbar-width: thin;
            scrollbar-color: var(--border) transparent;
        }

        .sidebar-scroll-area::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll-area::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll-area::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 9999px;
        }
        .sidebar-scroll-area::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

        /* Desktop Collapsible Sidebar State (PC / Tablet >= 1024px) */
        @media (min-width: 1024px) {
            html.sidebar-collapsed-init .sidebar,
            .app-container.sidebar-collapsed .sidebar {
                width: 0 !important;
                min-width: 0 !important;
                max-width: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                margin: 0 !important;
                border-right-width: 0 !important;
                opacity: 0 !important;
                overflow: hidden !important;
                pointer-events: none !important;
                transform: translateX(-100%) !important;
            }

            .sidebar-close-btn {
                display: none !important;
            }
        }

        .sidebar-title {
            font-size: 1.35rem;
            font-weight: 800;
            margin-bottom: 1.25rem;
            color: var(--primary);
            letter-spacing: -0.025em;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-close-btn {
            display: none;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.375rem;
        }
        .sidebar-close-btn:hover {
            color: var(--text-heading);
            background: var(--bg-hover);
        }

        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            flex: 1;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.85rem;
            border-radius: 0.625rem;
            color: var(--text-muted) !important;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .nav-link svg.nav-icon {
            width: 1.2rem;
            height: 1.2rem;
            flex-shrink: 0;
            opacity: 0.85;
            transition: transform 0.2s ease, opacity 0.2s ease, color 0.2s ease;
        }

        .nav-link:hover svg.nav-icon {
            opacity: 1;
            transform: scale(1.08);
        }

        .nav-link:hover {
            background: var(--bg-hover) !important;
            color: var(--text-heading) !important;
        }

        .nav-link.active {
            background: var(--bg-active) !important;
            color: var(--primary) !important;
            font-weight: 600;
        }

        .nav-link.active svg.nav-icon {
            color: var(--primary) !important;
            opacity: 1;
        }

        /* Submenu */
        .nav-item-has-submenu {
            position: relative;
        }

        .submenu-trigger {
            justify-content: space-between;
            user-select: none;
        }

        .submenu-trigger .trigger-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .submenu-trigger .arrow {
            font-size: 0.65rem;
            transition: transform 0.3s ease;
            opacity: 0.5;
            margin-left: 0.5rem;
        }

        .nav-item-has-submenu.open > .submenu-trigger .arrow {
            transform: rotate(180deg);
            opacity: 0.8;
        }

        .nav-item-has-submenu.open > .submenu-trigger {
            color: var(--text-heading) !important;
        }

        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            margin-left: 1.25rem;
            padding-left: 0.75rem;
            border-left: 2px solid var(--primary-border);
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height 0.35s ease, opacity 0.25s ease, margin-top 0.25s ease;
            margin-top: 0;
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .nav-item-has-submenu.open > .submenu {
            max-height: 800px;
            opacity: 1;
            margin-top: 0.35rem;
            margin-bottom: 0.35rem;
        }

        .submenu .nav-link {
            padding: 0.45rem 0.65rem;
            font-size: 0.825rem;
            gap: 0.6rem;
            border-radius: 0.5rem;
        }

        .submenu .nav-link svg.nav-icon {
            width: 1rem;
            height: 1rem;
            opacity: 0.75;
        }

        .submenu .nav-link.active {
            background: var(--bg-active) !important;
            color: var(--primary) !important;
        }

        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow-x: hidden;
            background-color: var(--bg-color);
        }

        .main-content {
            flex: 1;
            padding: 1.75rem;
            overflow-y: auto;
            min-width: 0;
        }

        /* Cards & Form Components */
        .card {
            background: var(--bg-card) !important;
            backdrop-filter: blur(12px);
            border: 1px solid var(--border) !important;
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.75rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.25s ease;
        }

        .card:hover {
            border-color: var(--primary-border) !important;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .page-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin: 0;
            color: var(--text-heading) !important;
            letter-spacing: -0.03em;
        }

        @media (min-width: 768px) {
            .page-title {
                font-size: 2.15rem;
            }
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label, label.form-label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .form-group input,
        .form-group select,
        .form-group textarea,
        .form-control {
            width: 100%;
            padding: 0.65rem 0.95rem;
            background: var(--bg-input) !important;
            border: 1px solid var(--border) !important;
            border-radius: 0.625rem;
            color: var(--text-color) !important;
            font-size: 0.925rem;
            font-family: inherit;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus,
        .form-control:focus {
            outline: none;
            border-color: var(--primary) !important;
            background: var(--bg-input-focus) !important;
            box-shadow: 0 0 0 3px var(--primary-light) !important;
        }

        select option {
            background-color: var(--bg-surface) !important;
            color: var(--text-color) !important;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 0.625rem;
            font-weight: 600;
            font-size: 0.875rem;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            box-sizing: border-box;
        }
        .btn:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background: var(--primary) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px var(--primary-light);
        }
        .btn-primary:hover {
            background: var(--primary-hover) !important;
            box-shadow: 0 6px 16px var(--primary-light);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--bg-hover) !important;
            border-color: var(--border) !important;
            color: var(--text-color) !important;
        }
        .btn-secondary:hover {
            background: var(--border) !important;
            color: var(--text-heading) !important;
        }

        .btn-danger {
            background: var(--danger) !important;
            color: #ffffff !important;
        }
        .btn-danger:hover {
            background: var(--danger-hover) !important;
        }

        .btn-success {
            background: var(--success) !important;
            color: #ffffff !important;
        }
        .btn-success:hover {
            background: var(--success-hover) !important;
        }

        .btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 0.5rem;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
        }
        .badge-role, .badge-primary {
            background: var(--primary-light) !important;
            color: var(--primary) !important;
            border: 1px solid var(--primary-border) !important;
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
            border-radius: 0.85rem;
            border: 1px solid var(--border) !important;
            background: var(--bg-card) !important;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: var(--bg-hover) !important;
            padding: 0.85rem 1rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted) !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border) !important;
            white-space: nowrap;
        }

        td {
            padding: 0.85rem 1rem;
            border-top: 1px solid var(--border-light) !important;
            font-size: 0.9rem;
            color: var(--text-color) !important;
        }

        tbody tr {
            transition: background 0.15s ease;
        }
        tbody tr:hover {
            background: var(--bg-hover) !important;
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-success {
            background: var(--success-light) !important;
            color: var(--success) !important;
            border: 1px solid var(--success-border) !important;
        }
        .alert-error {
            background: var(--danger-light) !important;
            color: var(--danger) !important;
            border: 1px solid var(--danger-border) !important;
        }

        /* Theme Toggle Button */
        .theme-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.45rem 0.75rem;
            background: var(--bg-hover);
            border: 1px solid var(--border);
            border-radius: 0.625rem;
            color: var(--text-color);
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .theme-toggle-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary-border);
            color: var(--primary);
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .mobile-header {
                display: flex;
            }

            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                height: 100vh;
                transform: translateX(-100%);
                box-shadow: var(--shadow-lg);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .sidebar-close-btn {
                display: block;
            }

            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    @auth
        @php /** @var \App\Models\User $user */ $user = Auth::user(); @endphp
        <div class="app-container">
            <!-- Modularized Responsive Sidebar Component -->
            @include('layouts.sidebar')

            <!-- Main Content Area with Responsive Unified Topbar -->
            <div class="main-wrapper">
                <header class="app-topbar">
                    <!-- Left: Menu Toggle Button (for all screens: Desktop collapse / Mobile drawer) -->
                    <div class="flex items-center gap-3">
                        <button type="button" 
                                id="mobile-menu-toggle" 
                                onclick="toggleSidebarMenu()" 
                                class="topbar-btn" 
                                aria-label="Mostrar u ocultar menú lateral"
                                title="Mostrar / Ocultar menú lateral (Ctrl+B)">
                            <svg class="w-5 h-5 text-slate-700 dark:text-slate-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <span id="sidebar-toggle-label" class="text-xs font-black hidden sm:inline text-slate-800 dark:text-slate-100">
                                Menú
                            </span>
                            <span id="sidebar-toggle-badge" class="hidden text-[10px] font-bold px-1.5 py-0.5 rounded bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                                Oculto
                            </span>
                        </button>

                        <div class="hidden sm:flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-300 dark:text-slate-700">|</span>
                            <span class="text-sm font-black text-slate-900 dark:text-white tracking-tight">
                                GR Intranet EDU
                            </span>
                        </div>
                    </div>

                    <!-- Center / Mobile Branding -->
                    <div class="sm:hidden font-black text-xs text-slate-900 dark:text-white tracking-tight">
                        GR Intranet EDU
                    </div>

                    <!-- Right: Shortcuts, Theme Switch & Profile -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Quick Shortcut to Gestor de Salidas -->
                        @canany(['salidas.view', 'salidas.create'])
                        <a href="{{ route('salidas.index') }}" 
                           class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800/80 transition"
                           title="Gestor de Salidas (Pases de Aula)">
                            <span>🚪</span>
                            <span class="hidden md:inline">Salidas</span>
                        </a>
                        @endcanany

                        <!-- Quick Shortcut to Parte de Hoy (Live) -->
                        @can('guardias.view')
                        <a href="{{ route('guardias.parte') }}" 
                           class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900/50 border border-sky-200 dark:border-sky-800/80 transition"
                           title="Ir al Parte de Guardia en vivo">
                            <span>🛡️</span>
                            <span class="hidden md:inline">Parte de Hoy</span>
                        </a>
                        @endcan

                        <!-- Global Theme Toggle Button (Thumb-friendly >= 44px) -->
                        <button type="button" onclick="toggleTheme()" class="topbar-btn" title="Cambiar modo claro / oscuro" aria-label="Alternar tema claro y oscuro">
                            <svg class="theme-sun-icon w-5 h-5 text-amber-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg class="theme-moon-icon w-5 h-5 text-sky-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <!-- User Profile Pill -->
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 pl-1 text-decoration-none group" title="Ver mi perfil">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700 group-hover:scale-105 transition-transform duration-200">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-600 text-white font-extrabold text-xs flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform duration-200">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="hidden lg:block text-left leading-tight pr-1">
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate max-w-[120px] group-hover:text-sky-500 transition-colors">
                                    {{ $user->name }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-medium">
                                    {{ $user->getRoleNames()->first() ?: 'Docente' }}
                                </div>
                            </div>
                        </a>
                    </div>
                </header>

                <main class="main-content">
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <main class="main-content" style="background-color: var(--bg-color); min-height: 100vh; padding: 0;">
            @yield('content')
        </main>
    @endauth

    <script>
        // Synchronize and update Sun/Moon Icons
        function updateThemeIcons() {
            const isLight = document.documentElement.classList.contains('light') || document.documentElement.getAttribute('data-theme') === 'light';
            document.querySelectorAll('.theme-sun-icon').forEach(el => {
                el.classList.toggle('hidden', isLight);
            });
            document.querySelectorAll('.theme-moon-icon').forEach(el => {
                el.classList.toggle('hidden', !isLight);
            });
        }

        // Global Theme Toggle Function
        function toggleTheme() {
            const isLight = document.documentElement.classList.contains('light') || document.documentElement.getAttribute('data-theme') === 'light';
            const newTheme = isLight ? 'dark' : 'light';
            
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(newTheme);
            document.documentElement.setAttribute('data-theme', newTheme);
            
            if (document.body) {
                document.body.classList.remove('light', 'dark');
                document.body.classList.add(newTheme);
            }
            
            localStorage.setItem('theme', newTheme);
            updateThemeIcons();
        }

        // Unified Sidebar Menu Toggle (Mobile Drawer vs Desktop Collapse)
        function toggleSidebarMenu() {
            if (window.innerWidth < 1024) {
                const sidebar = document.getElementById('main-sidebar');
                const isOpen = sidebar && sidebar.classList.contains('mobile-open');
                if (isOpen) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else {
                toggleDesktopSidebar();
            }
        }

        function openMobileSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar) sidebar.classList.add('mobile-open');
            if (backdrop) backdrop.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar) sidebar.classList.remove('mobile-open');
            if (backdrop) backdrop.classList.remove('active');
            document.body.style.overflow = '';
        }

        function toggleDesktopSidebar() {
            const container = document.querySelector('.app-container');
            if (!container) return;

            document.documentElement.classList.remove('sidebar-collapsed-init');
            const isCurrentlyCollapsed = container.classList.contains('sidebar-collapsed');

            if (isCurrentlyCollapsed) {
                container.classList.remove('sidebar-collapsed');
                try { localStorage.setItem('sidebar_collapsed', 'false'); } catch(e) {}
            } else {
                container.classList.add('sidebar-collapsed');
                try { localStorage.setItem('sidebar_collapsed', 'true'); } catch(e) {}
            }
            updateSidebarUI();
        }

        function updateSidebarUI() {
            const container = document.querySelector('.app-container');
            const isCollapsed = container && container.classList.contains('sidebar-collapsed');
            const badge = document.getElementById('sidebar-toggle-badge');
            const label = document.getElementById('sidebar-toggle-label');
            if (badge) {
                badge.style.display = (isCollapsed && window.innerWidth >= 1024) ? 'inline-block' : 'none';
            }
            if (label && window.innerWidth >= 1024) {
                label.textContent = isCollapsed ? 'Mostrar Menú' : 'Menú';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateThemeIcons();

            // Initialize desktop sidebar collapse state from localStorage
            const container = document.querySelector('.app-container');
            if (container && window.innerWidth >= 1024) {
                if (localStorage.getItem('sidebar_collapsed') === 'true' || document.documentElement.classList.contains('sidebar-collapsed-init')) {
                    container.classList.add('sidebar-collapsed');
                }
                updateSidebarUI();
            }

            const backdrop = document.getElementById('sidebar-backdrop');
            if (backdrop) {
                backdrop.addEventListener('click', closeMobileSidebar);
            }

            // Keyboard shortcut Ctrl+B or Cmd+B to toggle sidebar on desktop
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
                    e.preventDefault();
                    toggleDesktopSidebar();
                }
                if (e.key === 'Escape') {
                    closeMobileSidebar();
                }
            });

            // Handle submenu toggle clicks
            document.querySelectorAll('.submenu-trigger').forEach(function(trigger) {
                trigger.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var parent = trigger.closest('.nav-item-has-submenu');
                    
                    // Close other open submenus (accordion behavior)
                    document.querySelectorAll('.nav-item-has-submenu.open').forEach(function(item) {
                        if (item !== parent) {
                            item.classList.remove('open');
                        }
                    });
                    
                    // Toggle current submenu
                    parent.classList.toggle('open');
                });
            });

            // Ensure submenus with active children stay open on load
            document.querySelectorAll('.submenu .nav-link.active').forEach(function(activeLink) {
                var parentSubmenu = activeLink.closest('.nav-item-has-submenu');
                if (parentSubmenu) {
                    parentSubmenu.classList.add('open');
                }
            });
        });

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(reg) {
                    console.log('Guardia PWA Service Worker activo:', reg.scope);
                }).catch(function(err) {
                    console.warn('Error registrando Service Worker:', err);
                });
            });
        }
    </script>
    @stack('scripts')
</body>

</html>