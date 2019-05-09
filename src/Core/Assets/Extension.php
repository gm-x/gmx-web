<?php
namespace GameX\Core\Assets;

use \Psr\Container\ContainerInterface;
use \Twig_Extension;
use \Twig_SimpleFunction;

class Extension extends Twig_Extension
{
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'assets_require',
                [$this, 'assetsRequire']
            ),
	        new Twig_SimpleFunction(
		        'assets_styles',
		        [$this, 'assetsStyles']
	        ),
	        new Twig_SimpleFunction(
		        'assets_scripts',
		        [$this, 'assetsScripts']
	        ),
        ];
    }
    
    /**
     * @param string $asset
     */
    public function assetsRequire($asset)
    {
        $this->getManager()->includeAsset($asset);
    }

    /**
     * @return array
     */
    public function assetsStyles()
    {
        return $this->getManager()->getIncludedAssetsStyles();
    }

    /**
     * @return array
     */
    public function assetsScripts()
    {
        return $this->getManager()->getIncludedAssetsScripts();
    }

	/**
	 * @return Manager
	 */
    protected function getManager()
    {
    	return $this->container->get('assets');
    }
}
