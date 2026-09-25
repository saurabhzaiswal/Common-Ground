<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ListingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'category' => ['sometimes', 'string', 'max:100'],
            'city' => ['sometimes', 'string', 'max:100'],
            'country' => ['sometimes', 'string', 'max:100'],
            'state' => ['sometimes', 'string', 'max:100'],
            'q' => ['sometimes', 'string', 'max:120'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $listings = Listing::query()
            ->with(['category', 'subcategory', 'user'])
            ->when(isset($filters['category']), function (Builder $query) use ($filters): void {
                $category = $filters['category'];

                $query->whereHas('category', function (Builder $categoryQuery) use ($category): void {
                    $categoryQuery->where('slug', $category);

                    if (ctype_digit($category)) {
                        $categoryQuery->orWhere('id', (int) $category);
                    }
                });
            })
            ->when(isset($filters['city']), fn (Builder $query) => $query->whereRaw('LOWER(city) = ?', [Str::lower($filters['city'])]))
            ->when(isset($filters['country']), fn (Builder $query) => $query->whereRaw('LOWER(country) = ?', [Str::lower($filters['country'])]))
            ->when(isset($filters['state']), fn (Builder $query) => $query->whereRaw('LOWER(state) = ?', [Str::lower($filters['state'])]))
            ->when(isset($filters['q']), function (Builder $query) use ($filters): void {
                $search = '%'.addcslashes($filters['q'], '%_\\').'%';

                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery->where('title', 'like', $search)
                        ->orWhere('detail', 'like', $search);
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return ListingResource::collection($listings);
    }

    public function mylistings(Request $request): AnonymousResourceCollection
    {
        $listings = $request->user()
            ->listings()
            ->with(['category', 'subcategory', 'user'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(12);

        return ListingResource::collection($listings);
    }

    public function store(StoreListingRequest $request, ActivityLogger $activityLogger): JsonResponse
    {
        Gate::authorize('create', Listing::class);

        $attributes = $request->safe()->except(['images', 'existing_images', 'replace_images']);
        $imagePaths = $this->storeImages($request);

        try {
            $listing = $request->user()->listings()->create([...$attributes, 'images' => $imagePaths]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePaths);

            throw $exception;
        }

        $activityLogger->record($request->user(), 'listing.created', 'listing', $listing->id, $listing->title);

        return ListingResource::make($listing->load(['category', 'subcategory', 'user']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Listing $listing): ListingResource
    {
        return ListingResource::make($listing->load(['category', 'subcategory', 'user']));
    }

    public function update(
        UpdateListingRequest $request,
        Listing $listing,
        ActivityLogger $activityLogger,
    ): ListingResource {
        Gate::authorize('update', $listing);

        $validated = $request->validated();
        $attributes = $request->safe()->except(['images', 'existing_images', 'replace_images']);
        $currentImagePaths = $listing->images ?? [];
        $retainedImagePaths = $request->boolean('replace_images')
            ? array_values(array_intersect($currentImagePaths, $validated['existing_images'] ?? []))
            : $currentImagePaths;
        $newImagePaths = $this->storeImages($request);
        $removedImagePaths = array_values(array_diff($currentImagePaths, $retainedImagePaths));

        $listing->fill([...$attributes, 'images' => [...$retainedImagePaths, ...$newImagePaths]]);
        $changedFields = array_keys($listing->getDirty());

        try {
            $listing->save();
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newImagePaths);

            throw $exception;
        }

        Storage::disk('public')->delete($removedImagePaths);

        if ($changedFields !== []) {
            $activityLogger->record(
                $request->user(),
                'listing.updated',
                'listing',
                $listing->id,
                $listing->title,
                ['changed_fields' => $changedFields],
            );
        }

        return ListingResource::make($listing->load(['category', 'subcategory', 'user']));
    }

    public function destroy(Listing $listing, Request $request, ActivityLogger $activityLogger): JsonResponse
    {
        Gate::authorize('delete', $listing);
        $activityLogger->record($request->user(), 'listing.deleted', 'listing', $listing->id, $listing->title);

        $imagePaths = $listing->images ?? [];
        $listing->delete();
        Storage::disk('public')->delete($imagePaths);

        return response()->json([
            'message' => 'Listing deleted successfully.',
        ]);
    }

    /** @return array<int, string> */
    private function storeImages(StoreListingRequest $request): array
    {
        $imagePaths = [];

        try {
            foreach ($request->file('images', []) as $image) {
                if (! $image instanceof UploadedFile) {
                    continue;
                }

                $path = $image->storePublicly('listings', 'public');

                if (! is_string($path)) {
                    throw new RuntimeException('The listing image could not be stored.');
                }

                $imagePaths[] = $path;
            }
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePaths);

            throw $exception;
        }

        return $imagePaths;
    }
}
