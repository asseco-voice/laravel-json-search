<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Http\Requests;

class PaginatedSearchRequest extends SearchRequest
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
        return array_merge(parent::rules(), [
            'page'  => 'nullable|integer|required_with:limit',
            'limit' => 'integer|nullable|required_with:page',
        ]);
    }
}
