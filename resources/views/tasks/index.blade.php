@extends('layouts.app')

@section('title', 'タスク一覧')

@section('content')
    <h1 class="page-title">今日は何をする？</h1>

    {{-- 追加フォーム --}}
    <div class="card">
        <form action="{{ route('tasks.store') }}" method="post">
            @csrf
            <div class="form-row">
                <input type="text" name="name" value="{{ old('name') }}" placeholder="洗濯物をたたむ...">
                <button type="submit">追加する</button>
            </div>
            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror
        </form>
    </div>

    {{-- 未完了タスク一覧 --}}
    @if ($tasks->isEmpty())
        <p class="empty">未完了のタスクはありません。</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>タスク</th>
                    <th>登録日時</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                    <tr>
                        <td>{{ $task->name }}</td>
                        <td class="muted">{{ $task->created_at->format('Y/m/d H:i') }}</td>
                        <td class="actions">
                            <form action="{{ route('tasks.complete', $task->id) }}" method="post">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-success btn-sm">完了</button>
                            </form>

                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-accent btn-sm">編集</a>

                            <form action="{{ route('tasks.destroy', $task->id) }}" method="post"
                                  onsubmit="return confirm('本当に削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">削除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- 完了済みタスク一覧 --}}
    @if ($doneTasks->isNotEmpty())
        <h2 class="page-title" style="margin-top: 48px;">完了したタスク</h2>
        <table>
            <tbody>
                @foreach ($doneTasks as $task)
                    <tr>
                        <td class="done">{{ $task->name }}</td>
                        <td class="actions">
                            <form action="{{ route('tasks.destroy', $task->id) }}" method="post"
                                  onsubmit="return confirm('本当に削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">削除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
