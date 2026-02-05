<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('move', $this->route('card'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_column_id' => ['required', 'exists:columns,id'],
            'target_position' => ['required', 'integer', 'min:0'],
        ];
    }
}
