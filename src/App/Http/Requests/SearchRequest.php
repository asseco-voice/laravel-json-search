<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
        return [
            'search'       => 'array',
            'returns'      => 'string_or_array|nullable',
            'order_by'     => 'array|nullable',
            'group_by'     => 'string_or_array|nullable',
            'relations'    => 'string_or_array|nullable',
            'limit'        => 'integer|nullable|required_with:offset',
            'offset'       => 'integer|nullable',
            'count'        => 'boolean|nullable',
            'soft_deleted' => 'boolean|nullable',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->addExtension('string_or_array', function ($attribute, $value, $parameters, $validator) {
            return is_string($value) || is_array($value);
        });

        $validator->addReplacer('string_or_array', function ($message, $attribute, $rule, $parameters, $validator) {
            return __('The :attribute must be a string or an array.', compact('attribute'));
        });
    }
}
