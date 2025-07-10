<?php
class CarouselBuilderTest extends WP_UnitTestCase {
    function test_filter_removes_html_body() {
        $html  = '<img src="a.jpg" /><img src="b.jpg" /><img src="c.jpg" />';
        $output = apply_filters( 'the_content', $html );
        $this->assertStringNotContainsString( '<html', $output );
        $this->assertStringNotContainsString( '<body', $output );
    }

    function test_two_images_create_carousel() {
        $html  = '<img src="a.jpg" /><img src="b.jpg" />';
        $output = apply_filters( 'the_content', $html );
        $this->assertStringContainsString( 'kacb-carousel', $output );
    }

    function test_width_shortcode_applies_style() {
        $html  = '[kacb width="40%"]<img src="a.jpg" /><img src="b.jpg" />';
        $output = apply_filters( 'the_content', $html );
        $this->assertStringContainsString( 'width:40%', $output );
    }

    function test_style_shortcode_applies_inline() {
        $html  = '[kacb style="float:right"]<img src="a.jpg" /><img src="b.jpg" />';
        $output = apply_filters( 'the_content', $html );
        $this->assertStringContainsString( 'float:right', $output );
    }
}
