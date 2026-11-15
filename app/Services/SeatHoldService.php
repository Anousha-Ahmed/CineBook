<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\SeatHold;
use App\Models\Showtime;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SeatHoldService
{
    public function soldSeatIds(int $showtimeId): array
    {
        return DB::table('booking_seat')
            ->join('bookings', 'bookings.id', '=', 'booking_seat.booking_id')
            ->where('bookings.showtime_id', $showtimeId)
            ->whereIn('bookings.status', ['confirmed', 'pending_payment'])
            ->pluck('booking_seat.seat_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function heldSeatIds(int $showtimeId): array
    {
        return SeatHold::query()
            ->where('showtime_id', $showtimeId)
            ->where('expires_at', '>', now())
            ->pluck('seat_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function unavailableSeatIds(int $showtimeId): array
    {
        return array_values(array_unique(array_merge(
            $this->heldSeatIds($showtimeId),
            $this->soldSeatIds($showtimeId),
        )));
    }

    /**
     * Hold all requested seats or none at all.
     *
     * @param  array<int, int|string>  $seatIds
     *
     * @throws Exception with a customer-safe message
     */
    public function holdSeats(int $userId, int $showtimeId, array $seatIds): Carbon
    {
        $maxSeats = (int) SettingsService::get('max_seats', 6);
        $seatIds = array_values(array_unique(array_map('intval', $seatIds)));

        if (count($seatIds) === 0) {
            throw new Exception('Please select at least one seat.');
        }

        if (count($seatIds) > $maxSeats) {
            throw new Exception("You can reserve a maximum of {$maxSeats} seats per booking.");
        }

        try {
            return DB::transaction(function () use ($userId, $showtimeId, $seatIds) {
                $showtime = Showtime::with('movie')->findOrFail($showtimeId);

                if (! $showtime->isOnSale()) {
                    throw new Exception('Sales for this showtime are closed.');
                }

                // Expired holds never block a seat, even if the scheduler has not run yet.
                SeatHold::where('showtime_id', $showtimeId)
                    ->where('expires_at', '<=', now())
                    ->delete();

                $seats = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

                if ($seats->count() !== count($seatIds) || $seats->where('screen_id', $showtime->screen_id)->count() !== count($seatIds)) {
                    throw new Exception('Some of the selected seats are not valid for this screen.');
                }

                if ($seats->where('is_disabled', true)->isNotEmpty()) {
                    throw new Exception('One of the selected seats is not bookable.');
                }

                $unavailable = array_values(array_intersect($seatIds, $this->unavailableSeatIds($showtimeId)));

                if ($unavailable !== []) {
                    $labels = $seats->whereIn('id', $unavailable)->map(fn ($s) => $s->label)->implode(', ');
                    throw new Exception("Seat {$labels} is no longer available. Please choose another seat.");
                }

                // Replacing a previous selection? Release this user's old holds for the same show.
                SeatHold::where('user_id', $userId)
                    ->where('showtime_id', $showtimeId)
                    ->delete();

                $expiresAt = now()->addMinutes((int) SettingsService::get('hold_minutes', 10));

                foreach ($seats as $seat) {
                    SeatHold::create([
                        'user_id' => $userId,
                        'showtime_id' => $showtimeId,
                        'seat_id' => $seat->id,
                        'expires_at' => $expiresAt,
                    ]);
                }

                return $expiresAt;
            });
        } catch (QueryException $e) {
            // Unique constraint (showtime_id, seat_id) means another request won the race.
            if (str_contains(strtolower($e->getMessage()), 'unique')) {
                throw new Exception('Someone just took one of those seats. Please pick again.');
            }

            throw $e;
        }
    }

    public function releaseForUser(int $userId, ?int $showtimeId = null): void
    {
        SeatHold::where('user_id', $userId)
            ->when($showtimeId, fn ($q) => $q->where('showtime_id', $showtimeId))
            ->delete();
    }
}
