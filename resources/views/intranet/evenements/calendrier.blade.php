@extends('layouts.app')

@section('title', 'Calendrier')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Calendrier</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-calendar-days"></i></span>
                Calendrier
            </h1>
            <p class="page-subtitle">Vue globale de tous les événements</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('intranet.evenements.index') }}" class="btn btn-light">
                <i class="fas fa-list me-2"></i> Vue liste
            </a>
            @can('create:evenement')
            <a href="{{ route('intranet.evenements.create') }}" class="btn btn-intranet">
                <i class="fas fa-plus me-2"></i> Nouvel événement
            </a>
            @endcan
        </div>
    </div>

    {{-- Légende des types --}}
    @if($types->count())
    <div class="cal-legend mb-3">
        @foreach($types as $type)
            <span class="cal-legend-item">
                <span class="cal-legend-dot" style="background:{{ $type->couleur }};"></span>
                {{ $type->nom }}
            </span>
        @endforeach
    </div>
    @endif

    {{-- Conteneur FullCalendar --}}
    <div class="form-card">
        <div class="form-card-body">
            <div id="calendar"></div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<style>
    .cal-legend { display: flex; flex-wrap: wrap; gap: 1rem; padding: .8rem 1.1rem; background: #fff; border: 1px solid #F1F5F9; border-radius: 11px; font-size: .76rem; color: #475569; }
    .cal-legend-item { display: flex; align-items: center; gap: .35rem; }
    .cal-legend-dot { width: 12px; height: 12px; border-radius: 50%; }

    /* Personnalisation FullCalendar */
    .fc { font-family: 'Inter', sans-serif; font-size: .82rem; }
    .fc .fc-toolbar-title { font-size: 1.15rem; font-weight: 700; color: #0F172A; }
    .fc .fc-button {
        background: #fff;
        border: 1.5px solid #E2E8F0;
        color: #475569;
        font-weight: 600;
        font-size: .78rem;
        padding: .4rem .85rem;
        border-radius: 8px;
        text-transform: capitalize;
    }
    .fc .fc-button:hover { background: #F8FAFC; border-color: #7C3AED; color: #7C3AED; }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background: linear-gradient(135deg,#7C3AED,#4F46E5);
        border-color: #7C3AED;
        color: #fff;
    }
    .fc .fc-col-header-cell-cushion {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #94A3B8;
        letter-spacing: .04em;
        padding: .55rem .35rem;
    }
    .fc .fc-daygrid-day-number {
        font-size: .82rem;
        font-weight: 600;
        color: #475569;
        padding: .45rem .55rem;
    }
    .fc .fc-day-today {
        background: rgba(124, 58, 237, .06) !important;
    }
    .fc .fc-day-today .fc-daygrid-day-number {
        color: #7C3AED;
    }
    .fc .fc-event {
        border-radius: 6px;
        padding: 1px 5px;
        font-size: .72rem;
        border: none;
        cursor: pointer;
        transition: opacity .15s, transform .15s;
    }
    .fc .fc-event:hover { opacity: .85; transform: translateY(-1px); }
    .fc .fc-event-title { font-weight: 600; }
    .fc-theme-standard .fc-scrollgrid,
    .fc-theme-standard td,
    .fc-theme-standard th { border-color: #F1F5F9; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/fr.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('calendar');
    if (!el) return;

    const calendar = new FullCalendar.Calendar(el, {
        locale: 'fr',
        initialView: 'dayGridMonth',
        height: 'auto',
        firstDay: 1,
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        buttonText: {
            today: "Aujourd'hui",
            month: 'Mois',
            week:  'Semaine',
            day:   'Jour',
            list:  'Liste',
        },
        events: '{{ route("intranet.calendrier.feed") }}',
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            if (info.event.url) window.location.href = info.event.url;
        },
        // Clic sur une date / cellule vide → création d'événement pré-rempli
        dateClick: function (info) {
            const base = '{{ route("intranet.evenements.create") }}';
            const date = info.dateStr.substring(0, 10);
            const time = info.dateStr.length > 10 ? info.dateStr.substring(11, 16) : '09:00';
            window.location.href = base + '?date_debut=' + encodeURIComponent(date) + '&heure_debut=' + encodeURIComponent(time);
        },
        // Sélection d'une plage (drag) → création avec dates de début/fin
        selectable: true,
        select: function (info) {
            const base = '{{ route("intranet.evenements.create") }}';
            window.location.href = base
                + '?date_debut=' + encodeURIComponent(info.startStr.substring(0, 10))
                + '&date_fin='   + encodeURIComponent(info.endStr.substring(0, 10));
        },
        eventDidMount: function (info) {
            if (info.event.extendedProps.lieu) {
                info.el.title = info.event.extendedProps.lieu;
            }
        },
        dayMaxEvents: 3,
        nowIndicator: true,
    });
    calendar.render();
});
</script>
@endpush
