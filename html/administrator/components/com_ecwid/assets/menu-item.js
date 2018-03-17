var processEcwidCategories;
jQuery(document).ready(function() {

    var $parent = null;
    if (getMenuItemPropertiesParent().length > 0) {
        $parent = getMenuItemPropertiesParent();
        prepareEcwidCategories();
        if (getStoreIdInput().val() > 0) {
            fetchEcwidCategories(jQuery('#jform_params_storeID', $parent).val());
        }
    }

    function fetchEcwidCategories(storeId) {
        getDefaultCategoryInputContainer().attr('data-ecwid-state', 'loading');

        var url = 'https://my.ecwid.com/categories.js?ownerid=' + storeId + '&callback=processEcwidCategories';
        getDefaultCategoryInputContainer()
        jQuery.ajax(url, {dataType: 'jsonp'});

    }

    function getMenuItemPropertiesParent()
    {
        var $result = jQuery('#attrib-ecwidBasic');
        if ($result.length == 0) {
            // Maybe we're run in j2.5
            $result = jQuery('#ecwidBasic-options').next('div.content');
        }

        return $result;
    }

    function getStoreIdInput()
    {
        return jQuery('#jform_params_storeID');
    }

    function getDefaultCategoryIdInput()
    {
        return jQuery('#jform_params_defaultCategory');
    }

    function getDefaultCategoryInputContainer()
    {
        var $result = getDefaultCategoryIdInput().closest('.controls');

        if ($result.length == 0) { // J25 maybe
            $result = getDefaultCategoryIdInput().parent('li');
        }

        return $result;
    }

    function getDefaultCategoryDropDown()
    {
        return jQuery('#ecwid-default-category-id-dropdown');
    }

    function prepareEcwidCategories()
    {
        getDefaultCategoryInputContainer()
            .append('<select id="ecwid-default-category-id-dropdown"></select>')
            .addClass('ecwid-default-category-id')
            .attr('data-ecwid-state', 'default')
            .append('<div id="ecwid-default-category-id-loading">Loading...</div>');

        if (typeof getDefaultCategoryDropDown().chosen != 'undefined') {
            getDefaultCategoryDropDown().chosen().change(function () {
                getDefaultCategoryIdInput().val(getDefaultCategoryDropDown().chosen().val());
            });
        } else {
            getDefaultCategoryDropDown().change(function() {
                getDefaultCategoryIdInput().val(getDefaultCategoryDropDown().val());
            });
        }
    }

    getStoreIdInput().change(function() {
        var value = jQuery(this).val().trim();
        if (parseInt(value) > 0) {
            fetchEcwidCategories(jQuery(this).val());
        } else {
            getDefaultCategoryInputContainer().attr('data-ecwid-state', 'default');
        }
    });

    processEcwidCategories = function(categories) {
        getDefaultCategoryDropDown().find('option').remove();
        getDefaultCategoryDropDown().append('<option value="0">Store root category</option>');
        addCategoryToLevel(categories, 0);
        getDefaultCategoryDropDown().val(getDefaultCategoryIdInput().val());

        getDefaultCategoryDropDown()
            .trigger('liszt:updated')  // Cuz current version of chosen is outdated
            .trigger('chosen:updated'); // Cuz joomla *may* update version of chosen in future, who knows

        getDefaultCategoryInputContainer().attr('data-ecwid-state', 'ready');
    }

    function addCategoryToLevel(categories, level) {
        for (var i = 0; i < categories.length; i++) {
            var indent = new Array(level + 1).join( '&nbsp;-&nbsp;' );
            jQuery('<option>')
                .attr('value', categories[i].id)
                .html(indent + categories[i].name)
                .appendTo(getDefaultCategoryDropDown());

            if (categories[i].sub && categories[i].sub.length > 0) {
                addCategoryToLevel(categories[i].sub, level + 1);
            }
        }
    }
});
