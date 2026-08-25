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
