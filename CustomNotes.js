$(document).ready(function(){
    var $form = $("#ModuleEditForm"); 

    // Check if $form exist to prevent unespected display behavieours out of the module
    if (typeof $form[0] !== 'undefined') {
        // remove scripts, because they've already been executed since we are manipulating the DOM below (WireTabs)
        // which would cause any scripts to get executed twice
        $form.find("script").remove();
        $form.WireTabs({
            items: $(".CN-WireTab"),
            // skipRememberTabIDs: ['ProcessModuleNameTabID'],// if you need this option
            rememberTabs: true// if you need it
        });
    }
});