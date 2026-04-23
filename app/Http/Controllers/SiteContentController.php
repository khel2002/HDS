<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteContentController extends Controller
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Load all content rows for one section, keyed by `key`.
     */
    private function section(string $section): array
    {
        return DB::table('site_content')
            ->where('section', $section)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key')
            ->toArray();
    }

    // ── Main page ──────────────────────────────────────────────────────────────

    public function index()
    {
        $data = [
            'hero'             => $this->section('hero'),
            'amenities_header' => $this->section('amenities_header'),
            'rooms_header'     => $this->section('rooms_header'),
            'why_us'           => $this->section('why_us'),
            'contact'          => $this->section('contact'),
            'footer'           => $this->section('footer'),
            'amenities'        => DB::table('landing_amenities')->orderBy('sort_order')->get(),
            'why_items'        => DB::table('landing_why_items')->orderBy('sort_order')->get(),
        ];

        return view('superadmin.site-content.index', $data);
    }

    // ── Generic section update ─────────────────────────────────────────────────

    /**
     * POST /super-admin/site-content/{section}
     * Updates all key→value pairs for a named section.
     */
    public function updateSection(Request $request, string $section)
    {
        $allowed = ['hero', 'amenities_header', 'rooms_header', 'why_us', 'contact', 'footer'];

        if (! in_array($section, $allowed)) {
            return back()->with('error', 'Invalid section.');
        }

        $fields = $request->except(['_token', '_method']);

        foreach ($fields as $key => $value) {
            DB::table('site_content')
                ->where('section', $section)
                ->where('key', $key)
                ->update([
                    'value'      => $value,
                    'updated_at' => now(),
                ]);
        }

        return back()->with('success', 'Section updated successfully.');
    }

    // ── Amenity cards ──────────────────────────────────────────────────────────

    public function storeAmenity(Request $request)
    {
        $request->validate([
            'icon'        => 'required|string|max:100',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $max = DB::table('landing_amenities')->max('sort_order') ?? 0;

        DB::table('landing_amenities')->insert([
            'icon'        => $request->icon,
            'title'       => $request->title,
            'description' => $request->description,
            'sort_order'  => $max + 1,
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Amenity added.');
    }

    public function updateAmenity(Request $request, int $id)
    {
        $request->validate([
            'icon'        => 'required|string|max:100',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);

        DB::table('landing_amenities')->where('id', $id)->update([
            'icon'        => $request->icon,
            'title'       => $request->title,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Amenity updated.');
    }

    public function destroyAmenity(int $id)
    {
        DB::table('landing_amenities')->where('id', $id)->delete();

        return back()->with('success', 'Amenity deleted.');
    }

    public function reorderAmenities(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        foreach ($request->order as $position => $id) {
            DB::table('landing_amenities')
                ->where('id', $id)
                ->update(['sort_order' => $position + 1, 'updated_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    // ── Why Choose Us items ────────────────────────────────────────────────────

    public function storeWhyItem(Request $request)
    {
        $request->validate(['text' => 'required|string|max:255']);

        $max = DB::table('landing_why_items')->max('sort_order') ?? 0;

        DB::table('landing_why_items')->insert([
            'text'       => $request->text,
            'sort_order' => $max + 1,
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Item added.');
    }

    public function updateWhyItem(Request $request, int $id)
    {
        $request->validate([
            'text'      => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        DB::table('landing_why_items')->where('id', $id)->update([
            'text'       => $request->text,
            'is_active'  => $request->boolean('is_active', true),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Item updated.');
    }

    public function destroyWhyItem(int $id)
    {
        DB::table('landing_why_items')->where('id', $id)->delete();

        return back()->with('success', 'Item deleted.');
    }
}   