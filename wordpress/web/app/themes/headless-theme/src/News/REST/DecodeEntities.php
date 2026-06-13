<?php
namespace Gafotas\HeadlessNewsTheme\News\REST;

class DecodeEntities {
    public function register() {
        add_filter( 'rest_prepare_post', [ $this, 'decode_post_entities' ], 10, 3 );
    }

    public function decode_post_entities( $response, $post, $request ) {
        $data = $response->get_data();
        
        // Decode title
        if ( isset( $data['title']['rendered'] ) ) {
            $data['title']['rendered'] = html_entity_decode( $data['title']['rendered'], ENT_QUOTES, 'UTF-8' );
        }
        
        // Decode excerpt
        if ( isset( $data['excerpt']['rendered'] ) ) {
            $data['excerpt']['rendered'] = html_entity_decode( $data['excerpt']['rendered'], ENT_QUOTES, 'UTF-8' );
        }
        
        // Optional: decode content as well (if needed)
        if ( isset( $data['content']['rendered'] ) ) {
            $data['content']['rendered'] = html_entity_decode( $data['content']['rendered'], ENT_QUOTES, 'UTF-8' );
        }
        
        $response->set_data( $data );
        return $response;
    }
}