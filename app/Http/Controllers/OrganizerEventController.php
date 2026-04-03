<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OrganizerEventController extends Controller
{
    private function authorizeEvent(Event $event): void
    {
        $user = auth()->user();
        $owns = $event->user_id === $user->id
            || (is_null($event->user_id) && $event->submitter_email === $user->email);
        abort_unless($owns, 403);
    }

    public function show(Event $event)
    {
        $this->authorizeEvent($event);
        $event->load(['location', 'category']);

        return view('dashboard.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $this->authorizeEvent($event);

        $locations  = Location::orderBy('name')->pluck('name', 'id');
        $categories = Category::orderBy('name')->pluck('name', 'id');

        return view('dashboard.events.edit', compact('event', 'locations', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $isApproved = $event->status === 'approved';

        $rules = [
            'description' => 'required|string',
            'organizer'   => 'required|string|max:255',
            'website'     => 'nullable|url|max:255',
            'image'       => 'nullable|image|max:2048',
        ];

        if (! $isApproved) {
            $rules += [
                'title'       => 'required|string|max:255',
                'start_date'  => 'required|date_format:Y-m-d',
                'start_time'  => 'nullable|date_format:H:i',
                'end_date'    => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
                'end_time'    => 'nullable|date_format:H:i',
                'is_all_day'  => 'boolean',
                'location_id' => ['required', Rule::exists('locations', 'id')],
                'category_id' => ['nullable', Rule::exists('categories', 'id')],
            ];
        }

        $validated = $request->validate($rules);

        if (! $isApproved) {
            $isAllDay = $request->boolean('is_all_day');
            $startTime = (!$isAllDay && !empty($validated['start_time'])) ? $validated['start_time'] : '00:00';
            $validated['start_date'] = $validated['start_date'] . ' ' . $startTime . ':00';
            if (!empty($validated['end_date'])) {
                $endTime = (!$isAllDay && !empty($validated['end_time'])) ? $validated['end_time'] : '23:59';
                $validated['end_date'] = $validated['end_date'] . ' ' . $endTime . ':00';
            }
            $validated['is_all_day'] = $isAllDay;
            unset($validated['start_time'], $validated['end_time']);
        }

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        return redirect()->route('dashboard')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);

        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Event deleted successfully.');
    }
}
