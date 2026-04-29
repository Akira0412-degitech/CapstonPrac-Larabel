<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SqlRequest;

class ApprovalController extends Controller
{
    // GET /approve
    // → pending状態の申請一覧を表示
    public function index()
    {
        // pendingの申請を全部取得
        // ヒント：SqlRequest::where('status', 'pending')->____()->get()
        // with('user')でリレーションも一緒に取得できる
        $pendingRequests = SqlRequest::where('status', 'pending')-> with('user')->get();

        return view('approve', compact('pendingRequests'));
    }

    // POST /approve/{id}
    // → 申請を承認する
    public function approve($id)
    {
        $sqlRequest = SqlRequest::findOrFail($id);
        $sqlRequest->status = 'approved';
        $sqlRequest->save();

        return redirect()->route('approve.index')
            ->with('success', 'Request approved!');
    }

    // POST /reject/{id}
    // → 申請を拒否する
    public function reject($id)
    {
        $sqlRequest = SqlRequest::findOrFail($id);
        $sqlRequest->status = 'rejected';
        $sqlRequest->save();

        return redirect()->route('approve.index')
            ->with('success', 'Request rejected!');

    }
}