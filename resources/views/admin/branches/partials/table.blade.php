<div class="overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Cabang</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolam</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Aktif</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($branches as $branch)
            <tr class="hover:bg-gray-50">
                <td class="py-3 px-4 text-sm font-medium text-gray-900">
                    @if($searchTerm)
                        {!! str_ireplace($searchTerm, '<mark class="bg-yellow-200 px-1 rounded">' . $searchTerm . '</mark>', $branch->name) !!}
                    @else
                        {{ $branch->name }}
                    @endif
                </td>
                <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->location }}</td>
                <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->pic_name }}</td>
                <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->ponds_count ?? 0 }}</td>
                <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->statistics['total_active_batches'] ?? 0 }}</td>
                <td class="py-3 px-4 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                </td>
                <td class="py-3 px-4 text-sm">
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.branches.detail', $branch) }}" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        {{-- <a href="{{ route('admin.branches.edit', $branch) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a> --}}
                        <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang {{ $branch->name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-building text-gray-300 text-4xl mb-4"></i>
                        @if($searchTerm)
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Cabang tidak ditemukan</h3>
                            <p class="text-gray-500 mb-4">
                                Tidak ada cabang dengan nama yang mengandung kata <strong>"{{ $searchTerm }}"</strong>.
                                <br>
                                Coba gunakan kata kunci lain atau periksa ejaan pencarian Anda.
                            </p>
                            <div class="flex gap-2">
                                <button onclick="resetSearch()" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600 transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i> Lihat Semua Cabang
                                </button>
                                <a href="{{ route('admin.branches.create') }}"
                                   class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i> Tambah Cabang Baru
                                </a>
                            </div>
                        @else
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada cabang</h3>
                            <p class="text-gray-500 mb-4">Mulai dengan menambahkan cabang pertama Anda.</p>
                            <a href="{{ route('admin.branches.create') }}"
                               class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i> Tambah Cabang
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
