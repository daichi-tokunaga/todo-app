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
