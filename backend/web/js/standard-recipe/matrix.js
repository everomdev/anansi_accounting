$(function () {
    const ctx = document.getElementById('bcgChart');

    new Chart(ctx, {
        type: 'bubble',
        data: {
            datasets: [{
                label: '% Ventas',
                data: chartData,
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
                                currency: currencySymbol
                            })
                            if (x == popularityAxis && y == costEffectivenessAxis) {
                                const limit = `Límite: \n- Popularidad: ${formatter.format(costEffectivenessAxis)} \n- Margen de Contribución: ${costEffectivenessAxis}%`;

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
                                currency: currencySymbol
                            }).format(value);
                        }
                    },
                    min: 0
                },

            }
        }
    })
})

$(document).on('change', '#category', function (event) {
    event.preventDefault();
    const _this = $(this);
    let url = _this.data('url');
    let val = _this.val();

    if (val.length === 0) {
        url += "?type=all";
    } else {
        url += "?type=" + val;
    }

    window.location.href = url;

    return false;
})
