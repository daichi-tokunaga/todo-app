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

## 進め方

1. まず [docs/00-setup.md](docs/00-setup.md) で環境を動かす
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
| [11](docs/11-advanced.md) | 発展課題 | 自力で機能追加 |

補助資料

- [チートシート](docs/cheatsheet.md) … よく使う artisan コマンドと Blade 構文
- [困ったときは](docs/troubleshooting.md) … エラーメッセージ別の対処法
- [演習の解答例](docs/99-solutions.md) … 最後の手段

---

## 自己採点テスト

このリポジトリには、章ごとの合格判定テストが入っています。

```bash
php artisan test
```

- **最初はほとんど失敗します。それが正常です。**（まだ何も作っていないので）
- 章を進めるごとに緑（✓）が増えていきます
- 全 14 件が緑になったらゴールです

特定の章だけ確認したいとき：

```bash
php artisan test --filter=ch04
```

> テストは MySQL ではなくメモリ上の SQLite で動くので、開発中のデータは消えません。安心して何度でも実行してください。

---

## この教材の前提

- PHP 8.0.2 以上（XAMPP / MAMP に入っている PHP で OK）
- Composer
- MySQL（XAMPP / MAMP の MySQL）
- **Node.js は使いません。** CSS は `public/css/app.css` を最初から用意してあります

参考記事：[Laravelで簡単なTodoアプリを作ってみる（B-Risk）](https://b-risk.jp/blog/2022/08/laravel/)
