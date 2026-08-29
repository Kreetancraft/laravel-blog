@once
    <style>
        .compact-table [data-flux-cell],
        .compact-table [data-flux-column] {
            padding-top: 0.375rem !important;
            padding-bottom: 0.375rem !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        .compact-table [data-flux-cell]:first-child,
        .compact-table [data-flux-column]:first-child {
            padding-left: 0.75rem !important;
        }
        .compact-table [data-flux-cell]:last-child,
        .compact-table [data-flux-column]:last-child {
            padding-right: 0.75rem !important;
        }
        .compact-table tbody tr {
            transition: background-color 100ms ease-in-out;
        }
    </style>
@endonce
