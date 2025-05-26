(function(jQuery) {
    function normalizeValues(value) {
        if(
            value === false ||
            value == null ||
            value === '' ||
            Number.isNaN(value) ||
            value === 0 ||
            value === Infinity || value === -Infinity
        ) {
            value = '';
        }
        return value
            .trim()
            .replace(/\s+/g, ' ')
            .split(' ')
            .filter(Boolean);
    }

    jQuery.extend(jQuery.fn, {
        addData     : function(attribute, value) {
            return this.each(function(i, item) {
                const $item = jQuery(item);
                let data = new Set(normalizeValues($item.attr('data-'+ attribute)));
                let values = normalizeValues(value);
        
                values.forEach(function(val) {
                    data.add(val);
                });
                
                $item.attr('data-'+ attribute, Array.from(data).join(' '));
            });
        },
        eraseData  : function(attribute, value) {
            return this.each(function(i, item) {
                const $item = jQuery(item);
                let data = new Set(normalizeValues($item.attr('data-' + attribute)));
                let values = normalizeValues(value);
        
                values.forEach(function(val) {
                    data.delete(val); // Удаляем напрямую из Set
                });
        
                if (data.size) {
                    $item.attr('data-' + attribute, Array.from(data).join(' '));
                }
                else {
                    $item.removeAttr('data-' + attribute);
                }
            });
        },
        toggleData  : function(attribute, value) {
            return this.each(function(i, item) {
                const $item = jQuery(item);
                let values = normalizeValues(value);
        
                values.forEach(function(val) {
                    if($item.hasData(attribute, val)) {
                        $item.eraseData(attribute, val); // Удаляем, если есть
                    }
                    else {
                        $item.addData(attribute, val); // Добавляем, если нет
                    }
                });
            });
        },
        hasData     : function(attribute, value) {
            let has_data = false;

            this.each(function(i, item) {
                const $item = jQuery(item);
                let data = new Set(normalizeValues($item.attr('data-' + attribute))); // Убираем пустые строки

                let values = normalizeValues(value); // Убираем пустые строки из переданных значений

                has_data = values.every(function(val) {
                    return data.has(val);
                });
                
                if(has_data) {
                    return false; // Прерываем .each(), если нашли
                }
            });

            return has_data;
        },
    });
})(jQuery);