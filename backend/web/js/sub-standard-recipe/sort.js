/**
 * Script para manejar la ordenación de columnas en la tabla de recetas
 */
document.addEventListener('DOMContentLoaded', function() {
    // Configuración
    const gridId = 'sub-standard-recipes-grid';
    const tableSelector = `#${gridId} table`;
    
    // Estado inicial de ordenación
    let sortState = {
        column: null,
        direction: 'asc'
    };

    // Obtener todas las columnas ordenables
    const sortableColumns = document.querySelectorAll('.sortable-column');
    
    // Añadir listeners de clic a cada columna ordenable
    sortableColumns.forEach(headerCell => {
        headerCell.addEventListener('click', function() {
            const columnName = this.getAttribute('data-sort-by');
            
            // Actualizar dirección de ordenación
            if (sortState.column === columnName) {
                sortState.direction = sortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                sortState.column = columnName;
                sortState.direction = 'asc';
            }
            
            // Ordenar la tabla
            sortTable(columnName, sortState.direction);
            
            // Actualizar indicadores visuales
            updateSortIndicators(columnName, sortState.direction);
        });
    });

    /**
     * Ordena la tabla según la columna y dirección especificadas
     */
    function sortTable(columnName, direction) {
        const table = document.querySelector(tableSelector);
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Obtener el índice de la columna a ordenar
        const headerCells = Array.from(table.querySelectorAll('thead th'));
        let columnIndex = -1;
        
        headerCells.forEach((cell, index) => {
            if (cell.classList.contains('sortable-column') && 
                cell.getAttribute('data-sort-by') === columnName) {
                columnIndex = index;
            }
        });
        
        if (columnIndex === -1) return;
        
        // Función para extraer el valor numérico de una celda
        const getCellValue = (row, index) => {
            const cell = row.querySelector(`td:nth-child(${index + 1})`);
            if (!cell) return '';
            
            let value = cell.textContent.trim();
            
            // Extraer valor numérico para diferentes formatos
            if (columnName === 'recipeLastPrice') {
                // Para valores de moneda, eliminar símbolos y formateo
                value = parseFloat(value.replace(/[^\d.,]/g, '')
                                       .replace(',', '.')) || 0;
            } else if (columnName === 'ingredientCount' || columnName === 'subRecipeCount') {
                // Para conteos de ingredientes y subrecetas, convertir a número
                value = parseInt(value) || 0;
            } else if (columnName === 'costPercent') {
                // Para porcentajes, convertir a decimal
                value = parseFloat(value.replace('%', '').trim()) / 100 || 0;
            }
            
            return value;
        };
        
        // Ordenar filas
        const sortedRows = rows.sort((a, b) => {
            const aValue = getCellValue(a, columnIndex);
            const bValue = getCellValue(b, columnIndex);
            
            // Ordenación numérica
            if (direction === 'asc') {
                return aValue - bValue;
            } else {
                return bValue - aValue;
            }
        });
        
        // Limpiar y reconstruir la tabla
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }
        
        sortedRows.forEach(row => {
            tbody.appendChild(row);
        });
    }

    /**
     * Actualiza los indicadores visuales de ordenación
     */
    function updateSortIndicators(columnName, direction) {
        // Eliminar todos los indicadores existentes
        document.querySelectorAll('.sort-indicator').forEach(el => el.remove());
        
        // Añadir el indicador a la columna activa
        const activeHeader = document.querySelector(`.sortable-column[data-sort-by="${columnName}"]`);
        if (activeHeader) {
            // Crear el indicador
            const indicator = document.createElement('span');
            indicator.className = 'sort-indicator';
            indicator.innerHTML = direction === 'asc' ? ' ▲' : ' ▼';
            
            // Añadir el indicador al encabezado
            activeHeader.appendChild(indicator);
            
            // Actualizar clases para estilos
            document.querySelectorAll('.sortable-column').forEach(col => {
                col.classList.remove('sorted-asc', 'sorted-desc');
            });
            
            activeHeader.classList.add(`sorted-${direction}`);
        }
    }
});