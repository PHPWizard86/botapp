<?php

namespace Library;

use Respect\Validation\Validator as v;
use League\Uri\Uri;

class DomainChecker {
    
    private $blocked_ips;

    public function __construct($blocked_ips = null) {
        
        $this->blocked_ips = $blocked_ips ?? [
            "10.10.34.34",
            "10.10.34.35",
            "10.10.34.36"
        ];
        
    }

    public function acheck($domain) {
        
        $domain = trim( $domain );
        
        if( !v::domain()->validate( $domain ) ) {
            
            $domain = Uri::new( $domain )->getHost();
            
            if( empty( $domain ) ) {
                
                return null;
                
            }
            
        }
        
        $ip_list = $this->resolveDomain($domain);
        
        if( $ip_list === null ) {
            
            return true;
            
        }
        
        $is_blocked = false;

        foreach ($ip_list as $ip) {
            if (in_array($ip, $this->blocked_ips)) {
                $is_blocked = true;
                break;
            }
        }

        return $is_blocked;
        
    }

    private function resolveDomain($domain) {
        
        $ip_list = gethostbynamel($domain);
        
        if( $ip_list === false ) {
            
            return null;
            
        }

        return $ip_list;
        
    }
    
}