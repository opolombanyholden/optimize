{{-- ============================================================
     Scripts Tom Select pour les sélecteurs de cibles publication
     À inclure dans @push('scripts') du formulaire
     ============================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const palette = ['#7C3AED','#059669','#D97706','#DC2626','#0891B2','#0D9488','#4F46E5','#BE185D'];
    const colorOf = (s) => {
        let h = 0;
        for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) & 0xffffffff;
        return palette[Math.abs(h) % palette.length];
    };

    const usersEl = document.getElementById('select-cibles-users');
    if (usersEl) {
        new TomSelect(usersEl, {
            plugins: ['remove_button', 'checkbox_options'],
            maxOptions: 500,
            searchField: ['text', 'subtitle'],
            dropdownParent: 'body',
            render: {
                option: (data, escape) => {
                    const initials = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                    const sub = data.subtitle ? `<div class="ts-opt-sub">${escape(data.subtitle)}</div>` : '';
                    return `<div class="ts-opt">
                        <span class="ts-opt-avatar" style="background:${colorOf(data.text)};">${escape(initials)}</span>
                        <div class="ts-opt-body"><div class="ts-opt-name">${escape(data.text)}</div>${sub}</div>
                    </div>`;
                },
                item: (data, escape) => {
                    const initials = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                    return `<div class="ts-item-user">
                        <span class="ts-item-avatar" style="background:${colorOf(data.text)};">${escape(initials)}</span>
                        ${escape(data.text)}
                    </div>`;
                },
                no_results: () => '<div class="no-results">Aucun résultat</div>',
            },
            onInitialize: function () {
                Array.from(usersEl.options).forEach(opt => {
                    if (this.options[opt.value]) {
                        this.options[opt.value].initials = opt.dataset.initials || '';
                        this.options[opt.value].subtitle = opt.dataset.subtitle || '';
                    }
                });
            },
        });
    }

    const groupesEl = document.getElementById('select-cibles-groupes');
    if (groupesEl) {
        new TomSelect(groupesEl, {
            plugins: ['remove_button', 'checkbox_options'],
            maxOptions: 500,
            dropdownParent: 'body',
            render: {
                option: (d, e) => `<div class="ts-opt"><span class="ts-opt-color" style="background:${e(d.color || '#7C3AED')};"></span><div class="ts-opt-body"><div class="ts-opt-name">${e(d.text)}</div></div></div>`,
                item: (d, e) => `<div class="ts-item-group"><span class="ts-item-dot" style="background:${e(d.color || '#7C3AED')};"></span>${e(d.text)}</div>`,
                no_results: () => '<div class="no-results">Aucun résultat</div>',
            },
            onInitialize: function () {
                Array.from(groupesEl.options).forEach(opt => {
                    if (this.options[opt.value]) this.options[opt.value].color = opt.dataset.color || '#7C3AED';
                });
            },
        });
    }

    const visSelect  = document.getElementById('visibiliteSelect');
    const ciblesCard = document.getElementById('ciblesCard');
    function update() {
        if (!visSelect || !ciblesCard) return;
        ciblesCard.style.opacity = visSelect.value === 'prive' ? '1' : '.5';
        ciblesCard.style.pointerEvents = visSelect.value === 'prive' ? 'auto' : 'none';
    }
    if (visSelect) { visSelect.addEventListener('change', update); update(); }
});
</script>
