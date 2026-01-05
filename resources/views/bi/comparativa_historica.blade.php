@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">Comparativa Histórica de Ciclos</h1>
            <p class="mb-4">Compare dos ciclos de siembra finalizados lado a lado</p>
            
            <!-- Panel de Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Filtros de Comparación</h5>
                </div>
                <div class="card-body">
                    <form id="form-comparativa">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="ciclo_a">Ciclo A:</label>
                                <select class="form-control" id="ciclo_a" name="ciclo_a" required>
                                    <option value="">Seleccione ciclo A</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="ciclo_b">Ciclo B:</label>
                                <select class="form-control" id="ciclo_b" name="ciclo_b" required>
                                    <option value="">Seleccione ciclo B</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="tipo_dato">Tipo de Dato:</label>
                                <select class="form-control" id="tipo_dato" name="tipo_dato">
                                    <option value="humedad_cama1">Humedad Cama 1</option>
                                    <option value="humedad_cama2">Humedad Cama 2</option>
                                    <option value="consumo_agua">Consumo Agua</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2" id="div_tipo_riego" style="display: none;">
                                <label for="tipo_riego">Tipo de Riego:</label>
                                <select class="form-control" id="tipo_riego" name="tipo_riego">
                                    <option value="manual">Riego Manual</option>
                                    <option value="valvula">Válvula</option>
                                    <option value="total">Total (Ambos)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-chart-line"></i> Comparar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Panel de Resultados -->
            <div id="panel-resultados" style="display: none;">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 id="titulo-comparativa">Resultados de Comparación</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 id="info-ciclo-a"></h6>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 id="info-ciclo-b"></h6>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <canvas id="grafica-comparativa" height="100"></canvas>
                                    </div>
                                </div>
                                
                                <div class="row mt-4" id="panel-estadisticas" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Promedios Ciclo A</h6>
                                                <p id="estadistica-a"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Promedios Ciclo B</h6>
                                                <p id="estadistica-b"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Panel de Mensajes -->
            <div id="panel-mensajes" style="display: none;">
                <div class="alert alert-danger" id="mensaje-error"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let graficaComparativa = null;
    
    $(document).ready(function() {
        // Cargar ciclos finalizados
        cargarCiclosFinalizados();
        
        // Event listeners
        $('#form-comparativa').submit(function(e) {
            e.preventDefault();
            compararCiclos();
        });
        
        // Mostrar/ocultar selector de tipo de riego
        $('#tipo_dato').change(function() {
            if ($(this).val() === 'consumo_agua') {
                $('#div_tipo_riego').show();
            } else {
                $('#div_tipo_riego').hide();
            }
        });
    });
    
    // Cargar ciclos finalizados
    function cargarCiclosFinalizados() {
        $.ajax({
            url: '/comparativa/ciclos-finalizados',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#ciclo_a').html('<option value="">Seleccione ciclo A</option>' + response.options);
                    $('#ciclo_b').html('<option value="">Seleccione ciclo B</option>' + response.options);
                } else {
                    mostrarError('Error al cargar ciclos: ' + response.error);
                }
            },
            error: function(xhr, status, error) {
                mostrarError('Error de conexión al cargar ciclos');
                console.log('Error:', error);
            }
        });
    }
    
    // Comparar ciclos
    function compararCiclos() {
        const formData = {
            ciclo_a: $('#ciclo_a').val(),
            ciclo_b: $('#ciclo_b').val(),
            tipo_dato: $('#tipo_dato').val(),
            tipo_riego: $('#tipo_riego').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Validar que sean ciclos diferentes
        if (formData.ciclo_a === formData.ciclo_b) {
            mostrarError('Debe seleccionar ciclos diferentes para comparar');
            return;
        }
        
        $.ajax({
            url: '/comparativa/comparar',
            method: 'POST',
            data: formData,
            beforeSend: function() {
                $('#panel-mensajes').hide();
                // Mostrar indicador de carga
                $('#titulo-comparativa').html('<i class="fas fa-spinner fa-spin"></i> Generando comparativa...');
            },
            success: function(response) {
                if (response.success) {
                    mostrarResultados(response);
                } else {
                    mostrarError(response.error);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Error al comparar ciclos';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                mostrarError(errorMessage);
                console.log('Error:', error);
            }
        });
    }
    
    // Mostrar resultados
    function mostrarResultados(datos) {
        // Actualizar información de ciclos
        $('#info-ciclo-a').html(`<strong>${datos.ciclo_a_nombre}</strong><br>${datos.ciclo_a_fechas}`);
        $('#info-ciclo-b').html(`<strong>${datos.ciclo_b_nombre}</strong><br>${datos.ciclo_b_fechas}`);
        
        // Actualizar título
        let titulo = 'Comparativa: ';
        switch(datos.tipo_dato) {
            case 'humedad_cama1':
                titulo += 'Humedad Cama 1';
                break;
            case 'humedad_cama2':
                titulo += 'Humedad Cama 2';
                break;
            case 'consumo_agua':
                titulo += 'Consumo de Agua';
                if (datos.tipo_riego === 'manual') titulo += ' (Riego Manual)';
                else if (datos.tipo_riego === 'valvula') titulo += ' (Válvula)';
                else titulo += ' (Total)';
                break;
        }
        $('#titulo-comparativa').text(titulo);
        
        // Crear gráfica
        crearGrafica(datos);
        
        // Mostrar estadísticas si es humedad
        if (datos.tipo_dato.includes('humedad')) {
            $('#estadistica-a').html(`Promedio: <strong>${datos.datos.ciclo_a_promedio}%</strong>`);
            $('#estadistica-b').html(`Promedio: <strong>${datos.datos.ciclo_b_promedio}%</strong>`);
            $('#panel-estadisticas').show();
        } else if (datos.tipo_dato === 'consumo_agua') {
            $('#estadistica-a').html(`Total: <strong>${datos.datos.ciclo_a_total} litros</strong>`);
            $('#estadistica-b').html(`Total: <strong>${datos.datos.ciclo_b_total} litros</strong>`);
            $('#panel-estadisticas').show();
        } else {
            $('#panel-estadisticas').hide();
        }
        
        // Mostrar panel de resultados
        $('#panel-resultados').show();
    }
    
    // Crear gráfica
    function crearGrafica(datos) {
        // Destruir gráfica anterior si existe
        if (graficaComparativa) {
            graficaComparativa.destroy();
        }
        
        const ctx = document.getElementById('grafica-comparativa').getContext('2d');
        
        // Configurar colores según el tipo de dato
        let colorA, colorB, unidad;
        if (datos.tipo_dato.includes('humedad')) {
            colorA = 'rgb(54, 162, 235)'; // Azul
            colorB = 'rgb(255, 99, 132)'; // Rojo
            unidad = '%';
        } else {
            colorA = 'rgb(75, 192, 192)'; // Verde
            colorB = 'rgb(255, 159, 64)'; // Naranja
            unidad = 'L';
        }
        
        graficaComparativa = new Chart(ctx, {
            type: 'line',
            data: {
                labels: datos.datos.etiquetas,
                datasets: [{
                    label: datos.ciclo_a_nombre,
                    data: datos.datos.ciclo_a_datos,
                    borderColor: colorA,
                    backgroundColor: colorA.replace(')', ', 0.1)').replace('rgb', 'rgba'),
                    tension: 0.1,
                    fill: false
                }, {
                    label: datos.ciclo_b_nombre,
                    data: datos.datos.ciclo_b_datos,
                    borderColor: colorB,
                    backgroundColor: colorB.replace(')', ', 0.1)').replace('rgb', 'rgba'),
                    tension: 0.1,
                    fill: false
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
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y + unidad;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: datos.tipo_dato.includes('humedad') ? 'Humedad (%)' : 'Litros'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Día del Ciclo'
                        }
                    }
                }
            }
        });
    }
    
    // Mostrar mensaje de error
    function mostrarError(mensaje) {
        $('#mensaje-error').text(mensaje);
        $('#panel-mensajes').show();
        $('#panel-resultados').hide();
        $('#titulo-comparativa').text('Resultados de Comparación');
    }
</script>
@endsection