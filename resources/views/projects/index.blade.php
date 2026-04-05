<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Projects') }}
            </h2>
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Create Project') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($projects->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p class="text-lg">{{ __('You don\'t have any projects yet.') }}</p>
                        <a href="{{ route('projects.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            {{ __('Create Your First Project') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($projects as $project)
                                <div class="border rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <a href="{{ route('projects.show', $project) }}" class="hover:text-blue-600">
                                                {{ $project->name }}
                                            </a>
                                        </h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $project->status->color() }}-100 text-{{ $project->status->color() }}-800">
                                            {{ $project->status->label() }}
                                        </span>
                                    </div>
                                    @if($project->description)
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $project->description }}</p>
                                    @endif
                                    <div class="text-xs text-gray-500">
                                        {{ $project->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
