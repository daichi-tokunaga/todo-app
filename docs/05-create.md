# 第 5 章　タスク追加（Create）

**目安：40 分**

## この章のゴール

一覧画面の入力欄にタスク名を打って「追加する」を押すと、DB に保存されて一覧に増える。

## この章で学ぶこと

フォーム / POST / `@csrf` / `$request->input()` / `Task::create()` / リダイレクト

---

## Step 5-1　ルートを増やす

`routes/web.php` の `->only([...])` に `'store'` を追加します。

```php
Route::resource('tasks', TaskController::class)
    ->only(['index', 'store']);
```

```bash
php artisan route:list --path=tasks
```

```
GET|HEAD  tasks ...... tasks.index › TaskController@index
POST      tasks ...... tasks.store › TaskController@store
```

**URL は同じ `/tasks` なのに、GET と POST で行き先が違う** ことに注目してください。
Web アプリはこの「メソッド × URL」の組み合わせで処理を切り替えています。

---

## Step 5-2　追加フォームを置く

`resources/views/tasks/index.blade.php` の `<h1>` の下に、次を **追加** します。

```blade
    {{-- 追加フォーム --}}
    <div class="card">
        <form action="{{ route('tasks.store') }}" method="post">
            @csrf
            <div class="form-row">
                <input type="text" name="name" placeholder="洗濯物をたたむ...">
                <button type="submit">追加する</button>
            </div>
        </form>
    </div>
```

### `@csrf` は絶対に忘れないこと

`@csrf` は展開されると、こんな隠しフィールドになります。

```html
<input type="hidden" name="_token" value="ランダムな文字列">
```

これがないと Laravel は **419 Page Expired** というエラーを返します。

これは **CSRF（クロスサイトリクエストフォージェリ）対策** です。
悪意のあるサイトが「勝手にあなたのアカウントで投稿する」フォームを仕込んでも、
このトークンを知らないので弾かれる、という仕組みです。

**Laravel で `method="post"` を書いたら、次の行は必ず `@csrf`。** そう覚えてください。

---

## Step 5-3　`store()` を実装する

`app/Http/Controllers/TaskController.php` の `store()` を書き換えます。

```php
    /**
     * POST /tasks  新規登録
     */
    public function store(Request $request)
    {
        Task::create([
            'name'   => $request->input('name'),
            'status' => false,
        ]);

        return redirect()->route('tasks.index');
    }
```

### `$request` から値を取り出す

```php
$request->input('name')  // <input name="name"> に入力された値
```

`name` 属性の名前がそのままキーになります。
フォームの `name="name"` と、ここの `input('name')` は **必ず一致させてください**。

### なぜ `return view()` ではなく `redirect()` なのか

保存したあとにそのまま画面を返すと、ユーザーがブラウザを再読み込みしたときに
**同じ POST がもう一度送信されて、タスクが二重登録** されてしまいます。

そこで「保存したら一覧 URL に転送する」ようにします。
これは **PRG パターン（Post → Redirect → Get）** と呼ばれる定番の書き方です。

```
POST /tasks  →  保存  →  302 リダイレクト  →  GET /tasks
```

---

## 動作確認

1. http://127.0.0.1:8000/tasks を開く
2. 入力欄に「カフェに行く」と入力して「追加する」を押す
3. 一覧の一番上に「カフェに行く」が増える
4. ブラウザを再読み込みしても二重登録されない

```bash
php artisan test --filter=ch05
```

```
✓ ch05 タスクを追加できる
```

---

## いま起きていること（整理）

```
[ブラウザ] 追加ボタンを押す
    ↓ POST /tasks  （name=カフェに行く, _token=...）
[web.php] Route::resource → TaskController@store
    ↓
[Controller] Task::create([...])
    ↓
[Model] INSERT INTO tasks (name, status, created_at, updated_at) VALUES (...)
    ↓
[Controller] redirect()->route('tasks.index')
    ↓ 302
[ブラウザ] GET /tasks  → 一覧が再表示される
```

---

## 演習

**演習 5-A**
入力欄を空のまま「追加する」を押すとどうなりますか？
実際にやってみて、DB に何が入るか phpMyAdmin で確認してください。
（この問題は次の第 6 章で解決します）

**演習 5-B**
`store()` の中の `'status' => false` を `true` に変えて 1 件登録し、
phpMyAdmin で `status` カラムの値がどうなるか確認してください。
**確認したら `false` に戻すこと。**

**演習 5-C**
`@csrf` を一時的にコメントアウト（`{{-- @csrf --}}`）して追加してみてください。
どんなエラー画面が出ますか？ **確認したら必ず戻すこと。**

---

## つまずいたら

| 症状 | 原因 |
| --- | --- |
| `419 PAGE EXPIRED` | `@csrf` を書き忘れている |
| `The GET method is not supported for this route.` | `<form>` に `method="post"` がない |
| ボタンを押しても何も起きない | `<button>` に `type="submit"` があるか確認 |
| `name` が空で保存される | `<input name="name">` と `input('name')` が食い違っている／`$fillable` に `'name'` がない |
| `Route [tasks.store] not defined.` | `->only([...])` に `'store'` を追加したか確認 |

➡ 次は [第 6 章 入力チェック（バリデーション）](06-validation.md)
