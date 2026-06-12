<div class="d-flex flex-column align-items-center py-10">

    <div class="d-flex align-items-center justify-content-center
                w-70px h-70px rounded-3 bg-light-{{ $color }} mb-6">
        <i class="ki-outline {{ $icon }} fs-2x text-{{ $color }}"></i>
    </div>

    <h4 class="fw-bold text-gray-800 mb-2">{{ $title }}</h4>
    <p class="text-muted text-center fs-6 mb-6 mw-400px">{{ $description }}</p>

    @if(!empty($features))
    <div class="d-flex flex-column gap-2 mb-8">
        @foreach($features as $f)
        <div class="d-flex align-items-center gap-2 text-muted fs-7">
            <i class="ki-outline ki-check-circle fs-5 text-{{ $color }}"></i>
            {{ $f }}
        </div>
        @endforeach
    </div>
    @endif

    <span class="badge badge-light-{{ $color }} fs-7 px-4 py-2 fw-semibold">
        <i class="ki-outline ki-time fs-6 me-1 text-{{ $color }}"></i>В разработке
    </span>

</div>
