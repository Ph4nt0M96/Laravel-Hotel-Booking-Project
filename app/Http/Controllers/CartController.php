<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\room;
use App\Models\room_type;
use App\Models\view;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $cart = session()->get('cart', []);
        $roomsToHold = Room::where('room_type_id', $request->room_type_id)
            ->where('view_id', $request->view_id)
            ->where('is_held', 0)
            ->limit($request->quantity)
            ->get();

        if ($roomsToHold->count() < $request->quantity) {
            return response()->json(['success' => false, 'message' => 'Not enough available rooms'], 400);
        }

        foreach ($roomsToHold as $room) {
            $room->is_held = 1;
            $room->save();
        }

        $roomTypeName = Room_Type::find($request->room_type_id)->room_type;
        $viewName = View::find($request->view_id)->view_name;

        $cart[] = [
            'room_type_id' => $request->room_type_id,
            'view_id' => $request->view_id,
            'room_type' => $roomTypeName,
            'view_name' => $viewName,
            'quantity' => $request->quantity,
            'room_ids' => $roomsToHold->pluck('room_id')->toArray(),
        ];

        session()->put('cart', $cart);

        $availableRooms = Room::where('view_id', $request->view_id)
            ->where('room_type_id', $request->room_type_id)
            ->where('is_held', 0)
            ->where('is_available', 0)
            ->count();

        $heldRooms = Room::where('view_id', $request->view_id)
            ->where('room_type_id', $request->room_type_id)
            ->where('is_held', 1)
            ->count();

        return response()->json([
            'success' => true,
            'availableRooms' => $availableRooms,
            'heldRooms' => $heldRooms,
            'cart' => $cart,
        ]);
    }


    public function showCart()
    {
        $cart = session()->get('cart', []);

        return response()->json([
            'cart' => $cart,
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$request->index])) {
            $removedItem = $cart[$request->index];

            if (!empty($removedItem['room_ids'])) {
                Room::whereIn('room_id', $removedItem['room_ids'])->update(['is_held' => 0]);
            }

            // Remove item from cart
            unset($cart[$request->index]);
            session()->put('cart', array_values($cart));

            $availableRooms = $this->calculateAvailableRooms($removedItem['view_id'], $removedItem['room_type_id']);

            $heldRooms = Room::where('view_id', $removedItem['view_id'])
                ->where('room_type_id', $removedItem['room_type_id'])
                ->where('is_held', 1)
                ->count();

            return response()->json([
                'success' => true,
                'availableRooms' => $availableRooms,
                'heldRooms' => $heldRooms,
                'cart' => $cart,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Item not found in cart'], 404);
    }



    public function clearCart()
    {
        try {
            $cart = session()->get('cart', []);

            foreach ($cart as $item) {
                Room::whereIn('room_id', $item['room_ids'])->update(['is_held' => 0]);
            }

            session()->forget('cart');

            $views = View::all()->mapWithKeys(function ($view) {
                $roomTypes = Room_Type::all();
                return $roomTypes->mapWithKeys(function ($roomType) use ($view) {
                    $availableRooms = Room::where('view_id', $view->view_id)
                        ->where('room_type_id', $roomType->room_type_id)
                        ->where('is_available', 0)
                        ->where('is_held', 0)
                        ->count();

                    $heldRooms = Room::where('view_id', $view->view_id)
                        ->where('room_type_id', $roomType->room_type_id)
                        ->where('is_held', 1)
                        ->count();

                    return [
                        "{$view->view_id}-{$roomType->room_type_id}" => [
                            'availableRooms' => $availableRooms,
                            'heldRooms' => $heldRooms,
                        ],
                    ];
                });
            });

            return response()->json([
                'success' => true,
                'views' => $views,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in clearCart: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to clear cart'], 500);
        }
    }


    private function calculateAvailableRooms($viewId, $roomTypeId)
    {
        return Room::where('view_id', $viewId)
            ->where('room_type_id', $roomTypeId)
            ->where('is_held', 0)
            ->where('is_available', 0)
            ->count();
    }
}
