<style>
    :root {
        --brand: #4338ca;
        --ink: #0f172a;
        --muted: #52607a;
        --line: #e2e8f0;
        --surface: #ffffff;
        --bg: #f7f8fc;
        --soft: #eef2ff;
        --radius-lg: 20px;
        --radius: 14px;
        --radius-sm: 10px;
        --shadow: 0 18px 40px -24px rgba(15, 23, 42, .35);
        --shadow-sm: 0 8px 20px -16px rgba(15, 23, 42, .4);
        --max: 1180px;
    }

    * { box-sizing: border-box; }

    html { scroll-behavior: smooth; }

    body.site {
        margin: 0;
        background: var(--bg);
        color: var(--ink);
        font-family: "Inter", "Segoe UI", Tahoma, "Noto Sans Arabic", Arial, sans-serif;
        font-size: 16px;
        line-height: 1.65;
        -webkit-font-smoothing: antialiased;
    }

    .site h1, .site h2, .site h3, .site h4 {
        margin: 0;
        line-height: 1.2;
        letter-spacing: -.02em;
        font-weight: 700;
    }

    .site p { margin: 0; }

    .site a { color: inherit; text-decoration: none; }

    .site a:hover { color: var(--brand); }

    .shell {
        width: min(var(--max), calc(100% - 40px));
        margin-inline: auto;
    }

    .skip-link {
        position: absolute;
        inset-inline-start: 16px;
        top: -60px;
        z-index: 100;
        padding: 10px 16px;
        border-radius: 8px;
        background: var(--brand);
        color: #fff;
        transition: top .2s ease;
    }

    .skip-link:focus { top: 16px; color: #fff; }

    .announcement {
        background: var(--brand);
        color: #fff;
        text-align: center;
        padding: 9px 20px;
        font-size: 14px;
    }

    .site-header {
        position: sticky;
        top: 0;
        z-index: 50;
        background: rgba(255, 255, 255, .92);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--line);
    }

    .nav-shell {
        display: flex;
        align-items: center;
        gap: 24px;
        min-height: 72px;
    }

    .brand {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 18px;
        white-space: nowrap;
    }

    .brand-mark {
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        border-radius: 11px;
        background: var(--brand);
        color: #fff;
        flex: none;
    }

    .brand-mark svg { width: 20px; height: 20px; }

    .desktop-nav {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-inline: auto;
    }

    .desktop-nav a {
        padding: 8px 12px;
        border-radius: 9px;
        font-size: 14.5px;
        font-weight: 500;
        color: #334155;
    }

    .desktop-nav a:hover, .desktop-nav a.active {
        background: var(--soft);
        color: var(--brand);
    }

    .nav-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .lang-switch {
        display: inline-flex;
        border: 1px solid var(--line);
        border-radius: 999px;
        overflow: hidden;
        font-size: 12.5px;
        font-weight: 600;
    }

    .lang-switch a {
        padding: 5px 10px;
        color: var(--muted);
    }

    .lang-switch a[aria-current="true"] {
        background: var(--ink);
        color: #fff;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 20px;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 14.5px;
        font-weight: 600;
        line-height: 1.2;
        cursor: pointer;
        transition: transform .15s ease, background .15s ease, box-shadow .15s ease;
    }

    .btn:hover { transform: translateY(-1px); }

    .btn-primary {
        background: var(--brand);
        color: #fff;
        box-shadow: 0 10px 24px -14px color-mix(in srgb, var(--brand) 80%, black);
    }

    .btn-primary:hover { background: color-mix(in srgb, var(--brand) 86%, black); color: #fff; }

    .btn-ghost {
        border-color: var(--line);
        background: var(--surface);
        color: var(--ink);
    }

    .btn-ghost:hover { border-color: color-mix(in srgb, var(--brand) 40%, var(--line)); color: var(--brand); }

    .btn-light {
        background: #fff;
        color: var(--brand);
    }

    .btn-outline-light {
        border-color: rgba(255, 255, 255, .5);
        color: #fff;
        background: transparent;
    }

    .btn-outline-light:hover { background: rgba(255, 255, 255, .12); color: #fff; }

    .btn-sm { padding: 8px 15px; font-size: 13.5px; }

    .mobile-nav { display: none; margin-inline-start: auto; position: relative; }

    .mobile-nav summary {
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border: 1px solid var(--line);
        border-radius: 10px;
        cursor: pointer;
        list-style: none;
    }

    .mobile-nav summary::-webkit-details-marker { display: none; }

    .mobile-nav summary:focus-visible { outline: 3px solid color-mix(in srgb, var(--brand) 40%, transparent); }

    .mobile-panel {
        position: absolute;
        inset-inline-end: 0;
        top: 52px;
        width: min(320px, calc(100vw - 40px));
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 14px;
        display: grid;
        gap: 4px;
    }

    .mobile-panel a {
        padding: 10px 12px;
        border-radius: 9px;
        font-weight: 500;
    }

    .mobile-panel a:hover { background: var(--soft); color: var(--brand); }

    .mobile-panel .mobile-cta { margin-top: 8px; }

    .hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(900px 480px at 92% -10%, color-mix(in srgb, var(--brand) 13%, white), transparent 70%),
            linear-gradient(180deg, #fff 0%, var(--bg) 100%);
        border-bottom: 1px solid var(--line);
    }

    .hero-inner {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr);
        gap: 56px;
        align-items: center;
        padding-block: 84px 92px;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border: 1px solid color-mix(in srgb, var(--brand) 22%, white);
        border-radius: 999px;
        background: color-mix(in srgb, var(--brand) 7%, white);
        color: color-mix(in srgb, var(--brand) 80%, black);
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: .02em;
    }

    .hero h1 {
        font-size: clamp(34px, 5vw, 58px);
        margin-top: 20px;
        max-width: 15ch;
    }

    .hero-copy > p {
        margin-top: 20px;
        color: var(--muted);
        font-size: clamp(16px, 1.6vw, 19px);
        max-width: 54ch;
    }

    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 30px;
    }

    .hero-note {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        margin-top: 26px;
        color: var(--muted);
        font-size: 13.5px;
    }

    .hero-note span { display: inline-flex; align-items: center; gap: 7px; }

    .tick {
        display: inline-grid;
        place-items: center;
        width: 18px;
        height: 18px;
        border-radius: 999px;
        background: color-mix(in srgb, #059669 14%, white);
        color: #047857;
        flex: none;
    }

    .tick svg { width: 11px; height: 11px; }

    .hero-visual { position: relative; }

    .mock {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .mock-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--line);
        background: #fbfcfe;
    }

    .dot { width: 9px; height: 9px; border-radius: 999px; background: #d8dee9; }

    .mock-url {
        margin-inline-start: 10px;
        flex: 1;
        height: 24px;
        border-radius: 7px;
        background: #f1f5f9;
    }

    .mock-body {
        display: grid;
        grid-template-columns: 142px minmax(0, 1fr);
        min-height: 360px;
    }

    .mock-side {
        padding: 18px 14px;
        border-inline-end: 1px solid var(--line);
        background: #fbfcfe;
        display: grid;
        align-content: start;
        gap: 9px;
    }

    .mock-side .side-brand {
        width: 84px;
        height: 10px;
        border-radius: 999px;
        background: var(--brand);
        margin-bottom: 8px;
    }

    .mock-side span {
        height: 8px;
        border-radius: 999px;
        background: #e6ebf2;
    }

    .mock-side span.active { background: color-mix(in srgb, var(--brand) 28%, white); }

    .mock-main { padding: 18px; display: grid; gap: 14px; align-content: start; }

    .mock-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }

    .kpi {
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 10px;
        background: #fff;
    }

    .kpi small { display: block; font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; }

    .kpi b { display: block; margin-top: 6px; font-size: 17px; color: var(--ink); }

    .kpi .bar { margin-top: 8px; height: 6px; border-radius: 999px; background: #eef2f7; overflow: hidden; }

    .kpi .bar i { display: block; height: 100%; width: 62%; background: var(--brand); border-radius: inherit; }

    .mock-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }

    .mock-product { border: 1px solid var(--line); border-radius: 10px; overflow: hidden; background: #fff; }

    .mock-product .thumb {
        height: 62px;
        background: linear-gradient(135deg, color-mix(in srgb, var(--brand) 16%, white), #f1f5f9);
    }

    .mock-product .lines { padding: 8px; display: grid; gap: 5px; }

    .mock-product .lines i { display: block; height: 6px; border-radius: 999px; background: #e8edf4; }

    .mock-product .lines i:last-child { width: 60%; }

    .float-card {
        position: absolute;
        inset-inline-start: -22px;
        bottom: -26px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: var(--shadow-sm);
        font-size: 13px;
        font-weight: 600;
    }

    .float-card .tick { width: 24px; height: 24px; }

    .section { padding-block: 78px; }

    .section.tight { padding-block: 56px; }

    .section-head { max-width: 720px; margin-bottom: 40px; }

    .section-head.center { margin-inline: auto; text-align: center; }

    .section-head h2 { font-size: clamp(26px, 3.4vw, 40px); margin-top: 14px; }

    .section-head p { margin-top: 14px; color: var(--muted); font-size: 17px; }

    .section-head.center p { margin-inline: auto; }

    .grid { display: grid; gap: 20px; }

    .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }

    .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }

    .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

    .card {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 24px;
    }

    .feature-card {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 24px;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .feature-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-sm);
        border-color: color-mix(in srgb, var(--brand) 24%, var(--line));
    }

    .icon-badge {
        display: inline-grid;
        place-items: center;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: color-mix(in srgb, var(--brand) 9%, white);
        color: var(--brand);
        margin-bottom: 16px;
    }

    .icon-badge svg { width: 22px; height: 22px; }

    .feature-card h3 { font-size: 17px; }

    .feature-card p { margin-top: 9px; color: var(--muted); font-size: 14.5px; }

    .feature-card .benefit {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 14px;
        font-size: 12.5px;
        font-weight: 600;
        color: #047857;
    }

    .benefit svg { width: 13px; height: 13px; }

    .values {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .value-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 16px;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--radius-sm);
    }

    .value-item .icon-badge { width: 36px; height: 36px; margin: 0; flex: none; border-radius: 10px; }

    .value-item .icon-badge svg { width: 18px; height: 18px; }

    .value-item h3 { font-size: 14.5px; }

    .value-item p { margin-top: 4px; color: var(--muted); font-size: 13px; }

    .steps { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; counter-reset: step; }

    .step {
        position: relative;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 24px;
    }

    .step-number {
        display: inline-grid;
        place-items: center;
        width: 38px;
        height: 38px;
        border-radius: 11px;
        background: var(--ink);
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        margin-bottom: 16px;
    }

    .step h3 { font-size: 17px; }

    .step p { margin-top: 8px; color: var(--muted); font-size: 14.5px; }

    .showcase {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        align-items: stretch;
    }

    .showcase-card {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        padding: 22px;
        box-shadow: var(--shadow-sm);
    }

    .showcase-card h3 { font-size: 16px; }

    .showcase-card > p { margin-top: 6px; color: var(--muted); font-size: 13.5px; }

    .mini-ui { margin-top: 16px; display: grid; gap: 8px; }

    .mini-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 11px;
        border: 1px solid var(--line);
        border-radius: 9px;
        background: #fbfcfe;
        font-size: 12px;
        color: var(--muted);
    }

    .mini-row .swatch { width: 24px; height: 24px; border-radius: 7px; background: color-mix(in srgb, var(--brand) 18%, white); flex: none; }

    .mini-row .grow { flex: 1; }

    .mini-row .pill { padding: 2px 8px; border-radius: 999px; background: #e8f7ef; color: #047857; font-size: 10.5px; font-weight: 700; }

    .phone-ui {
        width: 190px;
        margin-inline: auto;
        border: 6px solid #1e293b;
        border-radius: 26px;
        padding: 12px 10px;
        background: #fff;
        display: grid;
        gap: 8px;
    }

    .phone-ui .ph-img { height: 92px; border-radius: 12px; background: linear-gradient(135deg, color-mix(in srgb, var(--brand) 20%, white), #eef2f7); }

    .phone-ui .ph-line { height: 7px; border-radius: 999px; background: #e8edf4; }

    .phone-ui .ph-line.short { width: 55%; }

    .phone-ui .ph-btn { height: 26px; border-radius: 999px; background: var(--brand); }

    .pricing-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; align-items: stretch; }

    .price-card {
        position: relative;
        display: flex;
        flex-direction: column;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        padding: 28px;
        box-shadow: var(--shadow-sm);
    }

    .price-card.featured {
        border-color: color-mix(in srgb, var(--brand) 45%, var(--line));
        box-shadow: 0 24px 50px -30px color-mix(in srgb, var(--brand) 60%, black);
    }

    .price-badge {
        position: absolute;
        inset-inline-end: 20px;
        top: -13px;
        padding: 4px 11px;
        border-radius: 999px;
        background: var(--brand);
        color: #fff;
        font-size: 11.5px;
        font-weight: 700;
    }

    .price-card h3 { font-size: 20px; }

    .price-card .plan-description { margin-top: 8px; color: var(--muted); font-size: 14px; min-height: 44px; }

    .price-amount { display: flex; align-items: baseline; gap: 7px; margin-top: 18px; }

    .price-amount strong { font-size: 38px; letter-spacing: -.03em; }

    .price-amount span { color: var(--muted); font-size: 13.5px; }

    .trial-note {
        display: inline-block;
        margin-top: 8px;
        padding: 4px 10px;
        border-radius: 999px;
        background: color-mix(in srgb, #059669 10%, white);
        color: #047857;
        font-size: 12px;
        font-weight: 600;
    }

    .price-lists { margin-top: 20px; display: grid; gap: 18px; flex: 1; }

    .price-lists h4 {
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--muted);
        margin-bottom: 9px;
    }

    .price-lists ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }

    .price-lists li { display: flex; gap: 9px; align-items: flex-start; font-size: 13.5px; }

    .price-lists li .tick, .price-lists li .cross { margin-top: 2px; }

    .price-lists .cross {
        display: inline-grid;
        place-items: center;
        width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #94a3b8;
        flex: none;
    }

    .price-lists .cross svg { width: 10px; height: 10px; }

    .price-card .btn { margin-top: 24px; width: 100%; }

    .empty-state {
        max-width: 640px;
        margin-inline: auto;
        text-align: center;
        background: var(--surface);
        border: 1px dashed var(--line);
        border-radius: var(--radius);
        padding: 40px 28px;
    }

    .empty-state p { margin-top: 10px; color: var(--muted); }

    .faq-list { max-width: 820px; margin-inline: auto; display: grid; gap: 12px; }

    .faq-item {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--radius-sm);
        padding: 0 20px;
    }

    .faq-item summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 0;
        cursor: pointer;
        font-weight: 600;
        font-size: 15.5px;
        list-style: none;
    }

    .faq-item summary::-webkit-details-marker { display: none; }

    .faq-item summary::after {
        content: "+";
        font-size: 20px;
        color: var(--brand);
        line-height: 1;
        flex: none;
    }

    .faq-item[open] summary::after { content: "\2212"; }

    .faq-item .faq-answer { padding-block: 0 18px; color: var(--muted); font-size: 14.5px; }

    .cta-panel {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        background:
            radial-gradient(600px 300px at 100% 0%, rgba(255, 255, 255, .16), transparent 60%),
            var(--brand);
        color: #fff;
        padding: 54px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 28px;
    }

    .cta-panel h2 { font-size: clamp(26px, 3.4vw, 38px); max-width: 20ch; }

    .cta-panel p { margin-top: 12px; color: rgba(255, 255, 255, .82); max-width: 52ch; }

    .cta-actions { display: flex; flex-wrap: wrap; gap: 12px; }

    .page-hero {
        background: linear-gradient(180deg, #fff 0%, var(--bg) 100%);
        border-bottom: 1px solid var(--line);
        padding-block: 64px 56px;
    }

    .page-hero h1 { font-size: clamp(30px, 4.4vw, 48px); margin-top: 16px; max-width: 20ch; }

    .page-hero p { margin-top: 16px; color: var(--muted); font-size: 17px; max-width: 60ch; }

    .page-hero .hero-actions { margin-top: 26px; }

    .list-check { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }

    .list-check li { display: flex; gap: 10px; align-items: flex-start; color: var(--muted); font-size: 14.5px; }

    .list-check li .tick { margin-top: 3px; }

    .prose { max-width: 760px; margin-inline: auto; }

    .prose h2 { font-size: 24px; margin-top: 38px; }

    .prose h2:first-child { margin-top: 0; }

    .prose p { margin-top: 12px; color: var(--muted); }

    .prose ul { margin: 12px 0 0; padding-inline-start: 20px; color: var(--muted); }

    .prose li { margin-top: 6px; }

    .notice {
        margin-top: 32px;
        padding: 16px 18px;
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #92400e;
        border-radius: var(--radius-sm);
        font-size: 13.5px;
    }

    .form-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr); gap: 32px; align-items: start; }

    .field { margin-bottom: 16px; }

    .field label { display: block; font-weight: 600; font-size: 13.5px; margin-bottom: 6px; }

    .field input, .field select, .field textarea {
        width: 100%;
        padding: 11px 13px;
        border: 1px solid var(--line);
        border-radius: var(--radius-sm);
        background: #fff;
        color: var(--ink);
        font: inherit;
        font-size: 14.5px;
    }

    .field input:focus, .field select:focus, .field textarea:focus {
        outline: none;
        border-color: color-mix(in srgb, var(--brand) 55%, var(--line));
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 14%, transparent);
    }

    .field textarea { min-height: 150px; resize: vertical; }

    .field .error { margin-top: 6px; color: #b91c1c; font-size: 12.5px; }

    .check-line { display: flex; gap: 10px; align-items: flex-start; font-size: 13.5px; color: var(--muted); }

    .check-line input { width: 17px; height: 17px; margin-top: 3px; flex: none; }

    .honeypot { position: absolute; inline-size: 1px; block-size: 1px; overflow: hidden; clip-path: inset(50%); }

    .alert {
        padding: 14px 16px;
        border-radius: var(--radius-sm);
        margin-bottom: 18px;
        font-size: 14px;
    }

    .alert.success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }

    .alert.error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

    .side-panel {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 24px;
    }

    .side-panel h3 { font-size: 15px; }

    .side-panel p, .side-panel li { color: var(--muted); font-size: 13.5px; }

    .side-panel ul { list-style: none; margin: 12px 0 0; padding: 0; display: grid; gap: 10px; }

    .site-footer { background: #0b1220; color: #cbd5e1; margin-top: 0; }

    .footer-grid {
        display: grid;
        grid-template-columns: 1.4fr repeat(4, minmax(0, 1fr));
        gap: 32px;
        padding-block: 62px 44px;
    }

    .site-footer .brand { color: #fff; }

    .footer-about p { margin-top: 14px; color: #94a3b8; font-size: 13.5px; max-width: 34ch; }

    .site-footer h3 {
        color: #fff;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 14px;
    }

    .site-footer ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 9px; }

    .site-footer a { color: #94a3b8; font-size: 13.5px; }

    .site-footer a:hover { color: #fff; }

    .footer-bottom {
        border-top: 1px solid #1e293b;
        padding-block: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        color: #64748b;
        font-size: 12.5px;
    }

    .admin-link { color: #64748b !important; }

    .social-links { display: flex; gap: 8px; margin-top: 16px; }

    .social-links a {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        border: 1px solid #1e293b;
        color: #cbd5e1;
    }

    .social-links a:hover { background: #1e293b; color: #fff; }

    .social-links svg { width: 16px; height: 16px; }

    .mt-0 { margin-top: 0; }
    .mt-1 { margin-top: 10px; }
    .mt-2 { margin-top: 20px; }
    .mt-3 { margin-top: 30px; }
    .mb-2 { margin-bottom: 20px; }
    .muted { color: var(--muted); }
    .small { font-size: 13px; }
    .center { text-align: center; }
    .nowrap { white-space: nowrap; }

    @media (max-width: 1040px) {
        .hero-inner { grid-template-columns: 1fr; gap: 44px; padding-block: 64px 72px; }
        .hero h1 { max-width: 22ch; }
        .float-card { inset-inline-start: auto; inset-inline-end: 16px; bottom: -20px; }
        .desktop-nav { display: none; }
        .nav-actions { display: none; }
        .mobile-nav { display: block; }
        .grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .footer-grid { grid-template-columns: 1.2fr 1fr 1fr; }
        .pricing-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 820px) {
        .grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .steps { grid-template-columns: 1fr; }
        .showcase { grid-template-columns: 1fr; }
        .form-grid { grid-template-columns: 1fr; }
        .values { grid-template-columns: 1fr; }
        .cta-panel { padding: 34px; }
        .footer-grid { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 620px) {
        .shell { width: calc(100% - 28px); }
        .section { padding-block: 56px; }
        .hero-inner { padding-block: 48px 60px; }
        .grid-2, .grid-3, .grid-4, .pricing-grid { grid-template-columns: 1fr; }
        .mock-body { grid-template-columns: 96px minmax(0, 1fr); }
        .mock-kpis { grid-template-columns: 1fr 1fr; }
        .mock-grid { grid-template-columns: 1fr 1fr; }
        .footer-grid { grid-template-columns: 1fr; gap: 26px; }
        .cta-panel { padding: 28px 24px; }
        .price-card { padding: 24px; }
    }

    @media (prefers-reduced-motion: reduce) {
        html { scroll-behavior: auto; }
        .btn, .feature-card { transition: none; }
        .btn:hover, .feature-card:hover { transform: none; }
    }
</style>
