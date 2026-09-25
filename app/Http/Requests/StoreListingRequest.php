<?php

namespace App\Http\Requests;

use App\Models\Listing;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreListingRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'detail' => ['required', 'string', 'min:10', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'subcategory_id' => [
                'nullable',
                'integer',
                Rule::exists('subcategories', 'id')->where('category_id', $this->input('category_id')),
            ],
            'country' => ['required', 'string', 'min:2', 'max:100'],
            'state' => ['required', 'string', 'min:2', 'max:100'],
            'city' => ['required', 'string', 'min:2', 'max:100'],
            'area' => ['nullable', 'string', 'min:2', 'max:150'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'images' => ['sometimes', 'array', 'max:3'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'existing_images' => ['sometimes', 'array', 'max:3'],
            'existing_images.*' => ['string', 'max:255'],
            'replace_images' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Validate the combined number of retained and newly uploaded images.
     *
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $listing = $this->route('listing');
            $currentImages = $listing instanceof Listing ? ($listing->images ?? []) : [];

            if ($listing instanceof Listing && $this->boolean('replace_images')) {
                $currentImages = array_values(array_intersect($currentImages, $this->input('existing_images', [])));
            }

            $newImages = $this->file('images', []);
            $newImages = is_array($newImages) ? $newImages : ($newImages ? [$newImages] : []);

            if (count($currentImages) + count($newImages) > 3) {
                $validator->errors()->add('images', 'A listing can have no more than 3 images.');
            }
        }];
    }
}
