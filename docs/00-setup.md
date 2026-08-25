# 第 0 章　環境構築と起動確認

**目安：30 分**

## この章のゴール

- ブラウザに Laravel の初期画面が表示される
- `php artisan migrate` が成功する（＝ MySQL につながっている）

## この章で学ぶこと

`composer install` / `.env` / アプリケーションキー / `php artisan serve` / `php artisan migrate`

---

## Step 0-1　必要なソフトを確認する

ターミナル（Windows は PowerShell、Mac は ターミナル.app）で次を 1 行ずつ実行してください。

```bash
php -v
composer -V
git --version
```

3 つともバージョンが表示されれば OK です。
`php -v` が `8.0.2` 未満、または「そんなコマンドはない」と言われた場合は講師に声をかけてください。

> **Windows / XAMPP の人へ**
> `php -v` が動かない場合、環境変数 PATH に `C:\xampp\php` が入っていません。
> XAMPP Control Panel の「Shell」ボタンから開いた黒い画面なら、そのまま php が使えます。

---

## Step 0-2　リポジトリを取得する

配布された URL を使ってクローンします（すでにフォルダを渡されている人はこの手順は不要です）。

```bash
git clone <配布されたリポジトリのURL> todo-app
cd todo-app
```

**以降のコマンドは、すべてこの `todo-app` フォルダの中で実行します。**

---

## Step 0-3　ライブラリをインストールする

Laravel 本体は `vendor/` フォルダに入りますが、これは Git に含まれていません。
次のコマンドでダウンロードします（3〜5 分かかります）。

```bash
composer install
```

`Generating optimized autoload files` と出て、`vendor` フォルダができれば成功です。

---

## Step 0-4　`.env` ファイルを作る

`.env` はデータベースの接続先など「環境ごとに違う設定」を書くファイルです。
**セキュリティ上の理由から Git には含まれていません**ので、自分で作ります。

**Windows（PowerShell）**

```powershell
Copy-Item .env.example .env
```

**Mac / Linux**

```bash
cp .env.example .env
```

続いて、このアプリ専用の暗号化キーを生成します。

```bash
php artisan key:generate
```

`.env` を開いて、`APP_KEY=base64:...` という長い文字列が入っていれば成功です。

---

## Step 0-5　MySQL を起動してデータベースを作る

XAMPP Control Panel（Mac の人は MAMP）を開いて、**MySQL の Start** を押します。
※ Apache は使いません（Laravel には開発用サーバーが付いているため）。

次に、データベースを 1 つ作ります。方法はどちらでも構いません。

**方法 A：phpMyAdmin（GUI）**

1. XAMPP Control Panel の MySQL 行にある **Admin** を押す
2. 左上の「新規作成」をクリック
3. データベース名：`todo_app`
4. 照合順序：`utf8mb4_unicode_ci`
5. 「作成」を押す

**方法 B：コマンド**

```bash
mysql -u root -e "CREATE DATABASE todo_app DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## Step 0-6　`.env` の接続情報を確認する

`.env` をエディタで開き、`DB_` で始まる行を確認します。

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todo_app
DB_USERNAME=root
DB_PASSWORD=
```

- **XAMPP を使っている人**：上の状態のままで OK（root にパスワードなし）
- **MAMP を使っている人**：`DB_PORT=8889` と `DB_PASSWORD=root` に書き換えてください

> `.env` を書き換えたのに反映されないときは `php artisan config:clear` を実行してください。

---

## Step 0-7　接続できているか確かめる

```bash
php artisan migrate
```

次のように表示されれば、MySQL への接続に成功しています。

```
INFO  Running migrations.

2014_10_12_000000_create_users_table ............ DONE
2014_10_12_100000_create_password_resets_table .. DONE
2019_08_19_000000_create_failed_jobs_table ...... DONE
2019_12_14_000001_create_personal_access_tokens_table  DONE
```

phpMyAdmin で `todo_app` を開くと、`users` などのテーブルができているはずです。
（これは Laravel が最初から用意しているテーブルです。Todo アプリのテーブルは第 2 章で作ります）

---

## Step 0-8　サーバーを起動する

```bash
php artisan serve
```

```
INFO  Server running on [http://127.0.0.1:8000].
```

と出たら、ブラウザで **http://127.0.0.1:8000** を開いてください。
Laravel のロゴが入った初期画面が表示されれば成功です 🎉

> **このターミナルは開いたままにしておきます。**
> 止めたいときは `Ctrl + C`。
> artisan コマンドを打ちたいときは、**別のターミナルをもう 1 つ開いて** そちらで実行してください。

---

## Step 0-9　タイムゾーンを日本時間にする（ここから手を動かします）

Laravel の初期設定は世界標準時（UTC）です。このままだと登録日時が 9 時間ずれます。

`config/app.php` の 72 行目あたりを探して、自分で書き換えてください。

```php
// 変更前
'timezone' => 'UTC',

// 変更後
'timezone' => 'Asia/Tokyo',
```

---

## 動作確認

`php artisan tinker` は、アプリの中身を 1 行ずつ試せる対話モードです。

```bash
php artisan tinker
```

`>` が出たら、次を 1 行ずつ入力してみましょう。

```php
config('app.timezone')
now()
exit
```

- `config('app.timezone')` が `"Asia/Tokyo"` を返す
- `now()` が **今の日本時間** を返す

この 2 つが確認できたら、この章はクリアです。

> `Asia/Tokyo` にならないときは `php artisan config:clear` を実行してからもう一度。

---

## 自己採点テストを触ってみる

```bash
php artisan test
```

真っ赤なエラーがずらっと出ますが、**それが正常です。**
まだ Todo アプリを何も作っていないので、当然すべて失敗します。

これから章を進めるたびに、緑の ✓ が増えていきます。
最後にこれが全部緑になることが、この課題のゴールです。

---

## この章のチェックリスト

- [ ] `composer install` が成功した
- [ ] `.env` があり、`APP_KEY` が入っている
- [ ] `php artisan migrate` が成功した
- [ ] ブラウザに Laravel の初期画面が出た
- [ ] `config/app.php` のタイムゾーンを `Asia/Tokyo` にした

つまずいたら [困ったときは](troubleshooting.md) を見てください。

➡ 次は [第 1 章 ルーティングとビュー](01-routing-view.md)
