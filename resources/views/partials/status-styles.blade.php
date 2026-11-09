<style>
@foreach(['pending', 'approved', 'cancelled'] as $status)
    @php
        $background = $settings?->{'badge_'.$status.'_bg'} ?? ['pending' => '#fef3c7', 'approved' => '#d1fae5', 'cancelled' => '#fee2e2'][$status];
        $foreground = $settings?->{'badge_'.$status.'_text'} ?? ['pending' => '#92400e', 'approved' => '#065f46', 'cancelled' => '#991b1b'][$status];
    @endphp
    .badge-{{ $status }}, .status-badge.{{ $status }}, span.status-{{ $status }}, .badge.status-{{ $status }} {
        background: {{ $background }} !important;
        color: {{ $foreground }} !important;
        font-weight: {{ ($settings?->{'badge_'.$status.'_bold'} ?? true) ? '700' : '400' }} !important;
        font-size: 0.75rem !important;
        line-height: 1.3 !important;
        padding: 4px 10px !important;
        border-radius: 999px !important;
        display: inline-flex;
        align-items: center;
    }
@endforeach
</style>
