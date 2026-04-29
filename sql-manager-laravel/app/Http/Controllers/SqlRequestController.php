<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SqlRequest;

class SqlRequestController extends Controller
{
    // GET /submit
    // → テーブル一覧を取得してビューに渡す
    public function index()
    {
        // DBのテーブル一覧を取得
        // ヒント：DB::select('SHOW TABLES')
        $tables = DB::select('SHOW TABLES');
        
        return view('submit', compact('tables'));
    }

    // POST /submit
    // → 申請をDBに保存する
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'sql_text' => 'required|string',
        ]);

        // DBに保存
        // ヒント：SqlRequest::create()
        SqlRequest::create([
            'user_id'  => auth()->id(),  // ログイン中のユーザーID
            'sql_text' => $request->sql_text,  // フォームから送られたSQL
            'status'   => 'pending',
        ]);

        return redirect()->route('submit.index')
            ->with('success', 'Request submitted successfully!');
    }

    public function myRequests(){
        $myRequests = SqlRequest::where('user_id', auth()->id())
        ->orderBy('created_at','desc')
        ->get();

        return view('my-requests', compact('myRequests'));
    }
}