jQuery(document).on('DOMContentLoaded', function() {
    jQuery(document)
        .on('click', '[data-expander] .expander__head', toggleExpander)
        .on('click', '[data-accordion] .accordion__head', toggleAccordion);
});

function toggleExpander(eventObject) {
    const $this_expander_head   = jQuery(eventObject.currentTarget);
    const $this_expander        = $this_expander_head.closest('[data-expander]');
    const $this_expander_body   = $this_expander.find('.expander__body');

    const has_active            = $this_expander.hasData('status', 'active');
    const $all_expanders        = jQuery('[data-expander="'+ String($this_expander.attr('data-expander')) +'"]');

    $all_expanders.each(function(i, expander) {
        const $expander         = jQuery(expander);
        const $expander_body    = $expander.find('.expander__body');
        const this_active       = $expander.hasData('status', 'active');

        if(has_active) {
            if(this_active) {
                elementClose($expander, $expander_body);
            }
        }
        else {
            if(this_active) {
                elementClose($expander, $expander_body);
            }
            elementOpen($expander, $expander_body);
        }
    });
    // if($this_expander.hasData('status', 'active')) {
    //     elementClose($this_expander, $this_expander_body);
    // }
    // else {
    //     elementOpen($this_expander, $this_expander_body);
    // }
}

function toggleAccordion(eventObject) {
    const $this_accordion_head  = jQuery(eventObject.currentTarget);
    const $this_accordion       = $this_accordion_head.closest('.accordion');
    const $this_accordion_group = $this_accordion.closest('[data-accordion]');

    const this_index            = $this_accordion_group.find('.accordion').index($this_accordion);
    const this_status           = $this_accordion.hasData('status', 'active');

    const $all_accordion_groups = jQuery('[data-accordion="'+ String($this_accordion_group.attr('data-accordion')) +'"]');
    
    $all_accordion_groups.each(function(i, accordion_group) {
        const $accordion_group = jQuery(accordion_group);
        const $active_accordions = $accordion_group.find('.accordion[data-status~="active"]');
        
        $active_accordions.each(function(i, accordion) {
            const $accordion = jQuery(accordion);
            const $accordion_body = $accordion.find('.accordion__body');

            elementClose($accordion, $accordion_body)
        });

        if(!this_status) {
            const $target_accordion = $accordion_group.find('.accordion').eq(this_index);
            const $target_accordion_body = $target_accordion.find('.accordion__body');

            elementOpen($target_accordion, $target_accordion_body);
        }
    });
}

function elementOpen($element, $element_body) {
    $element.addData('status', 'opening');
    $element_body.slideDown(300, function() {
        $element.eraseData('status', 'opening').addData('status', 'active');
    });
}

function elementClose($element, $element_body) {
    $element.addData('status', 'closing').eraseData('status', 'active');
    $element_body.slideUp(300, function() {
        $element.eraseData('status', 'closing');
    });
}