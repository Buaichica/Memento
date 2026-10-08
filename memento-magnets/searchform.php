<?php
/**
 * Search form (header search panel, search results page, 404 page).
 *
 * @package memento-magnets
 */

$memento_search_id = 'search-field-' . wp_unique_id();
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label class="sr-only" for="<?php echo esc_attr( $memento_search_id ); ?>"><?php esc_html_e( 'Search the shop', 'memento-magnets' ); ?></label>
    <div class="search-form__field">
        <svg class="search-form__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="20" height="20" aria-hidden="true">
            <circle cx="11" cy="11" r="7.5"/>
            <path d="m20 20-3.6-3.6"/>
        </svg>
        <input
            type="search"
            id="<?php echo esc_attr( $memento_search_id ); ?>"
            class="search-field"
            placeholder="<?php esc_attr_e( 'Search magnets, gift ideas, help…', 'memento-magnets' ); ?>"
            value="<?php echo esc_attr( get_search_query() ); ?>"
            name="s"
            autocomplete="off"
            spellcheck="false"
        >
        <button type="submit" class="search-submit">
            <?php esc_html_e( 'Search', 'memento-magnets' ); ?>
        </button>
    </div>
</form>
