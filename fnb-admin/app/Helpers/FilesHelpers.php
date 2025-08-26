<?php namespace App\Helpers;
use Illuminate\Support\Facades\DB;

class FilesHelpers
{

    public static function maybe_create_upload_path($path = '')
    {
        if (!file_exists($path)) {
            @mkdir($path, 0755);
//            @fopen(rtrim($path, '/') . '/' . 'index.html', 'w');
            @fopen(rtrim($path, '/') . '/', 'w');
        }
    }

    public static function uploadFileData($FILE, $Name, $paste, $pasteShort) {
        $arr = ['image/jpeg','image/png','image/gif','image/bmp','image/tiff','image/webp','image/heif','image/heic','image/svg+xml','image/avif','image/apng','image/x-mng'];
        if (is_array($FILE)) {
            @FilesHelpers::maybe_create_upload_path($paste);
            $data_file = [];
            foreach ($FILE as $key => $file) {
                if (!empty($file)) {
                    $fileType = $file->getMimeType();
                    if (!in_array($fileType,$arr)) {
                        return null;
                    }
                    $fileName = time() . '_' . preg_replace("/[\/\?\'\"\$]/", "_", FilesHelpers::convert_vi_to_en(($file->getClientOriginalName())));
                    if ($file->move($paste,$fileName)){
                        $data_file[] = $pasteShort . $fileName;
                    }
                }
            }
            if (!empty($data_file)) {
                return $data_file;
            }
        } else {
            if (!empty($FILE)) {
                $fileType = $FILE->getMimeType();
                if (!in_array($fileType,$arr)) {
                    return null;
                }
                FilesHelpers::maybe_create_upload_path($paste);
                $fileName = time() . '_' . preg_replace("/[\/\?\'\"\$]/", "_", FilesHelpers::convert_vi_to_en(($FILE->getClientOriginalName())));
                if ($FILE->move($paste,$fileName)){
                    $data_file = $pasteShort . $fileName;
                    return $data_file;
                }
            }
        }
        return false;
    }

    public static function uploadFileDataOld($FILE, $Name, $paste, $pasteShort) {
        $arr = ['image/jpeg','image/png','image/gif','image/bmp','image/tiff','image/webp','image/heif','image/heic','image/svg+xml','image/avif','image/apng','image/x-mng'];
        if (is_array($FILE[$Name]['name'])) {
            @FilesHelpers::maybe_create_upload_path($paste);
            $data_file = [];
            foreach ($FILE[$Name]['name'] as $kFile => $vFile) {
                if (!empty($FILE[$Name]['name'][$kFile])) {
                    $fileType = $FILE[$Name]['type'][$kFile];
                    if (!in_array($fileType,$arr)) {
                        return null;
                    }
                    $fileName = time() . $kFile . '_' . preg_replace("/[\/\?\'\"\$]/", "_", FilesHelpers::convert_vi_to_en(($FILE[$Name]['name'][$kFile])));
                    if (is_uploaded_file($FILE[$Name]['tmp_name'][$kFile])) {
                        $source_path_image = $FILE[$Name]['tmp_name'][$kFile];
                        $target_path_image = $paste . $fileName;
                        if (move_uploaded_file($source_path_image, $target_path_image)) {
                            $data_file[] = $pasteShort . $fileName;
                        }
                    }
                }
            }
            if (!empty($data_file)) {
                return $data_file;
            }
        } else {
            if (!empty($FILE[$Name]['name']) && file_exists($FILE[$Name]['tmp_name'])) {
                $fileType = $FILE[$Name]['type'];
                if (!in_array($fileType,$arr)) {
                    return null;
                }
                FilesHelpers::maybe_create_upload_path($paste);
                $fileName = time() . '_' . preg_replace("/[\/\?\'\"\$]/", "_", FilesHelpers::convert_vi_to_en(($FILE[$Name]['name'])));
                if (is_uploaded_file($FILE[$Name]['tmp_name'])) {
                    $source_path_image = $FILE[$Name]['tmp_name'];
                    $target_path_image = $paste . $fileName;
                    if (@move_uploaded_file($source_path_image, $target_path_image)) {
                        $data_file = $pasteShort . $fileName;
                        return $data_file;
                    }
                }
            }
        }
        return false;
    }

    public static function convert_vi_to_en($str)
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
