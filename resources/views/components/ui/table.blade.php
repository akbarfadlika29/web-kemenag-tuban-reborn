@props([
    'striped' => false,
])

<div class="ui-table-responsive">
    <table
        {{ $attributes->class([
            'ui-table',
            'ui-table-striped' => $striped,
        ]) }}
    >
        {{ $slot }}
    </table>
</div>