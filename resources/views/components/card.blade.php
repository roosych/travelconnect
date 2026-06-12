@props(['title' => null, 'toolbar' => null, 'flush' => false])

<div class="card {{ $flush ? 'card-flush' : '' }}">
    @if($title || $toolbar)
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        @if($title)
        <div class="card-title">
            <h3 class="card-label fw-bold fs-4 mb-0">{{ $title }}</h3>
        </div>
        @endif
        @if($toolbar)
        <div class="card-toolbar flex-row-fluid justify-content-end gap-3">
            {{ $toolbar }}
        </div>
        @endif
    </div>
    @endif
    <div class="card-body {{ $flush ? 'pt-0' : '' }}">
        {{ $slot }}
    </div>
</div>
