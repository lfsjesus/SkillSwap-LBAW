<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\File;

class FileController extends Controller
{

    public static function uploadFiles(Request $request, $post_id = null, $comment_id = null)
    {
        
        $files = $request->file('files');
        
        if (!Auth::check()) {
            return back()->with('error', 'You must be logged in to upload files.');
        }

        if (isset($files)) {
            try {
                DB::beginTransaction();
            
                foreach ($files as $file) {
                    $fileModel = new File();
                                        
                    $fileModel->title = time() . '_' . $file->getClientOriginalName();
        
                    $fileModel->post_id = $post_id;
                    $fileModel->comment_id = $comment_id;
                    $fileModel->file_path = '';
                    $fileModel->date = now(); // Use the now() function to get the current date and time
                    
                    
                    $fileModel->save();
                    

                    $file->storeAs('public/uploads', $fileModel->title);
                    $fileModel->file_path = 'storage/uploads/' . $fileModel->title;
                    $fileModel->save();
                }
                
        
                DB::commit();

                //return back()->with('success', 'File(s) have been uploaded.');
            }
            catch (\Exception $e) {
                DB::rollback();
                //return back()->with('error', 'Error in uploading file(s).');
            }

        }
    }

    public static function deleteFilesFromStorage($files)
    {
        if (!Auth::check()) {
            return back()->with('error', 'You must be logged in to delete files.');
        }

        foreach ($files as $file) {
            if (file_exists($file->file_path)) {
                unlink($file->file_path);
            }
        }

    }
}