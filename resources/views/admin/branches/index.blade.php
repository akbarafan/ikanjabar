@extends('admin.layouts.app')

@section('title', 'Daftar Cabang')
@section('page-title', 'Manajemen Cabang')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Cabang</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $branches->total() }}</h3>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-building text-blue-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $branches->total() }}</span> cabang terdaftar
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Kolam</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $branches->sum('ponds_count') }}</h3>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-swimming-pool text-green-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $branches->sum('ponds_count') }}</span> kolam aktif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Pengguna</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $branches->sum('users_count') }}</h3>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <i class="fas fa-users text-purple-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $branches->sum('users_count') }}</span> pengguna aktif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Batch Aktif</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $branches->sum(function($branch) { return $branch->statistics['total_active_batches'] ?? 0; }) }}</h3>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-fish text-green-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $branches->sum(function($branch) { return $branch->statistics['total_active_batches'] ?? 0; }) }}</span> batch aktif
        </div>
    </div>
</div>

<!-- Branches List -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Cabang</h3>
        <a href="{{ route('admin.branches.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i> Tambah Cabang
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

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
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $branch->name }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->location }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->pic_name }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->ponds_count ?? 0 }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->statistics['total_active_batches'] ?? 0 }}</td>
                    <td class="py-3 px-4 text-sm">
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                    </td>
                    <td class="py-3 px-4 text-sm">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.branches.show', $branch) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="text-yellow-600 hover:text-yellow-900">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
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
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada cabang</h3>
                            <p class="text-gray-500 mb-4">Mulai dengan menambahkan cabang pertama Anda.</p>
                            <a href="{{ route('admin.branches.create') }}"
                               class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i> Tambah Cabang
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $branches->links() }}
    </div>
</div>
@endsection
