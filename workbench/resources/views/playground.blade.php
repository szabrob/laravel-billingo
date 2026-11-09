<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Billingo Playground</title>
    <style>
        :root {
            --bg: #f6f7f9; --panel: #fff; --text: #1d2330; --muted: #6b7280; --border: #e3e6eb;
            --accent: #2f6fed; --accent-soft: #e8f0fe; --ok: #138a52; --ok-soft: #e3f6ec;
            --warn: #b26a00; --warn-soft: #fff3df; --danger: #c62828; --danger-soft: #fdeaea;
            --code: #f3f4f6; --mono: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #12151b; --panel: #1a1e26; --text: #e6e8ec; --muted: #9aa1ad; --border: #2a303b;
                --accent: #6b9bff; --accent-soft: #1f2a44; --ok: #4cc38a; --ok-soft: #15301f;
                --warn: #f0b04a; --warn-soft: #34280f; --danger: #f07171; --danger-soft: #3a1717; --code: #11141a;
            }
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--text); font: 14px/1.5 system-ui, -apple-system, Segoe UI, sans-serif; }
        header { display: flex; flex-wrap: wrap; gap: 12px 24px; align-items: center; padding: 12px 20px; background: var(--panel); border-bottom: 1px solid var(--border); }
        header h1 { font-size: 16px; margin: 0; }
        header .cfg { display: flex; flex-wrap: wrap; gap: 6px 16px; color: var(--muted); font-size: 12px; }
        header .cfg b { color: var(--text); font-weight: 600; font-family: var(--mono); }
        header label { margin-left: auto; display: flex; gap: 8px; align-items: center; font-size: 12px; color: var(--muted); }
        header input { width: 260px; }
        .layout { display: grid; grid-template-columns: 260px minmax(0, 1fr); min-height: calc(100vh - 57px); }
        nav { background: var(--panel); border-right: 1px solid var(--border); padding: 12px 0; overflow-y: auto; max-height: calc(100vh - 57px); position: sticky; top: 0; }
        nav input { margin: 0 12px 8px; width: calc(100% - 24px); }
        nav h2 { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin: 14px 16px 4px; }
        nav button { display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 5px 16px; border: 0; background: none; color: var(--text); font: 13px var(--mono); text-align: left; cursor: pointer; }
        nav button:hover { background: var(--accent-soft); }
        nav button.active { background: var(--accent-soft); color: var(--accent); font-weight: 600; }
        main { padding: 20px; display: grid; gap: 16px; align-content: start; }
        .card { background: var(--panel); border: 1px solid var(--border); border-radius: 8px; padding: 16px; }
        .card h3 { margin: 0 0 4px; font: 600 15px var(--mono); }
        .muted { color: var(--muted); }
        .badge { font: 600 10px system-ui; text-transform: uppercase; padding: 1px 6px; border-radius: 4px; }
        .badge.read { background: var(--ok-soft); color: var(--ok); }
        .badge.write { background: var(--warn-soft); color: var(--warn); }
        .badge.danger { background: var(--danger-soft); color: var(--danger); }
        .field { display: grid; gap: 4px; margin-top: 12px; }
        .field label { font: 600 12px var(--mono); }
        .field label span { font-weight: 400; color: var(--muted); }
        input, select, textarea { font: 13px var(--mono); color: var(--text); background: var(--bg); border: 1px solid var(--border); border-radius: 6px; padding: 6px 8px; }
        textarea { min-height: 180px; resize: vertical; }
        .actions { display: flex; gap: 8px; align-items: center; margin-top: 16px; flex-wrap: wrap; }
        .btn { border: 1px solid var(--border); background: var(--panel); color: var(--text); padding: 7px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn.primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        .btn.primary.danger { background: var(--danger); border-color: var(--danger); }
        .btn:disabled { opacity: .6; cursor: wait; }
        pre { margin: 0; padding: 12px; background: var(--code); border: 1px solid var(--border); border-radius: 6px; overflow: auto; max-height: 560px; font: 12px/1.45 var(--mono); white-space: pre-wrap; word-break: break-word; }
        .status { display: flex; gap: 12px; align-items: center; margin-bottom: 12px; flex-wrap: wrap; }
        .status .pill { padding: 2px 10px; border-radius: 999px; font-weight: 600; font-size: 12px; }
        .pill.ok { background: var(--ok-soft); color: var(--ok); }
        .pill.fail { background: var(--danger-soft); color: var(--danger); }
        .tabs { display: flex; gap: 4px; margin-bottom: 10px; }
        .tabs button { border: 0; background: none; color: var(--muted); padding: 6px 10px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .tabs button.active { background: var(--accent-soft); color: var(--accent); }
        .http-entry + .http-entry { margin-top: 14px; }
        .http-entry summary { cursor: pointer; font: 12px var(--mono); padding: 4px 0; }
        .http-entry pre { margin-top: 6px; }
        .empty { text-align: center; padding: 60px 20px; }
        @media (max-width: 760px) {
            .layout { grid-template-columns: 1fr; }
            nav { position: static; max-height: 280px; border-right: 0; border-bottom: 1px solid var(--border); }
            header label { margin-left: 0; width: 100%; }
            header input { flex: 1; width: auto; }
            main { padding: 16px; }
        }
    </style>
</head>
<body>
<header>
    <h1>Billingo Playground</h1>
    <div class="cfg">
        <span>API key: <b>{{ $config['api_key'] ?? 'nincs beállítva' }}</b></span>
        <span>Base URL: <b>{{ $config['base_url'] }}</b></span>
        <span>Timeout: <b>{{ $config['timeout'] }}s</b></span>
        <span>Rate limit: <b>{{ $config['rate_limit']['retries'] ?? 0 }}× / max {{ $config['rate_limit']['max_delay'] ?? 0 }}s</b></span>
    </div>
    <label>withApiKey()
        <input id="api-key" type="password" placeholder="opcionális felülírás" autocomplete="off">
    </label>
</header>

<div class="layout">
    <nav id="nav">
        <input id="filter" type="search" placeholder="Szűrés…">
    </nav>
    <main id="main">
        <div class="card empty muted">
            Válassz egy metódust a bal oldali listából.<br>
            A <span class="badge write">write</span> és <span class="badge danger">danger</span> metódusok valódi adatot módosítanak a Billingo fiókodban.
        </div>
    </main>
</div>

<script>
    const catalog = @json($catalog);
    const callUrl = @json(route('playground.call'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const nav = document.getElementById('nav');
    const main = document.getElementById('main');
    const apiKeyInput = document.getElementById('api-key');
    const store = {
        get(key) { try { return JSON.parse(localStorage.getItem('billingo-playground:' + key)); } catch { return null; } },
        set(key, value) { try { localStorage.setItem('billingo-playground:' + key, JSON.stringify(value)); } catch {} },
    };

    const el = (tag, attrs = {}, ...children) => {
        const node = document.createElement(tag);
        for (const [key, value] of Object.entries(attrs)) {
            if (key === 'class') node.className = value;
            else if (key.startsWith('on')) node.addEventListener(key.slice(2), value);
            else if (value !== undefined && value !== null && value !== false) node.setAttribute(key, value === true ? '' : value);
        }
        node.append(...children.filter((child) => child !== null && child !== undefined));
        return node;
    };
    const pretty = (value) => JSON.stringify(value, null, 2);

    // Navigation
    const buttons = [];
    for (const [resource, methods] of Object.entries(catalog)) {
        const group = el('div', { 'data-group': resource }, el('h2', {}, resource + '()'));
        for (const method of methods) {
            const button = el('button', {
                onclick: () => select(resource, method, button),
                'data-search': (resource + '.' + method.name).toLowerCase(),
            }, method.name, el('span', { class: 'badge ' + method.kind }, method.kind));
            buttons.push(button);
            group.append(button);
        }
        nav.append(group);
    }

    document.getElementById('filter').addEventListener('input', (event) => {
        const term = event.target.value.toLowerCase();
        for (const group of nav.querySelectorAll('[data-group]')) {
            let visible = 0;
            for (const button of group.querySelectorAll('button')) {
                const show = button.dataset.search.includes(term);
                button.hidden = !show;
                visible += show;
            }
            group.hidden = visible === 0;
        }
    });

    // Method form
    function select(resource, method, button) {
        buttons.forEach((b) => b.classList.remove('active'));
        button.classList.add('active');
        location.hash = resource + '.' + method.name;

        const saved = store.get('args:' + resource + '.' + method.name) || {};
        const inputs = {};

        const form = el('form', { class: 'card', onsubmit: (event) => { event.preventDefault(); run(resource, method, inputs, submit); } },
            el('h3', {}, `Billingo::${resource}()->${method.name}()`),
            el('div', { class: 'muted' }, el('span', { class: 'badge ' + method.kind }, method.kind), ' → ', method.returns || 'mixed'),
            method.doc ? el('p', { class: 'muted', style: 'margin:8px 0 0' }, method.doc) : null,
        );

        if (method.params.length === 0) {
            form.append(el('p', { class: 'muted' }, 'Nincs paraméter.'));
        }

        for (const param of method.params) {
            const hint = [param.type, param.optional ? `= ${pretty(param.default)}` : null, param.nullable && !param.optional ? 'nullable' : null].filter(Boolean).join(' ');
            const id = 'param-' + param.name;
            let input;
            const savedValue = saved[param.name];

            if (param.input === 'select') {
                input = el('select', { id }, el('option', { value: '' }, param.nullable || param.optional ? '— nincs —' : '— válassz —'),
                    ...param.options.map((option) => el('option', { value: option, selected: String(savedValue ?? param.default) === String(option) }, option)));
            } else if (param.input === 'json') {
                input = el('textarea', { id, spellcheck: 'false' });
                input.value = savedValue ?? pretty(param.template);
            } else if (param.input === 'checkbox') {
                input = el('input', { id, type: 'checkbox', checked: savedValue ?? param.default ?? false });
            } else {
                input = el('input', { id, type: param.input, placeholder: param.default ?? '' });
                input.value = savedValue ?? '';
            }

            inputs[param.name] = { param, input };
            const extra = param.input === 'json'
                ? el('div', { class: 'actions', style: 'margin-top:4px' },
                    el('button', { type: 'button', class: 'btn', onclick: () => { input.value = pretty(param.template); } }, 'Sablon visszaállítása'),
                    el('button', { type: 'button', class: 'btn', onclick: () => { try { input.value = pretty(stripNulls(JSON.parse(input.value))); } catch (e) { alertInline(input, e.message); } } }, 'Null mezők törlése'),
                    param.nullable || param.optional ? el('button', { type: 'button', class: 'btn', onclick: () => { input.value = ''; } }, 'Ürítés (null)') : null)
                : null;

            form.append(el('div', { class: 'field' }, el('label', { for: id }, param.name, ' ', el('span', {}, hint)), input, extra));
        }

        const submit = el('button', { type: 'submit', class: 'btn primary' + (method.kind === 'danger' ? ' danger' : '') }, 'Futtatás');
        form.append(el('div', { class: 'actions' }, submit, el('code', { class: 'muted', id: 'snippet' })));

        const output = el('div', { class: 'card', id: 'output', hidden: true });
        main.replaceChildren(form, output);

        const updateSnippet = () => { document.getElementById('snippet').textContent = snippet(resource, method, inputs); };
        form.addEventListener('input', updateSnippet);
        updateSnippet();
    }

    function stripNulls(value) {
        if (Array.isArray(value)) return value.map(stripNulls);
        if (value && typeof value === 'object') {
            const result = {};
            for (const [key, item] of Object.entries(value)) {
                const cleaned = stripNulls(item);
                if (cleaned === null || (typeof cleaned === 'object' && !Array.isArray(cleaned) && Object.keys(cleaned).length === 0)) continue;
                result[key] = cleaned;
            }
            return result;
        }
        return value;
    }

    function alertInline(input, message) {
        input.setCustomValidity(message);
        input.reportValidity();
        setTimeout(() => input.setCustomValidity(''), 3000);
    }

    function collect(inputs) {
        const args = {};
        for (const [name, { param, input }] of Object.entries(inputs)) {
            args[name] = param.input === 'checkbox' ? input.checked : input.value;
        }
        return args;
    }

    function snippet(resource, method, inputs) {
        const args = Object.entries(collect(inputs))
            .filter(([, value]) => value !== '' && value !== false)
            .map(([name, value]) => {
                const { param } = inputs[name];
                if (param.input === 'json') return `${name}: ${param.type.split('\\').pop()}…`;
                if (param.input === 'number') return `${name}: ${value}`;
                if (param.input === 'select') return `${name}: ${param.type.split('\\').pop()}::from('${value}')`;
                return `${name}: '${value}'`;
            });
        return `Billingo::${resource}()->${method.name}(${args.join(', ')})`;
    }

    async function run(resource, method, inputs, submit) {
        if (method.kind !== 'read' && !confirm(`${resource}()->${method.name}() módosítja az adatokat a Billingo fiókban. Folytatod?`)) {
            return;
        }

        const args = collect(inputs);
        store.set('args:' + resource + '.' + method.name, args);

        submit.disabled = true;
        submit.textContent = 'Futtatás…';
        const output = document.getElementById('output');

        try {
            const response = await fetch(callUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ resource, method: method.name, args, api_key: apiKeyInput.value || null }),
            });
            const text = await response.text();
            let payload;
            try { payload = JSON.parse(text); } catch { payload = { ok: false, error: { message: `HTTP ${response.status}`, body: text.slice(0, 4000) } }; }
            render(output, payload);
        } catch (error) {
            render(output, { ok: false, error: { message: error.message } });
        } finally {
            submit.disabled = false;
            submit.textContent = 'Futtatás';
        }
    }

    function render(output, payload) {
        output.hidden = false;
        const http = payload.http || [];
        const status = el('div', { class: 'status' },
            el('span', { class: 'pill ' + (payload.ok ? 'ok' : 'fail') }, payload.ok ? 'Sikeres' : 'Hiba'),
            payload.duration_ms !== undefined ? el('span', { class: 'muted' }, payload.duration_ms + ' ms') : null,
            el('span', { class: 'muted' }, http.length + ' HTTP kérés'),
            payload.result ? el('code', { class: 'muted' }, payload.result.type) : null,
        );

        const panes = {
            'Eredmény': () => resultPane(payload),
            'HTTP napló': () => httpPane(http),
        };
        const body = el('div');
        const tabs = el('div', { class: 'tabs' });
        for (const name of Object.keys(panes)) {
            const tab = el('button', { type: 'button', onclick: () => {
                tabs.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
                tab.classList.add('active');
                body.replaceChildren(panes[name]());
            } }, name);
            tabs.append(tab);
        }
        output.replaceChildren(status, tabs, body);
        tabs.querySelector('button').click();
        output.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function resultPane(payload) {
        if (!payload.ok) return el('pre', {}, pretty(payload.error));

        const result = payload.result;
        if (result.type === 'binary') {
            const bytes = Uint8Array.from(atob(result.base64), (c) => c.charCodeAt(0));
            const url = URL.createObjectURL(new Blob([bytes], { type: result.mime }));
            return el('div', { class: 'actions', style: 'margin:0' },
                el('span', {}, `${result.mime}, ${result.size.toLocaleString()} bájt`),
                el('a', { class: 'btn', href: url, target: '_blank' }, 'Megnyitás'),
                el('a', { class: 'btn', href: url, download: result.mime === 'application/pdf' ? 'billingo.pdf' : 'billingo.bin' }, 'Letöltés'));
        }

        return el('div', {},
            result.note ? el('p', { class: 'muted', style: 'margin-top:0' }, result.note) : null,
            result.meta ? el('pre', { style: 'margin-bottom:10px' }, pretty(result.meta)) : null,
            el('pre', {}, result.type === 'void' ? 'void (nincs visszatérési érték)' : pretty(result.value)));
    }

    function httpPane(http) {
        if (http.length === 0) return el('p', { class: 'muted' }, 'Nem történt HTTP kérés.');

        return el('div', {}, ...http.map((entry, index) => el('details', { class: 'http-entry', open: index === http.length - 1 },
            el('summary', {}, `#${index + 1} ${entry.method} ${entry.url} → `, el('b', {}, entry.status ?? 'connection failed')),
            entry.error ? el('pre', {}, entry.error) : null,
            entry.request_body !== null && entry.request_body !== undefined ? el('div', {}, el('p', { class: 'muted' }, 'Request body'), el('pre', {}, pretty(entry.request_body))) : null,
            entry.response_headers ? el('div', {}, el('p', { class: 'muted' }, 'Response headers'), el('pre', {}, pretty(entry.response_headers))) : null,
            entry.response_body !== undefined ? el('div', {}, el('p', { class: 'muted' }, 'Response body'), el('pre', {}, typeof entry.response_body === 'string' ? entry.response_body : pretty(entry.response_body))) : null,
        )));
    }

    // Select the method named in the URL hash, e.g. #partners.list
    const selectFromHash = () => {
        const target = buttons.find((button) => button.dataset.search === decodeURIComponent(location.hash.slice(1)).toLowerCase());
        if (target && !target.classList.contains('active')) target.click();
    };
    window.addEventListener('hashchange', selectFromHash);
    selectFromHash();
</script>
</body>
</html>
