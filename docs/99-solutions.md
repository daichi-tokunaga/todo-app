# 演習の解答例

**まず 10 分は自分で考えてください。** 見てしまうと身に付きません。

ここに載っているのは「解答例」であって唯一の正解ではありません。
違う書き方で動いていれば、それも正解です。

---

## 第 1 章

### 演習 1-A　`/about` ページを作る

`routes/web.php`

```php
Route::get('/about', function () {
    return view('about');
});
```

`resources/views/about.blade.php`

```blade
@extends('layouts.app')

@section('title', '自己紹介')

@section('content')
    <h1 class="page-title">自己紹介</h1>

    <div class="card">
        <p>山田 太郎</p>
        <p>Laravel を勉強中です。</p>
    </div>
@endsection
```

### 演習 1-B　フッターを変える

`resources/views/layouts/app.blade.php`

```blade
    <footer class="site-footer">
        <div class="container inner">作成：山田 太郎</div>
    </footer>
```

`/tasks` と `/about` の両方で変わります。
レイアウトは 1 か所直せば全ページに反映される、これが `@extends` の効果です。

---

## 第 2 章

### 演習 2-A　rollback して戻す

```bash
php artisan migrate:rollback
# → phpMyAdmin で tasks テーブルが消えていることを確認
php artisan migrate
```

`migrate:rollback` は `down()` を実行します。
`down()` に `Schema::dropIfExists('tasks');` を書いていたので、テーブルが消えます。

### 演習 2-B

```bash
php artisan migrate:status
```

`Ran?` 列がすべて `Yes` になっていれば OK です。

---

## 第 3 章

### 演習 3-B　`status` の違い

```php
Task::where('status', true)->get();   // 完了済み  → 1 件
Task::where('status', false)->get();  // 未完了    → 4 件
```

シーダーで完了済みを 1 件だけ作っているためです。
DB の中では `true` が `1`、`false` が `0` として保存されています。

### 演習 3-C

`TaskSeeder.php` の `$names` 配列を書き換えて、

```bash
php artisan migrate:fresh --seed
```

---

## 第 4 章

### 演習 4-A　件数を表示する

```blade
    <h1 class="page-title">今日は何をする？</h1>

    <p class="muted">登録件数：{{ $tasks->count() }} 件</p>
```

### 演習 4-B　古い順にする

```php
$tasks = Task::orderBy('created_at', 'asc')->get();
```

`asc` = 昇順（古い順）、`desc` = 降順（新しい順）。
`orderBy('created_at')` のように省略すると `asc` になります。

### 演習 4-C　XSS

`{{ }}` … `<script>alert('hack')</script>` が **そのまま文字として** 表示される
`{!! !!}` … スクリプトが **実行されてしまう**（アラートが出る）

`{!! !!}` は「自分が用意した安全な HTML」を出力するときだけ使うもので、
**ユーザーの入力には絶対に使ってはいけません。**

---

## 第 5 章

### 演習 5-A　空のまま送信

`name` カラムに空文字（`''`）が入ったタスクが登録されます。
一覧には名前のない行が並びます。第 6 章のバリデーションで防ぎます。

### 演習 5-C　`@csrf` を消す

```
419 | PAGE EXPIRED
```

CSRF トークンがないため、Laravel がリクエストを拒否しています。

---

## 第 6 章

### 演習 6-A　3 文字以上

```php
        $validated = $request->validate([
            'name' => 'required|min:3|max:100',
        ], [
            'name.required' => 'タスク名を入力してください。',
            'name.min'      => 'タスク名は3文字以上で入力してください。',
            'name.max'      => 'タスク名は100文字以内で入力してください。',
        ]);
```

### 演習 6-B　重複禁止

```php
        $validated = $request->validate([
            'name' => 'required|max:100|unique:tasks,name',
        ], [
            'name.required' => 'タスク名を入力してください。',
            'name.max'      => 'タスク名は100文字以内で入力してください。',
            'name.unique'   => 'そのタスクはすでに登録されています。',
        ]);
```

> **注意**：編集（`update`）でこのルールを使うと、
> 「自分自身と重複している」と判定されて更新できなくなります。
> その場合は `unique:tasks,name,' . $id` のように、除外する ID を指定します。

---

## 第 7 章

### 演習 7-A　`@method('PATCH')` を消す

```
The POST method is not supported for this route. Supported methods: PATCH.
```

フォームは POST で送られますが、ルートは PATCH でしか受け付けないためです。

### 演習 7-C　未完了に戻す

`routes/web.php`

```php
Route::patch('/tasks/{id}/undo', [TaskController::class, 'undo'])
    ->name('tasks.undo');
```

`app/Http/Controllers/TaskController.php`

```php
    /**
     * PATCH /tasks/{id}/undo  未完了に戻す
     */
    public function undo($id)
    {
        $task = Task::findOrFail($id);
        $task->status = false;
        $task->save();

        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを未完了に戻しました。');
    }
```

ボタン（完了済み一覧の `<td class="actions">` の中）

```blade
                            <form action="{{ route('tasks.undo', $task->id) }}" method="post">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-sm">戻す</button>
                            </form>
```

> `complete()` と `undo()` はほとんど同じです。
> 1 つのメソッドにまとめて `toggle()` にする書き方もあります。どちらでも構いません。

---

## 第 8 章

### 演習 8-A　日時を表示する

`resources/views/tasks/edit.blade.php` の `</form>` の下あたりに追加します。

```blade
        <p class="muted">
            登録：{{ $task->created_at->format('Y/m/d H:i') }} ／
            更新：{{ $task->updated_at->format('Y/m/d H:i') }}
        </p>
```

### 演習 8-B　`find` と `findOrFail`

| 書き方 | 表示される画面 |
| --- | --- |
| `findOrFail($id)` | `404 NOT FOUND` のページ |
| `find($id)` | `Attempt to read property "name" on null` というエラー |

`find()` だと、原因が「データがない」ことだと分かりにくくなります。

### 演習 8-C　編集画面に完了ボタン

```blade
        <form action="{{ route('tasks.complete', $task->id) }}" method="post">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn-success">完了にする</button>
        </form>
```

> `<form>` の中に `<form>` は書けません。編集フォームの **外側** に置いてください。

---

## 第 9 章

### 演習 9-A　タスク名入りの確認ダイアログ

```blade
                            <form action="{{ route('tasks.destroy', $task->id) }}" method="post"
                                  onsubmit="return confirm('「{{ $task->name }}」を削除します。よろしいですか？');">
```

タスク名に `'` が入ると JavaScript が壊れます。`@js()` を使うと安全です。

```blade
                                  onsubmit="return confirm('削除します：' + {{ Js::from($task->name) }});"
```

（`use Illuminate\Support\Js;` は不要。Blade からそのまま呼べます）

### 演習 9-C　完了済みを一括削除

`app/Models/Task.php`

```php
    public static function deleteCompleted()
    {
        return static::where('status', true)->delete();
    }
```

ボタンを付ける場合：

```php
// routes/web.php
Route::delete('/tasks/completed/all', [TaskController::class, 'destroyCompleted'])
    ->name('tasks.destroyCompleted');
```

```php
// TaskController
public function destroyCompleted()
{
    $count = Task::deleteCompleted();

    return redirect()
        ->route('tasks.index')
        ->with('message', "完了済みタスクを {$count} 件削除しました。");
}
```

```blade
    <form action="{{ route('tasks.destroyCompleted') }}" method="post"
          onsubmit="return confirm('完了済みタスクをすべて削除します。よろしいですか？');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-danger btn-sm">完了済みを全部削除</button>
    </form>
```

> ルートを `/tasks/completed/all` にしているのは、`/tasks/{id}` と
> URL の形がぶつからないようにするためです。

---

## 第 10 章

### 演習 10-A　メッセージの種類で色を変える

コントローラ（削除のときだけ）

```php
        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを削除しました。')
            ->with('type', 'danger');
```

`layouts/app.blade.php`

```blade
            @if (session('message'))
                <p class="alert alert-{{ session('type', 'success') }}">{{ session('message') }}</p>
            @endif
```

`public/css/app.css` に追記

```css
.alert-danger {
  background: #fef2f2;
  border-color: #fca5a5;
  color: #991b1b;
}
```

`session('type', 'success')` の第 2 引数はデフォルト値です。
`type` を渡さなかった場合は `alert-success` になります。
