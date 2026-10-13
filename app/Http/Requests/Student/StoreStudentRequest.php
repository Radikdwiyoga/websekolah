<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:30', Rule::unique('students', 'nis')],
            'nisn' => ['nullable', 'string', 'max:30', Rule::unique('students', 'nisn')],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string', 'max:1000'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama siswa',
            'nis' => 'NIS',
            'nisn' => 'NISN',
            'gender' => 'jenis kelamin',
            'birth_date' => 'tanggal lahir',
            'address' => 'alamat',
            'class_id' => 'kelas',
            'email' => 'email',
            'phone' => 'telepon',
            'password' => 'password',
        ];
    }
}
