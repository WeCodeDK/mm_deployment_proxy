<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MatterMostProxyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string',
            'server' => 'required|array',
            'server.id' => 'required|integer',
            'server.name' => 'required|string',
            'site' => 'required|array',
            'site.id' => 'required|integer',
            'site.name' => 'required|string',
            'commit_hash' => 'required|string',
            'commit_url' => 'required|string',
            'commit_author' => 'required|string',
            'commit_message' => 'required|string',
        ];
    }
}
