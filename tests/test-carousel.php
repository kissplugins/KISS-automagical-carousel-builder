<?php
class CarouselBuilderTest extends WP_UnitTestCase {
    function test_filter_removes_html_body() {
        $html  = '<img src="a.jpg" /><img src="b.jpg" /><img src="c.jpg" />';
        $output = apply_filters( 'the_content', $html );
        $this->assertStringNotContainsString( '<html', $output );
        $this->assertStringNotContainsString( '<body', $output );
    }
}
