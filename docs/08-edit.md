# 第 8 章　タスクを編集する（Update）

**目安：40 分**

## この章のゴール

「編集」リンクから編集画面に移り、タスク名を書き換えて保存できる。

## この章で学ぶこと

2 つの画面を使う更新処理（`edit` → `update`） / `@method('PUT')` / `old()` の第 2 引数

---

## 編集は「2 段階」

| 段階 | メソッド | URL | やること |
| --- | --- | --- | --- |
| ① フォームを見せる | GET | `/tasks/{id}/edit` | 現在の値が入った入力欄を表示 |
| ② 保存する | PUT | `/tasks/{id}` | 送られてきた値で上書き |

`edit` と `update` の 2 つのメソッドが必要なのはこのためです。

---

## Step 8-1　ルートを増やす

```php
Route::resource('tasks', TaskController::class)
    ->only(['index', 'store', 'edit', 'update']);
```

```bash
php artisan route:list --path=tasks
```

```
GET|HEAD   tasks ................. tasks.index › TaskController@index
POST       tasks ................. tasks.store › TaskController@store
PATCH      tasks/{id}/complete ... tasks.complete › TaskController@complete
PUT|PATCH  tasks/{task} .......... tasks.update › TaskController@update
GET|HEAD   tasks/{task}/edit ..... tasks.edit › TaskController@edit
```

---

## Step 8-2　`edit()` を実装する

`TaskController` の `edit()` を書き換えます。

```php
    /**
     * GET /tasks/{id}/edit  編集フォーム表示
     */
    public function edit($id)
    {
        $task = Task::findOrFail($id);

        return view('tasks.edit', compact('task'));
    }
```

---

## Step 8-3　編集画面のビューを作る

`resources/views/tasks/edit.blade.php` を新規作成してください。

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

### `old('name', $task->name)` の第 2 引数

```php
old('name', $task->name)
```

「直前の入力値があればそれを、なければ `$task->name` を表示する」という意味です。

- **初回表示** … `old('name')` は空 → DB の現在の値が入る
- **バリデーションエラーで戻ってきたとき** … ユーザーが打った値が残る

この 1 行で両方に対応できます。第 6 章の `old('name')` との違いを見比べてください。

---

## Step 8-4　一覧に「編集」リンクを置く

`index.blade.php` の `<td class="actions">` の中、完了フォームの **下** に追加します。

```blade
                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-accent btn-sm">編集</a>
```

これで `<td class="actions">` はこうなります。

```blade
                    <td class="actions">
                        <form action="{{ route('tasks.complete', $task->id) }}" method="post">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-success btn-sm">完了</button>
                        </form>

                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-accent btn-sm">編集</a>
                    </td>
```

> 編集画面へは **ただページを開くだけ** なので、`<form>` ではなく普通の `<a>` タグです。
> データを変えない操作は GET、変える操作は POST/PUT/PATCH/DELETE。これが原則です。

---

## Step 8-5　`update()` を実装する

`TaskController` の `update()` を書き換えます。

```php
    /**
     * PUT /tasks/{id}  編集内容の保存
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
        ], [
            'name.required' => 'タスク名を入力してください。',
            'name.max'      => 'タスク名は100文字以内で入力してください。',
        ]);

        $task = Task::findOrFail($id);
        $task->name = $validated['name'];
        $task->save();

        return redirect()->route('tasks.index');
    }
```

`store()` とほとんど同じ形ですね。違いは

- `Task::create()` ではなく、**既存の 1 件を取ってきて `save()`**
- 引数に `$id` を受け取る

の 2 点だけです。

---

## Step 8-6　重複を整理する（リファクタリング）

`store()` と `update()` に、まったく同じルールとメッセージが 2 回書かれています。
ルールを変えたいときに 2 か所直すのは事故のもとなので、まとめましょう。

`TaskController` クラスの **一番上**（`class TaskController extends Controller {` の直後）に
次を追加してください。

```php
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
```

そのうえで、`store()` と `update()` の `validate()` の行を、両方ともこう書き換えます。

```php
        $validated = $request->validate($this->rules, $this->messages);
```

動きは変わりませんが、ルールの管理場所が 1 つになりました。
**同じコードが 2 回出てきたらまとめられないか考える。** これはとても大事な習慣です。

---

## 動作確認

1. 一覧の「編集」を押す → 編集画面が開き、**現在のタスク名が入力欄に入っている**
2. 名前を書き換えて「更新する」 → 一覧に戻り、名前が変わっている
3. 名前を空にして「更新する」 → エラーメッセージが出て、更新されない
4. 「一覧に戻る」で戻れる

```bash
php artisan test --filter=ch08
```

```
✓ ch08 編集画面に現在のタスク名が表示される
✓ ch08 タスク名を編集できる
✓ ch08 編集でも空文字は弾かれる
```

---

## 演習

**演習 8-A**
編集画面に、そのタスクの「登録日時」と「最終更新日時」を表示してください。
ヒント：`$task->created_at` と `$task->updated_at`。

**演習 8-B**
存在しない ID の編集画面（http://127.0.0.1:8000/tasks/99999/edit ）を開いて、
どんな画面が出るか確認してください。
そのあと `edit()` の `findOrFail` を `find` に変えて、もう一度開いてみてください。
エラーの分かりやすさがどう違いますか？ **確認したら `findOrFail` に戻すこと。**

**演習 8-C**
編集画面でも「完了にする」ボタンを押せるようにしてください。
ヒント：第 7 章で作った `tasks.complete` ルートをそのまま使えます。

---

## つまずいたら

| 症状 | 原因 |
| --- | --- |
| `View [tasks.edit] not found.` | ファイル名は `edit.blade.php`、置き場所は `resources/views/tasks/` |
| `The POST method is not supported for this route.` | `@method('PUT')` を書き忘れている |
| 入力欄が空で表示される | `value="{{ old('name', $task->name) }}"` になっているか確認 |
| `Undefined variable $task` | `compact('task')` を書き忘れ（`tasks` ではなく **単数形** `task`） |
| 更新しても変わらない | `$task->save();` を書き忘れている |

➡ 次は [第 9 章 タスクを削除する（Delete）](09-delete.md)
