# 第 1 章　ルーティングとビュー

**目安：40 分**

## この章のゴール

`http://127.0.0.1:8000/tasks` を開くと、ヘッダーとフッターのついた
「今日は何をする？」というページが表示される。

## この章で学ぶこと

ルーティング（`routes/web.php`） / Blade テンプレート / レイアウトの共通化（`@extends` `@yield` `@section`） / `asset()`

---

## Laravel の処理の流れ

Laravel は、ブラウザからのリクエストを次の順番で処理します。

```
ブラウザ  →  ルーティング  →  コントローラ  →  モデル（DB）
   ↑            web.php      Controller.php    Model.php
   └──────────  ビュー  ←──────────┘
                blade.php
```

この章では、まず一番左の **ルーティング** と、一番下の **ビュー** だけを触ります。
コントローラとモデルは第 2〜4 章で登場します。

---

## Step 1-1　ルートを 1 本追加する

`routes/web.php` を開いて、一番下に次の 3 行を **追記** してください。

```php
Route::get('/tasks', function () {
    return 'ここに Todo アプリを作ります';
});
```

保存したら、ブラウザで **http://127.0.0.1:8000/tasks** を開きます。
文字が表示されれば成功です。

### 読み方

```php
Route::get('/tasks', function () { ... });
//     ↑      ↑            ↑
//  HTTPメソッド  URL      この URL に来たときの処理
```

`Route::get` は「GET リクエスト（＝ブラウザで URL を開く）が来たら」という意味です。
第 5 章以降では `Route::post` や `Route::delete` も出てきます。

### 確認コマンド

いま登録されているルートの一覧は、コマンドで見られます。

```bash
php artisan route:list
```

---

## Step 1-2　ビュー（HTML）を返すようにする

文字列ではなく HTML ファイルを返すのが普通のやり方です。
Laravel の HTML ファイルは `resources/views/` の中に **`.blade.php`** という拡張子で置きます。

### ① フォルダとファイルを作る

`resources/views/tasks/index.blade.php` を新規作成してください。
（`tasks` フォルダも自分で作ります）

### ② 中身を書く

```blade
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>タスク一覧</title>
</head>
<body>
    <h1>今日は何をする？</h1>
</body>
</html>
```

### ③ ルートを書き換える

`routes/web.php` の先ほどの部分を、次のように変更します。

```php
Route::get('/tasks', function () {
    return view('tasks.index');
});
```

`view('tasks.index')` は `resources/views/tasks/index.blade.php` を指します。
**フォルダの区切りは `/` ではなく `.`（ドット）** です。ここはよく間違えるので注意。

ブラウザを再読み込みして、大きな文字で「今日は何をする？」と出れば成功です。

---

## Step 1-3　レイアウトを共通化する

これから一覧画面と編集画面の 2 ページを作ります。
`<head>` やヘッダー・フッターを両方に書くと、直すときに 2 か所直すことになって面倒です。

Blade には **共通部分を 1 つのファイルにまとめる仕組み** があります。

### ① レイアウトファイルを作る

`resources/views/layouts/app.blade.php` を新規作成し、以下をコピペしてください。

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
            <a href="/tasks">Todoアプリ</a>
            <span class="sub">Laravel ハンズオン</span>
        </div>
    </header>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="site-footer">
        <div class="container inner">Todoアプリ - Laravel ハンズオン</div>
    </footer>
</body>
</html>
```

### ② 一覧ページをレイアウトに乗せる

`resources/views/tasks/index.blade.php` を **まるごと書き換え** ます。

```blade
@extends('layouts.app')

@section('title', 'タスク一覧')

@section('content')
    <h1 class="page-title">今日は何をする？</h1>

    <p class="empty">ここにタスクの一覧が並ぶ予定です。</p>
@endsection
```

ブラウザを再読み込みすると、紺色のヘッダーとフッターがつき、
真ん中に白いカードが表示されるはずです。

### 読み方

| 書き方 | 意味 |
| --- | --- |
| `@extends('layouts.app')` | このファイルは `layouts/app.blade.php` の中に埋め込まれる |
| `@yield('content')` | レイアウト側の「ここに中身が入る」という穴 |
| `@section('content') ... @endsection` | 穴に入れる中身 |
| `@yield('title', 'Todoアプリ')` | 中身がなければ第 2 引数が使われる（デフォルト値） |

### CSS について

`{{ asset('css/app.css') }}` は `public/css/app.css` の URL を作る関数です。
このファイルは **最初から用意してあります**（この課題では CSS を書く必要はありません）。

見た目を自分好みにしたい人は `public/css/app.css` を自由に編集して構いません。

---

## 動作確認

- [ ] http://127.0.0.1:8000/tasks に紺色のヘッダーとフッターが表示される
- [ ] ブラウザのタブに「タスク一覧」と出ている
- [ ] `php artisan route:list` に `GET|HEAD  tasks` の行がある

---

## 演習

**演習 1-A**
`/about` という URL を追加し、`resources/views/about.blade.php` を作って
自分の名前と「Laravel を勉強中です」というメッセージを表示してください。
レイアウト（`layouts.app`）を使うこと。

**演習 1-B**
`layouts/app.blade.php` のフッターの文字を、自分の名前入りに変えてください。
一覧ページと `/about` の **両方** が同時に変わることを確認しましょう。
（これがレイアウトを共通化するメリットです）

> 解答例は [99-solutions.md](99-solutions.md) にあります。まず 10 分は自分で考えてみてください。

---

## つまずいたら

| 症状 | 原因 |
| --- | --- |
| `404 NOT FOUND` | URL のスペルミス、または `routes/web.php` の保存忘れ |
| `View [tasks.index] not found.` | ファイル名か置き場所が違う。`resources/views/tasks/index.blade.php` か確認 |
| 見た目が真っ白（CSS が効かない） | `public/css/app.css` があるか確認。ブラウザで `Ctrl + Shift + R` |

➡ 次は [第 2 章 テーブルを作る](02-migration.md)
