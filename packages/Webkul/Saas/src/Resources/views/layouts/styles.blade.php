<style>
    :root {
        --bg: #f6f7f9;
        --surface: #ffffff;
        --surface-muted: #f9fafb;
        --surface-hover: #f3f4f6;
        --border: #e5e7eb;
        --border-strong: #d1d5db;
        --text: #1f2937;
        --text-soft: #4b5563;
        --muted: #6b7280;
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --primary-soft: #eff6ff;
        --success: #059669;
        --success-soft: #ecfdf5;
        --warning: #b45309;
        --warning-soft: #fffbeb;
        --danger: #dc2626;
        --danger-soft: #fef2f2;
        --info: #0369a1;
        --info-soft: #f0f9ff;
        --purple: #7c3aed;
        --purple-soft: #f5f3ff;
        --radius: 8px;
        --radius-lg: 12px;
        --header-height: 64px;
        --sidebar-width: 252px;
        --shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 1px 3px rgba(16, 24, 40, .06);
        --shadow-lg: 0 12px 32px rgba(15, 23, 42, .12);
    }

    [data-theme="dark"] {
        --bg: #0f172a;
        --surface: #111c33;
        --surface-muted: #0d172b;
        --surface-hover: #1a2742;
        --border: #223252;
        --border-strong: #334566;
        --text: #e5e7eb;
        --text-soft: #cbd5e1;
        --muted: #94a3b8;
        --primary-soft: #172554;
        --success-soft: #052e22;
        --warning-soft: #2b1e06;
        --danger-soft: #2b0f0f;
        --info-soft: #082f49;
        --purple-soft: #221446;
        --shadow: 0 1px 2px rgba(0, 0, 0, .35);
        --shadow-lg: 0 16px 40px rgba(0, 0, 0, .5);
    }

    *, *::before, *::after { box-sizing: border-box; }

    html { height: 100%; }

    body {
        margin: 0;
        min-height: 100%;
        background: var(--bg);
        color: var(--text);
        font-family: "Poppins", -apple-system, BlinkMacSystemFont, "Segoe UI", Tahoma, Arial, sans-serif;
        font-size: 14px;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
    }

    a { color: var(--primary); text-decoration: none; }
    a:hover { text-decoration: none; }

    h1, h2, h3, h4, p { margin: 0; }
    ul, ol { margin: 0; padding: 0; }
    img { max-width: 100%; }
    svg { display: block; }

    :focus-visible {
        outline: 2px solid color-mix(in srgb, var(--primary) 55%, transparent);
        outline-offset: 2px;
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* Header */
    .app-header {
        position: sticky;
        top: 0;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        height: var(--header-height);
        padding: 0 24px;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
    }

    .header-left, .header-right { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .header-left { gap: 14px; }

    .brand {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: var(--text);
        font-weight: 700;
        font-size: 16px;
        white-space: nowrap;
    }

    .brand img { height: 30px; width: auto; }
    .brand .brand-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: var(--primary);
        color: #fff;
    }

    .brand .brand-mark svg { width: 18px; height: 18px; }
    .brand .brand-divider { width: 1px; height: 22px; background: var(--border); }
    .brand .brand-suffix { color: var(--muted); font-weight: 500; font-size: 13px; }

    [data-theme="dark"] .brand img { filter: brightness(0) invert(1); }

    .icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        border: 1px solid transparent;
        border-radius: 8px;
        background: transparent;
        color: var(--muted);
        cursor: pointer;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    .icon-btn:hover { background: var(--surface-hover); color: var(--text); }
    .icon-btn svg { width: 18px; height: 18px; }
    .icon-btn.bordered { border-color: var(--border); }

    .header-search {
        display: flex;
        align-items: center;
        gap: 8px;
        width: min(320px, 32vw);
        height: 38px;
        padding: 0 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface-muted);
        color: var(--muted);
    }

    .header-search svg { width: 16px; height: 16px; flex-shrink: 0; }
    .header-search input {
        width: 100%;
        border: 0;
        background: transparent;
        color: var(--text);
        font: inherit;
        outline: none;
    }

    .header-search kbd {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 20px;
        padding: 0 6px;
        border: 1px solid var(--border);
        border-radius: 4px;
        background: var(--surface);
        color: var(--muted);
        font-family: inherit;
        font-size: 10px;
        font-weight: 600;
    }

    .user-menu { position: relative; }

    .user-menu summary {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 4px 8px 4px 4px;
        border-radius: 10px;
        cursor: pointer;
        list-style: none;
    }

    .user-menu summary::-webkit-details-marker { display: none; }
    .user-menu summary:hover { background: var(--surface-hover); }
    .user-menu summary > svg { width: 14px; height: 14px; color: var(--muted); }

    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 13px;
        font-weight: 700;
    }

    .user-meta { display: grid; line-height: 1.2; }
    .user-name { font-size: 13px; font-weight: 600; color: var(--text); }
    .user-role { font-size: 11px; color: var(--muted); }

    .user-dropdown {
        position: absolute;
        inset-inline-end: 0;
        top: calc(100% + 8px);
        min-width: 220px;
        padding: 8px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        box-shadow: var(--shadow-lg);
        z-index: 50;
    }

    .user-dropdown .dropdown-user { padding: 8px 10px 10px; border-bottom: 1px solid var(--border); margin-bottom: 6px; }
    .user-dropdown .dropdown-user p { font-size: 13px; font-weight: 600; }
    .user-dropdown .dropdown-user span { display: block; font-size: 11px; color: var(--muted); margin-top: 2px; word-break: break-all; }

    .dropdown-link, .dropdown-button {
        display: flex;
        align-items: center;
        gap: 9px;
        width: 100%;
        padding: 8px 10px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: var(--text-soft);
        font: inherit;
        font-size: 13px;
        text-align: start;
        cursor: pointer;
    }

    .dropdown-link:hover, .dropdown-button:hover { background: var(--surface-hover); color: var(--text); text-decoration: none; }
    .dropdown-link svg, .dropdown-button svg { width: 16px; height: 16px; color: var(--muted); }
    .dropdown-button.danger { color: var(--danger); }
    .dropdown-button.danger svg { color: var(--danger); }

    /* Shell and sidebar */
    .app-body { display: flex; min-height: calc(100vh - var(--header-height)); }

    .app-sidebar {
        position: sticky;
        top: var(--header-height);
        z-index: 30;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        width: var(--sidebar-width);
        height: calc(100vh - var(--header-height));
        padding: 16px 14px 20px;
        background: var(--surface);
        border-inline-end: 1px solid var(--border);
        overflow-y: auto;
    }

    .app-sidebar::-webkit-scrollbar { width: 6px; }
    .app-sidebar::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 99px; }

    .nav-section {
        margin: 14px 10px 6px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .nav-section:first-child { margin-top: 2px; }

    .side-nav { display: flex; flex-direction: column; gap: 2px; }

    .side-nav a {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 38px;
        padding: 8px 11px;
        border-radius: 7px;
        color: var(--text-soft);
        font-size: 13px;
        font-weight: 500;
        transition: background .12s ease, color .12s ease;
    }

    .side-nav a svg { width: 17px; height: 17px; flex-shrink: 0; color: var(--muted); }
    .side-nav a:hover { background: var(--surface-hover); color: var(--text); }
    .side-nav a:hover svg { color: var(--text-soft); }
    .side-nav a.active { background: var(--primary); color: #fff; box-shadow: 0 6px 14px rgba(37, 99, 235, .22); }
    .side-nav a.active svg { color: #fff; }
    .side-nav a .nav-count {
        margin-inline-start: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: var(--surface-hover);
        color: var(--muted);
        font-size: 10px;
        font-weight: 700;
    }
    .side-nav a.active .nav-count { background: rgba(255,255,255,.22); color: #fff; }

    .sidebar-footer { margin-top: auto; padding: 14px 10px 0; border-top: 1px solid var(--border); }
    .sidebar-footer p { font-size: 11px; color: var(--muted); }
    .sidebar-footer a { font-size: 12px; font-weight: 600; }

    /* Main */
    .app-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

    .content {
        width: 100%;
        max-width: 1600px;
        margin: 0 auto;
        padding: 24px;
        flex: 1;
    }

    .content-footer {
        padding: 14px 24px;
        border-top: 1px solid var(--border);
        background: var(--surface);
        color: var(--muted);
        font-size: 11px;
        text-align: center;
    }

    .content-footer a { color: var(--primary); }

    .page-head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 20px;
    }

    .page-head h1 { font-size: 20px; font-weight: 700; letter-spacing: -.01em; }
    .page-head p { margin-top: 4px; color: var(--muted); font-size: 13px; }
    .page-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }

    .breadcrumb { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-bottom: 4px; color: var(--muted); font-size: 11px; }
    .breadcrumb a { color: var(--muted); }
    .breadcrumb a:hover { color: var(--primary); }
    .breadcrumb svg { width: 12px; height: 12px; }

    .section-title { margin-bottom: 10px; font-size: 14px; font-weight: 600; color: var(--text-soft); }

    /* Cards */
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }

    .card-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--border);
    }

    .card-header h2, .card-header h3 { font-size: 14px; font-weight: 600; }
    .card-header p { font-size: 12px; color: var(--muted); margin-top: 2px; }
    .card-header a { font-size: 12px; font-weight: 600; }
    .card-body { padding: 18px; }
    .card-body.flush { padding: 0; }

    .card + .card { margin-top: 16px; }

    .stack { display: flex; flex-direction: column; gap: 16px; }
    .grid { display: grid; gap: 16px; }
    .grid.cols-2 { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
    .grid.cols-3 { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
    .grid.cols-4 { grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
    .grid.start { align-items: start; }

    /* Stats */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; }
    .stats-grid.cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

    .tiles-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; }
    .tiles-grid.quad { grid-template-columns: repeat(4, minmax(0, 1fr)); }

    .stat-card {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 18px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }

    .stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: var(--primary-soft);
        color: var(--primary);
        flex-shrink: 0;
    }

    .stat-icon svg { width: 20px; height: 20px; }
    .stat-icon.green { background: var(--success-soft); color: var(--success); }
    .stat-icon.amber { background: var(--warning-soft); color: var(--warning); }
    .stat-icon.red { background: var(--danger-soft); color: var(--danger); }
    .stat-icon.purple { background: var(--purple-soft); color: var(--purple); }

    .stat-content { min-width: 0; }
    .stat-value { font-size: 22px; font-weight: 700; line-height: 1.2; letter-spacing: -.02em; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .stat-label { display: block; margin-top: 2px; color: var(--muted); font-size: 12px; }
    .stat-delta { display: inline-flex; align-items: center; gap: 3px; margin-top: 6px; font-size: 11px; font-weight: 600; }
    .stat-delta.up { color: var(--success); }
    .stat-delta.down { color: var(--danger); }
    .stat-delta.new { color: var(--muted); }
    .stat-delta svg { width: 11px; height: 11px; }

    .stat-strip { display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); }

    .stat-strip .stat-card {
        border: 0;
        border-radius: 0;
        box-shadow: none;
        padding: 18px;
    }

    .stat-strip .stat-card:not(:last-child) { border-inline-end: 1px solid var(--border); }

    .period-form { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .period-form input[type="date"] { width: auto; min-width: 142px; }
    .period-form .period-sep { color: var(--muted); font-size: 12px; }

    /* Dashboard */
    .dashboard-cols { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 16px; margin-top: 16px; align-items: start; }
    .dashboard-main, .dashboard-side { display: flex; flex-direction: column; gap: 16px; min-width: 0; }

    .row-list { list-style: none; }
    .row-item { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 18px; border-bottom: 1px solid var(--border); }
    .row-item:last-child { border-bottom: 0; }
    .row-main { min-width: 0; }
    .row-title { display: inline-block; font-size: 13px; font-weight: 600; color: var(--text); }
    .row-title:hover { color: var(--primary); }
    .row-sub { margin-top: 2px; font-size: 11px; color: var(--muted); }
    .row-side { flex-shrink: 0; text-align: end; }
    .row-amount { font-size: 13px; font-weight: 700; }

    .avatar-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .row-item.with-avatar { justify-content: flex-start; }
    .row-item.with-avatar .row-main { flex: 1; }

    /* Charts */
    .chart-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; padding: 16px 18px 6px; }
    .chart-head h3 { font-size: 14px; font-weight: 600; }
    .chart-head p { font-size: 11px; color: var(--muted); margin-top: 2px; }
    .chart-total { text-align: end; }
    .chart-total strong { display: block; font-size: 17px; font-weight: 700; letter-spacing: -.02em; }
    .chart-total span { font-size: 10px; color: var(--muted); }

    .chart { padding: 12px 18px 16px; }

    .chart-bars {
        display: flex;
        align-items: flex-end;
        gap: 4px;
        height: 150px;
        padding-top: 6px;
        background-image: linear-gradient(to top, var(--border) 1px, transparent 1px);
        background-size: 100% 33.333%;
    }

    .chart-col { flex: 1; display: flex; align-items: flex-end; justify-content: center; height: 100%; min-width: 0; }
    .chart-bar { width: 100%; max-width: 30px; min-height: 3px; border-radius: 4px 4px 0 0; background: linear-gradient(180deg, #3b82f6, #2563eb); transition: opacity .15s ease; }
    .chart-bar:hover { opacity: .8; }
    .chart-bar.zero { background: transparent; }
    .chart-bar.alt { background: linear-gradient(180deg, #a78bfa, #7c3aed); }

    .chart-labels { display: flex; gap: 4px; margin-top: 8px; font-size: 9px; color: var(--muted); }
    .chart-labels span { flex: 1; min-width: 0; text-align: center; white-space: nowrap; }
    .chart-labels span.muted-label { visibility: hidden; }

    .chart-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 150px;
        border: 1px dashed var(--border-strong);
        border-radius: 8px;
        color: var(--muted);
        font-size: 12px;
        text-align: center;
    }
    .chart-empty svg { width: 26px; height: 26px; opacity: .55; }

    .chart-legend { display: flex; flex-wrap: wrap; gap: 12px; padding: 0 18px 16px; font-size: 11px; color: var(--muted); }
    .chart-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .chart-legend i { width: 8px; height: 8px; border-radius: 2px; background: var(--primary); }

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 9px;
        border-radius: 999px;
        background: var(--surface-hover);
        color: var(--text-soft);
        font-size: 11px;
        font-weight: 600;
        line-height: 1.6;
        white-space: nowrap;
    }

    .badge::before { content: ""; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
    .badge.no-dot::before { display: none; }
    .badge.active, .badge.verified, .badge.paid, .badge.succeeded, .badge.completed, .badge.success { background: var(--success-soft); color: var(--success); }
    .badge.pending, .badge.trialing, .badge.open, .badge.provisioning, .badge.warning { background: var(--warning-soft); color: var(--warning); }
    .badge.suspended, .badge.cancelled, .badge.canceled, .badge.failed, .badge.past_due, .badge.expired, .badge.danger, .badge.fraud { background: var(--danger-soft); color: var(--danger); }
    .badge.info, .badge.draft, .badge.processing { background: var(--info-soft); color: var(--info); }
    .badge.neutral, .badge.void, .badge.refunded { background: var(--surface-hover); color: var(--muted); }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 36px;
        padding: 7px 14px;
        border: 1px solid transparent;
        border-radius: 7px;
        background: var(--primary);
        color: #fff;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s ease, border-color .15s ease, color .15s ease, opacity .15s ease;
    }

    .btn:hover { background: var(--primary-dark); text-decoration: none; }
    .btn svg { width: 15px; height: 15px; }
    .btn.secondary, .btn.btn-secondary { background: var(--surface); border-color: var(--primary); color: var(--primary); }
    .btn.secondary:hover, .btn.btn-secondary:hover { background: var(--primary-soft); }
    .btn.ghost { background: transparent; border-color: var(--border); color: var(--text-soft); }
    .btn.ghost:hover { background: var(--surface-hover); color: var(--text); }
    .btn.danger { background: var(--danger); }
    .btn.danger:hover { background: #b91c1c; }
    .btn.success { background: var(--success); }
    .btn.success:hover { background: #047857; }
    .btn.small, .btn.btn-sm { min-height: 30px; padding: 4px 10px; font-size: 12px; border-radius: 6px; }
    .btn.block { width: 100%; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    .btn-link { padding: 0; border: 0; background: none; color: var(--primary); font: inherit; font-weight: 600; cursor: pointer; }
    .btn-link.danger { color: var(--danger); }
    .actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .inline-form { display: inline; }

    /* Forms */
    .form-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
    .form-grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .form-grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .form-grid .span-2 { grid-column: 1 / -1; }

    .field { display: grid; gap: 6px; }
    .field > label { font-size: 12px; font-weight: 600; color: var(--text-soft); }
    .field .hint { font-size: 11px; color: var(--muted); }

    input, select, textarea {
        width: 100%;
        min-height: 38px;
        padding: 8px 11px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: var(--surface);
        color: var(--text);
        font: inherit;
        font-size: 13px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    input:focus, select:focus, textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 14%, transparent);
        outline: none;
    }

    textarea { min-height: 110px; resize: vertical; }
    input[type="checkbox"], input[type="radio"] { width: 16px; height: 16px; min-height: 0; accent-color: var(--primary); }
    input[type="file"] { padding: 7px; font-size: 12px; }
    input[type="file"]::file-selector-button {
        margin-inline-end: 10px;
        padding: 7px 14px;
        border: 1px solid var(--border-strong);
        border-radius: 7px;
        background: var(--surface-hover);
        color: var(--text);
        font: inherit;
        font-weight: 600;
        cursor: pointer;
    }

    .check { display: flex; align-items: flex-start; gap: 9px; font-size: 13px; color: var(--text-soft); }
    .check input { margin-top: 2px; }
    .check strong { display: block; color: var(--text); font-size: 13px; }
    .check span.hint { display: block; font-size: 11px; color: var(--muted); }

    .switch { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; color: var(--text-soft); }
    .switch input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .switch-track { position: relative; width: 38px; height: 22px; border-radius: 999px; background: var(--border-strong); transition: background .15s ease; flex-shrink: 0; }
    .switch-track::after { content: ""; position: absolute; top: 3px; inset-inline-start: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.25); transition: transform .15s ease; }
    .switch input:checked + .switch-track { background: var(--success); }
    .switch input:checked + .switch-track::after { transform: translateX(16px); }
    [dir="rtl"] .switch input:checked + .switch-track::after { transform: translateX(-16px); }

    .form-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--border); }
    .form-actions.start { justify-content: flex-start; }

    .image-upload { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .image-upload input[type="file"] { flex: 1; min-width: 180px; }
    .image-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        border: 1px dashed var(--border-strong);
        border-radius: 10px;
        background: var(--surface-muted);
        color: var(--muted);
        overflow: hidden;
        flex-shrink: 0;
    }
    .image-preview img { width: 100%; height: 100%; object-fit: cover; }
    .image-preview svg { width: 22px; height: 22px; }

    /* Alerts */
    .alert { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; margin-bottom: 16px; border: 1px solid transparent; border-radius: 8px; font-size: 13px; }
    .alert svg { width: 17px; height: 17px; flex-shrink: 0; margin-top: 1px; }
    .alert ul { margin: 4px 0 0; padding-inline-start: 18px; }
    .alert.success { background: var(--success-soft); border-color: color-mix(in srgb, var(--success) 25%, transparent); color: var(--success); }
    .alert.error { background: var(--danger-soft); border-color: color-mix(in srgb, var(--danger) 25%, transparent); color: var(--danger); }
    .alert.info { background: var(--info-soft); border-color: color-mix(in srgb, var(--info) 25%, transparent); color: var(--info); }

    /* Tables and datagrids */
    .table-wrap { overflow-x: auto; border-radius: var(--radius); background: var(--surface); border: 1px solid var(--border); }
    table.table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .table th { padding: 10px 12px; background: var(--surface-muted); border-bottom: 1px solid var(--border); color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; text-align: start; white-space: nowrap; }
    .table td { padding: 11px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; color: var(--text-soft); }
    .table tbody tr:last-child td { border-bottom: 0; }
    .table tbody tr:hover { background: var(--surface-muted); }
    .table .strong { color: var(--text); font-weight: 600; }
    .table .nowrap { white-space: nowrap; }
    .table .end { text-align: end; }
    .table .center { text-align: center; }

    .datagrid { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }

    .dg-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
    }

    .dg-toolbar-left, .dg-toolbar-right { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; min-width: 0; }

    .dg-search {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 230px;
        height: 36px;
        padding: 0 11px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: var(--surface);
        color: var(--muted);
    }

    .dg-search svg { width: 15px; height: 15px; flex-shrink: 0; }
    .dg-search input { min-height: 0; height: 100%; padding: 0; border: 0; background: transparent; font-size: 13px; }
    .dg-search input:focus { box-shadow: none; }

    .dg-count { font-size: 12px; color: var(--muted); }
    .dg-count strong { color: var(--text); }

    .dg-filter {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 36px;
        padding: 0 12px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: var(--surface);
        color: var(--text-soft);
        font: inherit;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .dg-filter:hover { background: var(--surface-hover); }
    .dg-filter svg { width: 15px; height: 15px; }

    .dg-select {
        width: auto;
        min-width: 92px;
        min-height: 36px;
        padding: 4px 10px;
        font-size: 12px;
    }

    .dg-per-page { display: inline-flex; align-items: center; gap: 8px; font-size: 12px; color: var(--muted); }
    .dg-per-page select { min-width: 68px; }

    .filter-popover { position: relative; }
    .filter-popover > summary { list-style: none; }
    .filter-popover > summary::-webkit-details-marker { display: none; }

    .filter-panel {
        position: absolute;
        inset-inline-end: 0;
        top: calc(100% + 6px);
        z-index: 20;
        display: grid;
        gap: 12px;
        width: min(320px, calc(100vw - 40px));
        padding: 16px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        box-shadow: var(--shadow-lg);
    }

    .filter-panel .field > label { font-size: 11px; }
    .filter-panel .form-actions { margin-top: 4px; padding-top: 12px; }

    .dg-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .dg-table th { padding: 11px 14px; background: var(--surface-muted); border-bottom: 1px solid var(--border); color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; text-align: start; white-space: nowrap; }
    .dg-table td { padding: 12px 14px; border-bottom: 1px solid var(--border); vertical-align: middle; color: var(--text-soft); }
    .dg-table tbody tr:last-child td { border-bottom: 0; }
    .dg-table tbody tr:hover { background: var(--surface-muted); }
    .dg-table .cell-title { color: var(--text); font-weight: 600; }
    .dg-table .cell-sub { margin-top: 2px; font-size: 11px; color: var(--muted); }
    .dg-table .end { text-align: end; }
    .dg-table .center { text-align: center; }
    .dg-table .nowrap { white-space: nowrap; }
    .dg-check { width: 40px; text-align: center; }

    .cell-media { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .cell-thumb {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: var(--surface-muted);
        color: var(--muted);
        overflow: hidden;
        flex-shrink: 0;
    }
    .cell-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .cell-thumb svg { width: 17px; height: 17px; }

    .dg-empty { padding: 46px 20px; text-align: center; color: var(--muted); }
    .dg-empty svg { width: 34px; height: 34px; margin: 0 auto 10px; opacity: .55; }
    .dg-empty p { font-size: 13px; }

    .dg-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; border-top: 1px solid var(--border); color: var(--muted); font-size: 12px; }

    /* Pagination */
    .pagination { margin-top: 4px; }
    .pagination-nav { display: inline-flex; align-items: center; gap: 6px; }
    .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 9px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: var(--surface);
        color: var(--text-soft);
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
    }
    .page-link:hover { background: var(--surface-hover); text-decoration: none; }
    .page-link.current { background: var(--primary); border-color: var(--primary); color: #fff; }
    .page-link.disabled { opacity: .4; pointer-events: none; }
    .page-link.dots { border-color: transparent; background: transparent; }
    .page-link svg { width: 14px; height: 14px; }
    [dir="rtl"] .page-link.prev svg { transform: scaleX(-1); }
    [dir="rtl"] .page-link.next svg { transform: scaleX(-1); }
    .pagination > div { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }

    /* Detail layouts */
    .detail-grid { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    .detail-list { display: grid; gap: 0; }
    .detail-row { display: grid; grid-template-columns: minmax(130px, 190px) minmax(0, 1fr); gap: 12px; padding: 11px 18px; border-bottom: 1px solid var(--border); font-size: 13px; }
    .detail-row:last-child { border-bottom: 0; }
    .detail-row dt, .detail-row .detail-label { color: var(--muted); font-size: 12px; }
    .detail-row dd, .detail-row .detail-value { margin: 0; color: var(--text); font-weight: 500; word-break: break-word; }

    .identity { display: flex; align-items: center; gap: 14px; }
    .identity-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 18px;
        font-weight: 700;
        overflow: hidden;
        flex-shrink: 0;
    }
    .identity-logo img { width: 100%; height: 100%; object-fit: cover; }
    .identity h1 { font-size: 19px; font-weight: 700; }
    .identity p { margin-top: 2px; color: var(--muted); font-size: 12px; }

    .info-tile { padding: 16px 18px; }
    .info-tile .info-label { font-size: 12px; color: var(--muted); }
    .info-tile .info-value { margin-top: 6px; font-size: 22px; font-weight: 700; letter-spacing: -.02em; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .info-tile .info-foot { margin-top: 4px; font-size: 11px; color: var(--muted); }

    .empty-block { padding: 28px 18px; text-align: center; color: var(--muted); }
    .empty-block svg { width: 28px; height: 28px; margin: 0 auto 8px; opacity: .5; }
    .empty-block p { font-size: 12px; }

    /* Progress */
    .progress { height: 7px; border-radius: 999px; background: var(--surface-hover); overflow: hidden; }
    .progress span { display: block; height: 100%; border-radius: inherit; background: var(--primary); }
    .progress span.over { background: var(--danger); }
    .progress span.ok { background: var(--success); }

    .usage-row { display: grid; gap: 6px; padding: 10px 18px; border-bottom: 1px solid var(--border); }
    .usage-row:last-child { border-bottom: 0; }
    .usage-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: 12px; }
    .usage-head strong { font-size: 12px; font-weight: 600; color: var(--text); }
    .usage-head span { color: var(--muted); }

    /* Checklist */
    .checklist { list-style: none; }
    .checklist li { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 18px; border-bottom: 1px solid var(--border); font-size: 13px; }
    .checklist li:last-child { border-bottom: 0; }
    .checklist strong { font-size: 12px; font-weight: 600; }

    /* Modals */
    dialog.modal {
        width: min(520px, calc(100vw - 32px));
        padding: 0;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: var(--surface);
        color: var(--text);
        box-shadow: var(--shadow-lg);
    }

    dialog.modal::backdrop { background: rgba(15, 23, 42, .55); backdrop-filter: blur(2px); }
    .modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 18px 20px 0; }
    .modal-head h2 { font-size: 16px; font-weight: 700; }
    .modal-head p { margin-top: 3px; font-size: 12px; color: var(--muted); }
    .modal-body { padding: 16px 20px; }
    .modal-foot { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; padding: 0 20px 18px; }

    /* Tabs */
    .tabs { display: flex; flex-wrap: wrap; gap: 4px; border-bottom: 1px solid var(--border); padding: 0 18px; }
    .tabs a, .tabs button {
        padding: 11px 10px;
        border: 0;
        border-bottom: 2px solid transparent;
        background: none;
        color: var(--muted);
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
    .tabs a:hover, .tabs button:hover { color: var(--text); }
    .tabs a.active, .tabs button.active { border-bottom-color: var(--primary); color: var(--primary); }

    /* Utilities */
    .muted { color: var(--muted); }
    .small { font-size: 12px; }
    .strong { font-weight: 600; color: var(--text); }
    .nowrap { white-space: nowrap; }
    .text-end { text-align: end; }
    .flex { display: flex; }
    .items-center { align-items: center; }
    .justify-between { justify-content: space-between; }
    .gap-8 { gap: 8px; }
    .gap-12 { gap: 12px; }
    .mt-0 { margin-top: 0; }
    .mt-8 { margin-top: 8px; }
    .mt-16 { margin-top: 16px; }
    .mt-24 { margin-top: 24px; }
    .mb-16 { margin-bottom: 16px; }
    .w-full { width: 100%; }
    .break-all { word-break: break-all; }

    /* Dark mode adjustments */
    [data-theme="dark"] .app-header, [data-theme="dark"] .app-sidebar, [data-theme="dark"] .content-footer { background: var(--surface); }
    [data-theme="dark"] .header-search { background: var(--surface-muted); }
    [data-theme="dark"] input, [data-theme="dark"] select, [data-theme="dark"] textarea { background: var(--surface-muted); }
    [data-theme="dark"] .cell-thumb, [data-theme="dark"] .image-preview { background: var(--surface-muted); }
    [data-theme="dark"] .stat-icon, [data-theme="dark"] .avatar, [data-theme="dark"] .avatar-sm, [data-theme="dark"] .identity-logo { background: var(--primary-soft); }
    [data-theme="dark"] .chart-bar { background: linear-gradient(180deg, #60a5fa, #3b82f6); }
    [data-theme="dark"] .chart-bar.alt { background: linear-gradient(180deg, #c4b5fd, #8b5cf6); }

    .theme-icon-sun { display: none; }
    [data-theme="dark"] .theme-icon-sun { display: block; }
    [data-theme="dark"] .theme-icon-moon { display: none; }

    /* Legacy shell classes kept for the pages that still use them */
    .shell { display: flex; min-height: 100vh; }
    .sidebar { width: var(--sidebar-width); flex-shrink: 0; background: var(--surface); border-inline-end: 1px solid var(--border); padding: 18px 14px; display: flex; flex-direction: column; gap: 16px; }
    .sidebar .brand { padding: 4px 10px; }
    .main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .main-top { display: flex; gap: 10px; align-items: center; justify-content: flex-end; padding: 12px 24px; background: var(--surface); border-bottom: 1px solid var(--border); }
    .container { width: 100%; max-width: 1600px; margin: 0 auto; padding: 24px; flex: 1; }
    .narrow { max-width: 560px; }
    .kv { display: grid; grid-template-columns: minmax(140px, 220px) 1fr; gap: 8px 16px; }
    .kv dt { color: var(--muted); }
    .kv dd { margin: 0; }

    /* Mixed-language copy keeps the direction of its own first strong character */
    .content-footer,
    .page-head p,
    .card-header p,
    .cell-title,
    .chart-total strong,
    .stat-label,
    .info-label,
    .empty-block p,
    .dg-empty p { unicode-bidi: plaintext; }

    /* Responsive */
    @media (max-width: 1180px) {
        .dashboard-cols { grid-template-columns: minmax(0, 1fr); }
        .detail-grid { grid-template-columns: minmax(0, 1fr); }
        .tiles-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 900px) {
        .stats-grid.cols-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 1023px) {
        .app-sidebar {
            position: fixed;
            top: var(--header-height);
            inset-inline-start: 0;
            bottom: 0;
            height: auto;
            transform: translateX(-105%);
            transition: transform .22s ease;
            box-shadow: var(--shadow-lg);
        }

        [dir="rtl"] .app-sidebar { transform: translateX(105%); }

        .app-sidebar.open { transform: translateX(0); }

        .sidebar-backdrop {
            position: fixed;
            inset: var(--header-height) 0 0;
            z-index: 25;
            background: rgba(15, 23, 42, .45);
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease;
        }

        .sidebar-backdrop.open { opacity: 1; pointer-events: auto; }
        .header-search { display: none; }
    }

    @media (max-width: 720px) {
        .app-header { padding: 0 14px; }
        .content { padding: 16px; }
        .user-meta { display: none; }
        .page-head h1 { font-size: 18px; }
        .stats-grid { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); }
        .dg-toolbar { align-items: stretch; }
        .dg-toolbar-left, .dg-toolbar-right { width: 100%; }
        .dg-search { flex: 1; min-width: 0; }
        .dg-table th { display: none; }
        .dg-table, .dg-table tbody, .dg-table tr, .dg-table td { display: block; width: 100%; }
        .dg-table tr { padding: 12px 14px; border-bottom: 1px solid var(--border); }
        .dg-table td { display: flex; justify-content: space-between; gap: 12px; padding: 4px 0; border: 0; text-align: end; }
        .dg-table td::before { content: attr(data-label); color: var(--muted); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; text-align: start; }
        .dg-table td.dg-check { display: none; }
        .dg-table td[data-label=""]::before { display: none; }
        .detail-row { grid-template-columns: 1fr; gap: 2px; }
    }
</style>
