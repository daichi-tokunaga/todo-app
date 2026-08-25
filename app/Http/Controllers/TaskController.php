<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * GET /tasks  一覧表示
     */
    public function index(): View
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
    public function store(TaskRequest $request): RedirectResponse
    {
        Task::create([
            'name'   => $request->validated('name'),
            'status' => false,
        ]);

        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを追加しました。');
    }

    /**
     * GET /tasks/{task}/edit  編集フォーム表示
     */
    public function edit(Task $task): View
    {
        return view('tasks.edit', compact('task'));
    }

    /**
     * PUT /tasks/{task}  編集内容の保存
     */
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $task->name = $request->validated('name');
        $task->save();

        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを更新しました。');
    }

    /**
     * PATCH /tasks/{task}/complete  完了にする
     */
    public function complete(Task $task): RedirectResponse
    {
        $task->status = true;
        $task->save();

        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを完了にしました。');
    }

    /**
     * DELETE /tasks/{task}  削除
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを削除しました。');
    }
}
