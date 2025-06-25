jQuery(document).on('DOMContentLoaded', function() {
    setGlobal('flags.tab', true);
    jQuery(document)
        .on('click', '[data-tab]', toggleTab);
});

function toggleTab(eventObject) {
    eventObject.preventDefault();
    
    const $this_tab = jQuery(eventObject.currentTarget);
    
    if(
        globals.flags.tab && 
        !$this_tab.hasData('status', 'active') && 
        !$this_tab.hasData('status', 'disabled')
    ) {
        setGlobal('flags.tab', false);

        const $this_tabs        = $this_tab
            .closest('[data-tabs]');
        const $this_tab_box     = jQuery('[data-tabs-box="'+ $this_tabs.attr('data-tabs') +'"] [data-tab-box="'+ $this_tab.attr('data-tab') +'"]');
        const $active_tab_box   = jQuery('[data-tabs-box="'+ $this_tabs.attr('data-tabs') +'"]')
            .find('[data-tab-box][data-status~="active"]');

        const $other_tabs       = $this_tabs
            .find('[data-tab]')
            .not($this_tab);
        
        $other_tabs
            .eraseData('status', 'active');
        $this_tab
            .addData('status', 'active');
        
        if($active_tab_box.length) {
            $active_tab_box
                .fadeOut(300, function() {
                    jQuery(this)
                        .eraseData('status', 'active');

                    $this_tab_box
                        .addData('status', 'active')
                        .fadeIn(300, function() {
                            setGlobal('flags.tab', true);
                        });
                });
        }
        else {
            setGlobal('flags.tab', true);
        }
    }
}