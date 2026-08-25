@extends('layouts.app')

@section('title', 'タスクの編集')

@section('content')
    <h1 class="page-title">タスクを編集する</h1>

    <div class="card">
        <form action="{{ route('tasks.update', $task->id) }}" method="post">
            @csrf
            @method('PUT')

            <input type="text" name="name" value="{{ old('name', $task->name) }}">
            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror

            <div class="form-actions">
                <button type="submit" class="btn-accent">更新する</button>
                <a href="{{ route('tasks.index') }}" class="btn-link">一覧に戻る</a>
            </div>
        </form>
    </div>
@endsection
