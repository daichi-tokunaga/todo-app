# 第 11 章　型を付けて堅くする（リファクタリング）

**目安：60 分**

## この章のゴール

**アプリの動きは 1 ミリも変えずに**、コードだけを型安全に書き直す。
最後まで `php artisan test` が 14 件緑のままであること。

## この章で学ぶこと

リファクタリング / 戻り値の型 / `declare(strict_types=1)` / ルートモデルバインディング / FormRequest / PHPDoc

---

## リファクタリングとは

**動きを変えずに、コードの構造だけを良くすること** です。

「良くする」の中身は、たとえば

- 読んだ人が誤解しない
- 間違った使い方をするとエラーで気付ける
- IDE が補完してくれる

型を付けるのは、この 3 つ全部に効きます。

### リファクタリングの前に必ずやること

**テストを緑にしておく。** これが安全網です。

```bash
php artisan test
```

```
Tests:  14 passed
```

ここが緑でないまま書き換えると、「元から壊れていたのか、いま壊したのか」が分からなくなります。
**この章では、1 ステップ書き換えるごとにテストを走らせてください。**

---

## いまのコードの型の状態

第 10 章まで書いたコードを見てみましょう。

```php
    public function index()                       // ← 何を返すのか読まないと分からない
    public function store(Request $request)       // ← 検証済みかどうか分からない
    public function edit($id)                     // ← $id は数値？文字列？
    public function destroy($id)
```

型の情報がほとんどありません。これを 1 つずつ埋めていきます。

---

## Step 11-1　戻り値の型を付ける

`TaskController` の先頭に `use` を 2 行足します。

```php
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
```

そのうえで、6 つのメソッドの `)` の後ろに戻り値の型を書き足します。

```php
    public function index(): View
    public function store(Request $request): RedirectResponse
    public function edit($id): View
    public function update(Request $request, $id): RedirectResponse
    public function complete($id): RedirectResponse
    public function destroy($id): RedirectResponse
```

```bash
php artisan test
```

14 件緑のままなら OK です。

### 何が嬉しいのか

- **メソッドの中身を読まなくても、何を返すか分かる**
- 書き間違えると **実行時に即エラー** になる（黙って変な値が返るより 100 倍マシ）
- VS Code が `redirect()->route(...)` の後ろで補完を出してくれるようになる

### 試してみる（体験してから戻す）

`index()` の戻り値の型を、わざと `: RedirectResponse` に変えて `/tasks` を開いてください。

```
App\Http\Controllers\TaskController::index(): Return value must be of type
Illuminate\Http\RedirectResponse, Illuminate\View\View returned
```

**「間違いをその場で教えてくれる」のが型の一番の価値です。** 確認したら戻してください。

---

## Step 11-2　`declare(strict_types=1)` を付ける

`TaskController.php` の 1 行目 `<?php` の直後に追加します。

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;
```

> **必ず `<?php` の直後**（`namespace` より前）に書きます。
> コメントより後ろに置くと `strict_types declaration must be the very first statement` というエラーになります。

### 何が変わるのか

PHP は普段、型が違っても **勝手に変換して通してしまいます**。

```php
function stars(int $count): string {
    return str_repeat('*', $count);
}

stars('3');   // strict_types なし → '3' が 3 に変換されて "***" が返る
stars('3');   // strict_types あり → TypeError で止まる
```

「文字列を渡してしまった」というバグが、**黙って動いてしまう**のか、
**その場で分かる**のかの違いです。

### よくある誤解

「`declare(strict_types=1)` を付けたら、URL の `/tasks/3/edit` の `3` が
文字列だから `edit(int $id)` でこけるのでは？」

**こけません。**
`strict_types` は「宣言したファイル」ではなく **「呼び出しを書いたファイル」** で判定されます。
コントローラを呼んでいるのは Laravel 本体（`vendor/` の中）で、そちらは `strict_types` なしなので、
`'3'` → `3` の変換が行われます。

`app/Models/Task.php` にも同じように `declare(strict_types=1);` を足しておきましょう。

```bash
php artisan test
```

---

## Step 11-3　ルートモデルバインディング（`$id` を消す）

いま、4 つのメソッドが同じ 2 行を書いています。

```php
    public function edit($id): View
    {
        $task = Task::findOrFail($id);      // ← これ
        return view('tasks.edit', compact('task'));
    }
```

Laravel には **「引数の型を書くだけで、URL の値から自動でモデルを探してくる」** 機能があります。

### ① ルートのパラメータ名を合わせる

`routes/web.php` の `{id}` を `{task}` に変えます。

```php
Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])
    ->name('tasks.complete');
```

`Route::resource` が作るルートは最初から `{task}` なので、こちらは変更不要です。

> **ここが肝心**：ルートの `{task}`、コントローラの引数名 `$task`、この 2 つが
> **同じ名前** でないと動きません。

### ② コントローラを書き換える

```php
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
    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate($this->rules, $this->messages);

        $task->name = $validated['name'];
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
```

`Task::findOrFail($id);` が 4 か所すべて消えました。

### 3 つ得をしています

| | Before | After |
| --- | --- | --- |
| 型 | `$id`（何型か不明） | `Task $task`（必ず Task） |
| 見つからないとき | `findOrFail` を書き忘れると `null` が漏れる | **自動で 404** |
| 行数 | 毎回 1 行 | 0 行 |

**`$task` が `null` である可能性がコードから消えた** のが一番大きい効果です。

### ビュー側は変更不要

`route('tasks.edit', $task->id)` はそのままで動きます
（`$task` を丸ごと渡す `route('tasks.edit', $task)` でも動きます）。

```bash
php artisan test
```

ここでも 14 件緑のはずです。**動きを変えていない証拠** です。

---

## Step 11-4　FormRequest でバリデーションを型にする

`store()` と `update()` はいま、`Request` を受け取ってから中で検証しています。

```php
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules, $this->messages);
```

これだと **「検証済みかどうか」がメソッドの外から見えません**。
FormRequest を使うと、**引数の型そのものが「検証済みです」という意味になります。**

### ① 作る

```bash
php artisan make:request TaskRequest
```

### ② `app/Http/Requests/TaskRequest.php` を書き換える

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * このリクエストを実行してよいか（今回は全員 OK）
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:100',
        ];
    }

    /**
     * エラーメッセージ
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'タスク名を入力してください。',
            'name.max'      => 'タスク名は100文字以内で入力してください。',
        ];
    }
}
```

> **`authorize()` は初期値が `false`** です。
> `true` に変え忘れると、フォームを送っても **403 エラー** になります。よくある詰まりどころです。

### ③ コントローラを書き換える

`use` を差し替えます。

```php
use App\Http\Requests\TaskRequest;   // 追加
// use Illuminate\Http\Request;      ← 使わなくなるので削除
```

`store()` と `update()` の引数の型を `TaskRequest` に変えます。

```php
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

    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $task->name = $request->validated('name');
        $task->save();

        return redirect()
            ->route('tasks.index')
            ->with('message', 'タスクを更新しました。');
    }
}
```

そして、クラスの先頭にあった `private array $rules` と `private array $messages` を
**削除** してください（`TaskRequest` に引っ越したので不要です）。

### 何が起きているのか

```
[リクエスト到着]
    ↓
Laravel が TaskRequest を組み立てる
    ↓
authorize() を実行 → false なら 403
    ↓
rules() で検証 → 失敗なら自動でリダイレクト（store() は呼ばれない）
    ↓
store(TaskRequest $request) が呼ばれる  ← ここに来た時点で検証済みが確定
```

`$request->validated('name')` は **ルールを通った値だけ** を返します。
「検証していない生の値をうっかり保存する」事故が、型のレベルで防がれます。

```bash
php artisan test
```

---

## Step 11-5　型を付けられないものに PHPDoc を書く

最後に `app/Models/Task.php` です。

### `$fillable` には型を付けられない

まず実験してみてください。

```php
    protected array $fillable = [   // ← array を足してみる
```

```
Type of App\Models\Task::$fillable must not be defined
(as in class Illuminate\Database\Eloquent\Model)
```

**PHP では、親クラスが型なしで宣言したプロパティに、子クラスで型を足せません。**
Laravel 9 の `Model` が `protected $fillable = []` と型なしで書いているので、これは諦めるしかありません。

こういうときは **PHPDoc** で型を伝えます。実行時には効きませんが、
IDE の補完と静的解析ツールはこれを読んでくれます。

### `Task.php` を書き換える

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * tasks テーブルの 1 行を表すモデル。
 *
 * カラムは実行時に動的に生えるため、IDE と静的解析に型を教えるには
 * PHPDoc の @property を使う。
 *
 * @property int                             $id
 * @property string                          $name
 * @property bool                            $status
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 */
class Task extends Model
{
    use HasFactory;

    /**
     * create() / update() でまとめて代入してよいカラム
     *
     * ※ 親の Model が型なしで宣言しているため、`protected array $fillable`
     *    のように型を付けることはできない（PHP の制約）。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * カラムの型変換（0/1 を true/false として扱う）
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];
}
```

これを書くと、VS Code で `$task->` と打ったときに `name` や `status` が候補に出るようになります。
`$task->nmae`（タイプミス）も指摘されます。

> ちなみに `$casts` の `'status' => 'boolean'` は、**実行時に効く型変換** です。
> これがあるおかげで `$task->status` は `1` ではなく `true` を返します。
> PHPDoc（お飾り）と `$casts`（実際の変換）は役割が違うので、混同しないでください。

---

## 最終確認

```bash
php artisan test
```

```
Tests:  14 passed
```

**1 件も落ちずに、コードだけが変わりました。**
これがリファクタリングです。ブラウザでも一通り触って確認しておきましょう。

---

## Before / After

```php
// Before（第10章）
public function edit($id)
{
    $task = Task::findOrFail($id);
    return view('tasks.edit', compact('task'));
}

public function store(Request $request)
{
    $validated = $request->validate($this->rules, $this->messages);
    Task::create(['name' => $validated['name'], 'status' => false]);
    return redirect()->route('tasks.index')->with('message', 'タスクを追加しました。');
}
```

```php
// After（第11章）
public function edit(Task $task): View
{
    return view('tasks.edit', compact('task'));
}

public function store(TaskRequest $request): RedirectResponse
{
    Task::create(['name' => $request->validated('name'), 'status' => false]);
    return redirect()->route('tasks.index')->with('message', 'タスクを追加しました。');
}
```

**シグネチャ（1 行目）を読むだけで、何を受け取って何を返すか全部分かる。** これが目標です。

---

## Step 11-6（任意）　静的解析ツールで確かめる

「本当に型が付いたのか」を機械に判定させます。時間と回線に余裕があるときにどうぞ。

```bash
composer require --dev "nunomaduro/larastan:^2.0"
```

プロジェクト直下に `phpstan.neon` を作ります。

```neon
includes:
    - ./vendor/nunomaduro/larastan/extension.neon

parameters:
    paths:
        - app
    level: 6
```

```bash
php vendor/phpstan/phpstan/phpstan.phar analyse --memory-limit=1G
```

**この章をやる前の第 10 章のコードだと 13 件の指摘が出ます。**
この章を終えたコードなら `[OK] No errors` になります。

```
 ------ ---------------------------------------------------------------------------
  Line   Http/Controllers/TaskController.php
 ------ ---------------------------------------------------------------------------
  13     Property TaskController::$rules type has no value type specified in
         iterable type array.
  ...
 ------ ---------------------------------------------------------------------------
 [ERROR] Found 13 errors
```

`level` は 0〜9 まであり、上げるほど厳しくなります。
実務では「level 5 から始めて、直しながら少しずつ上げる」のが定石です。

> `level: 9` まで上げると、この教材のコードでもかなりの指摘が出ます。挑戦してみてください。

---

## 演習

**演習 11-A**
`complete()` の戻り値の型を `: View` に書き換えて完了ボタンを押し、
どんなエラーが出るか確認してください。**確認したら戻すこと。**

**演習 11-B**
`routes/web.php` の `{task}` を `{id}` に戻すと、`complete()` はどうなりますか？
実際にやってエラーメッセージを読んでください。**確認したら戻すこと。**
（ルートのパラメータ名と引数名を揃える必要がある、という話の実地確認です）

**演習 11-C**
`TaskRequest` の `authorize()` を `false` に戻して、タスクを追加してみてください。
何番のエラーが出ますか？ **確認したら `true` に戻すこと。**

**演習 11-D**
第 12 章の課題をやるときは、追加したメソッドにも
**必ず引数と戻り値の型を書く** ようにしてください。もう `$id` は書かないこと。

---

## つまずいたら

| エラーメッセージ | 対処 |
| --- | --- |
| `strict_types declaration must be the very first statement` | `declare(strict_types=1);` を `<?php` の直後に移動する |
| `Return value must be of type ..., ... returned` | 戻り値の型と実際に返しているものが食い違っている |
| `Missing required parameter for [Route: tasks.complete]` | ルートの `{task}` と `route('tasks.complete', ...)` の引数が対応していない |
| `Argument #1 ($task) must be of type App\Models\Task, string given` | ルートのパラメータ名（`{id}`）と引数名（`$task`）が違う |
| `403 THIS ACTION IS UNAUTHORIZED` | `TaskRequest::authorize()` が `false` のまま |
| `Class "App\Http\Requests\TaskRequest" not found` | `use App\Http\Requests\TaskRequest;` を書き忘れ／`make:request` を実行していない |
| `Undefined variable $rules` | `$this->rules` を消したのに、まだ使っている箇所が残っている |

➡ さらに力をつけたい人は [第 12 章 発展課題](12-advanced.md) へ
