<?php

namespace GameX\Core\Upload;

use \Psr\Container\ContainerInterface;
use \Twig_Extension;
use \Twig_SimpleFunction;
use \GameX\Models\Upload as UploadModel;

class ViewExtension extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('upload_url', [$this, 'uploadUrl']),
        ];
    }
    
    /**
     * @param $id
     * @return string|null
     */
    public function uploadUrl($id)
    {
        if (!$id) {
            return null;
        }
        
        $model = UploadModel::find($id);
        if (!$model) {
            return null;
        }
        
        return $this->getUpload()->getUrl($model);
    }
    
    /**
     * @return Upload
     */
    protected function getUpload()
    {
        return $this->container->get('upload');
    }
}
