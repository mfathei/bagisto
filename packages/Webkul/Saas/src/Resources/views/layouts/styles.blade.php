<style>
    :root {
        --bg: #f5f7fb;
        --surface: #ffffff;
        --border: #e2e8f0;
        --text: #1f2937;
        --muted: #6b7280;
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --danger: #dc2626;
        --success: #059669;
        --warning: #d97706;
        --radius: 10px;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        background: var(--bg);
        color: var(--text);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Tahoma, Arial, sans-serif;
        font-size: 14px;
        line-height: 1.5;
    }

    a { color: var(--primary); text-decoration: none; }
    a:hover { text-decoration: underline; }

    .topbar {
        background: #111827;
        color: #f9fafb;
        padding: 14px 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
        justify-content: space-between;
    }

    .topbar .brand { font-weight: 700; font-size: 16px; color: #ffffff; }
    .topbar nav { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
    .topbar nav a { color: #d1d5db; font-size: 13px; }
    .topbar nav a.active, .topbar nav a:hover { color: #ffffff; text-decoration: none; }

    .container { max-width: 1200px;  padding: 24px; }
    .narrow { max-width: 520px; }

    .page-head { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .page-head h1 { font-size: 20px; margin: 0; }
    .page-head p { margin: 4px 0 0; color: var(--muted); }

    .grid { display: grid; gap: 16px; }
    .grid.cols-2 { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
    .grid.cols-3 { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
    .grid.cols-4 { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }

    .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 18px; }
    .card h2, .card h3 { margin: 0 0 12px; font-size: 15px; }
    .stat { font-size: 26px; font-weight: 700; margin: 6px 0 0; }
    .muted { color: var(--muted); }

    table { width: 100%; border-collapse: collapse; background: var(--surface); }
    th, td { text-align: start; padding: 10px 12px; border-bottom: 1px solid var(--border); vertical-align: top; }
    th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); background: #f9fafb; }
    tr:last-child td { border-bottom: none; }
    .table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow-x: auto; }

    .badge { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #e5e7eb; color: #374151; }
    .badge.active, .badge.verified, .badge.paid, .badge.succeeded { background: #d1fae5; color: #065f46; }
    .badge.pending, .badge.trialing, .badge.open, .badge.provisioning { background: #fef3c7; color: #92400e; }
    .badge.suspended, .badge.cancelled, .badge.failed, .badge.past_due, .badge.expired { background: #fee2e2; color: #991b1b; }

    label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; }
    input, select, textarea {
        width: 100%;
        padding: 9px 11px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #fff;
        color: var(--text);
        font: inherit;
    }
    input[type="checkbox"] { width: auto; }
    .field { margin-bottom: 14px; }
    .check { display: flex; gap: 8px; align-items: center; font-weight: 500; }
    .check input { margin: 0; }

    .btn {
        display: inline-block;
        padding: 9px 14px;
        border-radius: 8px;
        border: 1px solid transparent;
        background: var(--primary);
        color: #fff;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
    }
    .btn:hover { background: var(--primary-dark); text-decoration: none; }
    .btn.secondary { background: #fff; color: var(--text); border-color: var(--border); }
    .btn.danger { background: var(--danger); }
    .btn.small { padding: 5px 10px; font-size: 12px; }
    .btn-link { background: none; border: none; color: var(--primary); cursor: pointer; padding: 0; font: inherit; }

    .alert { padding: 11px 14px; border-radius: 8px; margin-bottom: 16px; border: 1px solid transparent; }
    .alert.success { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
    .alert.error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .alert.info { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }

    .actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .inline-form { display: inline; }
    .pagination { margin-top: 16px; }
    .pagination nav { display: flex; gap: 6px; flex-wrap: wrap; }
    .stack > * + * { margin-top: 16px; }
    .kv { display: grid; grid-template-columns: minmax(140px, 220px) 1fr; gap: 8px 16px; }
    .kv dt { color: var(--muted); }
    .kv dd { margin: 0; }
    .progress { height: 8px; border-radius: 999px; background: #e5e7eb; overflow: hidden; }
    .progress span { display: block; height: 100%; background: var(--primary); }
    .progress span.over { background: var(--danger); }
    .checklist { list-style: none; padding: 0; margin: 0; }
    .checklist li { padding: 8px 0; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; gap: 12px; }
    .checklist li:last-child { border-bottom: none; }
    .rtl { direction: rtl; }

    [data-theme="dark"] {
        --bg: #0b1220;
        --surface: #111a2c;
        --border: #243352;
        --text: #e5e7eb;
        --muted: #94a3b8;
    }

    [data-theme="dark"] th { background: #0f1830; }
    [data-theme="dark"] input, [data-theme="dark"] select, [data-theme="dark"] textarea { background: #0f1830; color: var(--text); }
    [data-theme="dark"] .btn.secondary { background: #111a2c; color: var(--text); }

    .shell { display: flex; min-height: 100vh; }

    .sidebar {
        width: 232px;
        flex-shrink: 0;
        background: var(--surface);
        border-inline-end: 1px solid var(--border);
        padding: 18px 12px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .sidebar .brand {
        display: flex;
        gap: 10px;
        align-items: center;
        padding: 4px 10px;
        color: var(--text);
        font-weight: 700;
        font-size: 16px;
    }

    .sidebar .brand:hover { text-decoration: none; }
    .sidebar .brand svg { width: 20px; height: 20px; color: var(--primary); }

    .side-nav { display: flex; flex-direction: column; gap: 2px; }

    .side-nav a {
        display: flex;
        gap: 10px;
        align-items: center;
        padding: 9px 12px;
        border-radius: 8px;
        color: var(--muted);
        font-size: 13px;
        font-weight: 500;
    }

    .side-nav a:hover { background: var(--bg); color: var(--text); text-decoration: none; }

    .side-nav a.active { background: var(--primary); color: #ffffff; }
    .side-nav a svg { width: 17px; height: 17px; flex-shrink: 0; }

    .main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

    .main-top {
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: flex-end;
        padding: 12px 24px;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
    }

    .icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: transparent;
        color: var(--muted);
        cursor: pointer;
    }

    .icon-btn:hover { color: var(--text); }
    .icon-btn svg { width: 17px; height: 17px; }
    [data-theme="dark"] .icon-sun { display: block; }
    [data-theme="dark"] .icon-moon { display: none; }
    .icon-sun { display: none; }
    .icon-moon { display: block; }

    .user-menu { position: relative; }
    .user-menu summary {
        display: flex;
        gap: 8px;
        align-items: center;
        cursor: pointer;
        list-style: none;
        padding: 4px 8px;
        border-radius: 8px;
    }

    .user-menu summary::-webkit-details-marker { display: none; }
    .user-menu summary:hover { background: var(--bg); }
    .user-menu summary svg { width: 14px; height: 14px; color: var(--muted); }
    .user-name { font-size: 13px; font-weight: 600; }

    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--primary);
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
    }

    .user-dropdown {
        position: absolute;
        inset-inline-end: 0;
        top: calc(100% + 6px);
        min-width: 200px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 12px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
        z-index: 20;
    }

    .user-email { margin: 0 0 8px; font-size: 12px; color: var(--muted); }

    .section-title { font-size: 15px; margin: 0 0 10px; }

    .stats-grid { grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }

    .stat-card { display: flex; gap: 14px; align-items: flex-start; }

    .stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: color-mix(in srgb, var(--primary) 12%, transparent);
        color: var(--primary);
        flex-shrink: 0;
    }

    .stat-icon svg { width: 20px; height: 20px; }
    .stat-value { font-size: 22px; font-weight: 700; margin: 0; line-height: 1.2; }
    .stat-label { display: block; font-size: 12px; color: var(--muted); margin-top: 2px; }

    .stat-delta { display: inline-block; font-size: 12px; font-weight: 600; margin-top: 4px; }
    .stat-delta.up { color: var(--success); }
    .stat-delta.down { color: var(--danger); }
    .stat-delta.new { color: var(--muted); }

    .range-form { display: flex; gap: 8px; align-items: center; }
    .range-form input { width: auto; }

    .dashboard-cols {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 16px;
        margin-top: 16px;
        align-items: start;
    }

    .dashboard-main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
    .dashboard-side { display: flex; flex-direction: column; gap: 16px; min-width: 0; }

    .row-list { list-style: none; margin: 0; padding: 0; }

    .row-item {
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
    }

    .row-item:last-child { border-bottom: none; }
    .row-item .row-main { min-width: 0; }
    .row-item .row-title { font-weight: 600; font-size: 13px; margin: 0; }
    .row-item .row-sub { font-size: 12px; color: var(--muted); margin: 2px 0 0; }
    .row-item .row-side { text-align: end; flex-shrink: 0; }
    .row-item .row-amount { font-weight: 700; font-size: 13px; margin: 0; }

    .card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .card-head h2 { margin: 0; }
    .card-head a { font-size: 12px; }

    .chart-head { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
    .chart-head h3 { margin: 0; font-size: 14px; }
    .chart-head .chart-total { font-size: 12px; color: var(--muted); text-align: end; }
    .chart-head .chart-total strong { display: block; font-size: 15px; color: var(--text); }

    .bars { display: flex; gap: 3px; align-items: flex-end; height: 140px; }
    .bar-col { flex: 1; display: flex; align-items: flex-end; height: 100%; min-width: 0; }

    .bar {
        width: 100%;
        min-height: 3px;
        border-radius: 3px 3px 0 0;
        background: var(--primary);
    }

    .bar.zero { background: var(--border); }

    .bar-labels {
        display: flex;
        gap: 3px;
        margin-top: 6px;
        font-size: 10px;
        color: var(--muted);
    }

    .bar-labels span { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .bar-labels span:nth-child(even) { visibility: hidden; }

    @media (max-width: 1024px) {
        .dashboard-cols { grid-template-columns: 1fr; }
    }

    @media (max-width: 820px) {
        .shell { flex-direction: column; }
        .sidebar { width: auto; flex-direction: row; align-items: center; overflow-x: auto; border-inline-end: none; border-bottom: 1px solid var(--border); }
        .side-nav { flex-direction: row; }
        .side-nav a { white-space: nowrap; }
        .range-form { flex-wrap: wrap; }
    }
</style>
