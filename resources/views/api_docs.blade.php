<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UnizFlow API Documentation</title>
  <style>
    :root {
      /* Primitive Color Tokens */
      --color-white: rgba(255, 255, 255, 1);
      --color-black: rgba(0, 0, 0, 1);
      --color-cream-50: rgba(252, 252, 249, 1);
      --color-cream-100: rgba(255, 255, 253, 1);
      --color-gray-200: rgba(245, 245, 245, 1);
      --color-gray-300: rgba(167, 169, 169, 1);
      --color-gray-400: rgba(119, 124, 124, 1);
      --color-slate-500: rgba(98, 108, 113, 1);
      --color-brown-600: rgba(94, 82, 64, 1);
      --color-charcoal-700: rgba(31, 33, 33, 1);
      --color-charcoal-800: rgba(38, 40, 40, 1);
      --color-slate-900: rgba(19, 52, 59, 1);
      --color-teal-300: rgba(50, 184, 198, 1);
      --color-teal-400: rgba(45, 166, 178, 1);
      --color-teal-500: rgba(60,90,118, 1);
      --color-teal-600: rgba(47, 76, 103, 1);
      --color-teal-700: rgba(26, 104, 115, 1);
      --color-teal-800: rgba(41, 150, 161, 1);
      --color-red-400: rgba(255, 84, 89, 1);
      --color-red-500: rgba(192, 21, 47, 1);
      --color-orange-400: rgba(230, 129, 97, 1);
      --color-orange-500: rgba(168, 75, 47, 1);

      --color-brown-600-rgb: 94, 82, 64;
      --color-teal-500-rgb: 60,90,118;
      --color-slate-900-rgb: 19, 52, 59;
      --color-slate-500-rgb: 98, 108, 113;
      --color-red-500-rgb: 192, 21, 47;
      --color-red-400-rgb: 255, 84, 89;
      --color-orange-500-rgb: 168, 75, 47;
      --color-orange-400-rgb: 230, 129, 97;

      --color-bg-1: rgba(59, 130, 246, 0.08);
      --color-bg-2: rgba(245, 158, 11, 0.08);
      --color-bg-3: rgba(34, 197, 94, 0.08);
      --color-bg-4: rgba(239, 68, 68, 0.08);
      --color-bg-5: rgba(147, 51, 234, 0.08);
      --color-bg-6: rgba(249, 115, 22, 0.08);
      --color-bg-7: rgba(236, 72, 153, 0.08);
      --color-bg-8: rgba(6, 182, 212, 0.08);

      --color-background: var(--color-cream-50);
      --color-surface: var(--color-cream-100);
      --color-text: var(--color-slate-900);
      --color-text-secondary: var(--color-slate-500);
      --color-primary: var(--color-teal-500);
      --color-primary-hover: var(--color-teal-600);
      --color-primary-active: var(--color-teal-700);
      --color-secondary: rgba(var(--color-brown-600-rgb), 0.12);
      --color-secondary-hover: rgba(var(--color-brown-600-rgb), 0.2);
      --color-secondary-active: rgba(var(--color-brown-600-rgb), 0.25);
      --color-border: rgba(var(--color-brown-600-rgb), 0.2);
      --color-btn-primary-text: var(--color-cream-50);
      --color-card-border: rgba(var(--color-brown-600-rgb), 0.12);
      --color-card-border-inner: rgba(var(--color-brown-600-rgb), 0.12);
      --color-error: var(--color-red-500);
      --color-success: var(--color-teal-500);
      --color-warning: var(--color-orange-500);
      --color-info: var(--color-slate-500);
      --color-focus-ring: rgba(var(--color-teal-500-rgb), 0.4);
      --color-select-caret: rgba(var(--color-slate-900-rgb), 0.8);

      --focus-ring: 0 0 0 3px var(--color-focus-ring);
      --focus-outline: 2px solid var(--color-primary);

      --color-success-rgb: 33, 128, 141;
      --color-error-rgb: 192, 21, 47;
      --color-warning-rgb: 168, 75, 47;
      --color-info-rgb: 98, 108, 113;

      --font-family-base: "FKGroteskNeue", "Geist", "Inter", -apple-system,
        BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      --font-family-mono: "Berkeley Mono", ui-monospace, SFMono-Regular, Menlo,
        Monaco, Consolas, monospace;
      --font-size-xs: 11px;
      --font-size-sm: 12px;
      --font-size-base: 14px;
      --font-size-md: 14px;
      --font-size-lg: 16px;
      --font-size-xl: 18px;
      --font-size-2xl: 20px;
      --font-size-3xl: 24px;
      --font-size-4xl: 30px;
      --font-weight-normal: 400;
      --font-weight-medium: 500;
      --font-weight-semibold: 550;
      --font-weight-bold: 600;
      --line-height-tight: 1.2;
      --line-height-normal: 1.5;
      --letter-spacing-tight: -0.01em;

      --space-0: 0;
      --space-1: 1px;
      --space-2: 2px;
      --space-4: 4px;
      --space-6: 6px;
      --space-8: 8px;
      --space-10: 10px;
      --space-12: 12px;
      --space-16: 16px;
      --space-20: 20px;
      --space-24: 24px;
      --space-32: 32px;

      --radius-sm: 6px;
      --radius-base: 8px;
      --radius-md: 10px;
      --radius-lg: 12px;
      --radius-full: 9999px;

      --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.02);
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.04),
        0 1px 2px rgba(0, 0, 0, 0.02);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.04),
        0 2px 4px -1px rgba(0, 0, 0, 0.02);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.04),
        0 4px 6px -2px rgba(0, 0, 0, 0.02);
      --shadow-inset-sm: inset 0 1px 0 rgba(255, 255, 255, 0.15),
        inset 0 -1px 0 rgba(0, 0, 0, 0.03);

      --duration-fast: 150ms;
      --duration-normal: 250ms;
      --ease-standard: cubic-bezier(0.16, 1, 0.3, 1);
    }

    @media (prefers-color-scheme: dark) {
      :root {
        --color-gray-400-rgb: 119, 124, 124;
        --color-teal-300-rgb: 50, 184, 198;
        --color-gray-300-rgb: 167, 169, 169;
        --color-gray-200-rgb: 245, 245, 245;

        --color-bg-1: rgba(29, 78, 216, 0.15);
        --color-bg-2: rgba(180, 83, 9, 0.15);
        --color-bg-3: rgba(21, 128, 61, 0.15);
        --color-bg-4: rgba(185, 28, 28, 0.15);
        --color-bg-5: rgba(107, 33, 168, 0.15);
        --color-bg-6: rgba(194, 65, 12, 0.15);
        --color-bg-7: rgba(190, 24, 93, 0.15);
        --color-bg-8: rgba(8, 145, 178, 0.15);

        --color-background: var(--color-charcoal-700);
        --color-surface: var(--color-charcoal-800);
        --color-text: var(--color-gray-200);
        --color-text-secondary: rgba(var(--color-gray-300-rgb), 0.7);
        --color-primary: var(--color-teal-300);
        --color-primary-hover: var(--color-teal-400);
        --color-primary-active: var(--color-teal-800);
        --color-secondary: rgba(var(--color-gray-400-rgb), 0.15);
        --color-secondary-hover: rgba(var(--color-gray-400-rgb), 0.25);
        --color-secondary-active: rgba(var(--color-gray-400-rgb), 0.3);
        --color-border: rgba(var(--color-gray-400-rgb), 0.3);
        --color-error: var(--color-red-400);
        --color-success: var(--color-teal-300);
        --color-warning: var(--color-orange-400);
        --color-info: var(--color-gray-300);
        --color-focus-ring: rgba(var(--color-teal-300-rgb), 0.4);
        --color-btn-primary-text: var(--color-slate-900);
        --color-card-border: rgba(var(--color-gray-400-rgb), 0.2);
        --color-card-border-inner: rgba(var(--color-gray-400-rgb), 0.15);
        --shadow-inset-sm: inset 0 1px 0 rgba(255, 255, 255, 0.1),
          inset 0 -1px 0 rgba(0, 0, 0, 0.15);
        --color-select-caret: rgba(var(--color-gray-200-rgb), 0.8);

        --color-success-rgb: var(--color-teal-300-rgb);
        --color-error-rgb: var(--color-red-400-rgb);
        --color-warning-rgb: var(--color-orange-400-rgb);
        --color-info-rgb: var(--color-gray-300-rgb);
      }
    }

    @font-face {
      font-family: "FKGroteskNeue";
      src: url("../../../../fonts/FKGroteskNeue.woff2") format("woff2");
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: var(--font-family-base);
      background-color: var(--color-background);
      color: var(--color-text);
      line-height: var(--line-height-normal);
      font-size: var(--font-size-base);
    }

    .app-container {
      display: flex;
      min-height: 100vh;
    }

    /* Header */
    .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 64px;
      background-color: var(--color-surface);
      border-bottom: 1px solid var(--color-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 var(--space-24);
      z-index: 100;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: var(--space-12);
      height: 100%;
    }

    .logo {
      font-size: var(--font-size-2xl);
      font-weight: var(--font-weight-bold);
      color: var(--color-primary);
      letter-spacing: var(--letter-spacing-tight);
      height: 100%;
    }

    .header-logo img{
      height: 100%;
    }

    .header-subtitle {
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
      padding-left: var(--space-12);
      border-left: 1px solid var(--color-border);
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: var(--space-16);
    }

    .stats-badge {
      background: var(--color-bg-1);
      padding: var(--space-6) var(--space-12);
      border-radius: var(--radius-full);
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
      color: var(--color-text);
    }

    .theme-toggle {
      background: var(--color-secondary);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-base);
      padding: var(--space-8);
      cursor: pointer;
      transition: all var(--duration-fast) var(--ease-standard);
      display: flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
    }

    .theme-toggle:hover {
      background: var(--color-secondary-hover);
    }

    .mobile-menu-btn {
      display: none;
      background: var(--color-secondary);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-base);
      padding: var(--space-8);
      cursor: pointer;
      width: 36px;
      height: 36px;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 64px;
      left: 0;
      width: 280px;
      height: calc(100vh - 64px);
      background-color: var(--color-surface);
      border-right: 1px solid var(--color-border);
      overflow-y: auto;
      padding: var(--space-24);
    }

    .sidebar-section {
      margin-bottom: var(--space-32);
    }

    .sidebar-title {
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-bold);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--color-text-secondary);
      margin-bottom: var(--space-12);
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: var(--space-12);
      padding: var(--space-10) var(--space-12);
      border-radius: var(--radius-base);
      cursor: pointer;
      transition: all var(--duration-fast) var(--ease-standard);
      margin-bottom: var(--space-4);
      font-size: var(--font-size-base);
      color: var(--color-text);
      text-decoration: none;
      border: 1px solid transparent;
    }

    .nav-item:hover {
      background: var(--color-secondary);
      border-color: var(--color-card-border);
    }

    .nav-item.active {
      background: var(--color-bg-1);
      border-color: var(--color-primary);
      color: var(--color-primary);
      font-weight: var(--font-weight-medium);
    }

    .nav-icon {
      width: 20px;
      height: 20px;
      flex-shrink: 0;
    }

    .method-badge {
      display: inline-block;
      padding: var(--space-2) var(--space-8);
      border-radius: var(--radius-sm);
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-bold);
      font-family: var(--font-family-mono);
      margin-left: auto;
    }

    .method-post {
      background: rgba(34, 197, 94, 0.15);
      color: #16a34a;
    }

    /* Main Content */
    .main-content {
      margin-left: 280px;
      margin-top: 64px;
      padding: var(--space-32);
      flex: 1;
      max-width: 1200px;
    }

    .content-section {
      display: none;
    }

    .content-section.active {
      display: block;
    }

    .section-header {
      margin-bottom: var(--space-32);
    }

    .section-title {
      font-size: var(--font-size-4xl);
      font-weight: var(--font-weight-bold);
      margin-bottom: var(--space-8);
      letter-spacing: var(--letter-spacing-tight);
    }

    .section-description {
      font-size: var(--font-size-lg);
      color: var(--color-text-secondary);
      margin-bottom: var(--space-16);
    }

    .endpoint-url {
      display: flex;
      align-items: center;
      gap: var(--space-12);
      padding: var(--space-16);
      background: var(--color-bg-2);
      border: 1px solid var(--color-card-border);
      border-radius: var(--radius-base);
      font-family: var(--font-family-mono);
      font-size: var(--font-size-sm);
      margin-bottom: var(--space-24);
    }

    .endpoint-method {
      padding: var(--space-4) var(--space-10);
      background: rgba(34, 197, 94, 0.2);
      color: #16a34a;
      border-radius: var(--radius-sm);
      font-weight: var(--font-weight-bold);
      font-size: var(--font-size-xs);
    }

    /* Cards */
    .card {
      background: var(--color-surface);
      border: 1px solid var(--color-card-border);
      border-radius: var(--radius-lg);
      padding: var(--space-24);
      margin-bottom: var(--space-24);
    }

    .card-title {
      font-size: var(--font-size-xl);
      font-weight: var(--font-weight-semibold);
      margin-bottom: var(--space-16);
      display: flex;
      align-items: center;
      gap: var(--space-8);
    }

    .card-icon {
      width: 24px;
      height: 24px;
      color: var(--color-primary);
    }

    /* Tables */
    .params-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: var(--space-16);
    }

    .params-table th {
      text-align: left;
      padding: var(--space-12);
      background: var(--color-secondary);
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-semibold);
      border-bottom: 1px solid var(--color-border);
    }

    .params-table td {
      padding: var(--space-12);
      border-bottom: 1px solid var(--color-card-border-inner);
      font-size: var(--font-size-sm);
    }

    .param-name {
      font-family: var(--font-family-mono);
      color: var(--color-primary);
      font-weight: var(--font-weight-medium);
    }

    .param-type {
      font-family: var(--font-family-mono);
      color: var(--color-text-secondary);
      font-size: var(--font-size-xs);
      background: var(--color-secondary);
      padding: var(--space-2) var(--space-6);
      border-radius: var(--radius-sm);
    }

    .param-required {
      color: var(--color-error);
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-medium);
    }

    .param-optional {
      color: var(--color-text-secondary);
      font-size: var(--font-size-xs);
    }

    /* Code Blocks */
    .code-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: var(--space-12) var(--space-16);
      background: var(--color-secondary);
      border-bottom: 1px solid var(--color-border);
      border-radius: var(--radius-base) var(--radius-base) 0 0;
    }

    .code-lang {
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-medium);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--color-text-secondary);
    }

    .copy-btn {
      background: var(--color-primary);
      color: var(--color-btn-primary-text);
      border: none;
      padding: var(--space-6) var(--space-12);
      border-radius: var(--radius-sm);
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-medium);
      cursor: pointer;
      transition: all var(--duration-fast) var(--ease-standard);
      display: flex;
      align-items: center;
      gap: var(--space-6);
    }

    .copy-btn:hover {
      background: var(--color-primary-hover);
    }

    .copy-btn.copied {
      background: var(--color-success);
    }

    .code-block {
      background: var(--color-charcoal-800);
      padding: var(--space-16);
      border-radius: 0 0 var(--radius-base) var(--radius-base);
      overflow-x: auto;
      font-family: var(--font-family-mono);
      font-size: var(--font-size-sm);
      line-height: 1.6;
      color: var(--color-gray-200);
    }

    .code-block pre {
      margin: 0;
    }

    /* Tabs */
    .tabs {
      display: flex;
      gap: var(--space-4);
      border-bottom: 1px solid var(--color-border);
      margin-bottom: var(--space-16);
    }

    .tab {
      padding: var(--space-10) var(--space-16);
      background: transparent;
      border: none;
      border-bottom: 2px solid transparent;
      color: var(--color-text-secondary);
      cursor: pointer;
      font-size: var(--font-size-base);
      font-weight: var(--font-weight-medium);
      transition: all var(--duration-fast) var(--ease-standard);
    }

    .tab:hover {
      color: var(--color-text);
    }

    .tab.active {
      color: var(--color-primary);
      border-bottom-color: var(--color-primary);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    /* Info Box */
    .info-box {
      padding: var(--space-16);
      border-radius: var(--radius-base);
      margin-bottom: var(--space-16);
      border-left: 3px solid;
    }

    .info-box.success {
      background: rgba(var(--color-success-rgb), 0.1);
      border-left-color: var(--color-success);
    }

    .info-box.warning {
      background: rgba(var(--color-warning-rgb), 0.1);
      border-left-color: var(--color-warning);
    }

    .info-box.error {
      background: rgba(var(--color-error-rgb), 0.1);
      border-left-color: var(--color-error);
    }

    .info-box-title {
      font-weight: var(--font-weight-semibold);
      margin-bottom: var(--space-8);
      display: flex;
      align-items: center;
      gap: var(--space-8);
    }

    /* Auth Section */
    .auth-credential {
      display: flex;
      align-items: center;
      gap: var(--space-12);
      padding: var(--space-12);
      background: var(--color-bg-3);
      border: 1px solid var(--color-card-border);
      border-radius: var(--radius-base);
      margin-bottom: var(--space-12);
    }

    .auth-label {
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-semibold);
      min-width: 100px;
    }

    .auth-value {
      flex: 1;
      font-family: var(--font-family-mono);
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .auth-value.masked {
      filter: blur(4px);
      user-select: none;
    }

    .reveal-btn {
      background: var(--color-secondary);
      border: 1px solid var(--color-border);
      padding: var(--space-4) var(--space-10);
      border-radius: var(--radius-sm);
      font-size: var(--font-size-xs);
      cursor: pointer;
      transition: all var(--duration-fast) var(--ease-standard);
    }

    .reveal-btn:hover {
      background: var(--color-secondary-hover);
    }

    /* Status Codes */
    .status-code-list {
      list-style: none;
    }

    .status-code-item {
      display: flex;
      align-items: flex-start;
      gap: var(--space-12);
      padding: var(--space-12);
      margin-bottom: var(--space-8);
      border-radius: var(--radius-base);
      background: var(--color-bg-4);
    }

    .status-code {
      font-family: var(--font-family-mono);
      font-weight: var(--font-weight-bold);
      font-size: var(--font-size-sm);
      min-width: 50px;
    }

    .status-200 {
      color: #16a34a;
    }

    .status-400 {
      color: var(--color-warning);
    }

    .status-401 {
      color: var(--color-error);
    }

    .status-404 {
      color: var(--color-error);
    }

    .status-500 {
      color: var(--color-error);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
      .mobile-menu-btn {
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .sidebar {
        transform: translateX(-100%);
        transition: transform var(--duration-normal) var(--ease-standard);
        z-index: 99;
      }

      .sidebar.mobile-open {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
        padding: var(--space-16);
      }

      .header-subtitle {
        display: none;
      }

      .stats-badge {
        display: none;
      }

      .section-title {
        font-size: var(--font-size-2xl);
      }

      .endpoint-url {
        flex-direction: column;
        align-items: flex-start;
      }

      .params-table {
        font-size: var(--font-size-xs);
      }

      .params-table th,
      .params-table td {
        padding: var(--space-8);
      }
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    ::-webkit-scrollbar-track {
      background: var(--color-secondary);
    }

    ::-webkit-scrollbar-thumb {
      background: var(--color-border);
      border-radius: var(--radius-full);
    }

    ::-webkit-scrollbar-thumb:hover {
      background: var(--color-text-secondary);
    }
  </style>
</head>

<body>
  <div class="app-container">
    <!-- Header -->
    <header class="header">
      <div class="header-left">
        <div class="logo">
          <a href="/" class="header-logo">
            <img src="{!! env("logo"); !!}" alt="logo">
          </a>
        </div>
      </div>
      <!-- <div class="header-right">
        <div class="stats-badge">3 Endpoints</div>
        <button
          class="theme-toggle"
          id="themeToggle"
          aria-label="Toggle theme">
          <svg
            width="20"
            height="20"
            viewbox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2">
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
          </svg>
        </button>
        <button
          class="mobile-menu-btn"
          id="mobileMenuBtn"
          aria-label="Toggle menu">
          <svg
            width="20"
            height="20"
            viewbox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>
      </div> -->
    </header>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-section">
        <div class="sidebar-title">Getting Started</div>
        <a href="#" class="nav-item active" data-section="getting-started">
          <svg
            class="nav-icon"
            viewbox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
          Introduction
        </a>
        <a href="#" class="nav-item" data-section="generate-token">
          <svg
            class="nav-icon"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4" />
          </svg>

          Generate Token
        </a>
        <a href="#" class="nav-item" data-section="authentication">
          <svg
            class="nav-icon"
            viewbox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          Authentication
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-title">Endpoints</div>
        <a href="#" class="nav-item" data-section="upi-orders-create">
          <svg
            class="nav-icon"
            viewbox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2">
            <path
              d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
          </svg>
          Create UPI Order
          <span class="method-badge method-post">POST</span>
        </a>
        <a href="#" class="nav-item" data-section="check-status">
          <svg
            class="nav-icon"
            viewbox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
          </svg>
          Check Status
          <span class="method-badge method-post">POST</span>
        </a>
        <a href="#" class="nav-item" data-section="payout-initiate">
          <svg
            class="nav-icon"
            viewbox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <polyline points="19 12 12 19 5 12"></polyline>
          </svg>
          Initiate Payout
          <span class="method-badge method-post">POST</span>
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-title">Reference</div>
        <a href="#" class="nav-item" data-section="status-codes">
          <svg
            class="nav-icon"
            viewbox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2">
            <polyline points="9 11 12 14 22 4"></polyline>
            <path
              d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
          </svg>
          Status Codes
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Getting Started -->
      <section class="content-section active" id="getting-started">
        <div class="section-header">
          <h1 class="section-title">Getting Started with UnizFlow API</h1>
          <p class="section-description">
            UnizFlow provides a simple and secure payment gateway API for UPI
            transactions in India. Accept payments, check transaction status,
            and send payouts with ease.
          </p>
        </div>

        <div class="card">
          <h2 class="card-title">Base URL</h2>
          <div class="code-block">
            <pre>https://partner.unizflow.com/api/v1</pre>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">Quick Start</h2>
          <ol style="padding-left: 20px; line-height: 2">
            <li>
              Obtain your Merchant ID and authentication Token from UnizFlow
            </li>
            <li>Include required headers in all API requests</li>
            <li>Make POST requests with JSON body parameters</li>
            <li>Handle responses and check transaction status</li>
          </ol>
        </div>

        <div class="info-box success">
          <div class="info-box-title">
            <svg
              width="20"
              height="20"
              viewbox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            All endpoints use HTTPS
          </div>
          <p
            style="
                color: var(--color-text-secondary);
                font-size: var(--font-size-sm);
              ">
            All API requests are encrypted and secure. Generate token before authentication.
          </p>
        </div>
      </section>

      <!-- Generate Token -->
      <section class="content-section" id="generate-token">
        <div class="section-header">
          <h1 class="section-title">Generate Token</h1>
          <p class="section-description">
            Generate a UPI QR code and payment link to collect payments from
            customers.
          </p>
        </div>

        <div class="endpoint-url">
          <span class="endpoint-method">GET</span>
          <span>https://partner.unizflow.com/generate-token</span>
        </div>

        <div class="card">
          <h2 class="card-title">
            <svg
              class="card-icon"
              viewbox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Response (200 OK)
          </h2>
          <div class="code-header">
            <span class="code-lang">JSON Response</span>
            <button
              class="copy-btn"
              onclick="copyCode('create-response-code', this)">
              Copy
            </button>
          </div>
          <div class="code-block">
            <pre id="create-response-code">
{
    "status": "success",
    "error": false,
    "message": "Token generated successfully.",
    "token": "9Y2XEveYvCsgKfdQsHt6zzCT5AmVJhu8Cle6w1g7S..."
}</pre>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">Response Fields</h2>
          <table class="params-table">
            <thead>
              <tr>
                <th>Field</th>
                <th>Type</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="param-name">status</span></td>
                <td><span class="param-type">string</span></td>
                <td>API call status (success/error)</td>
              </tr>
              <tr>
                <td>
                  <span class="param-name">token</span>
                </td>
                <td><span class="param-type">string</span></td>
                <td>UnizFlow generated token</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="info-box success">
          <div class="info-box-title">Next Steps</div>
          <p
            style="
                color: var(--color-text-secondary);
                font-size: var(--font-size-sm);
              ">
            All API requests are encrypted and secure. Make sure to keep your authentication token confidential.
          </p>
        </div>
      </section>

      <!-- Authentication -->
      <section class="content-section" id="authentication">
        <div class="section-header">
          <h1 class="section-title">Authentication</h1>
          <p class="section-description">
            All API requests require authentication using your Merchant ID and
            Token in the request headers.
          </p>
        </div>

        <div class="card">
          <h2 class="card-title">
            <svg
              class="card-icon"
              viewbox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            Required Headers
          </h2>
          <table class="params-table">
            <thead>
              <tr>
                <th>Header</th>
                <th>Value</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="param-name">Merchant-id</span></td>
                <td><span class="param-type">string</span></td>
                <td>Your unique merchant identifier</td>
              </tr>
              <tr>
                <td><span class="param-name">Token</span></td>
                <td><span class="param-type">string</span></td>
                <td>Your authentication token (base64 encoded)</td>
              </tr>
              <tr>
                <td><span class="param-name">Content-Type</span></td>
                <td><span class="param-type">string</span></td>
                <td>Must be <code>application/json</code></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card">
          <h2 class="card-title">Your Credentials</h2>
          <div class="auth-credential">
            <div class="auth-label">Merchant ID:</div>
            <div class="auth-value" id="merchantId">RD7E01BCA1</div>
            <button
              class="copy-btn"
              onclick="copyToClipboard('merchantId', this)">
              Copy
            </button>
          </div>
          <div class="auth-credential">
            <div class="auth-label">Token:</div>
            <div class="auth-value masked" id="authToken">
              9Y2XEveYvCsgKfdQsHt6zzCT5AmVJhu8Cle6w1g7S...
            </div>
            <button
              class="reveal-btn"
              onclick="toggleMask('authToken', this)">
              Reveal
            </button>
            <button
              class="copy-btn"
              onclick="copyToClipboard('authToken', this)">
              Copy
            </button>
          </div>
        </div>

        <div class="info-box warning">
          <div class="info-box-title">
            <svg
              width="20"
              height="20"
              viewbox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <path
                d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
              <line x1="12" y1="9" x2="12" y2="13"></line>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            Keep your credentials secure
          </div>
          <p
            style="
                color: var(--color-text-secondary);
                font-size: var(--font-size-sm);
              ">
            Never expose your authentication token in client-side code or
            public repositories. Always make API calls from your backend
            server.
          </p>
        </div>
      </section>

      <!-- Create UPI Order -->
      <section class="content-section" id="upi-orders-create">
        <div class="section-header">
          <h1 class="section-title">Create UPI Order</h1>
          <p class="section-description">
            Generate a UPI QR code and payment link to collect payments from
            customers.
          </p>
        </div>

        <div class="endpoint-url">
          <span class="endpoint-method">POST</span>
          <span>https://partner.unizflow.com/api/v1/upi/orders/create</span>
        </div>

        <div class="card">
          <h2 class="card-title">
            <svg
              class="card-icon"
              viewbox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <path
                d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            Request Parameters
          </h2>
          <table class="params-table">
            <thead>
              <tr>
                <th>Parameter</th>
                <th>Type</th>
                <th>Required</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="param-name">name</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Customer's full name</td>
              </tr>
              <tr>
                <td><span class="param-name">phone</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Customer's phone number (10 digits)</td>
              </tr>
              <tr>
                <td><span class="param-name">amount</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Payment amount in rupees</td>
              </tr>
              <tr>
                <td><span class="param-name">email</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Customer's email address</td>
              </tr>
              <tr>
                <td><span class="param-name">merchantTxnId</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Unique transaction ID from your system</td>
              </tr>
              <tr>
                <td><span class="param-name">notes</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-optional">Optional</span></td>
                <td>Additional notes or order reference</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card">
          <h2 class="card-title">Request Example</h2>
          <div class="tabs">
            <button
              class="tab active"
              onclick="switchTab(event, 'create-json')">
              JSON
            </button>
            <button class="tab" onclick="switchTab(event, 'create-curl')">
              cURL
            </button>
          </div>
          <div class="tab-content active" id="create-json">
            <div class="code-header">
              <span class="code-lang">JSON Request Body</span>
              <button
                class="copy-btn"
                onclick="copyCode('create-json-code', this)">
                Copy
              </button>
            </div>
            <div class="code-block">
              <pre id="create-json-code">
{
    "name": "Madan",
    "phone": "7737770291",
    "amount": "100",
    "email": "madan.choudhary@jigrotech.com",
    "merchantTxnId": "order_12345678",
    "notes": "ParkPay"
}</pre>
            </div>
          </div>
          <div class="tab-content" id="create-curl">
            <div class="code-header">
              <span class="code-lang">cURL Command</span>
              <button
                class="copy-btn"
                onclick="copyCode('create-curl-code', this)">
                Copy
              </button>
            </div>
            <div class="code-block">
              <pre id="create-curl-code">
curl --location 'https://partner.unizflow.com/api/v1/upi/orders/create' \
--header 'Merchant-id: RD7E01BCA1' \
--header 'Token: 9Y2XEveYvCsgKfdQsHt6zwNt9Kb+CCi/Kk/...' \
--header 'Content-Type: application/json' \
--data-raw '{
    "name": "Madan",
    "phone": "7737770291",
    "amount": "100",
    "email": "madan.choudhary@jigrotech.com",
    "merchantTxnId": "order_12345678",
    "notes": "ParkPay"
}'</pre>
            </div>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">
            <svg
              class="card-icon"
              viewbox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Response (200 OK)
          </h2>
          <div class="code-header">
            <span class="code-lang">JSON Response</span>
            <button
              class="copy-btn"
              onclick="copyCode('create-response-code', this)">
              Copy
            </button>
          </div>
          <div class="code-block">
            <pre id="create-response-code">
{
    "status": "success",
    "error": false,
    "message": "QR Code Generated Successfully!",
    "data": {
        "transaction": {
            "txnStatus": "Pending",
            "txnid": "MRP6916C922772CE8",
            "txnReference": "MRP12345659"
        },
        "commercials": {
            "amount": "200",
            "charges": "10.62",
            "gst": "0.00"
        },
        "paymentData": {
            "intent": "",
            "paymentLink": "http://localhost:8081/payment/dbe8fd9f63d68521b590cac210d60b234f3f7e7a5ecca1619ccf7ec9817d6fd2"
        },
        "datetime": "2025-11-14 11:46:04"
    }
}</pre>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">Response Fields</h2>
          <table class="params-table">
            <thead>
              <tr>
                <th>Field</th>
                <th>Type</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="param-name">status</span></td>
                <td><span class="param-type">string</span></td>
                <td>API call status (success/error)</td>
              </tr>
              <tr>
                <td>
                  <span class="param-name">data.transaction.txnid</span>
                </td>
                <td><span class="param-type">string</span></td>
                <td>UnizFlow transaction ID for tracking</td>
              </tr>
              <tr>
                <td>
                  <span class="param-name">data.transaction.txnStatus</span>
                </td>
                <td><span class="param-type">string</span></td>
                <td>Transaction status (Pending, Success, Failed)</td>
              </tr>
              <tr>
                <td>
                  <span class="param-name">data.paymentData.paymentLink</span>
                </td>
                <td><span class="param-type">string</span></td>
                <td>Payment link to share with customer</td>
              </tr>
              <tr>
                <td>
                  <span class="param-name">data.commercials.charges</span>
                </td>
                <td><span class="param-type">string</span></td>
                <td>Transaction processing charges</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="info-box success">
          <div class="info-box-title">Next Steps</div>
          <p
            style="
                color: var(--color-text-secondary);
                font-size: var(--font-size-sm);
              ">
            After creating an order, share the
            <strong>paymentLink</strong> with your customer. Use the
            <strong>txnid</strong> to check payment status using the Check
            Status endpoint.
          </p>
        </div>
      </section>

      <!-- Check Status -->
      <section class="content-section" id="check-status">
        <div class="section-header">
          <h1 class="section-title">Check Transaction Status</h1>
          <p class="section-description">
            Retrieve the current status and details of a payment transaction.
          </p>
        </div>

        <div class="endpoint-url">
          <span class="endpoint-method">POST</span>
          <span>https://partner.unizflow.com/api/v1/orders/check-status</span>
        </div>

        <div class="card">
          <h2 class="card-title">
            <svg
              class="card-icon"
              viewbox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <path
                d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            Request Parameters
          </h2>
          <table class="params-table">
            <thead>
              <tr>
                <th>Parameter</th>
                <th>Type</th>
                <th>Required</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="param-name">txnid</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>UnizFlow transaction ID received from create order</td>
              </tr>
              <tr>
                <td><span class="param-name">merchantTxnId</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Your system's transaction ID</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card">
          <h2 class="card-title">Request Example</h2>
          <div class="tabs">
            <button
              class="tab active"
              onclick="switchTab(event, 'status-json')">
              JSON
            </button>
            <button class="tab" onclick="switchTab(event, 'status-curl')">
              cURL
            </button>
          </div>
          <div class="tab-content active" id="status-json">
            <div class="code-header">
              <span class="code-lang">JSON Request Body</span>
              <button
                class="copy-btn"
                onclick="copyCode('status-json-code', this)">
                Copy
              </button>
            </div>
            <div class="code-block">
              <pre id="status-json-code">
{
    "txnid": "MRP6908546E82D097",
    "merchantTxnId": "order_123456"
}</pre>
            </div>
          </div>
          <div class="tab-content" id="status-curl">
            <div class="code-header">
              <span class="code-lang">cURL Command</span>
              <button
                class="copy-btn"
                onclick="copyCode('status-curl-code', this)">
                Copy
              </button>
            </div>
            <div class="code-block">
              <pre id="status-curl-code">
curl --location 'https://partner.unizflow.com/api/v1/orders/check-status' \
--header 'Merchant-id: RD7E01BCA1' \
--header 'Token: 9Y2XEveYvCsgKfdQsHt6z9Pv2KErvlx...' \
--header 'Content-Type: application/json' \
--data '{
    "txnid": "MRP6908546E82D097",
    "merchantTxnId": "order_123456"
}'</pre>
            </div>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">
            <svg
              class="card-icon"
              viewbox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Response (200 OK)
          </h2>
          <div class="code-header">
            <span class="code-lang">JSON Response</span>
            <button
              class="copy-btn"
              onclick="copyCode('status-response-code', this)">
              Copy
            </button>
          </div>
          <div class="code-block">
            <pre id="status-response-code">
{
    "status": "success",
    "error": false,
    "message": "Transaction Found!",
    "data": {
        "transaction": {
            "name": "Madan",
            "phone": "7737770291",
            "txnType": "payin",
            "txnStatus": "Pending",
            "txnid": "MRP6916C922772CE8",
            "txnReference": "MRP12345659",
            "rrn": "NA"
        },
        "commercials": {
            "amount": "200.00",
            "charges": "10.62",
            "gst": "0.00"
        },
        "payeeDetails": {
            "name": "NA",
            "vpa": "NA"
        },
        "paymentData": {
            "intent": "",
            "paymentLink": "http://localhost:8081/payment/dbe8fd9f63d68521b590cac210d60b234f3f7e7a5ecca1619ccf7ec9817d6fd2"
        },
        "datetime": "2025-11-14 11:54:03"
    }
}</pre>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">Transaction Status Values</h2>
          <table class="params-table">
            <thead>
              <tr>
                <th>Status</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="param-name">Pending</span></td>
                <td>Payment initiated but not yet completed by customer</td>
              </tr>
              <tr>
                <td><span class="param-name">Success</span></td>
                <td>Payment completed successfully</td>
              </tr>
              <tr>
                <td><span class="param-name">Failed</span></td>
                <td>Payment failed or rejected</td>
              </tr>
              <tr>
                <td><span class="param-name">Cancelled</span></td>
                <td>Transaction cancelled by user or system</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="info-box warning">
          <div class="info-box-title">Polling Recommendations</div>
          <p
            style="
                color: var(--color-text-secondary);
                font-size: var(--font-size-sm);
              ">
            Poll this endpoint every 3-5 seconds to check for payment status
            updates. Stop polling once status changes from "Pending" to a
            final state (Success/Failed/Cancelled).
          </p>
        </div>
      </section>

      <!-- Initiate Payout -->
      <section class="content-section" id="payout-initiate">
        <div class="section-header">
          <h1 class="section-title">Initiate UPI Payout</h1>
          <p class="section-description">
            Send money to a customer's bank account via UPI payout.
          </p>
        </div>

        <div class="endpoint-url">
          <span class="endpoint-method">POST</span>
          <span>https://partner.unizflow.com/api/v1/upi/payout/initiate</span>
        </div>

        <div class="card">
          <h2 class="card-title">
            <svg
              class="card-icon"
              viewbox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <path
                d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            Request Parameters
          </h2>
          <table class="params-table">
            <thead>
              <tr>
                <th>Parameter</th>
                <th>Type</th>
                <th>Required</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="param-name">name</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Payee/Beneficiary name</td>
              </tr>
              <tr>
                <td><span class="param-name">phone</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Payee phone number (10 digits)</td>
              </tr>
              <tr>
                <td><span class="param-name">amount</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Payout amount in rupees</td>
              </tr>
              <tr>
                <td><span class="param-name">account_number</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Beneficiary bank account number</td>
              </tr>
              <tr>
                <td><span class="param-name">ifsc_code</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Bank IFSC code (11 characters)</td>
              </tr>
              <tr>
                <td><span class="param-name">merchantTxnId</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-required">Required</span></td>
                <td>Unique transaction ID from your system</td>
              </tr>
              <tr>
                <td><span class="param-name">notes</span></td>
                <td><span class="param-type">string</span></td>
                <td><span class="param-optional">Optional</span></td>
                <td>Additional notes or reference</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card">
          <h2 class="card-title">Request Example</h2>
          <div class="tabs">
            <button
              class="tab active"
              onclick="switchTab(event, 'payout-json')">
              JSON
            </button>
            <button class="tab" onclick="switchTab(event, 'payout-curl')">
              cURL
            </button>
          </div>
          <div class="tab-content active" id="payout-json">
            <div class="code-header">
              <span class="code-lang">JSON Request Body</span>
              <button
                class="copy-btn"
                onclick="copyCode('payout-json-code', this)">
                Copy
              </button>
            </div>
            <div class="code-block">
              <pre id="payout-json-code">
{
    "name": "Keshav Kumar",
    "phone": "7865783456",
    "amount": "5",
    "account_number": "108001506949",
    "ifsc_code": "ICIC0001790",
    "merchantTxnId": "MRP6905A7B85BE0AC",
    "notes": "Order no. 76"
}</pre>
            </div>
          </div>
          <div class="tab-content" id="payout-curl">
            <div class="code-header">
              <span class="code-lang">cURL Command</span>
              <button
                class="copy-btn"
                onclick="copyCode('payout-curl-code', this)">
                Copy
              </button>
            </div>
            <div class="code-block">
              <pre id="payout-curl-code">
curl --location 'https://partner.unizflow.com/api/v1/upi/payout/initiate' \
--header 'Merchant-id: RD7E01BCA1' \
--header 'Token: 9Y2XEveYvCsgKfdQsHt6zzyO6NiDCCsXDA...' \
--header 'Content-Type: application/json' \
--data '{
    "name": "Keshav Kumar",
    "phone": "7865783456",
    "amount": "5",
    "account_number": "108001506949",
    "ifsc_code": "ICIC0001790",
    "merchantTxnId": "MRP6905A7B85BE0AC",
    "notes": "Order no. 76"
}'</pre>
            </div>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">
            <svg
              class="card-icon"
              viewbox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Response (200 OK)
          </h2>
          <div class="code-header">
            <span class="code-lang">JSON Response</span>
            <button
              class="copy-btn"
              onclick="copyCode('payout-response-code', this)">
              Copy
            </button>
          </div>
          <div class="code-block">
            <pre id="payout-response-code">
{
    "status": "success",
    "error": false,
    "message": "Payout request initialized successfully!",
    "data": {
        "transaction": {
            "txnStatus": "Pending",
            "txnid": "MRP6916CCD565916D",
            "txnReference": "MRP6905A7B85BE0AC",
            "utr": "NA"
        },
        "commercials": {
            "amount": "5",
            "charges": "0.11",
            "gst": "0.00"
        }
    }
}</pre>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">Response Fields</h2>
          <table class="params-table">
            <thead>
              <tr>
                <th>Field</th>
                <th>Type</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <span class="param-name">data.transaction.txnid</span>
                </td>
                <td><span class="param-type">string</span></td>
                <td>UnizFlow payout transaction ID</td>
              </tr>
              <tr>
                <td><span class="param-name">data.transaction.utr</span></td>
                <td><span class="param-type">string</span></td>
                <td>
                  Unique Transaction Reference from bank (available after
                  completion)
                </td>
              </tr>
              <tr>
                <td>
                  <span class="param-name">data.transaction.txnStatus</span>
                </td>
                <td><span class="param-type">string</span></td>
                <td>Payout status (Pending, Success, Failed)</td>
              </tr>
              <tr>
                <td>
                  <span class="param-name">data.commercials.charges</span>
                </td>
                <td><span class="param-type">string</span></td>
                <td>Payout processing charges</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="info-box warning">
          <div class="info-box-title">Payout Processing Time</div>
          <p
            style="
                color: var(--color-text-secondary);
                font-size: var(--font-size-sm);
              ">
            Payouts are typically processed within 5-10 minutes. Use the Check
            Status endpoint with the returned <strong>txnid</strong> to track
            the payout status and get the UTR once completed.
          </p>
        </div>

        <div class="info-box error">
          <div class="info-box-title">Important: Verify Bank Details</div>
          <p
            style="
                color: var(--color-text-secondary);
                font-size: var(--font-size-sm);
              ">
            Always verify the beneficiary's account number and IFSC code
            before initiating a payout. Failed payouts due to incorrect
            details may still incur charges.
          </p>
        </div>
      </section>

      <!-- Status Codes -->
      <section class="content-section" id="status-codes">
        <div class="section-header">
          <h1 class="section-title">HTTP Status Codes &amp; Errors</h1>
          <p class="section-description">
            Understanding API response codes and error handling.
          </p>
        </div>

        <div class="card">
          <h2 class="card-title">HTTP Status Codes</h2>
          <ul class="status-code-list">
            <li class="status-code-item">
              <span class="status-code status-200">200</span>
              <div>
                <strong>OK - Success</strong>
                <p
                  style="
                      color: var(--color-text-secondary);
                      font-size: var(--font-size-sm);
                      margin-top: 4px;
                    ">
                  Request completed successfully. Check the response body for
                  data.
                </p>
              </div>
            </li>
            <li class="status-code-item">
              <span class="status-code status-400">400</span>
              <div>
                <strong>Bad Request</strong>
                <p
                  style="
                      color: var(--color-text-secondary);
                      font-size: var(--font-size-sm);
                      margin-top: 4px;
                    ">
                  Invalid request parameters or missing required fields.
                </p>
              </div>
            </li>
            <li class="status-code-item">
              <span class="status-code status-401">401</span>
              <div>
                <strong>Unauthorized</strong>
                <p
                  style="
                      color: var(--color-text-secondary);
                      font-size: var(--font-size-sm);
                      margin-top: 4px;
                    ">
                  Invalid or missing Merchant-id or Token in headers.
                </p>
              </div>
            </li>
            <li class="status-code-item">
              <span class="status-code status-404">404</span>
              <div>
                <strong>Not Found</strong>
                <p
                  style="
                      color: var(--color-text-secondary);
                      font-size: var(--font-size-sm);
                      margin-top: 4px;
                    ">
                  Transaction not found or invalid endpoint URL.
                </p>
              </div>
            </li>
            <li class="status-code-item">
              <span class="status-code status-500">500</span>
              <div>
                <strong>Internal Server Error</strong>
                <p
                  style="
                      color: var(--color-text-secondary);
                      font-size: var(--font-size-sm);
                      margin-top: 4px;
                    ">
                  Server error while processing request. Try again later or
                  contact support.
                </p>
              </div>
            </li>
          </ul>
        </div>

        <div class="card">
          <h2 class="card-title">Error Response Format</h2>
          <div class="code-header">
            <span class="code-lang">JSON Error Response</span>
            <button
              class="copy-btn"
              onclick="copyCode('error-response-code', this)">
              Copy
            </button>
          </div>
          <div class="code-block">
            <pre id="error-response-code">
{
    "status": "error",
    "error": true,
    "message": "Invalid merchant credentials",
    "data": null
}</pre>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">Best Practices for Error Handling</h2>
          <ol style="padding-left: 20px; line-height: 2">
            <li>
              Always check the <code>status</code> and
              <code>error</code> fields in the response
            </li>
            <li>
              Implement retry logic for 5xx errors with exponential backoff
            </li>
            <li>Log error messages for debugging and support</li>
            <li>
              Display user-friendly messages to customers for 4xx errors
            </li>
            <li>Monitor 401 errors - they indicate authentication issues</li>
            <li>Validate all input parameters before making API calls</li>
          </ol>
        </div>

        <div class="info-box warning">
          <div class="info-box-title">Rate Limiting</div>
          <p
            style="
                color: var(--color-text-secondary);
                font-size: var(--font-size-sm);
              ">
            The API has rate limits to ensure fair usage. If you receive rate
            limit errors, implement exponential backoff in your retry logic.
            Contact support if you need higher limits.
          </p>
        </div>
      </section>
    </main>
  </div>

  <script>
    // Navigation
    const navItems = document.querySelectorAll(".nav-item");
    const sections = document.querySelectorAll(".content-section");
    const sidebar = document.getElementById("sidebar");
    const mobileMenuBtn = document.getElementById("mobileMenuBtn");

    navItems.forEach((item) => {
      item.addEventListener("click", (e) => {
        e.preventDefault();
        const sectionId = item.dataset.section;

        // Update active nav item
        navItems.forEach((nav) => nav.classList.remove("active"));
        item.classList.add("active");

        // Show corresponding section
        sections.forEach((section) => section.classList.remove("active"));
        document.getElementById(sectionId).classList.add("active");

        // Close mobile menu
        sidebar.classList.remove("mobile-open");

        // Scroll to top
        window.scrollTo({
          top: 0,
          behavior: "smooth"
        });
      });
    });

    // Mobile menu toggle
    mobileMenuBtn.addEventListener("click", () => {
      sidebar.classList.toggle("mobile-open");
    });

    // Theme toggle
    const themeToggle = document.getElementById("themeToggle");
    let currentTheme = "light";

    // Check system preference
    if (
      window.matchMedia &&
      window.matchMedia("(prefers-color-scheme: dark)").matches
    ) {
      currentTheme = "dark";
    }

    themeToggle.addEventListener("click", () => {
      currentTheme = currentTheme === "light" ? "dark" : "light";
      document.documentElement.setAttribute(
        "data-color-scheme",
        currentTheme
      );
    });

    // Tab switching
    function switchTab(event, tabId) {
      const tabButton = event.target;
      const tabContainer = tabButton.parentElement;
      const contentContainer = tabContainer.nextElementSibling.parentElement;

      // Update tab buttons
      tabContainer.querySelectorAll(".tab").forEach((tab) => {
        tab.classList.remove("active");
      });
      tabButton.classList.add("active");

      // Update tab content
      contentContainer.querySelectorAll(".tab-content").forEach((content) => {
        content.classList.remove("active");
      });
      document.getElementById(tabId).classList.add("active");
    }

    // Copy to clipboard
    function copyToClipboard(elementId, button) {
      const element = document.getElementById(elementId);
      const text = element.textContent || element.innerText;

      navigator.clipboard.writeText(text).then(() => {
        const originalText = button.textContent;
        button.textContent = "Copied!";
        button.classList.add("copied");

        setTimeout(() => {
          button.textContent = originalText;
          button.classList.remove("copied");
        }, 2000);
      });
    }

    // Copy code block
    function copyCode(codeId, button) {
      const codeBlock = document.getElementById(codeId);
      const text = codeBlock.textContent;

      navigator.clipboard.writeText(text).then(() => {
        button.textContent = "Copied!";
        button.classList.add("copied");

        setTimeout(() => {
          button.textContent = "Copy";
          button.classList.remove("copied");
        }, 2000);
      });
    }

    // Toggle mask for sensitive data
    function toggleMask(elementId, button) {
      const element = document.getElementById(elementId);
      element.classList.toggle("masked");
      button.textContent = element.classList.contains("masked") ?
        "Reveal" :
        "Hide";
    }

    // Make functions available globally
    window.switchTab = switchTab;
    window.copyToClipboard = copyToClipboard;
    window.copyCode = copyCode;
    window.toggleMask = toggleMask;
  </script>
</body>

</html>