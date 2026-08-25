<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * このリクエストを実行してよいか（今回は全員 OK）
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:100',
        ];
    }

    /**
     * エラーメッセージ
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'タスク名を入力してください。',
            'name.max'      => 'タスク名は100文字以内で入力してください。',
        ];
    }
}
