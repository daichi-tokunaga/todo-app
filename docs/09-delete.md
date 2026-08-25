# 第 9 章　タスクを削除する（Delete）

**目安：25 分**

## この章のゴール

「削除」ボタンを押すと確認ダイアログが出て、OK するとタスクが DB から消える。

## この章で学ぶこと

`@method('DELETE')` / `$task->delete()` / 確認ダイアログ

---

## Step 9-1　ルートを増やす

```php
Route::resource('tasks', TaskController::class)
    ->only(['index', 'store', 'edit', 'update', 'destroy']);
```

これで `Route::resource` から使う 5 本が揃いました。

---

## Step 9-2　`destroy()` を実装する

`TaskController` の `destroy()` を書き換えます。

```php
    /**
     * DELETE /tasks/{id}  削除
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()->route('tasks.index');
    }
```

---

## Step 9-3　削除ボタンを置く

`index.blade.php` の `<td class="actions">` の中、編集リンクの **下** に追加します。

```blade
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="post"
                              onsubmit="return confirm('本当に削除しますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">削除</button>
                        </form>
```

`<td class="actions">` の全体はこうなります。

```blade
                    <td class="actions">
                        <form action="{{ route('tasks.complete', $task->id) }}" method="post">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-success btn-sm">完了</button>
                        </form>

                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-accent btn-sm">編集</a>

                        <form action="{{ route('tasks.destroy', $task->id) }}" method="post"
                              onsubmit="return confirm('本当に削除しますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">削除</button>
                        </form>
                    </td>
```

### `onsubmit="return confirm(...)"`

削除は取り消せない操作なので、押し間違い防止の確認を入れます。

- `confirm('...')` … ブラウザ標準の確認ダイアログ。OK なら `true`、キャンセルなら `false`
- `onsubmit` が `false` を返すと、**送信そのものがキャンセルされる**

これは Laravel の機能ではなく素の JavaScript です。

> **注意**：これは「押し間違い防止」であって、セキュリティ対策ではありません。
> 開発者ツールを使えば簡単に回避できます。
> 本当に消えては困るデータには「論理削除（ソフトデリート）」を使います
> → [第 12 章](12-advanced.md) の発展課題で扱います。

---

## 動作確認

1. 「削除」ボタンを押す → 「本当に削除しますか？」が出る
2. **キャンセル** → 何も起きない
3. **OK** → 一覧から消える。phpMyAdmin でも行が消えている

```bash
php artisan test --filter=ch09
```

```
✓ ch09 タスクを削除できる
```

ここまでで **CRUD（Create / Read / Update / Delete）が全部揃いました**🎉

---

## 演習

**演習 9-A**
確認ダイアログの文言を「『(タスク名)』を削除します。よろしいですか？」に変えてください。
ヒント：Blade の `{{ $task->name }}` を JavaScript の文字列の中に埋め込みます。
（タスク名に `'` が含まれると壊れます。それをどう防ぐかも考えてみてください）

**演習 9-B**
`onsubmit` の部分を丸ごと消して削除してみてください。
確認なしで消えることを体感したら戻してください。

**演習 9-C（発展）**
`app/Models/Task.php` に次のメソッドを追加すると、Tinker から呼べます。

```php
public static function deleteCompleted()
{
    return static::where('status', true)->delete();
}
```

Tinker で `Task::deleteCompleted()` を実行し、完了済みタスクがまとめて消えることを確認してください。
余裕があれば、これを呼ぶ「完了済みを全部削除」ボタンを一覧画面に付けてみましょう。

---

## つまずいたら

| 症状 | 原因 |
| --- | --- |
| `The POST method is not supported for this route.` | `@method('DELETE')` を書き忘れている |
| `Route [tasks.destroy] not defined.` | `->only([...])` に `'destroy'` を追加したか確認 |
| ダイアログが出ない | `onsubmit` は `<button>` ではなく `<form>` に書く |
| ボタンが縦に並んでしまう | `<td>` に `class="actions"` が付いているか確認 |

➡ 次は [第 10 章 仕上げ](10-finishing.md)
