@extends('admin.layouts.app')

@section('title', 'Edit Cabang - ' . $branch->name)
@section('page-title', 'Edit Cabang')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Edit Cabang: {{ $branch->name }}</h1>
    <p class="text-gray-600">Perbarui informasi cabang</p>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <form action="{{ route('branches.update', $branch) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Cabang <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $branch->name) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('name') border-red-300 @enderror" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                    <textarea name="location" id="location" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('location') border-red-300 @enderror" required>{{ old('location', $branch->location) }}</textarea>
                    @error('location')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
