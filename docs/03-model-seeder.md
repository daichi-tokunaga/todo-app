# 第 3 章　モデルとダミーデータ

**目安：40 分**

## この章のゴール

Tinker から `Task::all()` でデータが取れる。
`php artisan migrate:fresh --seed` でダミーデータが 5 件入る。

## この章で学ぶこと

Eloquent モデル / `$fillable` / Tinker / シーダー

---

## モデルとは

**1 つのテーブルを PHP のクラスとして扱えるようにしたもの** です。

SQL を書かずに、

```php
Task::all();                       // SELECT * FROM tasks
Task::where('status', false)->get(); // SELECT * FROM tasks WHERE status = 0
Task::create(['name' => '牛乳を買う']); // INSERT INTO tasks ...
```

のように書けます。この仕組みを Laravel では **Eloquent（エロクアント）** と呼びます。

---

## Step 3-1　モデルを作る

```bash
php artisan make:model Task
```

`app/Models/Task.php` ができます。

> **名前のルールが超重要**
> モデル名は **単数形・先頭大文字**（`Task`）、テーブル名は **複数形・小文字**（`tasks`）。
> Laravel はこの規則から自動でテーブルを探します。
> `Task` モデル → `tasks` テーブル、`User` モデル → `users` テーブル。

---

## Step 3-2　モデルに設定を書く

`app/Models/Task.php` を、次の内容に書き換えてください。

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * create() / update() でまとめて代入してよいカラム
     */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * カラムの型変換（0/1 を true/false として扱う）
     */
    protected $casts = [
        'status' => 'boolean',
    ];
}
```

### `$fillable` はなぜ必要？

`Task::create($request->all())` のように「フォームの入力をまるごと保存」できると便利ですが、
悪意のあるユーザーが `id` や `is_admin` のような **触られたくない項目** を
勝手に送りつけてくる危険があります（**Mass Assignment 脆弱性**）。

そこで Laravel は「`$fillable` に書いたカラムしか、まとめて代入させない」という
安全装置を持っています。書き忘れると保存されないので注意してください。

---

## Step 3-3　Tinker で動かしてみる

```bash
php artisan tinker
```

`>` が出たら、**1 行ずつ** 入力して結果を見てください。

```php
// 1件登録する
Task::create(['name' => '牛乳を買う', 'status' => false]);

// 全件取得する
Task::all();

// 件数を数える
Task::count();

// 未完了だけ取得する
Task::where('status', false)->get();

// 1件だけ取り出して、中身を見る
$task = Task::first();
$task->name;
$task->created_at;

// 更新する
$task->name = '牛乳と卵を買う';
$task->save();

// 抜ける
exit
```

第 0 章でタイムゾーンを直したので、`created_at` が日本時間になっているはずです。

> Tinker は **本物のデータベースを触ります**。試したデータは実際に保存されます。

---

## Step 3-4　シーダーでダミーデータを用意する

毎回 Tinker で手入力するのは大変なので、
**ダミーデータを一発で流し込む仕組み（シーダー）** を作ります。

### ① シーダーファイルを作る

```bash
php artisan make:seeder TaskSeeder
```

### ② `database/seeders/TaskSeeder.php` を書き換える

```php
<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * 動作確認用のダミーデータを登録する
     */
    public function run()
    {
        $names = [
            '牛乳を買う',
            '洗濯物をたたむ',
            'Laravelの課題を進める',
            '部屋を掃除する',
        ];

        foreach ($names as $name) {
            Task::create([
                'name'   => $name,
                'status' => false,
            ]);
        }

        Task::create([
            'name'   => 'ゴミを出す（完了済みの例）',
            'status' => true,
        ]);
    }
}
```

### ③ 呼び出す設定をする

`database/seeders/DatabaseSeeder.php` の `run()` の中身を、次のように書き換えます。

```php
    public function run()
    {
        $this->call([
            TaskSeeder::class,
        ]);
    }
```

（元からある `// \App\Models\User::factory(10)->create();` などのコメント行は消して構いません）

### ④ 実行する

```bash
php artisan migrate:fresh --seed
```

- `migrate:fresh` … テーブルを全部削除して作り直す
- `--seed` … そのあとシーダーを流す

つまり **「まっさらな状態＋ダミーデータ」にリセットするコマンド** です。
これから先、データがぐちゃぐちゃになったらこのコマンドで作り直せます。

---

## 動作確認

```bash
php artisan tinker
```

```php
Task::count()            // 5 が返る
Task::where('status', false)->count()  // 4 が返る
exit
```

phpMyAdmin の `tasks` テーブルにも 5 件入っていることを確認してください。

---

## 演習

**演習 3-A**
Tinker を使って、自分の今日の予定を 3 件追加してください。

**演習 3-B**
Tinker で次の 2 つを実行し、返ってくる件数の違いを説明できるようにしてください。

```php
Task::where('status', true)->get();
Task::where('status', false)->get();
```

**演習 3-C**
`TaskSeeder.php` のダミーデータを、自分の好きなタスク名に書き換えて
`php artisan migrate:fresh --seed` で反映させてください。

---

## つまずいたら

| 症状 | 原因 |
| --- | --- |
| `Class "App\Models\Task" not found` | ファイル名が `Task.php` か、`namespace App\Models;` があるか確認 |
| `create()` したのに name が空で保存される | `$fillable` に `'name'` を書き忘れている |
| `Table 'todo_app.tasks' doesn't exist` | 第 2 章の `php artisan migrate` がまだ |

➡ 次は [第 4 章 一覧表示（Read）](04-controller-index.md)
