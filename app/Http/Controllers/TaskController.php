<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * バリデーションルールとエラーメッセージ（追加・編集で共通）
     */
    private array $rules = [
        'name' => 'required|max:100',
    ];

    private array $messages = [
        'name.required' => 'タスク名を入力してください。',
        'name.max'      => 'タスク名は100文字以内で入力してください。',
    ];

    /**
     * GET /tasks  一覧表示
     */
    public function index()
    {
        $tasks = Task::where('status', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $doneTasks = Task::where('status', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('tasks.index', compact('tasks', 'doneTasks'));
    }

    /**
     * POST /tasks  新規登録
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules, $this->messages);

        Task::create([
            'name'   => $validated['name'],
            'status' => false,
        ]);

        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを追加しました。');
    }

    /**
     * GET /tasks/{id}/edit  編集フォーム表示
     */
    public function edit($id)
    {
        $task = Task::findOrFail($id);

        return view('tasks.edit', compact('task'));
    }

    /**
     * PUT /tasks/{id}  編集内容の保存
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->rules, $this->messages);

        $task = Task::findOrFail($id);
        $task->name = $validated['name'];
        $task->save();

        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを更新しました。');
    }

    /**
     * PATCH /tasks/{id}/complete  完了にする
     */
    public function complete($id)
    {
        $task = Task::findOrFail($id);
        $task->status = true;
        $task->save();

        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを完了にしました。');
    }

    /**
     * DELETE /tasks/{id}  削除
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを削除しました。');
    }
}
