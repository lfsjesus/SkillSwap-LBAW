<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Notification;

class NotificationController extends Controller
{

    public function markAsRead(Request $request)
    {
        $notification_id = $request->input('notification_id');

        if (isset($notification_id)) {
            try {
                DB::beginTransaction();


                $notification = Notification::find($notification_id);
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
    }
}