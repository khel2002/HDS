<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\BreakfastMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BreakfastController extends Controller
{
    /**
     * Display a listing of breakfast menu items.
     */
    public function index()
    {
        $breakfastItems = BreakfastMenu::orderBy('meal_name')->get();
        $stats          = $this->buildStats($breakfastItems);

        return view('content.super-admin.breakfast.index', compact('breakfastItems', 'stats'));
    }

    /**
     * Store a newly created breakfast menu item (AJAX).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'meal_name'    => 'required|string|max:150|unique:breakfast_menu,meal_name',
            'description'  => 'nullable|string|max:500',
            'price'        => 'required|numeric|min:0',
            'is_available' => 'sometimes|boolean',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $validated['is_available'] = isset($validated['is_available'])
            ? (bool) $validated['is_available']
            : true;

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')
                ->store('img/breakfast', 'public');
        }

        unset($validated['image']);
        $item = BreakfastMenu::create($validated);

        return response()->json([
            'item'     => $this->itemPayload($item),
            'stats'    => $this->buildStats(BreakfastMenu::all()),
            'edit_url' => route('super_admin.breakfast.menu.update', $item->breakfast_id),
        ], 201);
    }

    /**
     * Update the specified breakfast menu item (AJAX).
     */
    public function update(Request $request, $id)
    {
        $item = BreakfastMenu::findOrFail($id);

        $validated = $request->validate([
            'meal_name'    => 'required|string|max:150|unique:breakfast_menu,meal_name,' . $id . ',breakfast_id',
            'description'  => 'nullable|string|max:500',
            'price'        => 'required|numeric|min:0',
            'is_available' => 'sometimes|boolean',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'remove_image' => 'sometimes|boolean',
        ]);

        $validated['is_available'] = isset($validated['is_available'])
            ? (bool) $validated['is_available']
            : false;

        // Remove image if requested
        if (!empty($validated['remove_image'])) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $validated['image_path'] = null;
        }

        // Replace image if a new one was uploaded
        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $validated['image_path'] = $request->file('image')
                ->store('img/breakfast', 'public');
        }

        unset($validated['image'], $validated['remove_image']);
        $item->update($validated);

        return response()->json([
            'item'  => $this->itemPayload($item),
            'stats' => $this->buildStats(BreakfastMenu::all()),
        ]);
    }

    /**
     * Toggle availability of a breakfast menu item (AJAX).
     */
    public function toggleAvailability($id)
    {
        $item = BreakfastMenu::findOrFail($id);
        $item->update(['is_available' => !$item->is_available]);

        return response()->json([
            'item'  => $this->itemPayload($item),
            'stats' => $this->buildStats(BreakfastMenu::all()),
        ]);
    }

    /**
     * Remove the specified breakfast menu item (AJAX).
     */
    public function destroy($id)
    {
        $item = BreakfastMenu::findOrFail($id);

        if ($item->serviceBreakfastOrders()->exists()) {
            return response()->json([
                'message' => "Cannot delete \"{$item->meal_name}\" because it has existing orders.",
            ], 422);
        }

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return response()->json([
            'stats' => $this->buildStats(BreakfastMenu::all()),
        ]);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function itemPayload(BreakfastMenu $item): array
    {
        return [
            'breakfast_id' => $item->breakfast_id,
            'meal_name'    => $item->meal_name,
            'description'  => $item->description,
            'price'        => $item->price,
            'is_available' => (bool) $item->is_available,
            'image_path'   => $item->image_path,
            'image_url'    => $item->image_path
                                ? asset('storage/' . $item->image_path)
                                : null,
            'toggle_url'   => route('super_admin.breakfast.menu.toggle',  $item->breakfast_id),
            'delete_url'   => route('super_admin.breakfast.menu.destroy', $item->breakfast_id),
        ];
    }

    private function buildStats($items): array
    {
        $collection = collect($items);

        return [
            'total_items'       => $collection->count(),
            'available_items'   => $collection->where('is_available', true)->count(),
            'unavailable_items' => $collection->where('is_available', false)->count(),
            'avg_price'         => round($collection->avg('price') ?? 0, 2),
        ];
    }
}