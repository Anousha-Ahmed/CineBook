<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\SeatHold;
use App\Models\Showtime;
use App\Services\PricingService;
use App\Services\SeatHoldService;
use App\Services\SettingsService;
use Exception;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function showSeats(Showtime $showtime, SeatHoldService $holds)
    {
        $showtime->load('movie.genres', 'screen');

        $seats = Seat::where('screen_id', $showtime->screen_id)
            ->orderBy('row')
            ->orderBy('number')
            ->get();

        $myHolds = auth()->check()
            ? SeatHold::where('user_id', auth()->id())
                ->where('showtime_id', $showtime->id)
                ->where('expires_at', '>', now())
                ->get()
            : collect();

        $soldSeats = $holds->soldSeatIds($showtime->id);
        $otherHolds = array_values(array_diff($holds->heldSeatIds($showtime->id), $myHolds->pluck('seat_id')->all()));

        $selectedSeats = $myHolds->pluck('seat_id')->map(fn ($id) => (int) $id)->all();
        $holdExpiresAt = $myHolds->min('expires_at');

        $maxSeats = (int) SettingsService::get('max_seats', 6);
        $salesClosed = ! $showtime->isOnSale();

        return view('booking.seats', compact(
            'showtime',
            'seats',
            'soldSeats',
            'otherHolds',
            'selectedSeats',
            'holdExpiresAt',
            'maxSeats',
            'salesClosed',
        ));
    }

    public function holdSeats(Request $request, Showtime $showtime, SeatHoldService $holds)
    {
        $data = $request->validate([
            'seat_ids' => ['required', 'array', 'min:1'],
            'seat_ids.*' => ['integer', 'exists:seats,id'],
        ]);

        try {
            $holds->holdSeats(auth()->id(), $showtime->id, $data['seat_ids']);

            return redirect()->route('checkout', ['showtime' => $showtime->id])
                ->with('success', 'Seats reserved. Complete payment within the hold window.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function release(Request $request, Showtime $showtime, SeatHoldService $holds)
    {
        $holds->releaseForUser(auth()->id(), $showtime->id);

        return redirect()->route('booking.seats', $showtime)
            ->with('status', 'Your seat hold has been released.');
    }
}
