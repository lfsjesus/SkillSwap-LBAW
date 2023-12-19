<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function markAsRead(Request $request) {
        $request->validate([
            'notification_id' => 'required|integer'
        ]);

        $notification_id = $request->input('notification_id');
        $notification = Notification::find($notification_id);

        if ($notification == null) {
            return json_encode([
                'success' => false,
                'id' => $notification_id,
                'error' => 'Notification not found'
            ]);
        }

        if ($notification->viewed) {
            return json_encode([
                'success' => false,
                'id' => $notification_id,
                'error' => 'Notification already viewed'
            ]);
        }

        $this->authorize('markAsRead', $notification);

        try {
            DB::beginTransaction();
            
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
        $this->authorize('markAllAsRead', Notification::class);
        
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