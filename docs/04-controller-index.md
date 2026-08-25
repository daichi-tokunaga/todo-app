# 第 4 章　一覧表示（Read）

**目安：50 分**

## この章のゴール

`/tasks` に、データベースに入っているタスクが表で並ぶ。

## この章で学ぶこと

コントローラ / `Route::resource` / ビューへの値の渡し方（`compact`） / `@foreach` / `route()` ヘルパ

---

## コントローラとは

第 1 章では、ルーティングの中に処理を直接書きました。

```php
Route::get('/tasks', function () {
    return view('tasks.index');
});
```

これは手軽ですが、処理が増えると `web.php` が数百行になって読めなくなります。
そこで **処理は専用のクラス（コントローラ）に書いて、ルーティングは対応表に徹する** のが定石です。

---

## Step 4-1　コントローラを作る

```bash
php artisan make:controller TaskController --resource
```

`app/Http/Controllers/TaskController.php` ができます。中を開いてみてください。
`index` `create` `store` `show` `edit` `update` `destroy` という **7 つの空メソッド** が並んでいます。

`--resource` を付けると、この 7 つが自動で用意されます。
これは「一覧・作成フォーム・登録・詳細・編集フォーム・更新・削除」という
**Web アプリでほぼ必ず必要になる 7 つの操作** に対応しています。

| メソッド | 役割 | HTTP | URL |
| --- | --- | --- | --- |
| `index` | 一覧を表示 | GET | `/tasks` |
| `create` | 作成フォームを表示 | GET | `/tasks/create` |
| `store` | 登録する | POST | `/tasks` |
| `show` | 1 件の詳細を表示 | GET | `/tasks/{id}` |
| `edit` | 編集フォームを表示 | GET | `/tasks/{id}/edit` |
| `update` | 更新する | PUT/PATCH | `/tasks/{id}` |
| `destroy` | 削除する | DELETE | `/tasks/{id}` |

今回の Todo アプリでは、追加フォームを一覧画面に置くので `create` と `show` は使いません。

---

## Step 4-2　ルーティングをコントローラに繋ぐ

`routes/web.php` を、次の内容に **書き換えて** ください。

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
    return view('welcome');
});

Route::resource('tasks', TaskController::class)
    ->only(['index']);
```

`Route::resource` は、さきほどの表の 7 本のルートを **1 行でまとめて登録** してくれます。
`->only([...])` で「このうち使うものだけ」を指定できます。
章を進めるたびに、この配列に少しずつ追加していきます。

確認しましょう。

```bash
php artisan route:list --path=tasks
```

```
GET|HEAD  tasks .......... tasks.index › TaskController@index
```

`tasks.index` の部分が **ルート名** です。あとで URL を書くときに使います。

---

## Step 4-3　`index()` を実装する

`app/Http/Controllers/TaskController.php` を開き、
先頭の `use` 文と `index()` メソッドを次のように書き換えます。

```php
<?php

namespace App\Http\Controllers;

use App\Models\Task;          // ← この行を追加
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * GET /tasks  一覧表示
     */
    public function index()
    {
        $tasks = Task::orderBy('created_at', 'desc')->get();

        return view('tasks.index', compact('tasks'));
    }

    // ...（create 以下はそのまま残しておく）
}
```

### `compact('tasks')` とは

```php
compact('tasks')
// ↓ これと同じ意味
['tasks' => $tasks]
```

「`$tasks` という変数を、`tasks` という名前でビューに渡す」という意味です。
ビュー側では `$tasks` として使えます。

---

## Step 4-4　ビューで一覧を表示する

`resources/views/tasks/index.blade.php` を書き換えます。

```blade
@extends('layouts.app')

@section('title', 'タスク一覧')

@section('content')
    <h1 class="page-title">今日は何をする？</h1>

    <table>
        <thead>
            <tr>
                <th>タスク</th>
                <th>登録日時</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $task)
                <tr>
                    <td>{{ $task->name }}</td>
                    <td class="muted">{{ $task->created_at->format('Y/m/d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
```

ブラウザで http://127.0.0.1:8000/tasks を開くと、
第 3 章で入れたダミーデータ 5 件が表で並んでいるはずです 🎉

### 読み方

| 書き方 | 意味 |
| --- | --- |
| `@foreach ($tasks as $task) ... @endforeach` | 配列を 1 件ずつ繰り返す |
| `{{ $task->name }}` | 変数を表示する |
| `{{ $task->created_at->format('Y/m/d H:i') }}` | 日時を好きな書式で表示 |

### `{{ }}` は安全装置でもある

`{{ }}` で出力すると、Laravel は自動で HTML エスケープをします。
たとえばタスク名に `<script>alert('hack')</script>` と入力されても、
**ただの文字として表示される** ので、スクリプトが実行されません（XSS 対策）。

あとで実際に試してみましょう。

---

## Step 4-5　リンクを「ルート名」で書く

第 1 章では、レイアウトのリンクを `href="/tasks"` とベタ書きしました。
Laravel では **ルート名から URL を組み立てる** のが推奨です。

`resources/views/layouts/app.blade.php` のヘッダー部分を修正してください。

```blade
        <!-- 変更前 -->
        <a href="/tasks">Todoアプリ</a>

        <!-- 変更後 -->
        <a href="{{ route('tasks.index') }}">Todoアプリ</a>
```

こうしておくと、あとで URL を `/todos` に変えたくなっても、
`web.php` を 1 か所直すだけで全ページのリンクが追随します。

---

## 動作確認

```bash
php artisan test --filter=ch04
```

```
✓ ch04 一覧に未完了タスクが表示される
```

---

## 演習

**演習 4-A**
一覧の上に「登録件数：5 件」のように件数を表示してください。
ヒント：Blade の中で `{{ $tasks->count() }}` が使えます。

**演習 4-B**
表示順を「登録が古い順」に変えてください。
ヒント：コントローラの `orderBy` の第 2 引数。

**演習 4-C（XSS を体験する）**
Tinker で次のタスクを登録し、一覧画面がどうなるか確認してください。

```php
Task::create(['name' => "<script>alert('hack')</script>", 'status' => false]);
```

そのあと、`index.blade.php` の `{{ $task->name }}` を一時的に `{!! $task->name !!}` に
書き換えると何が起きるか試してみてください（**確認したら必ず `{{ }}` に戻すこと**）。

---

## つまずいたら

| エラーメッセージ | 対処 |
| --- | --- |
| `Undefined variable $tasks` | `compact('tasks')` を書き忘れ。または `return view(...)` の第 2 引数がない |
| `Class "App\Models\Task" not found` | コントローラ冒頭の `use App\Models\Task;` がない |
| `Call to a member function format() on null` | `created_at` が空。第 2 章で `$table->timestamps();` を書いたか確認 |
| `Route [tasks.index] not defined.` | `Route::resource` を書いたか、`->only(['index'])` に index が入っているか確認 |

➡ 次は [第 5 章 タスク追加（Create）](05-create.md)
