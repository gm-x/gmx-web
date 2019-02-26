<?php

namespace GameX\Core\Helpers;

use \Psr\Http\Message\ServerRequestInterface;

class IpHelper
{
    const HEADERS_TO_INSPECT = [
        'Forwarded',
        'X-Forwarded-For',
        'X-Forwarded',
        'X-Cluster-Client-Ip',
        'Client-Ip',
    ];
    
    public static function getIPAddress(ServerRequestInterface $request)
    {
        $ipAddress = null;
    
        $serverParams = $request->getServerParams();
        if (isset($serverParams['REMOTE_ADDR']) && self::isValidIpAddress($serverParams['REMOTE_ADDR'])) {
            $ipAddress = $serverParams['REMOTE_ADDR'];
        }
        
        foreach (self::HEADERS_TO_INSPECT as $header) {
            if ($request->hasHeader($header)) {
                $ip = self::getFirstIpAddressFromHeader($request, $header);
                if (self::isValidIpAddress($ip)) {
                    $ipAddress = $ip;
                    break;
                }
            }
        }
        
        return $ipAddress;
    }
    
    private static function getFirstIpAddressFromHeader(ServerRequestInterface $request, $header)
    {
        $items = explode(',', $request->getHeaderLine($header));
        $headerValue = trim(reset($items));
        
        if (ucfirst($header) == 'Forwarded') {
            foreach (explode(';', $headerValue) as $headerPart) {
                if (strtolower(substr($headerPart, 0, 4)) == 'for=') {
                    $for = explode(']', $headerPart);
                    $headerValue = trim(substr(reset($for), 4), " \t\n\r\0\x0B" . "\"[]");
                    break;
                }
            }
        }
        
        return $headerValue;
    }
    
    protected static function isValidIpAddress($ip)
    {
        return (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false);
    }
}
