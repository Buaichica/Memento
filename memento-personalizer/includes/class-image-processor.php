<?php
/**
 * Server-side image validation and production-file generation.
 *
 * Production file policy (spec §9.2): only the square, print-ready JPEG is
 * retained. Originals are not kept server-side; the customer's browser keeps
 * the original in memory for re-editing during the session.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Image_Processor {

	/**
	 * Validate an uploaded temp file and write the production + thumbnail JPEGs.
	 *
	 * @param string $tmp_path   Path of the uploaded file (PHP temp file).
	 * @param string $dest_key   Storage key for the production JPEG.
	 * @param string $thumb_key  Storage key for the thumbnail JPEG.
	 * @return array|WP_Error    Image facts on success; WP_Error with a customer-safe message on failure.
	 */
	public static function process( $tmp_path, $dest_key, $thumb_key ) {
		$facts = self::validate( $tmp_path );
		if ( is_wp_error( $facts ) ) {
			return $facts;
		}

		$work_dir = Memento_PZ_Storage::root() . '/tmp';
		wp_mkdir_p( $work_dir );
		$work_id   = Memento_PZ_Storage::random_id();
		$source    = $tmp_path;
		$flattened = null;

		// Transparent PNG/WebP → flatten on white so print output isn't black.
		if ( 'image/jpeg' !== $facts['mime'] ) {
			$flattened = $work_dir . '/' . $work_id . '-flat.jpg';
			if ( self::flatten_to_jpeg( $tmp_path, $facts['mime'], $flattened ) ) {
				$source = $flattened;
			} else {
				$flattened = null;
			}
		}

		$editor = wp_get_image_editor( $source );
		if ( is_wp_error( $editor ) ) {
			self::cleanup( $flattened );
			Memento_PZ_Logger::warning( 'Image editor could not open upload: ' . $editor->get_error_code() );
			return self::error( 'corrupt' );
		}

		// Apply EXIF orientation before cropping (phones store rotation in EXIF).
		if ( method_exists( $editor, 'maybe_exif_rotate' ) ) {
			$editor->maybe_exif_rotate();
		}

		$size = $editor->get_size();
		$w    = (int) $size['width'];
		$h    = (int) $size['height'];
		$side = min( $w, $h );

		if ( $side < Memento_PZ_Settings::int( 'min_px_reject' ) ) {
			self::cleanup( $flattened );
			return self::error( 'too_small' );
		}

		// Centre-crop to 1:1 (matches the square slot preview). Edited images
		// arrive already square, so this is a no-op for them.
		if ( $w !== $h ) {
			$editor->crop( (int) floor( ( $w - $side ) / 2 ), (int) floor( ( $h - $side ) / 2 ), $side, $side );
		}

		// Downscale only — never upscale and claim better quality.
		$max = Memento_PZ_Settings::int( 'output_max_px' );
		if ( $side > $max ) {
			$editor->resize( $max, $max, false );
		}
		$editor->set_quality( Memento_PZ_Settings::int( 'jpeg_quality' ) );

		$out_path = $work_dir . '/' . $work_id . '-out.jpg';
		$saved    = $editor->save( $out_path, 'image/jpeg' );
		if ( is_wp_error( $saved ) ) {
			self::cleanup( $flattened );
			Memento_PZ_Logger::error( 'Could not save production image: ' . $saved->get_error_code() );
			return self::error( 'save_failed' );
		}
		self::strip_metadata( $saved['path'] );

		// Thumbnail for previews and the admin panel.
		$thumb_path = $work_dir . '/' . $work_id . '-thumb.jpg';
		$thumb      = wp_get_image_editor( $saved['path'] );
		if ( ! is_wp_error( $thumb ) ) {
			$tp = Memento_PZ_Settings::int( 'thumb_px' );
			$thumb->resize( $tp, $tp, false );
			$thumb->set_quality( 82 );
			$thumb_saved = $thumb->save( $thumb_path, 'image/jpeg' );
			if ( ! is_wp_error( $thumb_saved ) ) {
				self::strip_metadata( $thumb_saved['path'] );
			}
		}

		$out_w = (int) $saved['width'];
		$out_h = (int) $saved['height'];
		$bytes = (int) filesize( $saved['path'] );

		if ( ! Memento_PZ_Storage::put_file( $dest_key, $saved['path'] ) ) {
			self::cleanup( $flattened, $saved['path'], $thumb_path );
			Memento_PZ_Logger::error( 'Could not move production image into storage.' );
			return self::error( 'save_failed' );
		}
		$has_thumb = file_exists( $thumb_path ) && Memento_PZ_Storage::put_file( $thumb_key, $thumb_path );
		self::cleanup( $flattened, $thumb_path );

		return [
			'mime_type'  => 'image/jpeg',
			'width'      => $out_w,
			'height'     => $out_h,
			'bytes'      => $bytes,
			'src_width'  => $facts['width'],
			'src_height' => $facts['height'],
			'crop_px'    => $side,
			'quality'    => $side < Memento_PZ_Settings::int( 'min_px_recommended' ) ? 'low' : 'good',
			'thumb_key'  => $has_thumb ? $thumb_key : '',
		];
	}

	/**
	 * Check that the upload is a real, supported, reasonably-sized image —
	 * regardless of the filename, extension or client-declared MIME type.
	 *
	 * @return array|WP_Error
	 */
	public static function validate( $path ) {
		if ( ! is_file( $path ) || ! is_readable( $path ) ) {
			return self::error( 'no_file' );
		}
		$bytes = filesize( $path );
		if ( ! $bytes ) {
			return self::error( 'corrupt' );
		}
		if ( $bytes > Memento_PZ_Settings::int( 'max_file_mb' ) * MB_IN_BYTES ) {
			return self::error( 'too_large' );
		}

		$finfo = new finfo( FILEINFO_MIME_TYPE );
		$mime  = $finfo->file( $path );
		if ( ! in_array( $mime, (array) Memento_PZ_Settings::get( 'allowed_mimes' ), true ) ) {
			return self::error( 'bad_type' );
		}

		$info = @getimagesize( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		if ( ! $info || empty( $info[0] ) || empty( $info[1] ) || ( $info['mime'] ?? '' ) !== $mime ) {
			return self::error( 'corrupt' );
		}
		if ( ( $info[0] * $info[1] ) > Memento_PZ_Settings::int( 'max_megapixels' ) * 1000000 ) {
			return self::error( 'too_many_pixels' );
		}

		return [
			'mime'   => $mime,
			'width'  => (int) $info[0],
			'height' => (int) $info[1],
			'bytes'  => (int) $bytes,
		];
	}

	private static function flatten_to_jpeg( $src, $mime, $dest ) {
		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			try {
				$im = new Imagick( $src );
				$im->setImageBackgroundColor( 'white' );
				$im = $im->mergeImageLayers( Imagick::LAYERMETHOD_FLATTEN );
				$im->setImageFormat( 'jpeg' );
				$im->setImageCompressionQuality( 95 );
				$ok = $im->writeImage( $dest );
				$im->clear();
				return $ok;
			} catch ( Exception $e ) {
				Memento_PZ_Logger::debug( 'Imagick flatten failed: ' . get_class( $e ) );
			}
		}
		if ( function_exists( 'imagecreatetruecolor' ) ) {
			$img = 'image/png' === $mime ? @imagecreatefrompng( $src ) : ( function_exists( 'imagecreatefromwebp' ) ? @imagecreatefromwebp( $src ) : false ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			if ( ! $img ) {
				return false;
			}
			$w   = imagesx( $img );
			$h   = imagesy( $img );
			$bg  = imagecreatetruecolor( $w, $h );
			imagefill( $bg, 0, 0, imagecolorallocate( $bg, 255, 255, 255 ) );
			imagecopy( $bg, $img, 0, 0, 0, 0, $w, $h );
			$ok = imagejpeg( $bg, $dest, 95 );
			imagedestroy( $img );
			imagedestroy( $bg );
			return $ok;
		}
		return false;
	}

	/** Remove EXIF/GPS etc. (GD output never contains it; Imagick may). */
	private static function strip_metadata( $path ) {
		if ( ! extension_loaded( 'imagick' ) || ! class_exists( 'Imagick' ) ) {
			return;
		}
		try {
			$im       = new Imagick( $path );
			$profiles = $im->getImageProfiles( 'icc', true );
			$im->stripImage();
			if ( ! empty( $profiles['icc'] ) ) {
				$im->profileImage( 'icc', $profiles['icc'] ); // Keep colour profile for accurate printing.
			}
			$im->writeImage( $path );
			$im->clear();
		} catch ( Exception $e ) {
			Memento_PZ_Logger::debug( 'Metadata strip failed: ' . get_class( $e ) );
		}
	}

	private static function cleanup( ...$paths ) {
		foreach ( $paths as $p ) {
			if ( $p && is_file( $p ) ) {
				@unlink( $p ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			}
		}
	}

	/** Customer-safe error messages. Internal details go to the log only. */
	public static function error( $code ) {
		$messages = [
			'no_file'         => __( 'We didn\'t receive a photo. Please try again.', 'memento-personalizer' ),
			'bad_type'        => __( 'Please upload a JPG, PNG or WebP photo.', 'memento-personalizer' ),
			'corrupt'         => __( 'That file doesn\'t look like a valid photo. Please choose another.', 'memento-personalizer' ),
			/* translators: %d: max megabytes */
			'too_large'       => sprintf( __( 'That photo is too large. The maximum is %dMB.', 'memento-personalizer' ), Memento_PZ_Settings::int( 'max_file_mb' ) ),
			'too_many_pixels' => __( 'That photo\'s dimensions are too large to process. Please choose a smaller version.', 'memento-personalizer' ),
			/* translators: %d: minimum pixel size */
			'too_small'       => sprintf( __( 'That photo is too small to print well (needs at least %1$d×%1$dpx). Please choose a larger photo.', 'memento-personalizer' ), Memento_PZ_Settings::int( 'min_px_reject' ) ),
			'save_failed'     => __( 'We couldn\'t save your photo. Please try again.', 'memento-personalizer' ),
		];
		return new WP_Error( $code, $messages[ $code ] ?? $messages['save_failed'] );
	}
}
