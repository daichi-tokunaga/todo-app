# 第 10 章　仕上げ

**目安：40 分**

## この章のゴール

- 操作するたびに「タスクを追加しました。」などのお知らせが出る
- タスクが 0 件のときに「未完了のタスクはありません。」と出る
- 完了したタスクが下にまとめて表示される
- `http://127.0.0.1:8000/` を開くと自動で `/tasks` に飛ぶ
- **`php artisan test` が全部緑になる**

## この章で学ぶこと

フラッシュメッセージ（`with()` / `session()`） / 条件分岐（`@if`） / リダイレクト

---

## Step 10-1　フラッシュメッセージを出す

いまは登録しても削除しても、画面が切り替わるだけで何のフィードバックもありません。
「1 回だけ表示されるお知らせ」を出しましょう。

### ① コントローラで、リダイレクトにメッセージを添える

`TaskController` の **4 か所** の `return redirect()->route('tasks.index');` を、
それぞれ次のように書き換えてください。

```php
    // store()
    return redirect()
        ->route('tasks.index')
        ->with('message', 'タスクを追加しました。');

    // update()
    return redirect()
        ->route('tasks.index')
        ->with('message', 'タスクを更新しました。');

    // complete()
    return redirect()
        ->route('tasks.index')
        ->with('message', 'タスクを完了にしました。');

    // destroy()
    return redirect()
        ->route('tasks.index')
        ->with('message', 'タスクを削除しました。');
```

`->with('キー', '値')` は「この値をセッションに 1 回だけ置いてリダイレクトする」という意味です。
**次のリクエストで読み出されると自動で消えます**（だから「フラッシュ」と呼びます）。

### ② レイアウトで表示する

`resources/views/layouts/app.blade.php` の `<main>` の中を書き換えます。

```blade
    <main>
        <div class="container">
            {{-- 直前の処理からのお知らせ（フラッシュメッセージ） --}}
            @if (session('message'))
                <p class="alert">{{ session('message') }}</p>
            @endif

            @yield('content')
        </div>
    </main>
```

**レイアウトに書いたので、全ページで自動的に表示されます。**
各ビューに書かなくていいのが、共通化のありがたいところです。

動作確認：タスクを追加すると、緑の帯でお知らせが出ます。
そのあとブラウザを再読み込みすると、**お知らせが消える** ことも確認してください。

---

## Step 10-2　0 件のときの表示を出す

タスクが 1 件もないと、いまは見出しだけの空っぽの表が出ます。
`index.blade.php` の表全体を `@if` で囲みます。

```blade
    {{-- 未完了タスク一覧 --}}
    @if ($tasks->isEmpty())
        <p class="empty">未完了のタスクはありません。</p>
    @else
        <table>
            ...（いままでの表をそのまま）...
        </table>
    @endif
```

| メソッド | 意味 |
| --- | --- |
| `$tasks->isEmpty()` | 0 件なら true |
| `$tasks->isNotEmpty()` | 1 件以上なら true |
| `$tasks->count()` | 件数 |

---

## Step 10-3　完了したタスクを表示する

「完了を押すと消えてしまって、本当に完了したのか分からない」ので、
下に完了済みの一覧を出します。

### ① コントローラで 2 種類取得する

`index()` を書き換えます。

```php
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
```

`compact('tasks', 'doneTasks')` のように、渡す変数はいくつでも並べられます。

### ② ビューの一番下に追加する

```blade
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
```

`class="done"` を付けると、CSS で取り消し線とグレー表示になります。

---

## Step 10-4　トップページを `/tasks` に飛ばす

`http://127.0.0.1:8000/` はまだ Laravel の初期画面のままです。
`routes/web.php` の最初のルートを書き換えます。

```php
Route::get('/', function () {
    return redirect()->route('tasks.index');
});
```

ブラウザで http://127.0.0.1:8000/ を開くと、`/tasks` に転送されます。

---

<details>
<summary><b>完成版の全ファイル（答え合わせ用）</b></summary>

### routes/web.php

```php
<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('tasks.index');
});

Route::resource('tasks', TaskController::class)
    ->only(['index', 'store', 'edit', 'update', 'destroy']);

Route::patch('/tasks/{id}/complete', [TaskController::class, 'complete'])
    ->name('tasks.complete');
```

### app/Http/Controllers/TaskController.php

```php
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
```

> `--resource` で作られた `create()` と `show()` は使わないので、削除して構いません。

### resources/views/layouts/app.blade.php

```blade
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Todoアプリ')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container inner">
            <a href="{{ route('tasks.index') }}">Todoアプリ</a>
            <span class="sub">Laravel ハンズオン</span>
        </div>
    </header>

    <main>
        <div class="container">
            {{-- 直前の処理からのお知らせ（フラッシュメッセージ） --}}
            @if (session('message'))
                <p class="alert">{{ session('message') }}</p>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="site-footer">
        <div class="container inner">Todoアプリ - Laravel ハンズオン</div>
    </footer>
</body>
</html>
```

### resources/views/tasks/index.blade.php

```blade
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
```

### resources/views/tasks/edit.blade.php

```blade
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
```

</details>

---

## 最終チェック

```bash
php artisan test
```

```
Tests:  14 passed
```

**全部緑になったら完成です。おつかれさまでした！** 🎉

念のため、ブラウザでも一通り確認しましょう。

- [ ] `/` を開くと `/tasks` に飛ぶ
- [ ] タスクを追加できる／お知らせが出る
- [ ] 空で送るとエラーメッセージが出る
- [ ] 「完了」で下の完了済みリストに移動する
- [ ] 「編集」で名前を変えられる
- [ ] 「削除」で確認ダイアログが出て、消える
- [ ] 全部消すと「未完了のタスクはありません。」と出る

---

## 演習

**演習 10-A**
`with('message', ...)` に加えて `with('type', 'success')` のようにもう 1 つ値を渡し、
削除のときだけ赤い帯（`class="alert alert-danger"`）で表示されるようにしてください。
CSS は `public/css/app.css` に自分で追記します。

**演習 10-B**
完了済みタスクに「未完了に戻す」ボタンを付けてください
（第 7 章の演習 7-C で `undo()` を作った人はそれを使います）。

**演習 10-C**
`git log` を見て、自分がどこまで進んだか振り返ってください。
コミットしていない人は、ここでコミットしておきましょう。

```bash
git add .
git commit -m "Todoアプリを完成させた"
```

➡ さらに力をつけたい人は [第 11 章 発展課題](11-advanced.md) へ
