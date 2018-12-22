<?php
namespace GameX\Core\Upload;

use \Slim\Http\UploadedFile;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Models\Upload as UploadModel;

class Upload {
    protected $baseDir;
    protected $baseUrl;
    
    public function __construct($baseDir, $baseUrl) {
        $this->baseDir = $baseDir . DIRECTORY_SEPARATOR;
        $this->baseUrl = $baseUrl . '/';
    }
    
    public function upload(UserModel $user, UploadedFile $file) {
        $filename = $this->generateName($file);
        $path = $user->id . DIRECTORY_SEPARATOR;
        $this->checkDirectory($this->baseDir . $path);
        $file->moveTo($this->baseDir . $path . $filename);
        
        $model = new UploadModel();
        $model->fill([
            'owner_id' => $user->id,
            'filename' => $file->getClientFilename(),
            'path' => $path . $filename
        ]);
        $model->save();
        return $model;
    }
    
    public function getUrl(UploadModel $model) {
        return $this->baseUrl . $model->path;
    }
    
    protected function checkDirectory($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
    
    protected function generateName(UploadedFile $file) {
        return date('mdYHis') . uniqid() . '.' . pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
    }
}
