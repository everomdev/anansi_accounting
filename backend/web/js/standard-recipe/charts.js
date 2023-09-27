$(function(){
    const salesByFamily = document.getElementById('salesByFamily');
    const eightyPercentSales = document.getElementById('eightyPercentSales');
    const eightyPercentPopularity = document.getElementById('eightyPercentPopularity');
    const moreProfitable = document.getElementById('moreProfitable');
    const moreFrequent = document.getElementById('moreFrequent');
    const spendTheMost = document.getElementById('spendTheMost');

    new Chart(salesByFamily, {
        type: 'bar',

        data: {
            datasets: [{
                label: '% Ventas por familia',
                data: dataSalesByFamily
            }],
            labels: labelsSalesByFamily
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const y = context.parsed.y;
                            return `${y}%`;
                        },
                    }
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 100
                },
            }
        }
    });
    new Chart(eightyPercentSales, {
        type: 'bar',
        data: {
            datasets: [{
                label: '80% de la venta',
                data: dataEightyPercentSales
            }],
            labels: labelsEightyPercentSales
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const y = context.parsed.y;
                            return `${y}%`;
                        },
                    }
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 100
                },
            }
        }
    });
    new Chart(eightyPercentPopularity, {
        type: 'bar',
        data: {
            datasets: [{
                label: '80% de popularidad',
                data: dataEightyPercentPopularity
            }],
            labels: labelsEightyPercentPopularity
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const y = context.parsed.y;
                            return `${y}%`;
                        },
                    }
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 100
                },
            }
        }
    })
    new Chart(moreProfitable, {
        type: 'bar',
        data: {
            datasets: [{
                label: '20 recetas mas rentables',
                data: dataMoreProfitable
            }],
            labels: labelsMoreProfitable
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const y = context.parsed.y;
                            return `${y}%`;
                        },
                    }
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 100
                },
            }
        }
    })
    new Chart(moreFrequent, {
        type: 'bar',
        data: {
            datasets: [{
                label: '20 insumos comprados más frecuentemente',
                data: dataMoreFrequent
            }],
            labels: labelsMoreFrequent
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const y = context.parsed.y;
                            return `${y}%`;
                        },
                    }
                },
            },
            scales: {
                y: {
                    beginAtZero: true,

                },
            }
        }
    })
    new Chart(spendTheMost, {
        type: 'bar',
        data: {
            datasets: [{
                label: '20 insumos en los que mas se gasta',
                data: dataSpendTheMost
            }],
            labels: labelsSpendTheMost
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const y = context.parsed.y;
                            return new Intl.NumberFormat(locale, {
                                style: 'currency',
                                currency: currencySymbol
                            }).format(y);
                        },
                    }
                },
            },
            scales: {
                y: {
                    beginAtZero: true,

                },
            }
        }
    })
})
