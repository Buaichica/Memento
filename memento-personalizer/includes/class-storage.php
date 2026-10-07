<?php
/**
 * Protected file storage.
 *
 * Files live under a private root, addressed by server-generated storage keys
 * (e.g. "temp/{session}/{upload}.jpg"). Keys are validated against a strict
 * pattern and resolved paths are checked to stay inside the root, so no
 * client-supplied fragment can ever reach the filesystem.
 *
 * Root resolution order:
 *   1. MEMENTO_PZ_STORAGE_DIR constant (recommended: a folder outside public_html).
 *   2. The root chosen at activation (stored in the memento_pz_storage_root option):
 *      a "memento-private" folder next to the WordPress directory when writable,
 *      otherwise wp-content/uploads/memento-private with deny-all rules.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Storage {

	const OPTION   = 'memento_pz_storage_root';
	const KEY_REGEX = '#^(temp|orders|tmp)/[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)*(\.(jpg|jpeg|png|webp|gif|zip))?$#';

	public static function root() {
		if ( defined( 'MEMENTO_PZ_STORAGE_DIR' ) && MEMENTO_PZ_STORAGE_DIR ) {
			return untrailingslashit( MEMENTO_PZ_STORAGE_DIR );
		}
		$root = get_option( self::OPTION );
		if ( ! $root ) {
			$root = self::ensure_root();
		}
		return untrailingslashit( $root );
	}

	/**
	 * Create the storage root (if needed) and protect it from direct web access.
	 *
	 * @return string Root path.
	 */
	public static function ensure_root() {
		if ( defined( 'MEMENTO_PZ_STORAGE_DIR' ) && MEMENTO_PZ_STORAGE_DIR ) {
			$root = untrailingslashit( MEMENTO_PZ_STORAGE_DIR );
		} else {
			$root = get_option( self::OPTION );
			if ( ! $root ) {
				$outside = dirname( untrailingslashit( ABSPATH ) ) . '/memento-private';
				$parent  = dirname( $outside );
				if ( ( is_dir( $outside ) || ( @is_writable( $parent ) && wp_mkdir_p( $outside ) ) ) && is_writable( $outside ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
					$root = $outside;
				} else {
					$uploads = wp_upload_dir( null, false );
					$root    = $uploads['basedir'] . '/memento-private';
				}
				update_option( self::OPTION, $root, false );
			}
		}

		wp_mkdir_p( $root );
		self::write_guard_files( $root );
		return $root;
	}

	/** True when the root sits inside the web-served WordPress directory. */
	public static function is_web_accessible_root() {
		$root = wp_normalize_path( self::root() );
		$abs  = wp_normalize_path( untrailingslashit( ABSPATH ) );
		return 0 === strpos( $root, $abs . '/' ) || 0 === strpos( $root, wp_normalize_path( WP_CONTENT_DIR ) . '/' );
	}

	private static function write_guard_files( $root ) {
		$files = [
			'.htaccess'  => "# Memento Personalizer — deny all direct access\n<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tOrder deny,allow\n\tDeny from all\n</IfModule>\nOptions -Indexes\n",
			'index.php'  => "<?php\n// Silence is golden.\n",
			'web.config' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration><system.webServer><authorization><deny users=\"*\" /></authorization></system.webServer></configuration>\n",
		];
		foreach ( $files as $name => $contents ) {
			$path = $root . '/' . $name;
			if ( ! file_exists( $path ) ) {
				file_put_contents( $path, $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			}
		}
	}

	public static function is_valid_key( $key ) {
		return is_string( $key ) && strlen( $key ) < 255 && false === strpos( $key, '..' ) && preg_match( self::KEY_REGEX, $key );
	}

	/**
	 * Resolve a storage key to an absolute path inside the root.
	 *
	 * @return string|false
	 */
	public static function path( $key ) {
		if ( ! self::is_valid_key( $key ) ) {
			return false;
		}
		$path = self::root() . '/' . $key;

		// If the file exists, make sure symlinks etc. don't escape the root.
		if ( file_exists( $path ) ) {
			$real = realpath( $path );
			$root = realpath( self::root() );
			if ( ! $real || ! $root || 0 !== strpos( wp_normalize_path( $real ), wp_normalize_path( $root ) . '/' ) ) {
				return false;
			}
		}
		return $path;
	}

	public static function exists( $key ) {
		$path = self::path( $key );
		return $path && is_file( $path );
	}

	/** Write raw bytes to a key, creating directories as needed. */
	public static function put_file( $key, $source_path ) {
		$dest = self::path( $key );
		if ( ! $dest ) {
			return false;
		}
		wp_mkdir_p( dirname( $dest ) );
		if ( ! @rename( $source_path, $dest ) && ! @copy( $source_path, $dest ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
			return false;
		}
		@chmod( $dest, 0640 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return true;
	}

	/**
	 * Move a file between keys. Idempotent: if the source is gone but the
	 * destination exists (a previous run finished the move), report success.
	 */
	public static function move( $from_key, $to_key ) {
		if ( $from_key === $to_key ) {
			return self::exists( $to_key );
		}
		$from = self::path( $from_key );
		$to   = self::path( $to_key );
		if ( ! $from || ! $to ) {
			return false;
		}
		if ( ! file_exists( $from ) ) {
			return file_exists( $to );
		}
		wp_mkdir_p( dirname( $to ) );
		return @rename( $from, $to ) || ( @copy( $from, $to ) && @unlink( $from ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}

	public static function delete( $key ) {
		$path = self::path( $key );
		if ( $path && is_file( $path ) ) {
			return @unlink( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}
		return true;
	}

	/**
	 * Delete a directory (identified by a key prefix such as "temp/{session}")
	 * and everything inside it.
	 */
	public static function delete_dir( $key ) {
		$path = self::path( $key );
		if ( ! $path || ! is_dir( $path ) ) {
			return true;
		}
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ( $it as $file ) {
			if ( $file->isLink() || $file->isFile() ) {
				@unlink( $file->getPathname() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			} else {
				@rmdir( $file->getPathname() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			}
		}
		return @rmdir( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}

	/** List immediate subdirectory names under a key prefix (e.g. "temp"). */
	public static function list_dirs( $key ) {
		$path = self::root() . '/' . $key;
		if ( ! in_array( $key, [ 'temp', 'tmp', 'orders' ], true ) || ! is_dir( $path ) ) {
			return [];
		}
		$dirs = [];
		foreach ( new DirectoryIterator( $path ) as $entry ) {
			if ( $entry->isDir() && ! $entry->isDot() ) {
				$dirs[ $entry->getFilename() ] = $entry->getMTime();
			}
		}
		return $dirs;
	}

	public static function random_id() {
		return bin2hex( random_bytes( 16 ) );
	}
}
