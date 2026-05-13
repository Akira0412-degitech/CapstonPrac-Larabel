<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-6">Welcome, {{ auth()->user()->name }}! ({{ auth()->user()->role }})</p>

                    <div class="flex gap-4">
                        {{-- submitterだけ見える --}}
                        @if(auth()->user()->role === 'submitter')
                            <a href="{{ route('submit.index') }}"
                               class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                                Submit SQL Request
                            </a>
                            <a href="{{ route('my-requests.index') }}"
                               class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                                My Requests
                            </a>
                        @endif

                        {{-- approverだけ見える --}}
                        @if(auth()->user()->role === 'approver')
                            <a href="{{ route('approve.index') }}"
                               class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                                Approval Queue
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>