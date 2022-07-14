<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchFavoriteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $ownerId = $this->owner_id;

        return [
            'owner_id'    => 'nullable|string',
            'model'       => 'required|string',
            'name'        => [
                'required',
                'string',
                Rule::unique('search_favorites')->where(function ($query) use ($ownerId) {
                    return $query->where('owner_id', $ownerId);
                }),
            ],
            'description' => 'string',
            'search'      => 'required|array',
        ];
    }
}
