<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * create() / update() でまとめて代入してよいカラム
     */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * カラムの型変換（0/1 を true/false として扱う）
     */
    protected $casts = [
        'status' => 'boolean',
    ];
}
