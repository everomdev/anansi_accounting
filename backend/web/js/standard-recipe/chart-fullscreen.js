document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que los gráficos se inicialicen completamente
    setTimeout(function() {
        // Referencia al modal
        const fullscreenModal = document.getElementById('chartFullscreenModal');
        if (!fullscreenModal) return; // Salir si no existe el modal
        
        const bsFullscreenModal = new bootstrap.Modal(fullscreenModal);
        const fullscreenContainer = document.getElementById('fullscreen-chart-container');
        const modalTitle = document.getElementById('chartFullscreenModalLabel');
        
        // Almacenar referencia al gráfico original
        let originalCanvas = null;
        let originalChart = null;
        let clonedCanvas = null;
        
        // Capturar los clics en botones de pantalla completa
        document.querySelectorAll('.fullscreen-btn').forEach(button => {
            button.addEventListener('click', function() {
                const chartId = this.getAttribute('data-chart-id');
                
                // Buscar el título de manera más segura
                let chartTitle = 'Detalle del gráfico';
                const cardHeader = this.closest('.card-header');
                if (cardHeader) {
                    const titleElement = cardHeader.querySelector('.card-title');
                    if (titleElement) {
                        chartTitle = titleElement.textContent || chartTitle;
                    }
                }
                
                // Actualizar el título del modal
                if (modalTitle) {
                    modalTitle.textContent = chartTitle;
                }
                
                // Guardar referencias al canvas y chart original
                originalCanvas = document.getElementById(chartId);
                
                if (!originalCanvas) {
                    console.error('No se encontró el canvas con ID:', chartId);
                    return;
                }
                
                // Si hay un Chart.js asociado al canvas
                if (window.Chart) {
                    try {
                        // Intentar obtener la instancia del gráfico
                        originalChart = Chart.getChart(originalCanvas);
                        
                        // Limpiar el contenedor del modal
                        if (fullscreenContainer) {
                            fullscreenContainer.innerHTML = '';
                        }
                        
                        // Crear un nuevo canvas
                        clonedCanvas = document.createElement('canvas');
                        clonedCanvas.id = 'fullscreen-' + chartId;
                        clonedCanvas.style.width = '100%';
                        clonedCanvas.style.height = '80vh';
                        
                        // Añadir el canvas al modal
                        if (fullscreenContainer) {
                            fullscreenContainer.appendChild(clonedCanvas);
                        }
                        
                        // Mostrar el modal
                        bsFullscreenModal.show();
                        
                        // Recrear el gráfico en la pantalla completa
                        if (originalChart) {
                            // Pequeño retraso para asegurar que el modal está visible antes de crear el gráfico
                            setTimeout(() => {
                                try {
                                    const newChart = new Chart(clonedCanvas.getContext('2d'), {
                                        type: originalChart.config.type,
                                        data: JSON.parse(JSON.stringify(originalChart.data)),
                                        options: {
                                            ...JSON.parse(JSON.stringify(originalChart.options)),
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: {
                                                legend: {
                                                    position: 'bottom',
                                                    labels: {
                                                        font: {
                                                            size: 14
                                                        },
                                                        padding: 20
                                                    }
                                                },
                                                tooltip: {
                                                    bodyFont: {
                                                        size: 14
                                                    },
                                                    titleFont: {
                                                        size: 16
                                                    }
                                                }
                                            }
                                        }
                                    });
                                } catch (error) {
                                    console.error('Error al recrear el gráfico:', error);
                                    if (fullscreenContainer) {
                                        fullscreenContainer.innerHTML = '<div class="alert alert-danger">Error al cargar el gráfico en pantalla completa</div>';
                                    }
                                }
                            }, 300);
                        } else {
                            console.warn('No se encontró una instancia de Chart.js para el canvas:', chartId);
                        }
                    } catch (error) {
                        console.error('Error al procesar el gráfico:', error);
                    }
                } else {
                    console.warn('Chart.js no está disponible');
                }
            });
        });
        
        // Limpiar al cerrar el modal
        if (fullscreenModal) {
            fullscreenModal.addEventListener('hidden.bs.modal', function() {
                if (fullscreenContainer) {
                    fullscreenContainer.innerHTML = '';
                }
                clonedCanvas = null;
            });
        }
    }, 1000); // Esperar 1 segundo para asegurar que los gráficos están inicializados
});