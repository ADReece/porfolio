<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\Filesystem;
use App\Models\Media;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        if($request->isMethod('GET')){
            return view('upload.upload');
        }

        if(!is_null($request->file('media'))){
            $s3 = \Storage::disk('s3');

            foreach($request->file('media') as $media){
                $imageFileName = time() . \Str::random(5) . '.' . $media->getClientOriginalExtension();
                $filePath = "members/".\Auth::user()->id."/media/".$imageFileName;
                try{
                    $s3->put($filePath, file_get_contents($media->getRealPath()));

                    $media_model = Media::create([
                        'user_id' => \Auth::user()->id,
                        'url' => $filePath,
                        'size' => $media->getSize()
                    ]);
                } catch(\Throwable $e) {
                    Log::alert($e->getMessage());
                }

            }
        }

    }

}
