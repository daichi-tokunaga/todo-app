<h1>これはあなたのリポジトリではありません</h1>

<h1>初回のみやること</h1>

このリポジトリ(徳永のアカウントにあるリポジトリ)を自身のアカウントにフォークして課題を進めること。

<h2>フォーク手順</h2>

1. https://github.com/daichi-tokunaga/todo-app/fork にアクセス

1. Ownerが自分のアカウント、Repository nameがtodo-appになっていることを確認

1. 画面下のCreate fork(緑のボタン)を押下

1. 自身のアカウントにコピーされるので以降、そこに作ったものをpushしていく


# Laravel ハンズオン：Todo アプリを作ろう

このリポジトリは **Laravel 9 のまっさらな状態** です。
ここから 1 章ずつ手を動かして、Todo アプリを完成させます。

完成すると、こんなことができるアプリになります。

| 機能 | HTTP メソッド | URL |
| --- | --- | --- |
| タスク一覧を見る | GET | `/tasks` |
| タスクを追加する | POST | `/tasks` |
| タスクを完了にする | PATCH | `/tasks/{id}/complete` |
| タスクを編集する | GET / PUT | `/tasks/{id}/edit` , `/tasks/{id}` |
| タスクを削除する | DELETE | `/tasks/{id}` |

---

## セットアップ手順（クローンから起動まで）

> **⚠️ この教室の PC は、シャットダウンすると中身が消えます。**
> そのため、**授業のたびにこの手順を最初からやり直してください。**
> 詳しい説明は [docs/00-setup.md](docs/00-setup.md) にあります。ここは手順だけです。

### 1. XAMPP の MySQL を起動する

XAMPP Control Panel を開き、**MySQL の行の「Start」** を押します。
「Running」と緑色になれば OK です。（Apache は使いません）

### 2. リポジトリをクローンする

```powershell
cd $HOME\Documents
git clone <配布されたリポジトリのURL> todo-app
cd todo-app
```

**これ以降のコマンドは、すべて `todo-app` フォルダの中で実行します。**

### 3. ライブラリをインストールする（3〜5 分）

```powershell
composer install
```

### 4. `.env` を作って、アプリのキーを生成する

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### 5. データベースを作る

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS todo_app DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

> phpMyAdmin（XAMPP Control Panel の MySQL 行にある「Admin」）から
> 手で作っても構いません。データベース名は `todo_app`、照合順序は `utf8mb4_unicode_ci` です。

### 6. テーブルを作る

```powershell
php artisan migrate
```

> 第 3 章まで進んでいる人は、ダミーデータも入れておくと確認が楽です。
> ```powershell
> php artisan migrate:fresh --seed
> ```

### 7. サーバーを起動する

```powershell
php artisan serve
```

ブラウザで **http://127.0.0.1:8000** を開きます。

> **このターミナルは開いたままにしておきます。** 止めるときは `Ctrl + C`。
> artisan コマンドを打ちたいときは、**別のターミナルをもう 1 つ開いて** そちらで実行してください。

---

### まとめてコピペする用（2 回目以降の人向け）

XAMPP で MySQL を起動したあと、以下をターミナルに貼り付ければ 7 まで一気に進みます。
`<配布されたリポジトリのURL>` の部分だけ書き換えてください。

```powershell
cd $HOME\Documents
git clone <配布されたリポジトリのURL> todo-app
cd todo-app
composer install
Copy-Item .env.example .env
php artisan key:generate
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS todo_app DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
php artisan serve
```

うまくいかないときは [docs/troubleshooting.md](docs/troubleshooting.md) を見てください。

---

## ⚠️ 授業の終わりに、必ず作業を保存すること

**この PC は電源を切るとデータが消えます。書いたコードもすべて消えます。**
帰る前に、必ず次のどちらかをやってください。

**方法 A：自分の GitHub リポジトリに push する（おすすめ）**

```powershell
git add .
git commit -m "第5章まで完了"
git remote add mine <自分のリポジトリのURL>
git push mine main
```

次回は、配布リポジトリではなく **自分のリポジトリ** をクローンすれば続きから始められます。

**方法 B：フォルダごと USB メモリやクラウドにコピーする**

`todo-app` フォルダをまるごとコピーします。
`vendor` フォルダは大きいので、消してからコピーしても構いません
（次回 `composer install` すれば元に戻ります）。

---

## 進め方

1. 上の **セットアップ手順** で環境を動かす（詳しい解説は [docs/00-setup.md](docs/00-setup.md)）
2. あとは番号順に 1 章ずつ進める
3. 各章の最後にある **「動作確認」** と **「演習」** を必ずやる

**コードは基本的にコピペで動きます。**
ただし「演習」は自分で考えて書く部分です。ここが一番力になるので飛ばさないでください。
どうしても分からないときだけ [docs/99-solutions.md](docs/99-solutions.md) を見てください。

---

## 目次

| 章 | 内容 | 学ぶこと |
| --- | --- | --- |
| [00](docs/00-setup.md) | 環境構築と起動確認 | composer / .env / MySQL / artisan |
| [01](docs/01-routing-view.md) | ルーティングとビュー | Route / Blade / レイアウト |
| [02](docs/02-migration.md) | テーブルを作る | マイグレーション |
| [03](docs/03-model-seeder.md) | モデルとダミーデータ | Eloquent / Tinker / シーダー |
| [04](docs/04-controller-index.md) | 一覧表示（Read） | コントローラ / ビューへの値渡し |
| [05](docs/05-create.md) | タスク追加（Create） | フォーム / POST / CSRF |
| [06](docs/06-validation.md) | 入力チェック | バリデーション / エラー表示 |
| [07](docs/07-complete.md) | 完了にする（Update） | PATCH / 条件付き取得 |
| [08](docs/08-edit.md) | 編集する（Update） | 編集フォーム / PUT |
| [09](docs/09-delete.md) | 削除する（Delete） | DELETE / 確認ダイアログ |
| [10](docs/10-finishing.md) | 仕上げ | フラッシュメッセージ / 404 |
| [11](docs/11-refactoring-types.md) | 型を付けて堅くする | 型宣言 / FormRequest / ルートモデルバインディング |
| [12](docs/12-advanced.md) | 発展課題 | 自力で機能追加 |

補助資料

- [チートシート](docs/cheatsheet.md) … よく使う artisan コマンドと Blade 構文
- [困ったときは](docs/troubleshooting.md) … エラーメッセージ別の対処法
- [演習の解答例](docs/99-solutions.md) … 最後の手段

---

## 自己採点テスト

このリポジトリには、章ごとの合格判定テストが入っています。

```powershell
php artisan test
```

- **最初はほとんど失敗します。それが正常です。**（まだ何も作っていないので）
- 章を進めるごとに緑（✓）が増えていきます
- 全 14 件が緑になったらゴールです

特定の章だけ確認したいとき：

```powershell
php artisan test --filter=ch04
```

> テストは MySQL ではなくメモリ上の SQLite で動くので、開発中のデータは消えません。安心して何度でも実行してください。

---

## この教材の前提

- Windows
- PHP 8.0.2 以上（XAMPP に入っている PHP を使います）
- Composer
- MySQL（XAMPP の MySQL）
- Git
- **Node.js は使いません。** CSS は `public/css/app.css` を最初から用意してあります

`php -v` が動かない場合は、環境変数 PATH に `C:\xampp\php` が入っていません。
XAMPP Control Panel の右にある **Shell** ボタンから開いた黒い画面なら、そのまま `php` が使えます。

参考記事：[Laravelで簡単なTodoアプリを作ってみる（B-Risk）](https://b-risk.jp/blog/2022/08/laravel/)
