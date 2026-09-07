<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Guide & Docs') · OptimiZe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        :root {
            --ds-ground: #FAFAF7;
            --ds-panel: #FFFFFF;
            --ds-ink: #171717;
            --ds-body: #404040;
            --ds-mute: #737373;
            --ds-faint: #A3A29E;
            --ds-line: #E5E5E0;
            --ds-line-2: #F0EFEA;
            --ds-brand: #7C3AED;
            --ds-brand-soft: #E9D5FF;
            --ds-serif: Georgia, "Iowan Old Style", "Palatino Linotype", "Palatino", serif;
            --ds-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, "Helvetica Neue", Arial, sans-serif;
            --ds-mono: "SF Mono", "Menlo", "Consolas", ui-monospace, monospace;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: var(--ds-ground); color: var(--ds-body); font-family: var(--ds-serif); font-size: 16px; line-height: 1.65; -webkit-font-smoothing: antialiased; }

        /* Top bar */
        .ds-topbar {
            position: sticky; top: 0; z-index: 100;
            background: var(--ds-panel); border-bottom: 1px solid var(--ds-line);
            padding: .65rem 1.25rem;
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            font-family: var(--ds-sans);
        }
        .ds-nav {
            display: flex; align-items: center; gap: .5rem; min-width: 0; flex: 1;
        }
        .ds-brand {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .45rem .75rem; text-decoration: none;
            font-size: .78rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
            color: var(--ds-brand); background: var(--ds-brand-soft); border-radius: 4px;
            transition: opacity .15s;
        }
        .ds-brand:hover { opacity: .85; color: var(--ds-brand); }
        .ds-brand i { font-size: .82rem; }

        .ds-sep { color: var(--ds-faint); font-size: .9rem; padding: 0 .1rem; user-select: none; }

        .ds-crumb {
            color: var(--ds-ink); font-size: .9rem; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;
            text-decoration: none;
        }
        .ds-crumb.is-link { color: var(--ds-mute); font-weight: 500; }
        .ds-crumb.is-link:hover { color: var(--ds-brand); }

        .ds-actions { display: flex; gap: .5rem; flex-shrink: 0; }

        .ds-btn {
            display: inline-flex; align-items: center; gap: .45rem;
            padding: .5rem .85rem; border-radius: 4px; text-decoration: none;
            font-family: var(--ds-sans); font-size: .82rem; font-weight: 600;
            border: 1px solid transparent; transition: background .15s, border-color .15s;
            background: transparent; color: var(--ds-ink); cursor: pointer;
        }
        .ds-btn-primary { background: var(--ds-brand); color: #fff; border-color: var(--ds-brand); }
        .ds-btn-primary:hover { background: #6D28D9; color: #fff; }
        .ds-btn-outline { border-color: var(--ds-line); }
        .ds-btn-outline:hover { border-color: var(--ds-brand); color: var(--ds-brand); }
        .ds-btn-ghost:hover { background: var(--ds-line-2); }

        /* Content */
        .ds-main { padding: 2rem 0; }

        @media (max-width: 720px) {
            .ds-topbar { padding: .55rem .85rem; }
            .ds-brand { padding: .35rem .5rem; font-size: .68rem; }
            .ds-brand span.ds-brand-label { display: none; }
            .ds-crumb { font-size: .82rem; }
            .ds-btn { padding: .42rem .6rem; font-size: .74rem; }
            .ds-btn span.ds-btn-label { display: none; }
        }

        @media print {
            .ds-topbar, .ds-no-print { display: none !important; }
            .ds-main { padding: 0; }
            body { background: #fff; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="ds-topbar ds-no-print">
        <nav class="ds-nav">
            {{-- Toujours cliquable : ramène à la bibliothèque des guides --}}
            <a href="{{ route('docs.index') }}" class="ds-brand" title="Bibliothèque des guides">
                <i class="fas fa-book-open"></i>
                <span class="ds-brand-label">Guide &amp; Docs</span>
            </a>

            @hasSection('crumb')
                <span class="ds-sep">/</span>
                @yield('crumb')
            @endif
        </nav>

        <div class="ds-actions">
            @yield('actions')
            {{-- Toujours dispo : ré-ouvrir le sélecteur d'espaces (revient dans le cœur de l'app) --}}
            <button type="button" class="ds-btn ds-btn-outline"
                    onclick="if (window.openWorkspacePicker) window.openWorkspacePicker({ mandatory: false });"
                    title="Choisir un autre espace de travail">
                <i class="fas fa-grip"></i>
                <span class="ds-btn-label">Changer d'espace</span>
            </button>
        </div>
    </header>

    <main class="ds-main">
        @yield('content')
    </main>

    {{-- Workspace picker inclus ici pour permettre le retour vers l'app depuis l'espace Docs --}}
    @auth
        @include('layouts.partials.workspace-picker')
    @endauth

    @stack('scripts')
</body>
</html>
