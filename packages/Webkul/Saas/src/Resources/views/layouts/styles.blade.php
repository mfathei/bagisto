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

    body.registration-body {
        --bg: #f6f7ff;
        --surface: #ffffff;
        --border: #e2e6f2;
        --text: #141a33;
        --muted: #52607a;
        --primary: #4338ca;
        --primary-dark: #3730a3;
        min-height: 100vh;
        background:
            radial-gradient(ellipse at 88% 0%, rgba(99, 102, 241, .13), transparent 36rem),
            linear-gradient(180deg, #fbfbff 0%, var(--bg) 100%);
    }

    .registration-body .topbar {
        padding-inline: max(24px, calc((100vw - 1160px) / 2));
        background: rgba(255, 255, 255, .88);
        color: var(--text);
        border-bottom: 1px solid rgba(226, 230, 242, .9);
        backdrop-filter: blur(16px);
    }

    .registration-body .topbar .brand {
        color: var(--text);
        font-size: 17px;
        letter-spacing: -.02em;
    }

    .registration-body .topbar nav { gap: 10px; }

    .registration-body .topbar nav a.registration-signin {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 16px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: #fff;
        color: var(--text);
        font-size: 13px;
        font-weight: 650;
        text-decoration: none;
        transition: border-color .18s ease, color .18s ease, background .18s ease;
    }

    .registration-body .topbar nav a.registration-signin:hover {
        border-color: color-mix(in srgb, var(--primary) 36%, var(--border));
        background: #f7f6ff;
        color: var(--primary);
    }

    .registration-container {
        width: min(100%, 1200px);
        max-width: 1200px;
        margin-inline: auto;
        padding: 48px 24px 64px;
    }

    .registration-heading {
        max-width: 760px;
        margin: 0 auto 28px;
        text-align: center;
    }

    .registration-heading h1 {
        margin: 0;
        color: var(--text);
        font-size: clamp(30px, 4vw, 44px);
        font-weight: 750;
        line-height: 1.15;
        letter-spacing: -.035em;
    }

    .registration-heading p {
        margin: 12px 0 0;
        color: var(--muted);
        font-size: 16px;
        line-height: 1.7;
    }

    .registration-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(260px, .75fr);
        align-items: start;
        gap: 24px;
    }

    .registration-card {
        padding: clamp(24px, 3vw, 38px);
        border-color: rgba(219, 224, 239, .92);
        border-radius: 20px;
        box-shadow: 0 24px 70px -44px rgba(30, 41, 90, .42), 0 4px 14px rgba(31, 41, 85, .04);
    }

    .registration-form-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 22px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .registration-form-heading-spaced { margin-top: 28px; }

    .registration-form-heading h2 {
        margin: 0;
        color: var(--text);
        font-size: 18px;
        line-height: 1.35;
        letter-spacing: -.015em;
    }

    .registration-step {
        display: inline-grid;
        place-items: center;
        width: 34px;
        height: 34px;
        flex: none;
        border-radius: 11px;
        background: #eeedff;
        color: var(--primary);
        font-size: 12px;
        font-weight: 750;
        letter-spacing: .03em;
    }

    .registration-fields { gap: 4px 16px; }

    .registration-card .field { margin-bottom: 18px; }

    .registration-card label {
        margin-bottom: 7px;
        color: #27314d;
        font-size: 13px;
        font-weight: 650;
    }

    .registration-card input:not([type="checkbox"]),
    .registration-card select {
        min-height: 46px;
        padding: 10px 13px;
        border-color: #d9dfed;
        border-radius: 10px;
        background: #fff;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .registration-card input:not([type="checkbox"]):hover,
    .registration-card select:hover { border-color: #aab4d0; }

    .registration-card input:not([type="checkbox"]):focus,
    .registration-card select:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 4px rgba(67, 56, 202, .12);
    }

    .registration-card .check {
        align-items: flex-start;
        gap: 10px;
        color: var(--muted);
        font-weight: 450;
        line-height: 1.65;
    }

    .registration-card .check input {
        width: 18px;
        height: 18px;
        flex: none;
        margin-top: 2px;
        accent-color: var(--primary);
    }

    .field-hint {
        margin: 7px 0 0;
        color: var(--muted);
        font-size: 12.5px;
        line-height: 1.6;
    }

    .field-error {
        margin: 8px 0 0;
        color: #b42318;
        font-size: 12.5px;
        font-weight: 550;
        line-height: 1.5;
    }

    .registration-submit {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 50px;
        margin-top: 20px;
        border-radius: 11px;
        background: linear-gradient(135deg, #4f46e5, #4338ca 58%, #3730a3);
        box-shadow: 0 12px 24px -12px rgba(67, 56, 202, .62);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    }

    .registration-submit:hover {
        background: linear-gradient(135deg, #4338ca, #3730a3);
        box-shadow: 0 14px 28px -12px rgba(67, 56, 202, .72);
        color: #fff;
        transform: translateY(-1px);
    }

    .registration-aside {
        position: sticky;
        top: 24px;
        overflow: hidden;
        padding: 30px;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 20px;
        background:
            radial-gradient(ellipse at 100% 0%, rgba(255, 255, 255, .2), transparent 18rem),
            linear-gradient(155deg, #3730a3, #4338ca 55%, #5b50e8);
        box-shadow: 0 24px 64px -36px rgba(55, 48, 163, .7);
        color: #fff;
    }

    .registration-aside-mark {
        display: grid;
        place-items: center;
        width: 48px;
        height: 48px;
        margin-bottom: 42px;
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 15px;
        background: rgba(255, 255, 255, .12);
        color: #fff;
    }

    .registration-aside-mark svg { width: 25px; height: 25px; }

    .registration-aside-label {
        margin: 0;
        color: #dcd9ff;
        font-size: 12px;
        font-weight: 650;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .registration-aside h2 {
        margin: 8px 0 0;
        color: #fff;
        font-size: clamp(22px, 2.5vw, 30px);
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .registration-aside-trial {
        margin: 14px 0 0;
        color: #eeedff;
        font-size: 14px;
        line-height: 1.7;
    }

    .registration-pricing-link {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-top: 28px;
        padding: 11px 15px;
        border: 1px solid rgba(255, 255, 255, .4);
        border-radius: 10px;
        color: #fff;
        font-size: 13px;
        font-weight: 650;
        text-decoration: none;
    }

    .registration-pricing-link:hover {
        background: rgba(255, 255, 255, .12);
        color: #fff;
        text-decoration: none;
    }

    .registration-pricing-link span { font-size: 17px; line-height: 1; }

    .registration-signin-mobile { display: none; }

    .registration-body :where(a, button, input, select):focus-visible {
        outline: 3px solid #8b83f5;
        outline-offset: 3px;
    }

    .registration-body .registration-card input:not([type="checkbox"]):focus-visible,
    .registration-body .registration-card select:focus-visible {
        outline: 3px solid #8b83f5;
        outline-offset: 3px;
    }

    @media (max-width: 880px) {
        .registration-grid { grid-template-columns: minmax(0, 1fr) minmax(230px, .7fr); gap: 16px; }
        .registration-aside { padding: 24px; }
        .registration-card { padding: 26px; }
    }

    @media (max-width: 720px) {
        .registration-container { padding: 36px 20px 48px; }
        .registration-heading { margin-bottom: 22px; text-align: start; }
        .registration-grid { grid-template-columns: minmax(0, 1fr); }
        .registration-aside { position: static; grid-row: 1; }
        .registration-card { grid-row: 2; }
        .registration-aside-mark { margin-bottom: 24px; }
        .registration-aside h2 { font-size: 23px; }
        .registration-aside-trial { margin-top: 8px; }
        .registration-pricing-link { margin-top: 18px; }
    }

    @media (max-width: 480px) {
        .registration-body .topbar { padding-inline: 16px; }
        .registration-container { padding: 30px 14px 36px; }
        .registration-heading h1 { font-size: 30px; }
        .registration-heading p { font-size: 14px; }
        .registration-card { padding: 20px 16px; border-radius: 16px; }
        .registration-aside { padding: 22px; border-radius: 16px; }
        .registration-fields { grid-template-columns: minmax(0, 1fr); }
        .registration-signin-mobile { display: block; margin: 18px 4px 0; color: var(--muted); text-align: center; }
    }

    @media (prefers-reduced-motion: reduce) {
        .registration-body *, .registration-body *::before, .registration-body *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; animation-duration: .01ms !important; }
    }

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
