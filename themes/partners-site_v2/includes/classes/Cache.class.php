<?php

namespace Classes;

use function Env\env;
use Classes\MultisiteFixer;
use Controllers\CookiesController;
use \Predis\Client;

class Cache {
	private $redis;
	private $prefix;
	private $redis_local;
	private $cacheEnabled;
	private $redisInitialized = false;

	public function __construct() {
		$this->cacheEnabled = !env('DISABLE_REDIS_CACHE');
		$this->addActions();
	}

	private function initRedis(): void {
		if ($this->redisInitialized) {
			return;
		}
		$this->redisInitialized = true;

		if (!$this->cacheEnabled) {
			return;
		}

		$this->redis = new \Predis\Client([
			'scheme' => 'tcp',
			'host' => '80.190.80.248',
			'port' => 6379,
			'password' => 'q5M3wF7i02iW'
		]);
		$this->redis_local = new \Predis\Client([
			'scheme' => 'tcp',
			'host' => '127.0.0.1',
			'port' => 6379,
			'password' => 'q5M3wF7i02iW'
		]);
	}
	
	public function getDatabaseKey($key) {
		$this->initRedis();
		if (!$this->cacheEnabled) {
			return null;
		}
		$key = 'laravel_database_'.$key;
		$data = $this->redis_local->get($key);

		return $data;
	}
	public function get($key) {
		$this->initRedis();
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
        $this->initRedis();
        if (!$this->cacheEnabled) {
            return;
        }
        // Serialize the data before storing it in the cache
        $data = serialize($data);
        $this->redis_local->setex($key, $ttl, $data);
    }
    public function delete($key) {
        $this->initRedis();
        if (!$this->cacheEnabled) {
            return;
        }
        $this->redis_local->del($key);
    }
	public function addActions(): void {
		add_action(
			'admin_bar_menu',
			function ( $admin_bar ) {
				$admin_bar->add_menu(
					array(
						'id'    => 'purge-cache',
						'title' => __('Clear Cache', 'partners-site_v2'),
						'href'  => '#',
					)
				);
			},
			100
		);

		add_action( 'wp_ajax_purgeCache', array( $this, 'purgeSiteCache' ) );
	}

	public function purgeSiteCache() {
		$this->initRedis();
		if ($this->cacheEnabled) {
			$this->redis_local->flushAll();
		}

		    $blog_id = MultisiteFixer::getCurrentBlogId();
			   
			    // Use ABSPATH instead of get_home_path()
				$base_path = '/var/www/volvocars-partner.pl/web/';
			    
			    if ( $blog_id == 1 ) {
			        $this->deleteAllFiles( $base_path . 'cache' );
			    } else {
			        $this->deleteAllFiles( $base_path . 'cache/' . $blog_id );
			    }
			    
			   // Regenerate static HTML files after cache purge
			    $this->regenerateStaticHtml($blog_id);
			    
				 echo 'OK';
				 die();
	}
	public function regenerateStaticHtml($blog_id = 0) {
		$generator = new StaticHtmlGenerator();
		
		if ($blog_id === 0 || $blog_id === 1) {
			// Regenerate for all sites
			$generator->scheduleBackgroundGeneration();
		} else {
			// Regenerate for specific site
			$generator->generateAllPagesForSite($blog_id);
		}
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

	public static function getAsset( $filename, $fileContent = false ): string {
		
		if ( env( 'WP_ENV' ) === 'development' ) {
			if ( $fileContent ) {
				return get_template_directory() . '/assets/public/' . $filename;
			}
			return get_template_directory_uri() . '/assets/public/' . $filename;
		}
		$map         = get_template_directory() . '/assets/public/assets-manifest.json';
		static $hash = null;
		if ( null === $hash ) {
			$hash = file_exists( $map ) ? json_decode( file_get_contents( $map ), true ) : array();
		}
		if ( array_key_exists( $filename, $hash ) ) {
			if ( $fileContent ) {
				return get_template_directory() . '/assets/public/' . $hash[ $filename ];
			}
			return get_template_directory_uri() . '/assets/public/' . $hash[ $filename ];
		}
		return $filename;
	}

	public static function getHeadPlaceHolder(): string {
		return '<!--[[CACHE_PLACEHOLDER]]-->';
	}

	public static function getPage() {
		ob_start();
		
		if ( self::isPageCachable() ) {
		// 	// First, try to serve static HTML if it exists
        $staticFilePath = self::getStaticFilePath();
		
        if ( file_exists( $staticFilePath ) ) {
            $html = file_get_contents( $staticFilePath );
            echo $html;
            exit;
        }

        // Fallback to dynamic cache
			$filePath = self::getPageFilePath();

			if ( file_exists( $filePath ) ) {
				$html = self::replacePlaceholder( file_get_contents( $filePath ) );

				echo $html;

				exit;
			}
		}
	}

	public static function savePage() {
		$html = ob_get_clean();

		global $wp;

		$publicQueryVars = $wp->public_query_vars;

		$hasForbiddenQueryVars = ! empty( array_intersect( $publicQueryVars, array_keys( $_GET ) ) );

		if ( ! $hasForbiddenQueryVars && self::isPageCachable()) {
			$filePath = self::getPageFilePath();
			if ( ! file_exists( dirname( $filePath ) ) ) {
				mkdir( dirname( $filePath ), 0755, true );
			}
			
			$cacheFile = fopen( $filePath, 'w' );
			fwrite( $cacheFile, $html );
			fclose( $cacheFile );
			// Also save static HTML (for pages only)
        if ( is_page() && ! isset( $_GET['static_gen'] ) ) {
            $staticFilePath = self::getStaticFilePath();
            $staticDir = dirname( $staticFilePath );
            
            if ( ! file_exists( $staticDir ) ) {
                mkdir( $staticDir, 0755, true );
            }

            $finalHtml = self::replacePlaceholder( $html );
            file_put_contents( $staticFilePath, $finalHtml );
		}
		}

		echo self::replacePlaceholder( $html );

		ob_end_flush();
	}

	private function deleteAllFiles( $dir ) {
		foreach ( glob( $dir . '/*' ) as $file ) {
			if ( is_dir( $file ) ) {
				$this->deleteAllFiles( $file );
			} else {
				unlink( $file );
			}
		}
		rmdir( $dir );
	}

	private static function getPageFileName(): string {
		global $wp;
		$url = $wp->request;
		$url = str_replace( '/', '_', $url );

		if ( $url == '' ) {
			$url = 'index';
		}

		return 'cached-' . $url . '.html';
	}

	private static function getPageFilePath(): string {
		return 'cache/' . MultisiteFixer::getCurrentBlogId() . '/' . self::getPageFileName();
	}

	private static function replacePlaceholder( $html ): string {
		$googleTagManager = CookiesController::getGtmScript();
		return str_replace( '<!--[[CACHE_PLACEHOLDER]]-->', $googleTagManager, $html );
	}

	private static function isPageCachable(): bool {
		$post        = get_queried_object();
		$isStockPage = ($post && isset($post->post_name)) ? $post->post_name === 'dostepne-na-miejscu' : false;

		return ! is_user_logged_in() && ! is_404() && ! $isStockPage;
	}
	private static function getStaticFilePath(): string {
		    global $wp;
		    $url = $wp->request;
		    
		    if ( $url == '' ) {
		        $filename = 'index.html';
		    } else {
		        $url = str_replace( '/', '_', $url );
		        $filename = 'page-' . $url . '.html';
		    }
		    
		    // Use ABSPATH instead of get_home_path() to avoid namespace issues
		    $base_path = '/var/www/volvocars-partner.pl/web/';
			
		    return $base_path . 'cache/' . MultisiteFixer::getCurrentBlogId() . '/static/' . $filename;
		}
		
}
