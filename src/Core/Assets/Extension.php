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
		        'assets_set_data',
		        [$this, 'assetsSetData']
	        ),
	        new Twig_SimpleFunction(
		        'assets_get_data',
		        [$this, 'assetsGetData']
	        ),
            new Twig_SimpleFunction(
                'require_asset',
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
	 * @param $key
	 * @param $value
	 */
	public function assetsSetData($key, $value)
	{
		$this->getManager()->setData($key, $value);
	}

	/**
	 * @return array
	 */
    public function assetsGetData()
    {
        return $this->getManager()->getData();
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
