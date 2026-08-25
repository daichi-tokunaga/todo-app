# 第 7 章　タスクを完了にする（Update）

**目安：40 分**

## この章のゴール

「完了」ボタンを押すと、そのタスクが一覧から消える（＝ `status` が `true` になる）。

## この章で学ぶこと

`@method('PATCH')` / 自分でルートを 1 本足す / `findOrFail()` / `where()` での絞り込み

---

## Step 7-1　「完了にする」ルートを足す

`Route::resource` が用意する 7 つのメソッドには、
「完了フラグだけを立てる」という専用の操作はありません。

こういうときは **自分でルートを 1 本追加** します。
`routes/web.php` の `Route::resource(...)` の **下** に追記してください。

```php
Route::patch('/tasks/{id}/complete', [TaskController::class, 'complete'])
    ->name('tasks.complete');
```

### 読み方

```php
Route::patch('/tasks/{id}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
//     ↑          ↑                          ↑                                    ↑
//  HTTPメソッド   URL（{id} は可変部分）      呼ぶコントローラとメソッド              ルート名
```

`{id}` の部分には実際の数字が入ります（`/tasks/3/complete` など）。
この数字は、コントローラの引数として受け取れます。

### PUT / PATCH / DELETE の使い分け

| メソッド | 意味 |
| --- | --- |
| GET | 取得する（画面を見る） |
| POST | 新しく作る |
| PUT | まるごと置き換える |
| PATCH | 一部だけ更新する |
| DELETE | 削除する |

今回は「`status` だけ更新」なので `PATCH` を選びました。

---

## Step 7-2　`complete()` を実装する

`TaskController` に、次のメソッドを追加してください。
（`destroy()` の下あたり、クラスの閉じ括弧 `}` の内側に書きます）

```php
    /**
     * PATCH /tasks/{id}/complete  完了にする
     */
    public function complete($id)
    {
        $task = Task::findOrFail($id);
        $task->status = true;
        $task->save();

        return redirect()->route('tasks.index');
    }
```

### `findOrFail()`

```php
Task::find($id);        // 見つからなければ null を返す
Task::findOrFail($id);  // 見つからなければ 404 エラーページを出す
```

`find()` を使うと、存在しない ID のときに
`Attempt to assign property "status" on null` という分かりにくいエラーになります。
**基本は `findOrFail()` を使いましょう。**

### `save()` は 2 つの意味を持つ

- 新しいインスタンスに対して呼ぶ → `INSERT`
- DB から取ってきたインスタンスに対して呼ぶ → `UPDATE`

今回は後者なので `UPDATE tasks SET status = 1 WHERE id = ?` が実行されます。

---

## Step 7-3　完了ボタンを置く

`resources/views/tasks/index.blade.php` の表に、操作用の列を足します。

`<thead>` の `<tr>` に空の `<th>` を 1 つ追加：

```blade
        <thead>
            <tr>
                <th>タスク</th>
                <th>登録日時</th>
                <th></th>
            </tr>
        </thead>
```

`<tbody>` の各行に `<td class="actions">` を追加：

```blade
                <tr>
                    <td>{{ $task->name }}</td>
                    <td class="muted">{{ $task->created_at->format('Y/m/d H:i') }}</td>
                    <td class="actions">
                        <form action="{{ route('tasks.complete', $task->id) }}" method="post">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-success btn-sm">完了</button>
                        </form>
                    </td>
                </tr>
```

### `@method('PATCH')` が必要な理由

**HTML の `<form>` は GET と POST しかサポートしていません。**
PUT や PATCH、DELETE を送ることはできないのです。

そこで Laravel は「POST で送るけれど、`_method` という隠しフィールドに本当のメソッドを書いておく」
という方式を使います。`@method('PATCH')` は次の HTML に展開されます。

```html
<input type="hidden" name="_method" value="PATCH">
```

Laravel はこれを見て「PATCH として処理する」と判断します。

> **`<form method="post">` のまま**である点に注意してください。
> `method="patch"` と書くと動きません。

### `route()` に引数を渡す

```php
route('tasks.complete', $task->id)
// → /tasks/3/complete
```

ルート定義の `{id}` の部分に、第 2 引数の値が埋め込まれます。

---

## Step 7-4　未完了のタスクだけ表示する

いまのままだと、完了にしても一覧に残ったままです。
`TaskController` の `index()` に絞り込みを足します。

```php
    public function index()
    {
        $tasks = Task::where('status', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tasks.index', compact('tasks'));
    }
```

`where('status', false)` は SQL の `WHERE status = 0` に相当します。

---

<details>
<summary><b>index.blade.php の全文（うまくいかない人はこれをコピペ）</b></summary>

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
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
```

</details>

---

## 動作確認

1. 一覧の「完了」ボタンを押す
2. そのタスクが一覧から消える
3. phpMyAdmin で見ると、その行の `status` が `1` になっている

```bash
php artisan test --filter=ch07
```

```
✓ ch07 タスクを完了にできる
✓ ch07 完了済みタスクは未完了一覧に出ない
```

---

## 演習

**演習 7-A**
`@method('PATCH')` を消すとどうなりますか？
実際に消してボタンを押し、エラーメッセージを読んでください。**確認したら戻すこと。**

**演習 7-B**
`findOrFail($id)` を `find($id)` に変えて、
ブラウザで `/tasks/99999/complete` に PATCH を送るとどう違うか……は少し面倒なので、
代わりに次の第 8 章の編集画面で `/tasks/99999/edit` を開いて違いを確認しましょう。

**演習 7-C（少し難しい）**
「未完了に戻す」機能を作ってください。

- `PATCH /tasks/{id}/undo` というルートを追加
- `TaskController` に `undo()` メソッドを追加（`status` を `false` にする）

※ ボタンをどこに置くかは第 10 章で完了済み一覧を作ってからでも構いません。

---

## つまずいたら

| 症状 | 原因 |
| --- | --- |
| `The POST method is not supported for this route.` | `@method('PATCH')` を書き忘れている |
| `419 PAGE EXPIRED` | `@csrf` を書き忘れている |
| `Route [tasks.complete] not defined.` | `->name('tasks.complete')` を書き忘れている／`web.php` の保存忘れ |
| ボタンを押しても消えない | `index()` に `where('status', false)` を足したか確認 |
| `Attempt to assign property "status" on null` | `find()` を使っていて、その ID のデータが無い。`findOrFail()` にする |

➡ 次は [第 8 章 タスクを編集する（Update）](08-edit.md)
