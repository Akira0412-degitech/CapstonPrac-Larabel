<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approval Queue
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($pendingRequests->isEmpty())
                        <p class="text-gray-500">No pending requests.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-2">Requester</th>
                                    <th class="text-left p-2">SQL</th>
                                    <th class="text-left p-2">Submitted At</th>
                                    <th class="text-left p-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $req)
                                <tr class="border-b">
                                    <td class="p-2">{{ $req->user->name ?? 'Unknown' }}</td>
                                    <td class="p-2 font-mono">{{ $req->sql_text }}</td>
                                    <td class="p-2">{{ $req->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="p-2 flex gap-2">
                                        {{-- Approve --}}
                                        <form method="POST" action="{{ route('approve.approve', $req->id) }}">
                                            @csrf
                                            <button class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                                Approve
                                            </button>
                                        </form>
                                        {{-- Reject --}}
                                        <form method="POST" action="{{ route('approve.reject', $req->id) }}">
                                            @csrf
                                            <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                                Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
