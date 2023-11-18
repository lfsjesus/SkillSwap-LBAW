<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\File;

class FileController extends Controller
{

    public static function uploadFiles(Request $request, $post_id = null, $comment_id = null)
    {
        $files = array();
        try {
            DB::beginTransaction();
            $files = $request->file('files');
            if (isset($files)) {
                foreach ($files as $file) {
                    $fileModel = new File();
                                        
                    $fileModel->title = time() . '_' . $file->getClientOriginalName();
        
                    $fileModel->post_id = $post_id;
                    $fileModel->comment_id = $comment_id;
                    $fileModel->file_path = '';
                    $fileModel->date = now(); // Use the now() function to get the current date and time
        
                    $fileModel->save();

                    $file->storeAs('public/files', $fileModel->title);
                    $fileModel->file_path = 'storage/uploads/' . $fileModel->title;
                    $fileModel->save();

                }
    
                DB::commit();
    
                return back()->with('success', 'File(s) have been uploaded.');
            }
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Error in uploading file(s).');
        }

    }
    
}