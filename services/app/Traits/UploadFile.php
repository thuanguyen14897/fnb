<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Facades\Image as Img;
use Maestroerror\HeicToJpg;

trait UploadFile
{
    public function UploadFile($file = array(), $folder = null,$with = 150,$height = 150,$resize = true,$disk = 'public')
    {
        $arr = ['png','jpeg','jpg','gif','jfif','ai','AI','raw','RAW','EPS','eps','indd','INDD','PNG','JPEG','JPG','GIF','JFIF','webp','WEBP'];
        $arrFile = ['docx','DOCX','doc','DOC','Doc','XLSX','xlsx','XLS','xls','CSV','csv','PDF','pdf','HEIC','heic', 'heif', 'HEIF','svg','SVG','mp4','MP4','mov','MOV','avi','AVI','flv','FLV','wmv','WMV','mkv','MKV'];
        if (is_array($file)){
            $arrPath = [];
            foreach ($file as $key => $value){
                $FileName = Str::random(10);
                $file_name =  $FileName . "___" . $this->convert_vi_to_en($value->getClientOriginalName());
                $filetype = $value->getClientOriginalExtension();
                $filetype = strtolower($filetype);
                $fileName = $disk.'/'.$folder .'/'. $file_name;
                if (in_array($filetype,$arr)) {
                    if (!Storage::disk($disk)->exists($folder)) {
                        Storage::disk($disk)->makeDirectory($folder);
                    }
                    $file = Img::make($value)->orientate();
                    if (!empty($resize)) {
                        $path = $file->resize($with, $height, function ($const) {
                            $const->aspectRatio();
                        })->save(storage_path('app/' . $fileName));
                    } else {
                        $path = $file->save(storage_path('app/' . $fileName));
                    }
                    array_push($arrPath, $folder . '/' . $path->basename);
                } else {
                    if (in_array($filetype,$arrFile)) {
                        $path = $value->storeAs($folder, $file_name, $disk);
                        array_push($arrPath, $path);
                    } else {
                        if (empty($filetype)){
                            $path = Img::make($value)->orientate()->save(storage_path('app/' . $fileName));
                            array_push($arrPath, $folder . '/' . $path->basename);
                        }
                    }
                }
            }
            $arrPath = implode('||',$arrPath);
            return trim($arrPath,'||');
        } else {
            $FileName = Str::random(10);
            $filetype = $file->getClientOriginalExtension();
            $filetype = strtolower($filetype);
            $file_name = $FileName . "___" . $this->convert_vi_to_en($file->getClientOriginalName());
            $fileName = $disk.'/'.$folder.'/'. $file_name;
            if (in_array($filetype,$arr)){
                if (!Storage::disk($disk)->exists($folder)) {
                    Storage::disk($disk)->makeDirectory($folder);
                }
                if (!empty($resize)) {
                    $file = Img::make($file)->orientate();
                    if (!empty($resize)) {
                        $path = $file->resize($with, $height, function ($const) {
                            $const->aspectRatio();
                        })->save(storage_path('app/' . $fileName));
                    } else {
                        $path = $file->save(storage_path('app/' . $fileName));
                    }
                    return $folder . '/' . $path->basename;
                } else {
                    $path = $file->storeAs($folder, $file_name, $disk);
                    return $path;
                }
            } else {
                if (in_array($filetype,$arrFile)) {
                    $path = $file->storeAs($folder, $file_name, $disk);
                    return $path;
                } elseif (empty($filetype)){
                    $path = Img::make($file)->orientate()->save(storage_path('app/' . $fileName));
                    return $folder . '/' . $path->basename;
                } else {
                    return null;
                }
            }
        }
    }

    public function deleteFile($path, $disk = 'public')
    {
        Storage::disk($disk)->delete($path);
    }

    public function UploadFileS3($file = array(), $folder = null,$with = 150,$height = 150,$resize = true,$disk = 'public'){
        $arr = ['png','jpeg','jpg','gif','jfif','ai','AI','raw','RAW','EPS','eps','indd','INDD','PNG','JPEG','JPG','GIF','JFIF','webp','WEBP'];
        if (is_array($file)){
            $arrPath = [];
            foreach ($file as $key => $value){
                $FileName = Str::random(10);
                $file_name =  $FileName . "___" . $this->convert_vi_to_en($value->getClientOriginalName());
                $fileName = $folder.'/'. $file_name;
                $filetype = $value->getClientOriginalExtension();
                if (in_array($filetype,$arr)){
                    if (!empty($resize)){
                        $image =  Img::make($value)->resize($with,$height, function ($const) {
                            $const->aspectRatio();
                        });
                        $imageData = $image->stream();
                        Storage::disk('s3')->put($fileName, $imageData->__toString(),'public');
                        $url = Storage::disk('s3')->url($fileName);
                    } else {
                        $path = $value->storeAs($folder, $file_name, 's3');
                        Storage::disk('s3')->setVisibility($path, 'public');
                        $url = Storage::disk('s3')->url($path);
                    }
                    array_push($arrPath, $url);
                } else {
                    $path = $value->storeAs($folder, $file_name, 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');
                    $url = Storage::disk('s3')->url($path);
                    array_push($arrPath, $url);
                }
            }
            $arrPath = implode('||',$arrPath);
            return trim($arrPath,'||');
        } else {

            $FileName = Str::random(10);
            $filetype = $file->getClientOriginalExtension();
            $file_name = $FileName . "___" . $this->convert_vi_to_en($file->getClientOriginalName());
            $fileName = $folder.'/'. $file_name;
            if (in_array($filetype,$arr)){
                if (!empty($resize)){
                    $image =  Img::make($file)->resize($with,$height, function ($const) {
                        $const->aspectRatio();
                    });
                    $imageData = $image->stream();
                    Storage::disk('s3')->put($fileName, $imageData->__toString(),'public');
                    $url = Storage::disk('s3')->url($fileName);
                } else {
                    $path = $file->storeAs($folder, $file_name, 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');
                    $url = Storage::disk('s3')->url($path);
                }
                return $url;
            } else {
                $path = $file->storeAs($folder, $file_name, 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $url = Storage::disk('s3')->url($path);
                return $url;
            }
        }
    }

    public function deleteFileS3($path, $disk = 's3')
    {
        $filePath = parse_url($path, PHP_URL_PATH);
        $filePath = ltrim($filePath, '/');
        Storage::disk($disk)->delete($filePath);
    }

    public function getImageS3($path, $disk = 's3')
    {
        $imageName = parse_url($path, PHP_URL_PATH);
        $imageName = ltrim($imageName, '/');
        $imageName = str_replace('%20',' ',$imageName);
        $cacheKey = 'image_' . $imageName;
        // Kiểm tra cache trước khi tải ảnh từ S3
        $image = Cache::remember($cacheKey, 3600, function () use ($imageName) {
            return Storage::disk('s3')->temporaryUrl(
                $imageName, now()->addMinutes(60)
            );
        });
        return $image;
    }

    public function convert_vi_to_en($str)
    {
        $str = preg_replace("(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)", "a", $str);
        $str = preg_replace("(à|á|ạ|ả|ã|â|ầ|ấ|ạ|ẩ|ẫ|ă|ẳ|ẵ|ặ|ắ|ằ)", "a", $str);
        $str = preg_replace("(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)", "e", $str);
        $str = preg_replace("(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)", "e", $str);
        $str = preg_replace("(ì|í|ị|ỉ|ĩ)", "i", $str);
        $str = preg_replace("(ì|í|ị|ỉ|ĩ)", "i", $str);
        $str = preg_replace("(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)", "o", $str);
        $str = preg_replace("(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)", "o", $str);
        $str = preg_replace("(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)", "u", $str);
        $str = preg_replace("(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)", "u", $str);
        $str = preg_replace("(ỳ|ý|ỵ|ỷ|ỹ)", "y", $str);
        $str = preg_replace("(ỳ|ý|ỵ|ỹ)", "y", $str);
        $str = preg_replace("(đ)", "d", $str);
        $str = preg_replace("(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)", "A", $str);
        $str = preg_replace("(À|Á|Ạ|Á|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ẵ|Ẳ|Ặ|Ắ|Ằ)", "A", $str);
        $str = preg_replace("(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)", "E", $str);
        $str = preg_replace("(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)", "E", $str);
        $str = preg_replace("(Ì|Í|Ị|Ỉ|Ĩ)", "I", $str);
        $str = preg_replace("(Ì|Í|Ị|Í|Ĩ)", "I", $str);
        $str = preg_replace("(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)", "O", $str);
        $str = preg_replace("(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)", "O", $str);
        $str = preg_replace("(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)", "U", $str);
        $str = preg_replace("(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)", "U", $str);
        $str = preg_replace("(Ỳ|Ý|Ỵ|Ỷ|Ỹ)", "Y", $str);
        $str = preg_replace("(Ỳ|Ý|Ỵ|Ý|Ỹ)", "Y", $str);
        $str = preg_replace("(Đ)", "D", $str);
        $str = preg_replace("(Đ)", "D", $str);
        return $str;
    }
}
