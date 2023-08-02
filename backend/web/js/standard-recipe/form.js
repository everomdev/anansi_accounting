
    $(document).ready(function() {
    function drawArrows() {
        $(".arrow").remove(); // Eliminar flechas previas
        let currentRow = 0;
        let rowTop = 0;
        let rowNodes = [];

        $(".node").each(function(index) {
            if (index > 0 && $(this).position().top > rowTop) {
                // Nueva fila de nodos
                if (currentRow % 2 === 1) {
                    rowNodes.reverse(); // Invertir el orden de los nodos
                }
                positionNodesInRow(rowNodes);
                rowNodes = [];
                currentRow++;
                rowTop = $(this).position().top;
            }
            rowNodes.push(this);
        });

        // Posicionar nodos en la última fila
        if (currentRow % 2 === 1) {
            rowNodes.reverse(); // Invertir el orden de los nodos
        }
        positionNodesInRow(rowNodes);
    }

    function positionNodesInRow(nodes) {
    if (nodes.length <= 1) return;

    let prevNode = $(nodes[0]);
    for (let i = 1; i < nodes.length; i++) {
    const currentNode = $(nodes[i]);
    const nodeLeft = prevNode.position().left + prevNode.outerWidth();
    const nodeTop = currentNode.position().top;
    const arrow = $("<div class='arrow'></div>").appendTo(".diagram");
    arrow.css("left", nodeLeft);
    arrow.css("top", nodeTop + currentNode.outerHeight() / 2);
    arrow.width(currentNode.position().left - nodeLeft);
    prevNode = currentNode;
}
}

    drawArrows(); // Dibujar flechas iniciales

    // Vuelve a dibujar las flechas cuando se cambie el tamaño de la ventana
    $(window).resize(function() {
    drawArrows();
});
});

$(document).on("filebeforedelete", "#stepsImagesInput", function(event, key, data){
    var aborted = !window.confirm('Are you sure you want to delete this image?');
    return aborted;
});

$(document).on("filedeleted", "#stepsImagesInput", function(event, key, data){
    var aborted = !window.confirm('Are you sure you want to delete this image?');
    return aborted;
});


