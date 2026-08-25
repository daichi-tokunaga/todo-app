# 第 11 章　発展課題

**目安：好きなだけ**

第 10 章までで基本の CRUD は完成しました。
ここから先は **ヒントだけ** です。完成コードは載せません。
公式ドキュメントを読みながら自力で実装してみてください。

📖 [Laravel 9.x 日本語ドキュメント](https://readouble.com/laravel/9.x/ja/)

難易度の目安：★☆☆ 易しい　★★☆ ふつう　★★★ 難しい

---

## 課題 1　期限（due_date）を付ける　★☆☆

タスクに「いつまでにやるか」を持たせます。

### やること

- `tasks` テーブルに `due_date`（DATE 型、空でも可）を追加する
- 追加フォームと編集フォームに `<input type="date" name="due_date">` を置く
- 一覧に期限を表示する
- 期限切れのタスクは赤字にする

### ヒント

既存のテーブルにカラムを足すときは、**新しいマイグレーションを作ります**
（作成済みのマイグレーションファイルを書き換えてはいけません）。

```bash
php artisan make:migration add_due_date_to_tasks_table --table=tasks
```

```php
public function up()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->date('due_date')->nullable()->after('name');
    });
}

public function down()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->dropColumn('due_date');
    });
}
```

- `Schema::create` ではなく **`Schema::table`**
- `$fillable` と `$casts` にも `due_date` を足すのを忘れずに
- バリデーションは `'due_date' => 'nullable|date'`
- 期限切れの判定は Blade で `@if ($task->due_date && $task->due_date->isPast())`

---

## 課題 2　キーワード検索　★☆☆

一覧画面に検索ボックスを付けて、タスク名の部分一致で絞り込みます。

### ヒント

検索は「データを変えない操作」なので **GET** を使います。

```blade
<form action="{{ route('tasks.index') }}" method="get">
    <input type="text" name="keyword" value="{{ request('keyword') }}">
    <button type="submit">検索</button>
</form>
```

```php
public function index(Request $request)
{
    $query = Task::where('status', false);

    if ($request->filled('keyword')) {
        $query->where('name', 'like', '%' . $request->input('keyword') . '%');
    }

    $tasks = $query->orderBy('created_at', 'desc')->get();
    // ...
}
```

`when()` を使うともっと短く書けます。調べてみてください。

---

## 課題 3　ページネーション　★☆☆

タスクが 100 件あっても 1 ページに全部出るのは辛いので、10 件ずつに分割します。

### ヒント

```php
$tasks = Task::where('status', false)->orderBy('created_at', 'desc')->paginate(10);
```

```blade
{{ $tasks->links() }}
```

Laravel 9 の標準ページネーションは Tailwind 用の HTML を出力します。
**Node.js を使わないこの環境では見た目が崩れる** ので、Bootstrap 版に切り替えるか、
自分でリンクを組み立ててください。

```php
// app/Providers/AppServiceProvider.php の boot() に追加
use Illuminate\Pagination\Paginator;

public function boot()
{
    Paginator::useBootstrapFive();
}
```

または、`$tasks->previousPageUrl()` / `nextPageUrl()` を使って
「前へ／次へ」のリンクを自分で書くほうが勉強になります。

---

## 課題 4　FormRequest でバリデーションを分離する　★★☆

コントローラからバリデーションを追い出して、専用クラスにします。

### ヒント

```bash
php artisan make:request StoreTaskRequest
```

```php
// app/Http/Requests/StoreTaskRequest.php
public function authorize()
{
    return true;   // ← 初期値は false なので必ず true に変える
}

public function rules()
{
    return ['name' => 'required|max:100'];
}

public function messages()
{
    return [
        'name.required' => 'タスク名を入力してください。',
        'name.max'      => 'タスク名は100文字以内で入力してください。',
    ];
}
```

```php
// コントローラ側
public function store(StoreTaskRequest $request)
{
    $validated = $request->validated();
    // ...
}
```

型指定を変えるだけで、**メソッドに入る前に自動で検証されます**。
`authorize()` を `false` のままにすると 403 エラーになるので注意。

---

## 課題 5　ルートモデルバインディング　★★☆

`Task::findOrFail($id)` を書かなくて済むようにします。

### ヒント

```php
// Before
public function edit($id)
{
    $task = Task::findOrFail($id);
    return view('tasks.edit', compact('task'));
}

// After
public function edit(Task $task)
{
    return view('tasks.edit', compact('task'));
}
```

引数の型に `Task` を書くだけで、Laravel が URL の `{task}` から自動で探してくれます
（見つからなければ自動で 404）。

**注意**：ルート定義の `{...}` の名前と、引数名を一致させる必要があります。
`Route::resource` が作るルートは `{task}` なのでそのまま動きますが、
自分で書いた `tasks/{id}/complete` は `tasks/{task}/complete` に直す必要があります。

---

## 課題 6　バリデーションメッセージを日本語ファイルにまとめる　★★☆

いまはコントローラにメッセージをベタ書きしていますが、
本来は言語ファイルにまとめます。

### ヒント

- `lang/ja/validation.php` を作る（`lang/en/validation.php` をコピーして翻訳）
- `config/app.php` の `'locale' => 'ja'` に変更
- 項目名は `attributes` 配列で日本語にできる

```php
// lang/ja/validation.php（抜粋）
return [
    'required' => ':attributeは必須です。',
    'max'      => [
        'string' => ':attributeは:max文字以内で入力してください。',
    ],
    'attributes' => [
        'name' => 'タスク名',
    ],
];
```

これができたら、コントローラの `$this->messages` は削除できます。

---

## 課題 7　ソフトデリート（論理削除）　★★☆

「削除」しても DB からは消さず、隠すだけにします。誤削除に強くなります。

### ヒント

- マイグレーションで `$table->softDeletes();`（`deleted_at` カラムが増える）
- モデルに `use Illuminate\Database\Eloquent\SoftDeletes;` と `use SoftDeletes;`
- 以降 `delete()` は「隠す」だけになる
- `Task::onlyTrashed()->get()` でゴミ箱の中身を取得
- `$task->restore()` で復活、`$task->forceDelete()` で本当に削除

「ゴミ箱」画面と「復元」ボタンを作ってみてください。

---

## 課題 8　カテゴリを付ける（リレーション）　★★★

タスクを「仕事」「プライベート」などのカテゴリで分類します。

### ヒント

- `categories` テーブルを作る（`id`, `name`）
- `tasks` に `category_id` を追加（外部キー）

```php
// マイグレーション
$table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
```

```php
// app/Models/Task.php
public function category()
{
    return $this->belongsTo(Category::class);
}

// app/Models/Category.php
public function tasks()
{
    return $this->hasMany(Task::class);
}
```

```blade
{{ $task->category?->name }}
```

一覧で全タスクのカテゴリ名を出すと **N+1 問題** が起きます。
`Task::with('category')->get()` で解決できることも確認してください。

---

## 課題 9　自分でテストを書く　★★★

`tests/Feature/TodoAppTest.php` を参考に、自分の追加機能のテストを書きます。

```bash
php artisan make:test MyFeatureTest
```

よく使うアサーション：

| 書き方 | 意味 |
| --- | --- |
| `$this->get('/tasks')->assertStatus(200)` | 200 が返る |
| `->assertSee('文字列')` | 画面にその文字が出ている |
| `->assertRedirect('/tasks')` | そこへリダイレクトされる |
| `->assertSessionHasErrors('name')` | `name` のバリデーションエラーがある |
| `$this->assertDatabaseHas('tasks', [...])` | DB にその行がある |
| `$this->assertDatabaseMissing('tasks', [...])` | DB にその行がない |

---

## 課題 10　ログイン機能　★★★

「自分のタスクだけ見える」ようにします。

### 注意

Laravel Breeze や Jetstream は **Node.js（npm）が必要** なので、この環境では使えません。
自力で作る場合は、Laravel の認証機能を直接使います。

- `users` テーブルは第 0 章の `migrate` ですでにできています
- `Auth::attempt(['email' => ..., 'password' => ...])` でログイン
- `Auth::user()` でログイン中のユーザー
- `Route::middleware('auth')->group(function () { ... })` でログイン必須にする
- `tasks` に `user_id` を追加し、`Task::where('user_id', Auth::id())` で絞り込む

かなり歯ごたえがあります。時間のある人はぜひ。

---

## 提出するときは

```bash
git add .
git commit -m "発展課題：〇〇を実装した"
```

どの課題をやったか、README にメモを残しておくと親切です。
