<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\SiteMedia;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteMediaController extends Controller
{
    public function __construct(
        private CloudinaryService $cloudinary
    ) {
        $this->middleware(['auth', 'super_admin']);
    }

    public function index(Request $request): View
    {
        $type = $request->input('type', SiteMedia::TYPE_SLIDER);
        if (! array_key_exists($type, SiteMedia::types())) {
            $type = SiteMedia::TYPE_SLIDER;
        }

        $items = SiteMedia::query()
            ->ofType($type)
            ->ordered()
            ->paginate(24)
            ->withQueryString();

        return view('erp.media.index', [
            'items' => $items,
            'type' => $type,
            'types' => SiteMedia::types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:gallery,slider',
            'image' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:8192',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'cta_label' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $uploaded = $this->cloudinary->uploadImage(
            $request->file('image'),
            $data['type']
        );

        $maxSort = (int) SiteMedia::query()->ofType($data['type'])->max('sort_order');

        SiteMedia::create([
            'type' => $data['type'],
            'url' => $uploaded['url'],
            'public_id' => $uploaded['public_id'],
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'cta_label' => $data['cta_label'] ?? null,
            'cta_url' => $data['cta_url'] ?? null,
            'sort_order' => $data['sort_order'] ?? ($maxSort + 1),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('erp.media.index', ['type' => $data['type']])
            ->with('success', 'Image uploaded to Cloudinary.');
    }

    public function update(Request $request, SiteMedia $medium): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'cta_label' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $medium->update([
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'cta_label' => $data['cta_label'] ?? null,
            'cta_url' => $data['cta_url'] ?? null,
            'sort_order' => $data['sort_order'] ?? $medium->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Media updated.');
    }

    public function destroy(SiteMedia $medium): RedirectResponse
    {
        $type = $medium->type;
        if ($medium->public_id) {
            try {
                $this->cloudinary->delete($medium->public_id);
            } catch (\Throwable $e) {
                // Still remove DB record if Cloudinary delete fails
            }
        }
        $medium->delete();

        return redirect()
            ->route('erp.media.index', ['type' => $type])
            ->with('success', 'Media deleted.');
    }
}
