<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Event;
use App\Models\Booking;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $report = DB::select("
            SELECT e.id, e.title, e.starts_at, e.capacity, 
                COUNT(b.id) AS booking,
                (e.capacity - COUNT(b.id)) AS remaining
            FROM events e
            LEFT JOIN bookings b 
                ON e.id = b.event_id 
                AND b.status = 'confirmed'
            WHERE e.organiser_id = ?
            GROUP BY e.id, e.title, e.starts_at, e.capacity
            ORDER BY e.starts_at DESC
        ", [$userId]);

        return view('dashboard.index', ['report' => $report, 'user' => $request->user()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
