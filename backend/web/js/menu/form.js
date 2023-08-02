$(function(){
    let dlb1 = new DualListbox('#menu-_recipes', {
        availableTitle,
        selectedTitle,
        addButtonText,
        addAllButtonText,
        removeButtonText,
        removeAllButtonText,
        searchPlaceholder,
    });
    $(".dual-listbox__search").addClass("form-control");
});
