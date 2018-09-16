<?php
namespace GameX\Core\Upload;

use \Twig_Extension;
use \Twig_SimpleFunction;
use \GameX\Models\Upload as UploadModel;

class ViewExtension extends Twig_Extension {
    protected $upload;
    
    public function __construct(Upload $upload) {
        $this->upload = $upload;
    }
    
    /**
     * @return array
     */
    public function getFunctions() {
        return [
            new Twig_SimpleFunction(
                'upload_url',
                [$this, 'uploadUrl']
            ),
        ];
    }
    
    public function uploadUrl($id) {
        if (!$id) {
            return null;
        }
    
        $model = UploadModel::find($id);
        if (!$model) {
            return null;
        }
        
        return $this->upload->getUrl($model);
    }
}
