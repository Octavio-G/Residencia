@extends('layouts.app')

@section('title', 'Comparativa Histórica')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Comparativa Histórica de Ciclos de Siembra</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5>Filtros de Comparación</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="ciclo_a">Ciclo A:</label>
                    <select class="form-control" id="ciclo_a" name="ciclo_a">
                        <option value="">Seleccione un ciclo</option>
                        <!-- Opciones se cargarán dinámicamente -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="ciclo_b">Ciclo B:</label>
                    <select class="form-control" id="ciclo_b" name="ciclo_b">
                        <option value="">Seleccione un ciclo</option>
                        <!-- Opciones se cargarán dinámicamente -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="tipo_grafica">Tipo de Gráfica:</label>
                    <select class="form-control" id="tipo_grafica" name="tipo_grafica">
                        <option value="lineal">Lineal</option>
                        <option value="barra">Barra</option>
                        <option value="pastel">Pastel</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary form-control" id="btn-comparar-completo">
                        <i class="fas fa-sync-alt"></i> Comparar
                    </button>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <label for="tipo_dato">Tipo de Dato:</label>
                    <select class="form-control" id="tipo_dato" name="tipo_dato">
                        <option value="humedad">Humedad Promedio (Cama 1 y 2)</option>
                        <option value="humedad_cama1">Humedad Cama 1</option>
                        <option value="humedad_cama2">Humedad Cama 2</option>
                        <option value="consumo_agua">Consumo de Agua</option>
                    </select>
                </div>
                <div class="col-md-4" id="tipo_riego_container" style="display: none;">
                    <label for="tipo_riego">Tipo de Riego:</label>
                    <select class="form-control" id="tipo_riego" name="tipo_riego">
                        <option value="manual">Riego Manual</option>
                        <option value="valvula">Válvula</option>
                        <option value="ambos">Ambos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mensaje de error -->
    <div id="panel_mensajes_comparativa" style="display: none;">
        <div class="alert alert-danger" id="mensaje_error_comparativa">
            <!-- Mensaje de error se cargará aquí -->
        </div>
    </div>
    
    <!-- Gráficas de comparación (ocultas inicialmente) -->
    <div id="graficas_comparativa" style="display: none;">
        <div class="row">
            <div class="col-md-12">
                <h5>Resultados de Comparación</h5>
            </div>
        </div>
        
        <!-- Contenedor para gráfica principal -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0" id="titulo_grafica">Gráfica de Comparación</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoComparativo" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenedor para gráficas adicionales (pastel) -->
        <div class="row mb-4" id="graficas_adicionales" style="display: none;">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">Ciclo A - Distribución de Riego</h6>
                    </div>
                    <div class="card-body text-center">
                        <canvas id="graficoPastelCicloA" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Ciclo B - Distribución de Riego</h6>
                    </div>
                    <div class="card-body text-center">
                        <canvas id="graficoPastelCicloB" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Función para cargar ciclos finalizados en los dropdowns
    function cargarCiclosFinalizados() {
        $.ajax({
            url: '/bi/comparativa/ciclos-finalizados',
            method: 'GET',
            success: function(data) {
                if (data.options) {
                    $('#ciclo_a').html(data.options);
                    $('#ciclo_b').html(data.options);
                } else {
                    $('#ciclo_a').html('<option value="">No hay ciclos disponibles</option>');
                    $('#ciclo_b').html('<option value="">No hay ciclos disponibles</option>');
                }
            },
            error: function(xhr, status, error) {
                console.log('Error al cargar ciclos finalizados:', xhr.responseText);
                $('#ciclo_a').html('<option value="">Error al cargar ciclos</option>');
                $('#ciclo_b').html('<option value="">Error al cargar ciclos</option>');
            }
        });
    }

    // Mostrar/ocultar contenedor de tipo de riego según el tipo de dato seleccionado
    $('#tipo_dato').change(function() {
        var tipoDato = $(this).val();
        if (tipoDato === 'consumo_agua') {
            $('#tipo_riego_container').show();
        } else {
            $('#tipo_riego_container').hide();
        }
    });

    // Función para crear gráfica lineal
    function crearGraficaLineal(datosCicloA, datosCicloB, titulo, etiquetaCicloA, etiquetaCicloB) {
        var ctx = document.getElementById('graficoComparativo').getContext('2d');
        
        // Destruir gráfica anterior si existe
        if (window.graficoComparativo) {
            window.graficoComparativo.destroy();
        }
        
        var labels = datosCicloA.map(item => 'Día ' + item.dia);
        
        window.graficoComparativo = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: etiquetaCicloA,
                        data: datosCicloA.map(item => item.valor),
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
                    },
                    {
                        label: etiquetaCicloB,
                        data: datosCicloB.map(item => item.valor),
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Función para crear gráfica de barras
    function crearGraficaBarras(datosCicloA, datosCicloB, titulo, etiquetaCicloA, etiquetaCicloB) {
        var ctx = document.getElementById('graficoComparativo').getContext('2d');
        
        // Destruir gráfica anterior si existe
        if (window.graficoComparativo) {
            window.graficoComparativo.destroy();
        }
        
        var labels = datosCicloA.map(item => 'Día ' + item.dia);
        
        window.graficoComparativo = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: etiquetaCicloA,
                        data: datosCicloA.map(item => item.valor),
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgb(255, 99, 132)',
                        borderWidth: 1
                    },
                    {
                        label: etiquetaCicloB,
                        data: datosCicloB.map(item => item.valor),
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Función para crear gráfica de pastel
    function crearGraficaPastel(datosCicloA, datosCicloB, titulo, etiquetaCicloA, etiquetaCicloB) {
        var ctx = document.getElementById('graficoComparativo').getContext('2d');
        
        // Destruir gráfica anterior si existe
        if (window.graficoComparativo) {
            window.graficoComparativo.destroy();
        }
        
        // Calcular totales
        var totalA = datosCicloA.reduce((sum, item) => sum + item.valor, 0);
        var totalB = datosCicloB.reduce((sum, item) => sum + item.valor, 0);
        
        window.graficoComparativo = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [etiquetaCicloA, etiquetaCicloB],
                datasets: [{
                    data: [totalA, totalB],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)'
                    ],
                    borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed.toFixed(2);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Función para crear gráficas de pastel para distribución de riego
    function crearGraficasPastelDistribucion(totalesCicloA, totalesCicloB) {
        // Gráfica para Ciclo A
        var ctxA = document.getElementById('graficoPastelCicloA').getContext('2d');
        
        // Destruir gráfica anterior si existe
        if (window.graficoPastelCicloA) {
            window.graficoPastelCicloA.destroy();
        }
        
        window.graficoPastelCicloA = new Chart(ctxA, {
            type: 'pie',
            data: {
                labels: ['Riego Manual', 'Válvula'],
                datasets: [{
                    data: [totalesCicloA.manual, totalesCicloA.valvula],
                    backgroundColor: [
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(75, 192, 192, 0.7)'
                    ],
                    borderColor: [
                        'rgb(255, 159, 64)',
                        'rgb(75, 192, 192)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed.toFixed(2) + ' L';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
        
        // Gráfica para Ciclo B
        var ctxB = document.getElementById('graficoPastelCicloB').getContext('2d');
        
        // Destruir gráfica anterior si existe
        if (window.graficoPastelCicloB) {
            window.graficoPastelCicloB.destroy();
        }
        
        window.graficoPastelCicloB = new Chart(ctxB, {
            type: 'pie',
            data: {
                labels: ['Riego Manual', 'Válvula'],
                datasets: [{
                    data: [totalesCicloB.manual, totalesCicloB.valvula],
                    backgroundColor: [
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(75, 192, 192, 0.7)'
                    ],
                    borderColor: [
                        'rgb(255, 159, 64)',
                        'rgb(75, 192, 192)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed.toFixed(2) + ' L';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
        
        // Mostrar contenedor de gráficas adicionales
        $('#graficas_adicionales').show();
    }

    // Función para comparar ciclos
    function compararCiclos() {
        var cicloA = $('#ciclo_a').val();
        var cicloB = $('#ciclo_b').val();
        var tipoGrafica = $('#tipo_grafica').val();
        var tipoDato = $('#tipo_dato').val();
        var tipoRiego = $('#tipo_riego').val();
        
        if (!cicloA || !cicloB) {
            $('#mensaje_error_comparativa').text('Debe seleccionar ambos ciclos');
            $('#panel_mensajes_comparativa').show();
            return;
        }
        
        // Ocultar mensajes de error anteriores
        $('#panel_mensajes_comparativa').hide();
        
        // Mostrar indicador de carga
        $('#btn-comparar-completo').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
        
        // Determinar si se necesita obtener totales para gráficas de pastel
        var url = '/bi/comparativa/comparar';
        var data = {
            ciclo_a: cicloA,
            ciclo_b: cicloB,
            tipo_grafica: tipoGrafica,
            tipo_dato: tipoDato,
            tipo_riego: tipoRiego,
            _token: '{{ csrf_token() }}'
        };
        
        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            success: function(response) {
                // Ocultar gráficas adicionales
                $('#graficas_adicionales').hide();
                
                // Actualizar título de la gráfica
                var titulo = 'Comparación de ';
                if (tipoDato === 'humedad') {
                    titulo += 'Humedad Promedio (Cama 1 y 2)';
                } else if (tipoDato === 'humedad_cama1') {
                    titulo += 'Humedad Cama 1';
                } else if (tipoDato === 'humedad_cama2') {
                    titulo += 'Humedad Cama 2';
                } else if (tipoDato === 'consumo_agua') {
                    titulo += 'Consumo de Agua';
                }
                titulo += ' - ' + tipoGrafica.charAt(0).toUpperCase() + tipoGrafica.slice(1);
                $('#titulo_grafica').text(titulo);
                
                // Mostrar gráfica
                $('#graficas_comparativa').show();
                
                // Crear gráfica según el tipo seleccionado
                var etiquetaCicloA = response.ciclo_a.nombre;
                var etiquetaCicloB = response.ciclo_b.nombre;
                
                switch (tipoGrafica) {
                    case 'lineal':
                        crearGraficaLineal(response.ciclo_a.datos, response.ciclo_b.datos, titulo, etiquetaCicloA, etiquetaCicloB);
                        break;
                    case 'barra':
                        crearGraficaBarras(response.ciclo_a.datos, response.ciclo_b.datos, titulo, etiquetaCicloA, etiquetaCicloB);
                        break;
                    case 'pastel':
                        // Para pastel, mostramos totales
                        if (tipoDato === 'consumo_agua') {
                            // Solicitar totales específicos para distribución
                            $.ajax({
                                url: '/bi/comparativa/totales',
                                method: 'POST',
                                data: {
                                    ciclo_a: cicloA,
                                    ciclo_b: cicloB,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(totalesResponse) {
                                    crearGraficaPastel(response.ciclo_a.datos, response.ciclo_b.datos, titulo, etiquetaCicloA, etiquetaCicloB);
                                    crearGraficasPastelDistribucion(
                                        totalesResponse.ciclo_a.totales,
                                        totalesResponse.ciclo_b.totales
                                    );
                                },
                                error: function(xhr, status, error) {
                                    console.log('Error al obtener totales:', xhr.responseText);
                                    crearGraficaPastel(response.ciclo_a.datos, response.ciclo_b.datos, titulo, etiquetaCicloA, etiquetaCicloB);
                                }
                            });
                        } else {
                            crearGraficaPastel(response.ciclo_a.datos, response.ciclo_b.datos, titulo, etiquetaCicloA, etiquetaCicloB);
                        }
                        break;
                }
            },
            error: function(xhr, status, error) {
                console.log('Error al comparar ciclos:', xhr.responseText);
                var errorMessage = 'Error al comparar ciclos';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseText) {
                    errorMessage = 'Error: ' + xhr.responseText;
                }
                $('#mensaje_error_comparativa').text(errorMessage);
                $('#panel_mensajes_comparativa').show();
            },
            complete: function() {
                // Restaurar botón
                $('#btn-comparar-completo').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Comparar');
            }
        });
    }

    // Evento para el botón de comparar
    $('#btn-comparar-completo').click(function() {
        compararCiclos();
    });

    // Cargar ciclos al cargar la página
    $(document).ready(function() {
        cargarCiclosFinalizados();
    });
</script>
@endsection