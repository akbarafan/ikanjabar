@extends('admin.layouts.app')

@section('title', 'Tambah Cabang Baru')
@section('page-title', 'Tambah Cabang')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Tambah Cabang Baru</h1>
    <p class="text-gray-600">Buat cabang budidaya ikan baru</p>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <form action="{{ route('branches.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Cabang <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('name') border-red-300 @enderror" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                    <textarea name="location" id="location" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('location') border-red-300 @enderror" required>{{ old('location') }}</textarea>
                    @error('location')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <div class="mb-4">
                    <label for="pic_name" class="block text-sm font-medium text-gray-700 mb-1">Nama PIC <span class="text-red-500">*</span></label>
                    <input type="text" name="pic_name" id="pic_name" value="{{ old('pic_name') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('pic_name') border-red-300 @enderror" required>
                    @error('pic_name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">Kontak Person <span class="text-red-500">*</span></label>
                    <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('contact_person') border-red-300 @enderror" required>
                    @error('contact_person')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-6 space-x-3">
            <a href="{{ route('branches.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                Simpan Cabang
            </button>
        </div>
    </form>
</div>
@endsection
