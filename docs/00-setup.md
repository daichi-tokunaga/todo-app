# 第 0 章　環境構築と起動確認

**目安：30 分**

> **2 回目以降の人へ**
> 手順だけ知りたい場合は [README のセットアップ手順](../README.md#セットアップ手順クローンから起動まで) に
> コピペ用のコマンドがまとまっています。この章は「その手順が何をしているのか」の解説です。
>
> **この教室の PC はシャットダウンすると中身が消えます。**
> 授業のたびにこの手順を最初からやり直すことになるので、
> 何をやっているのかを 1 回目にちゃんと理解しておくと、2 回目以降が速くなります。

## この章のゴール

- ブラウザに Laravel の初期画面が表示される
- `php artisan migrate` が成功する（＝ MySQL につながっている）

## この章で学ぶこと

`composer install` / `.env` / アプリケーションキー / `php artisan serve` / `php artisan migrate`

---

## Step 0-1　必要なソフトを確認する

PowerShell（スタートメニューで「PowerShell」と検索）を開き、次を 1 行ずつ実行してください。

```powershell
php -v
composer -V
git --version
```

3 つともバージョンが表示されれば OK です。
`php -v` が `8.0.2` 未満、または「そんなコマンドはない」と言われた場合は講師に声をかけてください。

> **`php -v` が動かないとき**
> 環境変数 PATH に `C:\xampp\php` が入っていません。
> XAMPP Control Panel の右にある **Shell** ボタンから開いた黒い画面なら、そのまま `php` が使えます。

---

## Step 0-2　リポジトリを取得する

配布された URL を使ってクローンします。

```powershell
cd $HOME\Documents
git clone <配布されたリポジトリのURL> todo-app
cd todo-app
```

> **2 回目以降で、前回の続きからやる人**
> 配布リポジトリではなく、**前回 push した自分のリポジトリ** をクローンしてください。
> USB にコピーして帰った人は、そのフォルダを Documents に戻せば OK です。

**以降のコマンドは、すべてこの `todo-app` フォルダの中で実行します。**

---

## Step 0-3　ライブラリをインストールする

Laravel 本体は `vendor/` フォルダに入りますが、これは Git に含まれていません。
次のコマンドでダウンロードします（3〜5 分かかります）。

```powershell
composer install
```

`Generating optimized autoload files` と出て、`vendor` フォルダができれば成功です。

---

## Step 0-4　`.env` ファイルを作る

`.env` はデータベースの接続先など「環境ごとに違う設定」を書くファイルです。
**セキュリティ上の理由から Git には含まれていません**ので、自分で作ります。

```powershell
Copy-Item .env.example .env
```

続いて、このアプリ専用の暗号化キーを生成します。

```powershell
php artisan key:generate
```

`.env` を開いて、`APP_KEY=base64:...` という長い文字列が入っていれば成功です。

---

## Step 0-5　MySQL を起動してデータベースを作る

XAMPP Control Panel を開いて、**MySQL の行の Start** を押します。
「Running」と緑色になれば起動しています。

※ Apache は使いません（Laravel には開発用サーバーが付いているため）。

次に、データベースを 1 つ作ります。方法はどちらでも構いません。

**方法 A：phpMyAdmin（GUI）**

1. XAMPP Control Panel の MySQL 行にある **Admin** を押す
2. 左上の「新規作成」をクリック
3. データベース名：`todo_app`
4. 照合順序：`utf8mb4_unicode_ci`
5. 「作成」を押す

**方法 B：コマンド**

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS todo_app DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

> **この PC はシャットダウンすると中身が消えます。**
> つまり、**このデータベースも毎回作り直しになります。**
> 「昨日作ったのに `Unknown database 'todo_app'` と言われる」ときは、
> この Step をやり忘れていないか確認してください。

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

XAMPP の初期設定では、ユーザーが `root`、パスワードなしです。
**上の状態のまま、書き換える必要はありません。**

> `.env` を書き換えたのに反映されないときは `php artisan config:clear` を実行してください。

---

## Step 0-7　接続できているか確かめる

```powershell
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

```powershell
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

```powershell
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

```powershell
php artisan test
```

真っ赤なエラーがずらっと出ますが、**それが正常です。**
まだ Todo アプリを何も作っていないので、当然すべて失敗します。

これから章を進めるたびに、緑の ✓ が増えていきます。
最後にこれが全部緑になることが、この課題のゴールです。

---

## ⚠️ 帰る前に必ずやること

**この PC は電源を切るとデータが消えます。書いたコードも全部消えます。**

授業の終わりに、必ずどちらかをやってください。

**方法 A：自分の GitHub リポジトリに push する（おすすめ）**

```powershell
git add .
git commit -m "第0章まで完了"
git remote add mine <自分のリポジトリのURL>
git push mine main
```

2 回目以降は `git push mine main` だけで済みます。
次回は **自分のリポジトリ** をクローンすれば続きから始められます。

**方法 B：フォルダごと USB メモリやクラウドにコピーする**

`todo-app` フォルダをまるごとコピーします。
`vendor` フォルダはとても大きいので、消してからコピーしても構いません
（次回 `composer install` すれば元に戻ります）。

---

## この章のチェックリスト

- [ ] XAMPP で MySQL が Running になっている
- [ ] `composer install` が成功した
- [ ] `.env` があり、`APP_KEY` が入っている
- [ ] `todo_app` データベースを作った
- [ ] `php artisan migrate` が成功した
- [ ] ブラウザに Laravel の初期画面が出た
- [ ] `config/app.php` のタイムゾーンを `Asia/Tokyo` にした
- [ ] 帰る前の保存方法（A か B）を決めた

つまずいたら [困ったときは](troubleshooting.md) を見てください。

➡ 次は [第 1 章 ルーティングとビュー](01-routing-view.md)
