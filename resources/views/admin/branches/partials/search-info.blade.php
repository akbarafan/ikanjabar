@if($searchTerm)
    @if($hasResults)
        <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-400">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-400 mr-2"></i>
                <span class="text-sm text-blue-700">
                    Menampilkan hasil pencarian untuk: <strong>"{{ $searchTerm }}"</strong>
                    ({{ $total }} cabang ditemukan)
                </span>
            </div>
        </div>
    @else
        <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-400">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
                <span class="text-sm text-red-700">
                    <strong>Hasil Tidak Ditemukan</strong> - Pencarian untuk: <strong>"{{ $searchTerm }}"</strong>
                </span>
            </div>
        </div>
    @endif
@endif
