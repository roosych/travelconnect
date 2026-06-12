@props(['title', 'breadcrumbs' => [], 'actions' => null])

<div class="d-flex flex-stack mb-6">
    <div>
        <h2 class="fw-bold text-gray-900 mb-1">{{ $title }}</h2>
        @if(count($breadcrumbs))
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
            </li>
            @foreach($breadcrumbs as $crumb)
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">
                @if(isset($crumb['url']))
                    <a href="{{ $crumb['url'] }}" class="text-muted text-hover-primary">{{ $crumb['label'] }}</a>
                @else
                    {{ $crumb['label'] }}
                @endif
            </li>
            @endforeach
        </ul>
        @endif
    </div>
    @if($actions)
    <div class="d-flex gap-2">
        {{ $actions }}
    </div>
    @endif
</div>
