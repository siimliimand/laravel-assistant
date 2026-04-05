<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Edit') }}
                </a>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Are you sure you want to delete this project?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Status Badge -->
                    <div class="mb-6">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $project->status->color() }}-100 text-{{ $project->status->color() }}-800">
                            {{ $project->status->label() }}
                        </span>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Description') }}</h3>
                        @if($project->description)
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $project->description }}</p>
                        @else
                            <p class="text-gray-400 italic">{{ __('No description provided.') }}</p>
                        @endif
                    </div>

                    <!-- Metadata -->
                    <div class="border-t border-gray-200 pt-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Created') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $project->created_at->format('F j, Y g:i A') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Last Updated') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $project->updated_at->format('F j, Y g:i A') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex space-x-4">
                        <a href="{{ route('projects.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                            {{ __('Back to Projects') }}
                        </a>
                        <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            {{ __('Edit Project') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
