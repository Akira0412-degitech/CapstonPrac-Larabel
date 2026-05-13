<?php

namespace App\Http\Controllers;

use App\Models\SqlRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SqlRequestController extends Controller
{
    private function getAppDatabaseName(): string
    {
        return (string) config('database.connections.mysql.database');
    }

    private function getAllowedTables(): array
    {
        $database = $this->getAppDatabaseName();

        return DB::table('information_schema.tables')
            ->where('table_schema', $database)
            ->where('table_type', 'BASE TABLE')
            ->orderBy('table_name')
            ->selectRaw('table_name as table_name_alias')
            ->pluck('table_name_alias')
            ->all();
    }

    public function index()
    {
        $tables = $this->getAllowedTables();

        return view('submit', compact('tables'));
    }

    public function columns(Request $request)
    {
        $validated = $request->validate([
            'table' => ['required', 'string'],
        ]);

        $table = $validated['table'];
        $tables = $this->getAllowedTables();
        if (! in_array($table, $tables, true)) {
            return response()->json(['message' => 'Unknown table'], 422);
        }

        return response()->json(Schema::getColumnListing($table));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sql_text' => 'required|string',
        ]);

        SqlRequest::create([
            'user_id' => auth()->id(),
            'sql_text' => $request->sql_text,
            'status' => 'pending',
        ]);

        return redirect()->route('submit.index')
            ->with('success', 'Request submitted successfully!');
    }

    public function myRequests()
    {
        $myRequests = SqlRequest::where('user_id', auth()->id())
            ->with(['decisionLog.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('my-requests', compact('myRequests'));
    }
}
