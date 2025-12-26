$(function () {
    let chart = null; // Variable para almacenar la instancia del gráfico

    // Inicializar el gráfico cuando el modal se muestra
    $('#modal-bcg').on('show.bs.modal', function () {
        const ctx = document.getElementById('bcgChart');
        if (ctx) {
            // Destruir gráfico anterior si existe
            if (chart) {
                chart.destroy();
            }
            // Filtrar datos válidos
            const validData = chartData.filter(point => 
                point && 
                typeof point.x === 'number' && !isNaN(point.x) && 
                typeof point.y === 'number' && !isNaN(point.y) && 
                typeof point.r === 'number' && !isNaN(point.r)
            );
            chart = new Chart(ctx, {
                type: 'bubble',
                data: {
                    datasets: [{
                        label: '% Ventas',
                        data: validData,
                        backgroundColor: 'rgb(247,214,14)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const x = context.parsed.x;
                                    const y = context.parsed.y;
                                    const formatter = new Intl.NumberFormat(locale, {
                                        style: 'currency',
                                        currency: currencyCode
                                    })
                                    if (x == popularityAxis && y == costEffectivenessAxis) {
                                        const limit = `Límite: \n- Popularidad: ${popularityAxis}% \n- Margen de Contribución: ${formatter.format(costEffectivenessAxis)}`;

                                        return limit;
                                    } else {
                                        let label = context.dataset.label || '';

                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += y.toString() + " % | ";

                                            label += formatter.format(x);
                                        }
                                        return label;
                                    }
                                },
                            }
                        },
                    },
                    scales: {
                        y: {
                            ticks: {
                                // Include a dollar sign in the ticks
                                callback: function (value, index, ticks) {
                                    return value + '%';
                                }
                            },
                            min: 0
                        },
                        x: {
                            ticks: {
                                // Include a dollar sign in the ticks
                                callback: function (value, index, ticks) {
                                    return new Intl.NumberFormat(locale, {
                                        style: 'currency',
                                        currency: currencyCode
                                    }).format(value);
                                }
                            },
                            min: 0
                        },

                    }
                }
            });
        }
    });

    // Destruir el gráfico cuando el modal se oculta para evitar problemas
    $('#modal-bcg').on('hidden.bs.modal', function () {
        if (chart) {
            chart.destroy();
            chart = null;
        }
    });
})

$(document).on('change', '#category', function (event) {
    event.preventDefault();
    const _this = $(this);
    let url = _this.data('url');
    let val = _this.val();
    let year = $('#year-select').val() || '';

    if (val.length === 0) {
        url += "?type=all";
    } else {
        url += "?type=" + val;
    }
    
    // Añadir el año seleccionado si existe
    if (year.length > 0) {
        url += "&year=" + year;
    }

    window.location.href = url;

    return false;
})
