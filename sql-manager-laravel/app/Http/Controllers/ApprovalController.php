<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\SqlRequest;

class ApprovalController extends Controller
{
    // GET /approve
    // → pending状態の申請一覧を表示
    public function index()
        {
            // approverじゃなかったら403を返す
            if (auth()->user()->role !== 'approver') {
                abort(403);
            }

            $pendingRequests = SqlRequest::where('status', 'pending')->with('user')->get();
            return view('approve', compact('pendingRequests'));
        }

    // POST /approve/{id}
    // → 申請を承認する
    public function approve($id)
    {
        if (auth()->user()->role !== 'approver') {
        abort(403);
    }
        $sqlRequest = SqlRequest::findOrFail($id);
        $sqlRequest->status = 'approved';
        $sqlRequest->save();

        AuditLog::create([
            'done_by' => auth()->id(),
            'request_id' => $sqlRequest->id,
            'action' => 'approved',
            'created_at' => now(),
        ]);

        return redirect()->route('approve.index')
            ->with('success', 'Request approved!');
    }

    // POST /reject/{id}
    // → 申請を拒否する
    public function reject($id)
    {
        if (auth()->user()->role !== 'approver') {
        abort(403);
    }
        $sqlRequest = SqlRequest::findOrFail($id);
        $sqlRequest->status = 'rejected';
        $sqlRequest->save();

        AuditLog::create([
            'done_by' => auth()->id(),
            'request_id' => $sqlRequest->id,
            'action' => 'rejected',
            'created_at' => now(),
        ]);

        return redirect()->route('approve.index')
            ->with('success', 'Request rejected!');

    }

    public function auditLogs()
    {
        if (auth()->user()->role !== 'approver') {
            abort(403);
        }

        $logs = AuditLog::with(['user', 'sqlRequest.user'])
            ->latest()
            ->get();

        return view('audit-logs', compact('logs'));
    }
}
