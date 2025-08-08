<?php

namespace App\Http\Requests;

use App\Enums\Difficulty;
use App\Enums\MovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateExerciseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'slug' => ['required'],
            'primary_muscle_group' => ['required', 'exists:muscle_groups,slug'],
            'secondary_muscle_group' => ['nullable', 'exists:muscle_groups,slug', 'different:primary_muscle_group'],
            'movement_type' => ['required', Rule::enum(MovementType::class)],
            'difficulty' => ['required', Rule::enum(Difficulty::class)],
            'equipment' => ['nullable'],
        ];
    }
}
