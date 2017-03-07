<?
class Uploader {
    public function handle($files, $dir){
        if ( is_array($files['upfile']['error']) ) {
            throw new RuntimeException('Invalid parameters.');
        }

        switch ($files['upfile']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
                throw new RuntimeException('Exceeded INI filesize limit.');
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded form filesize limit.');
            default:
                throw new RuntimeException('Unknown errors.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($files['upfile']['tmp_name']),
            array(
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'rar' => 'application/rar'
        ),
            true
        )) {
            throw new RuntimeException('Invalid file format.');
        }

        $name = basename($files['upfile']['name']);
        $name = preg_replace('/[^\x20-\x7E]/','', $name);
        if ($name != ".htaccess"){
            if (!move_uploaded_file(
                $files['upfile']['tmp_name'],
                $dir . $name)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
        } else {
            throw new RuntimeException('Invalid filename.');
        }

        return $name;
    }
}
?>
