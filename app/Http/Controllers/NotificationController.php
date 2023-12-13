<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function markAsRead(Request $request) {
        $notification_id = $request->input('notification_id');

        try {
            DB::beginTransaction();


            $notification = Notification::find($notification_id);

            if ($notification == null) {
                throw new \Exception('Notification not found');
            }
            
            $notification->viewed = true;

            $notification->save();


            DB::commit();

            $response = [
                'success' => true,
                'id' => $notification_id
            ];

            return json_encode($response);
        }
        catch (\Exception $e) {
            DB::rollback();
            $response = [
                'success' => false,
                'id' => $notification_id
            ];
            return json_encode($response);
        }      
    }

    public function markAllAsRead() {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            $notifications = $user->notifications->where('viewed', false);
            $ids = array();

            foreach ($notifications as $notification) {
                $notification->viewed = true;
                $notification->save();
                array_push($ids, $notification->id);
            }

            DB::commit();

            $response = [
                'success' => true,
                'ids' => $ids
            ];

            return json_encode($response);
        }
        catch (\Exception $e) {
            DB::rollback();
            $response = [
                'success' => false,
                'ids' => $ids
            ];
            return json_encode($response);
        }       
    }
}