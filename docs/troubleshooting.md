# 困ったときは

## まず落ち着いて、エラー画面を読む

`APP_DEBUG=true` のとき、Laravel は **かなり親切なエラー画面** を出します。

見るべき場所は 3 つです。

1. **一番上の太字** … エラーの種類と内容（英語でも、翻訳にかければすぐ分かります）
2. **ファイル名と行番号** … どこで起きたか
3. **ハイライトされたコード** … その行に何が書いてあるか

「エラーが出た」と言う前に、**この 3 つをメモしてから** 質問すると解決が早くなります。

> エラー画面が出ずに真っ白なときは、`storage/logs/laravel.log` の一番下を見てください。

---

## 環境まわり

### `php` は内部コマンドまたは外部コマンドとして認識されていません

PATH に PHP が入っていません。

- **XAMPP**：Control Panel の右にある **Shell** ボタンから黒い画面を開けば、そこでは `php` が使えます
- 恒久的に直すなら、Windows の環境変数 PATH に `C:\xampp\php` を追加してください

### `SQLSTATE[HY000] [2002] 接続できませんでした` / `Connection refused`

MySQL が起動していません。XAMPP Control Panel で **MySQL の Start** を押してください。

### `SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'`

`.env` のユーザー名・パスワードが違います。XAMPP では次が正しい状態です。

```env
DB_USERNAME=root
DB_PASSWORD=
```

直したら `php artisan config:clear` を実行してください。

### `SQLSTATE[HY000] [1049] Unknown database 'todo_app'`

データベースを作っていません。

**この PC は電源を切るとデータが消えるので、授業のたびに作り直しが必要です。**
「前回は動いたのに」というときは、たいていこれです。

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS todo_app DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
```

詳しくは [第 0 章 Step 0-5](00-setup.md) を見てください。

### テーブルはあるのに、前回入れたタスクが消えている

**正常です。** この PC はシャットダウンでデータベースの中身も消えます。

ダミーデータを入れ直すには（第 3 章まで進んでいる人）：

```powershell
php artisan migrate:fresh --seed
```

### `could not find driver`

PHP の MySQL 拡張が無効です。`php.ini` の次の行の先頭の `;` を消して、MySQL を再起動します。

```ini
extension=pdo_mysql
extension=fileinfo
```

`php --ini` で、どの `php.ini` が読まれているか確認できます。

### `No application encryption key has been specified.`

```bash
php artisan key:generate
```

### `Failed to listen on 127.0.0.1:8000`

すでに `php artisan serve` が動いています。別のポートを使うか、前のターミナルを閉じてください。

```bash
php artisan serve --port=8001
```

---

## ルーティング・URL まわり

### `404 | NOT FOUND`

| 確認すること |
| --- |
| URL のスペルは合っているか（`/task` ではなく `/tasks`） |
| `routes/web.php` を保存したか |
| `php artisan route:list` にそのルートが出ているか |
| `findOrFail()` で「データが無い」場合も 404 になります |

### `Route [tasks.store] not defined.`

そのルート名が登録されていません。

```bash
php artisan route:list --path=tasks
```

で確認してください。`Route::resource(...)->only([...])` の配列に、
その章で追加するはずのメソッド名が入っているか見直します。

### `The GET method is not supported for this route. Supported methods: POST.`

`<form>` に `method="post"` を書き忘れています。

### `The POST method is not supported for this route. Supported methods: PUT, PATCH.`

`@method('PUT')` または `@method('PATCH')` を書き忘れています。
`<form method="post">` はそのままで大丈夫です。

---

## フォームまわり

### `419 | PAGE EXPIRED`

`@csrf` を書き忘れています。**`method="post"` の次の行は必ず `@csrf`。**

（何度書いても出る場合は、ブラウザのタブを長時間開きっぱなしでトークンが失効しています。再読み込みしてください）

### 入力した値が保存されない／空で保存される

| 確認すること |
| --- |
| `<input name="name">` と `$request->input('name')` の名前が一致しているか |
| モデルの `$fillable` に そのカラム名 が入っているか |
| `<input>` が `<form>` タグの **内側** にあるか |

### エラーメッセージが表示されない

`@error('name')` の `'name'` が、`<input name="...">` と一致しているか確認してください。

---

## ビューまわり

### `View [tasks.index] not found.`

`resources/views/tasks/index.blade.php` があるか確認してください。

- 拡張子は `.blade.php`（`.php` だけではダメ）
- `view('tasks.index')` のドットは **フォルダの区切り**

### `Undefined variable $tasks`

コントローラからビューに変数を渡していません。

```php
return view('tasks.index', compact('tasks'));
```

`compact()` に渡す名前は **変数名から `$` を取ったもの** です。

### 変更したのに画面に反映されない

```bash
php artisan view:clear
```

そのうえで、ブラウザで **`Ctrl + Shift + R`**（Mac は `Cmd + Shift + R`）。

### CSS が効かない

- `public/css/app.css` があるか
- レイアウトに `<link rel="stylesheet" href="{{ asset('css/app.css') }}">` があるか
- ブラウザの開発者ツール（F12）→ Network タブで `app.css` が 200 で読めているか

---

## モデル・DB まわり

### `Class "App\Models\Task" not found`

| 確認すること |
| --- |
| ファイルが `app/Models/Task.php` にあるか |
| ファイルの先頭に `namespace App\Models;` があるか |
| コントローラの先頭に `use App\Models\Task;` があるか |

それでもダメなら `composer dump-autoload`。

### `Table 'todo_app.tasks' doesn't exist`

`php artisan migrate` を実行していません。

### マイグレーションファイルを直したのに反映されない

Laravel は「実行済み」を記録しているので、同じファイルを再実行しません。

```bash
php artisan migrate:rollback   # 取り消す
# ← ここでファイルを修正
php artisan migrate            # もう一度実行
```

どうにもならなくなったら、**データが消えてもよければ**

```bash
php artisan migrate:fresh --seed
```

### `Attempt to assign property "..." on null`

`find()` がデータを見つけられず `null` を返しています。
`findOrFail()` に変えると、原因が分かりやすい 404 になります。

### `Add [name] to fillable property to allow mass assignment.`

`app/Models/Task.php` の `$fillable` にそのカラム名を追加してください。

---

## 型まわり（第 11 章）

### `strict_types declaration must be the very first statement in the script`

`declare(strict_types=1);` は `<?php` の **直後** に書きます。
コメントや `namespace` より後ろに置くと、この致命的エラーになります。

```php
<?php

declare(strict_types=1);   // ← ここ

namespace App\Http\Controllers;
```

### `Return value must be of type X, Y returned`

メソッドに書いた戻り値の型と、実際に `return` しているものが違います。

| 返しているもの | 正しい型 |
| --- | --- |
| `view(...)` | `View` |
| `redirect()->...` | `RedirectResponse` |

### `Argument #1 ($task) must be of type App\Models\Task, string given`

ルートモデルバインディングが効いていません。
**ルート定義の `{...}` の名前と、引数名を一致させてください。**

```php
Route::patch('/tasks/{task}/complete', ...);   // {task}
public function complete(Task $task)           // $task
```

### `403 | THIS ACTION IS UNAUTHORIZED.`

FormRequest の `authorize()` が `false` のままです。`true` に変えてください。
`php artisan make:request` の初期値が `false` なので、非常によくある詰まりどころです。

### `Type of App\Models\Task::$fillable must not be defined`

`protected array $fillable` のように型を付けてしまっています。
親の `Model` が型なしで宣言しているため、子クラスで型を足すことは PHP の仕様上できません。
`protected $fillable` に戻し、型は PHPDoc（`@var array<int, string>`）で伝えてください。

### `Class "App\Http\Requests\TaskRequest" not found`

`php artisan make:request TaskRequest` を実行したか、
コントローラに `use App\Http\Requests\TaskRequest;` を書いたか確認してください。

---

## テストまわり

### 全部失敗する

まだ機能を作っていないだけかもしれません。まずは対象の章まで進めてください。

### `Database file at path [:memory:] does not exist`

`pdo_sqlite` 拡張が無効です。`php.ini` の `extension=pdo_sqlite` の `;` を消してください。

### テストを実行したら開発データが消えた

このリポジトリの `phpunit.xml` では、テストは **メモリ上の SQLite** を使う設定なので、
本来 MySQL のデータは消えません。消えた場合は `phpunit.xml` の

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

がコメントアウトされていないか確認してください。

---

## 最終手段

```bash
php artisan optimize:clear
composer dump-autoload
```

それでもダメなら、**エラーメッセージ全文** と **直前に何をしたか** を持って講師に相談してください。
「動きません」だけでは誰にも分かりません。

---

## Git で「元に戻す」

作業中にぐちゃぐちゃになったら、変更を捨てて最後のコミットに戻せます。

```bash
git status              # 何を変更したか確認
git diff                # 変更内容を確認
git checkout -- ファイル名   # そのファイルの変更を捨てる
```

**捨てた変更は戻せません。** 実行前に必ず `git diff` で確認してください。

こまめにコミットしておくと、安心して実験できます。

```bash
git add .
git commit -m "第5章まで完了"
```
