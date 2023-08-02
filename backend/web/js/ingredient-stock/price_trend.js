$(document).on('change', "#categoryId", function (event) {
    event.preventDefault();
    let _this = $(this);
    if(_this.val() === ''){
        return;
    }
    $("#ingredientId").val('');
    $("#ingredientId").trigger('change');


});
$(document).on('change', "#ingredientId", function (event) {
    event.preventDefault();
    let _this = $(this);
    if(_this.val() === ''){
        return;
    }
    $("#categoryId").val('');
    $("#categoryId").trigger('change');


});


const ctx = document.getElementById('priceTrend');

new Chart(ctx, {
    type: 'line',
    data: {
        labels,
        datasets: [{
            label: 'Price Trend',
            data: prices,
            borderWidth: 1,
            backgroundColor: 'rgb(105, 108,255, 1)',
            borderColor: 'rgb(105, 108,255, 1)',
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function (context) {
                        let label = context.dataset.label || '';

                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('en-US', {
                                style: 'currency',
                                currency: 'USD'
                            }).format(context.parsed.y);
                        }
                        return label;
                    },
                }
            }
        }
    },

});
