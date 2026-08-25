<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ハンズオンの自己採点用テスト。
 *
 *   php artisan test
 *
 * で実行します。章を進めるごとに緑（通過）になるテストが増えていきます。
 * 最後まで終われば全部緑になります。
 *
 * ※ テストはメモリ上の SQLite で動くので、MySQL の todo_app のデータは消えません。
 */
class TodoAppTest extends TestCase
{
    use RefreshDatabase;

    /** 第2章：tasks テーブルに必要なカラムがある */
    public function test_ch02_tasksテーブルが作られている()
    {
        $task = Task::create(['name' => 'テスト', 'status' => false]);

        $this->assertDatabaseHas('tasks', ['name' => 'テスト', 'status' => false]);
        $this->assertNotNull($task->created_at, 'created_at が保存されていません（timestamps() を書きましたか？）');
    }

    /** 第4章：一覧に未完了タスクが表示される */
    public function test_ch04_一覧に未完了タスクが表示される()
    {
        Task::create(['name' => '牛乳を買う', 'status' => false]);

        $this->get('/tasks')
            ->assertStatus(200)
            ->assertSee('牛乳を買う');
    }

    /** 第5章：タスクを追加できる */
    public function test_ch05_タスクを追加できる()
    {
        $this->post('/tasks', ['name' => '洗濯物をたたむ'])
            ->assertRedirect('/tasks');

        $this->assertDatabaseHas('tasks', ['name' => '洗濯物をたたむ', 'status' => false]);
    }

    /** 第6章：空文字はバリデーションで弾かれる */
    public function test_ch06_空のタスク名は登録できない()
    {
        $this->post('/tasks', ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertSame(0, Task::count(), '不正な入力なのに登録されています');
    }

    /** 第6章：101文字以上はバリデーションで弾かれる */
    public function test_ch06_101文字以上のタスク名は登録できない()
    {
        $this->post('/tasks', ['name' => str_repeat('あ', 101)])
            ->assertSessionHasErrors('name');

        $this->assertSame(0, Task::count(), '不正な入力なのに登録されています');
    }

    /** 第7章：完了にできる */
    public function test_ch07_タスクを完了にできる()
    {
        $task = Task::create(['name' => 'ゴミを出す', 'status' => false]);

        $this->patch("/tasks/{$task->id}/complete")
            ->assertRedirect('/tasks');

        $this->assertTrue($task->fresh()->status, 'status が true になっていません');
    }

    /** 第7章：完了済みタスクは未完了リストに混ざらない */
    public function test_ch07_完了済みタスクは未完了一覧に出ない()
    {
        $undone = Task::create(['name' => 'まだのタスク', 'status' => false]);
        $done   = Task::create(['name' => 'かたづけ済み', 'status' => true]);

        $html = $this->get('/tasks')->getContent();

        $this->assertStringContainsString(
            "/tasks/{$undone->id}/complete",
            $html,
            '未完了タスクの行に「完了」ボタンがありません'
        );
        $this->assertStringNotContainsString(
            "/tasks/{$done->id}/complete",
            $html,
            '完了済みタスクが未完了一覧に混ざっています（where で絞り込みましたか？）'
        );
    }

    /** 第8章：編集フォームに現在の値が入っている */
    public function test_ch08_編集画面に現在のタスク名が表示される()
    {
        $task = Task::create(['name' => '編集前の名前', 'status' => false]);

        $this->get("/tasks/{$task->id}/edit")
            ->assertStatus(200)
            ->assertSee('編集前の名前');
    }

    /** 第8章：編集内容が保存される */
    public function test_ch08_タスク名を編集できる()
    {
        $task = Task::create(['name' => '編集前の名前', 'status' => false]);

        $this->put("/tasks/{$task->id}", ['name' => '編集後の名前'])
            ->assertRedirect('/tasks');

        $this->assertSame('編集後の名前', $task->fresh()->name);
    }

    /** 第8章：編集時もバリデーションが効く */
    public function test_ch08_編集でも空文字は弾かれる()
    {
        $task = Task::create(['name' => '編集前の名前', 'status' => false]);

        $this->put("/tasks/{$task->id}", ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertSame('編集前の名前', $task->fresh()->name);
    }

    /** 第9章：削除できる */
    public function test_ch09_タスクを削除できる()
    {
        $task = Task::create(['name' => '消えるタスク', 'status' => false]);

        $this->delete("/tasks/{$task->id}")
            ->assertRedirect('/tasks');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** 第10章：処理後にお知らせ（フラッシュメッセージ）が出る */
    public function test_ch10_追加後にお知らせが表示される()
    {
        $this->post('/tasks', ['name' => 'お知らせの確認'])
            ->assertSessionHas('message');
    }

    /** 第10章：存在しない ID を触ると 404 になる */
    public function test_ch10_存在しないタスクは404になる()
    {
        $this->get('/tasks/99999/edit')->assertStatus(404);
    }
}
