jQuery(document).on('DOMContentLoaded', function() {
    jQuery(document)
        .on('click', 'span.nav__item_title', function(e) {
            const $this_item_title = jQuery(e.currentTarget);
            const $this_item = $this_item_title.closest('.nav__menu_item');
            const $this_submenu = $this_item.find('.nav__submenu_items');
            
            const $other_items = $this_item.closest('.nav__body').find('.nav__menu_item').not($this_item);
            const $other_submenus = $other_items.find('.nav__submenu_items');
            
            if ($this_item.is('[data-status~="open"]')) {
                $this_item.eraseData('status', 'open');
                // this_submenu.slideUp(3000);
            }
            else {
                $other_items.eraseData('status', 'open');
                // other_submenus.slideDown(300, function() {
                    $this_item.addData('status', 'open');
                // });
            }
        });
});