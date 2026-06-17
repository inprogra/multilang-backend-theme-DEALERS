<?php

namespace VGA\Classes;

use function Env\env;

class Cache {
	private $redis;
	private $prefix;
	private $redis_local;
	private $cacheEnabled;

	public function __construct() {
		$this->cacheEnabled = !env('DISABLE_REDIS_CACHE');
		
		if ($this->cacheEnabled) {
			$this->redis = new \Predis\Client([
				// 'scheme' => 'tcp',
				// 'host' => '127.0.0.1',
				// #'host'   => 'redis',
				// 'port' => 6379,
				// 'password' => 'q5M3wF7i02iW'
					'scheme' => 'tcp',
				'host' => '80.190.80.248',
				#'host'   => 'redis',
				'port' => 6379,
				'password' => 'q5M3wF7i02iW'
			]);
			$this->redis_local =  new \Predis\Client([
				'scheme' => 'tcp',
				'host' => '127.0.0.1',			
				'port' => 6379,
				// 'password' => 'q5M3wF7i02iW'
				// 	'scheme' => 'tcp',
				// 'host' => '80.190.80.248',
				// #'host'   => 'redis',
				// 'port' => 6379,
				'password' => 'q5M3wF7i02iW'
			]);
		}
	}
	
	public function getDatabaseKey($key) {
		if (!$this->cacheEnabled) {
			return null;
		}
		$key = 'laravel_database_'.$key;
		$data = $this->redis_local->get($key);

		return $data;
	}

	public function get($key) {
		if (!$this->cacheEnabled) {
			return null;
		}
        $data = $this->redis_local->get($key);
		
        if ($data !== null) {
            // Data found in the cache
			if (is_serialized($data)) {
				return unserialize($data);
			} else {
				return $data;
			}
            
        }
        // Data not found in the cache
        return null;
    }

    public function set($key, $data, $ttl = 3600) {
        if (!$this->cacheEnabled) {
            return;
        }
        // Serialize the data before storing it in the cache
        $data = serialize($data);
        $this->redis_local->setex($key, $ttl, $data);
    }

    public function delete($key) {
        if (!$this->cacheEnabled) {
            return;
        }
        $this->redis_local->del($key);
    }

	public static function getAttachmentHash( $attachmentId ): string {
		$hash = get_post_meta( $attachmentId, 'cache-hash' );
		if ( ! empty( $hash ) ) {
			$hash = $hash[0];
		} else {
			$hash = mt_rand();
			update_post_meta( $attachmentId, 'cache-hash', $hash );
		}

		return $hash;
	}

	public static function buildHashUrl( $url, $hash ): string {
		return $url;
	}
}
