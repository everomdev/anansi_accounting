$(function(){
    let dualListbox = new DualListbox('#menu-_recipes', {
        availableTitle,
        selectedTitle,
        addButtonText,
        addAllButtonText,
        removeButtonText,
        removeAllButtonText,
        searchPlaceholder,
    });
    dualListbox.searchLists = searchLists;
    $(".dual-listbox__search").addClass("form-control");

    dualListbox.add_button.setAttribute("data-type", "add");
    dualListbox.add_all_button.setAttribute("data-type", "add-all");
    dualListbox.remove_button.setAttribute("data-type", "remove");
    dualListbox.remove_all_button.setAttribute("data-type", "remove-all");

    $(".dual-listbox__item").on('click', function(){
        let selectedCount = $(".dual-listbox__item--selected").length;
        let isSelectedList = $(this).parent().hasClass("dual-listbox__selected");
        if(selectedCount > 0) {
            if(!isSelectedList) {
                $("button[data-type='add']").attr("style", "background-color: #fca311 !important;");
                $("button[data-type='add-all']").attr("style", "background-color: #fca311 !important;");
                $("button[data-type='remove']").removeAttr("style");
                $("button[data-type='remove-all']").removeAttr("style");
            }else{
                $("button[data-type='remove']").attr("style", "background-color: #fca311 !important;");
                $("button[data-type='remove-all']").attr("style", "background-color: #fca311 !important;");
                $("button[data-type='add']").removeAttr("style");
                $("button[data-type='add-all']").removeAttr("style");
            }
        }else{
            if(!isSelectedList) {
                $("button[data-type='add']").removeAttr("style");
                $("button[data-type='add-all']").removeAttr("style");
            }else{
                $("button[data-type='remove']").removeAttr("style");
                $("button[data-type='remove-all']").removeAttr("style");
            }
        }
    });
});

function searchLists(searchString, dualListbox) {
    let items = dualListbox.querySelectorAll(`.dual-listbox__item`);
    let lowerCaseSearchString = searchString.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, '');

    for (let i = 0; i < items.length; i++) {
        let item = items[i];
        let itemText = item.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, '');
        if (itemText.indexOf(lowerCaseSearchString) === -1) {
            item.style.display = "none";
        } else {
            item.style.display = "list-item";
        }
    }
}
