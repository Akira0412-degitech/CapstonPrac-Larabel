<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Audit Logs
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border px-3 py-2 text-left">Requester</th>
                                <th class="border px-3 py-2 text-left">Approver</th>
                                <th class="border px-3 py-2 text-left">SQL Text</th>
                                <th class="border px-3 py-2 text-left">Action</th>
                                <th class="border px-3 py-2 text-left">Action Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="border px-3 py-2">{{ $log->sqlRequest->user->name ?? 'Unknown' }}</td>
                                    <td class="border px-3 py-2">{{ $log->user->name ?? 'Unknown' }}</td>
                                    <td class="border px-3 py-2 break-all">{{ $log->sqlRequest->sql_text ?? 'N/A' }}</td>
                                    <td class="border px-3 py-2">{{ $log->action }}</td>
                                    <td class="border px-3 py-2">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="border px-3 py-4 text-center text-gray-500">
                                        No audit logs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
