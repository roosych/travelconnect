@props(['message' => 'Записей не найдено.', 'icon' => 'ki-outline ki-information-5'])

<div class="text-center py-12">
    <i class="{{ $icon }} fs-3x text-gray-300 mb-4 d-block"></i>
    <span class="text-muted fs-6">{{ $message }}</span>
</div>
