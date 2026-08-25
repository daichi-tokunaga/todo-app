# チートシート

---

## artisan コマンド

コマンドはすべて `todo-app` フォルダの中で実行します。

### 起動・確認

| コマンド | 意味 |
| --- | --- |
| `php artisan serve` | 開発サーバーを起動（Ctrl+C で停止） |
| `php artisan route:list` | ルート一覧 |
| `php artisan route:list --path=tasks` | `tasks` を含むルートだけ表示 |
| `php artisan tinker` | 対話モード（`exit` で抜ける） |
| `php artisan test` | テストを実行 |
| `php artisan test --filter=ch04` | 名前に `ch04` を含むテストだけ実行 |
| `php artisan --version` | Laravel のバージョン |

### ファイルを作る

| コマンド | できるもの |
| --- | --- |
| `php artisan make:model Task` | `app/Models/Task.php` |
| `php artisan make:controller TaskController --resource` | `app/Http/Controllers/TaskController.php`（7 メソッド付き） |
| `php artisan make:migration create_tasks_table` | `database/migrations/日時_create_tasks_table.php` |
| `php artisan make:migration add_xxx_to_tasks_table --table=tasks` | カラム追加用のマイグレーション |
| `php artisan make:seeder TaskSeeder` | `database/seeders/TaskSeeder.php` |
| `php artisan make:request StoreTaskRequest` | `app/Http/Requests/StoreTaskRequest.php` |

### データベース

| コマンド | 意味 |
| --- | --- |
| `php artisan migrate` | 未実行のマイグレーションを実行 |
| `php artisan migrate:status` | 実行済みかどうかの一覧 |
| `php artisan migrate:rollback` | 直前の 1 回分を取り消す |
| `php artisan migrate:fresh` | 全テーブル削除して作り直す（**データが消える**） |
| `php artisan migrate:fresh --seed` | 作り直し＋ダミーデータ投入 |
| `php artisan db:seed` | シーダーだけ実行 |
| `php artisan db:table tasks` | テーブルの構造を表示 |

### 困ったとき

| コマンド | 意味 |
| --- | --- |
| `php artisan config:clear` | `.env` や config の変更が効かないとき |
| `php artisan view:clear` | Blade の変更が効かないとき |
| `php artisan route:clear` | ルートの変更が効かないとき |
| `php artisan optimize:clear` | 上を全部まとめて |
| `composer dump-autoload` | 新しいクラスが `not found` になるとき |

---

## ルーティング（`routes/web.php`）

```php
Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
Route::patch('/tasks/{id}', [TaskController::class, 'update']);
Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');

// 7 本まとめて
Route::resource('tasks', TaskController::class);

// 一部だけ
Route::resource('tasks', TaskController::class)->only(['index', 'store']);
Route::resource('tasks', TaskController::class)->except(['show']);
```

`Route::resource` が作る 7 本

| メソッド | HTTP | URL | ルート名 |
| --- | --- | --- | --- |
| index | GET | `/tasks` | `tasks.index` |
| create | GET | `/tasks/create` | `tasks.create` |
| store | POST | `/tasks` | `tasks.store` |
| show | GET | `/tasks/{task}` | `tasks.show` |
| edit | GET | `/tasks/{task}/edit` | `tasks.edit` |
| update | PUT/PATCH | `/tasks/{task}` | `tasks.update` |
| destroy | DELETE | `/tasks/{task}` | `tasks.destroy` |

---

## Eloquent（モデル）

```php
// 取得
Task::all();                               // 全件
Task::find(1);                             // id=1（なければ null）
Task::findOrFail(1);                       // id=1（なければ 404）
Task::first();                             // 先頭 1 件
Task::where('status', false)->get();       // 条件付き
Task::where('name', 'like', '%買う%')->get(); // 部分一致
Task::orderBy('created_at', 'desc')->get();  // 並び替え
Task::count();                             // 件数
Task::paginate(10);                        // ページ分割

// 登録
Task::create(['name' => '牛乳を買う', 'status' => false]);

$task = new Task();
$task->name = '牛乳を買う';
$task->save();

// 更新
$task = Task::findOrFail(1);
$task->name = '牛乳と卵を買う';
$task->save();

Task::where('status', true)->update(['status' => false]);  // 一括更新

// 削除
Task::findOrFail(1)->delete();
Task::where('status', true)->delete();     // 一括削除
```

---

## コントローラ

```php
use App\Models\Task;
use Illuminate\Http\Request;

// リクエストの値を取る
$request->input('name');       // name の値
$request->all();               // 全部
$request->filled('keyword');   // 値が入っているか

// バリデーション
$validated = $request->validate([
    'name' => 'required|max:100',
], [
    'name.required' => 'タスク名を入力してください。',
]);

// ビューを返す
return view('tasks.index', compact('tasks'));
return view('tasks.index', ['tasks' => $tasks]);   // 同じ意味

// リダイレクト
return redirect()->route('tasks.index');
return redirect()->route('tasks.index')->with('message', '保存しました。');
return redirect()->back();     // 直前のページへ
```

---

## Blade

### 出力

| 書き方 | 意味 |
| --- | --- |
| `{{ $value }}` | 出力（HTML エスケープあり・**基本はこれ**） |
| `{!! $value !!}` | エスケープなし（自分が用意した HTML のみ） |
| `{{-- コメント --}}` | HTML に出ないコメント |

### 制御構文

```blade
@if ($tasks->isEmpty())
    ...
@elseif (...)
    ...
@else
    ...
@endif

@foreach ($tasks as $task)
    {{ $loop->index }}      {{-- 0 から始まる番号 --}}
    {{ $loop->iteration }}  {{-- 1 から始まる番号 --}}
    {{ $loop->first }}      {{-- 最初なら true --}}
    {{ $loop->last }}       {{-- 最後なら true --}}
@endforeach

@forelse ($tasks as $task)
    {{ $task->name }}
@empty
    タスクがありません
@endforelse
```

### レイアウト

```blade
{{-- layouts/app.blade.php --}}
@yield('content')
@yield('title', 'デフォルト値')

{{-- 各ページ --}}
@extends('layouts.app')
@section('title', 'タスク一覧')
@section('content')
    ...
@endsection

{{-- 部品を読み込む --}}
@include('tasks.partials.form')
```

### フォーム

```blade
<form action="{{ route('tasks.store') }}" method="post">
    @csrf                {{-- POST/PUT/PATCH/DELETE では必須 --}}
    @method('PUT')       {{-- PUT/PATCH/DELETE のとき必要 --}}
    <input type="text" name="name" value="{{ old('name') }}">
    @error('name')
        <p class="error">{{ $message }}</p>
    @enderror
    <button type="submit">送信</button>
</form>
```

### ヘルパ

| 書き方 | 意味 |
| --- | --- |
| `{{ route('tasks.index') }}` | ルート名から URL |
| `{{ route('tasks.edit', $task->id) }}` | パラメータ付き |
| `{{ asset('css/app.css') }}` | `public/` 以下のファイルの URL |
| `{{ url('/tasks') }}` | 絶対 URL |
| `{{ old('name') }}` | 直前の入力値 |
| `{{ old('name', $task->name) }}` | 直前の入力値、なければ第 2 引数 |
| `{{ session('message') }}` | セッションの値 |
| `{{ request('keyword') }}` | クエリパラメータ |

---

## よく使うバリデーションルール

| ルール | 意味 |
| --- | --- |
| `required` | 必須 |
| `nullable` | 空を許す |
| `max:100` | 100 文字以内 |
| `min:3` | 3 文字以上 |
| `integer` | 整数 |
| `numeric` | 数値 |
| `date` | 日付 |
| `email` | メール形式 |
| `url` | URL 形式 |
| `in:a,b,c` | いずれかと一致 |
| `unique:tasks,name` | `tasks.name` で重複なし |
| `exists:tasks,id` | `tasks.id` に存在する |
| `confirmed` | `name_confirmation` と一致 |

---

## マイグレーションのカラム型

```php
$table->id();                                // 主キー
$table->string('name', 100);                 // VARCHAR(100)
$table->text('memo');                        // TEXT
$table->integer('count');
$table->boolean('status');
$table->date('due_date');
$table->dateTime('published_at');
$table->timestamps();                        // created_at / updated_at
$table->softDeletes();                       // deleted_at
$table->foreignId('category_id')->constrained();

// 修飾子
->nullable()        // 空を許す
->default(false)    // 初期値
->unique()          // 重複禁止
->after('name')     // このカラムの後ろに追加
->comment('説明')
```

---

## 型宣言（第 11 章）

```php
<?php

declare(strict_types=1);          // ← 必ず <?php の直後

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
```

| 返すもの | 戻り値の型 | `use` |
| --- | --- | --- |
| `view(...)` | `View` | `Illuminate\View\View` |
| `redirect()->...` | `RedirectResponse` | `Illuminate\Http\RedirectResponse` |
| `response()->json(...)` | `JsonResponse` | `Illuminate\Http\JsonResponse` |
| `$this->belongsTo(...)` | `BelongsTo` | `Illuminate\Database\Eloquent\Relations\BelongsTo` |
| `$this->hasMany(...)` | `HasMany` | `Illuminate\Database\Eloquent\Relations\HasMany` |
| `Task::...->get()` | `Collection` | `Illuminate\Database\Eloquent\Collection` |

### ルートモデルバインディング

```php
// routes/web.php  ← {task} の名前と…
Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete']);

// コントローラ    ← $task の名前を一致させる
public function complete(Task $task): RedirectResponse
```

見つからなければ自動で 404。`findOrFail()` は不要になります。

### PHPDoc（実行時には効かないが、IDE と静的解析が読む）

```php
/**
 * @property int    $id
 * @property string $name
 * @property bool   $status
 */
class Task extends Model
{
    /** @var array<int, string> */
    protected $fillable = ['name', 'status'];
}
```

> `protected array $fillable` のように **型は付けられません**。
> 親の `Model` が型なしで宣言しているためです（PHP の制約）。

---

## HTTP メソッドの使い分け

| メソッド | 用途 | フォームで書けるか |
| --- | --- | --- |
| GET | 見るだけ（一覧・検索・画面表示） | ○ |
| POST | 新規作成 | ○ |
| PUT | まるごと更新 | ×（`@method('PUT')` が必要） |
| PATCH | 一部更新 | ×（`@method('PATCH')` が必要） |
| DELETE | 削除 | ×（`@method('DELETE')` が必要） |
