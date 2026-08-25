# 第 2 章　テーブルを作る（マイグレーション）

**目安：30 分**

## この章のゴール

`tasks` テーブルがデータベースに作られ、`php artisan test --filter=ch02` が緑になる。

## この章で学ぶこと

マイグレーション / `php artisan make:migration` / `migrate` / `rollback`

---

## マイグレーションとは

「テーブルの設計図を PHP で書いて、コマンドで DB に反映する仕組み」です。

phpMyAdmin で手作業でテーブルを作ることもできますが、それだと

- 他のメンバーが同じテーブルを作れない
- 「いつ・誰が・どう変えたか」が残らない

という問題があります。マイグレーションならファイルが Git に残るので、
`php artisan migrate` を打つだけで全員が同じテーブルを作れます。

---

## 今回作るテーブル

`tasks`

| カラム名 | 型 | 説明 |
| --- | --- | --- |
| `id` | 整数（自動採番） | 主キー |
| `name` | 文字列（最大 100） | タスク名 |
| `status` | 真偽値 | 完了フラグ。`false` = 未完了 |
| `created_at` | 日時 | 登録日時（Laravel が自動で入れる） |
| `updated_at` | 日時 | 更新日時（Laravel が自動で入れる） |

---

## Step 2-1　マイグレーションファイルを作る

```bash
php artisan make:migration create_tasks_table
```

```
INFO  Migration [database/migrations/2026_08_25_101530_create_tasks_table.php] created successfully.
```

`database/migrations/` の中に、**日時が入ったファイル名** で新しいファイルができます。
（日時は実行したタイミングによって変わるので、皆さんの手元とは違う数字になります）

> **なぜファイル名に日時が付くの？**
> マイグレーションは **古い順に実行される** からです。
> 「テーブルを作る」→「カラムを追加する」の順序が保証されます。

---

## Step 2-2　設計図を書く

いま作られたファイルを開いて、`up()` の中身を書き換えてください。

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーション実行時（php artisan migrate）
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();                              // 主キー（自動採番）
            $table->string('name', 100);               // タスク名（最大100文字）
            $table->boolean('status')->default(false); // 完了フラグ（false = 未完了）
            $table->timestamps();                      // created_at / updated_at
        });
    }

    /**
     * 巻き戻し時（php artisan migrate:rollback）
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
```

### よく使うカラムの型

| 書き方 | MySQL の型 |
| --- | --- |
| `$table->id()` | BIGINT UNSIGNED AUTO_INCREMENT（主キー） |
| `$table->string('name', 100)` | VARCHAR(100) |
| `$table->text('memo')` | TEXT（長文） |
| `$table->integer('count')` | INT |
| `$table->boolean('status')` | TINYINT(1) |
| `$table->date('due_date')` | DATE |
| `$table->timestamps()` | created_at と updated_at をまとめて作る |

`->default(false)` は「何も指定しなければ false を入れる」、
`->nullable()` は「空っぽでも許す」という意味です。

---

## Step 2-3　実行する

```bash
php artisan migrate
```

```
INFO  Running migrations.

2026_08_25_101530_create_tasks_table .............. DONE
```

---

## 動作確認

### ① コマンドで確認する

```bash
php artisan db:table tasks
```

カラムの一覧が表示されます。設計どおりか見比べてください。

### ② phpMyAdmin で確認する

`todo_app` → `tasks` を開き、「構造」タブでカラムを確認します。

### ③ テストを実行する

```bash
php artisan test --filter=ch02
```

```
✓ ch02 tasksテーブルが作られている
```

緑になったらこの章はクリアです。

---

## 知っておくと便利なコマンド

| コマンド | 意味 | 注意 |
| --- | --- | --- |
| `php artisan migrate` | まだ実行していないものを実行 | |
| `php artisan migrate:status` | どれが実行済みか一覧表示 | |
| `php artisan migrate:rollback` | **直前の 1 回分** を取り消す | データも消える |
| `php artisan migrate:fresh` | 全テーブルを削除して作り直す | **データが全部消える** |

> **重要**
> 一度 `migrate` したファイルを書き換えても、もう一度 `migrate` しただけでは反映されません
> （Laravel が「実行済み」と記録しているため）。
> 書き間違えたときは `php artisan migrate:rollback` → 修正 → `php artisan migrate` の順で直します。

---

## 演習

**演習 2-A**
`php artisan migrate:rollback` を実行して、phpMyAdmin から `tasks` テーブルが
消えることを確認してください。そのあと `php artisan migrate` で戻してください。
（`down()` メソッドが何のためにあるのか、体で理解するのが目的です）

**演習 2-B**
`php artisan migrate:status` を実行して、5 つのマイグレーションが
すべて `Ran` になっていることを確認してください。

---

## つまずいたら

| エラーメッセージ | 対処 |
| --- | --- |
| `SQLSTATE[HY000] [2002]` | MySQL が起動していない。XAMPP で MySQL を Start |
| `Base table or view already exists` | すでにテーブルがある。`migrate:rollback` してから作り直す |
| `Syntax error or access violation ... 1071 Specified key was too long` | `string()` の桁数が大きすぎる。今回の課題では起きません |

➡ 次は [第 3 章 モデルとダミーデータ](03-model-seeder.md)
