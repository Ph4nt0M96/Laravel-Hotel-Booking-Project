<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|in:Mr.,Mrs.',
            'first_name' => 'required|string|max:30',
            'last_name' => 'required|string|max:30', 
            'email' => ['required', 'string', 'lowercase', 'email', 'max:30', Rule::unique(User::class)->ignore($this->user()->id)],
            'gender' => 'required|string|in:Male,Female,Other', 
            'date_of_birth' => 'required|date|before:today',
            'phone_number' => 'required|string|min:8|max:15', 
            'nrc_no' => 'required|string|min:15|max:20', 
        ];
        Log::info('Validation Passed:', $request->all());
    }
}
