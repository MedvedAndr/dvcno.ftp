jQuery(document).on('DOMContentLoaded', function() {
    setGlobal('flags.tab', true);
    jQuery(document)
        .on('click', '[data-tab]', toggleTab)
        .on('change', '[data-radio-tab]', toggleRadioTab);
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

function toggleRadioTab(eventObject) {
    const $this_radio = jQuery(eventObject.currentTarget);
    const $this_radios = $this_radio.closest('[data-radio-tabs]');

    const $this_box = jQuery('[data-radio-tabs-box="'+ String($this_radios.attr('data-radio-tabs')) +'"]');
    const $check_box = $this_box.find('[data-radio-tab-box="'+ String($this_radio.attr('data-radio-tab')) +'"]');
    const $active_box = $this_box.find('[data-status~="active"]');
    
    $active_box.eraseData('status', 'active');
    $check_box.addData('status', 'active');
}