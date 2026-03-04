<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label class="sr-only" for="search-field-<?php echo uniqid(); ?>"><?php _e( 'Search', 'memento-magnets' ); ?></label>
    <div style="display:flex;gap:var(--space-2);">
        <input
            type="search"
            id="search-field-<?php echo uniqid(); ?>"
            class="search-field"
            placeholder="<?php esc_attr_e( 'Search magnets, blogs…', 'memento-magnets' ); ?>"
            value="<?php echo get_search_query(); ?>"
            name="s"
            aria-label="<?php esc_attr_e( 'Search', 'memento-magnets' ); ?>"
        >
        <button type="submit" class="btn btn--primary search-submit" aria-label="<?php esc_attr_e( 'Submit search', 'memento-magnets' ); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
        </button>
    </div>
</form>
