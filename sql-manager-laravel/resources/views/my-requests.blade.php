<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Requests
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($myRequests->isEmpty())
                        <p class="text-gray-500">You have no requests yet.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-2">SQL</th>
                                    <th class="text-left p-2">Status</th>
                                    <th class="text-left p-2">Submitted At</th>
                                    <th class="text-left p-2">Reviewed By</th>
                                    <th class="text-left p-2">Reviewed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myRequests as $req)
                                <tr class="border-b">
                                    <td class="p-2 font-mono">{{ $req->sql_text }}</td>
                                    <td class="p-2">
                                        @if($req->status === 'pending')
                                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">pending</span>
                                        @elseif($req->status === 'approved')
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">approved</span>
                                        @else
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">rejected</span>
                                        @endif
                                    </td>
                                    <td class="p-2">{{ $req->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="p-2">
                                        {{ $req->decisionLog?->user?->name ?? '-' }}
                                    </td>
                                    <td class="p-2">
                                        {{ $req->decisionLog?->created_at?->format('Y-m-d H:i') ?? '-' }}
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
