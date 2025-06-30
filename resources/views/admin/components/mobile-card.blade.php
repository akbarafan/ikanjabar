<div class="w-full max-w-sm mx-auto bg-white rounded-lg shadow-md overflow-hidden sm:max-w-md md:max-w-lg lg:max-w-xl">
    <!-- Mobile Card Header -->
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 sm:px-6">
        <h3 class="text-lg font-medium text-gray-900 sm:text-xl">
            {{ $title ?? 'Card Title' }}
        </h3>
    </div>
    
    <!-- Mobile Card Content -->
    <div class="p-4 sm:p-6">
        <div class="space-y-3 sm:space-y-4">
            {{ $slot }}
        </div>
    </div>
    
    <!-- Mobile Card Actions (if provided) -->
    @isset($actions)
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
            <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3 sm:justify-end">
                {{ $actions }}
            </div>
        </div>
    @endisset
</div>
