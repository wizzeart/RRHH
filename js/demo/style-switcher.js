/* Style Switcher
 * Author: HtmlCoder
 * Author URI:http://www.htmlcoder.me
 * Author e-mail:htmlcoder.me@gmail.com
 * Version:1.2.0
 * Created:20 May 2014
 * Updated:19 Oct 2014
 * File Description: Style Switcher
 */

jQuery(document).ready(function ($) {

    // switch colors
    $('.style-switcher .styleChange li').on('click', function () {
        var $this = $(this),
                tp_stylesheet_no_cookie = $this.data('style');
        $(".style-switcher .styleChange .selected").removeClass("selected");
        $this.addClass("selected");
    });
});    	