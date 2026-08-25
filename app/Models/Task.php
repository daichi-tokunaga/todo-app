<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * tasks テーブルの 1 行を表すモデル。
 *
 * カラムは実行時に動的に生えるため、IDE と静的解析に型を教えるには
 * PHPDoc の @property を使う。
 *
 * @property int                             $id
 * @property string                          $name
 * @property bool                            $status
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 */
class Task extends Model
{
    use HasFactory;

    /**
     * create() / update() でまとめて代入してよいカラム
     *
     * ※ 親の Model が型なしで宣言しているため、`protected array $fillable`
     *    のように型を付けることはできない（PHP の制約）。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * カラムの型変換（0/1 を true/false として扱う）
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];
}
