<?php
namespace GameX\Core\Helpers;

use \Psr\Http\Message\UriInterface;

class UriHelper {
    
    /**
     * @param UriInterface $uri
     * @param bool $absolute
     * @return string
     */
    public static function getUrl(UriInterface $uri, $absolute = false) {
        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();
        $basePath = $uri->getBasePath();
        $path = $uri->getPath();
        $query = $uri->getQuery();
        $fragment = $uri->getFragment();
    
        $path = $basePath . '/' . ltrim($path, '/');
    
        $result = '';
        if ($absolute) {
            $result .= ($scheme ? $scheme . ':' : '') . ($authority ? '//' . $authority : '');
        }
        $result .= $path . ($query ? '?' . $query : '') . ($fragment ? '#' . $fragment : '');
    
        return $result;
    }
}
