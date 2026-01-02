@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">Dashboard HortaView</h1>
            <p class="mb-4">Sistema de monitoreo agrícola avanzado</p>
            
            <!-- Pestañas principales -->
            <ul class="nav nav-tabs" id="biTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="indicador-salud-tab" data-toggle="tab" href="#indicador-salud" role="tab">
                        <i class="fas fa-heartbeat"></i> Indicador de Salud
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="analisis-historico-tab" data-toggle="tab" href="#analisis-historico" role="tab">
                        <i class="fas fa-chart-line"></i> Análisis Histórico
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="ciclos-siembra-tab" data-toggle="tab" href="#ciclos-siembra" role="tab">
                        <i class="fas fa-seedling"></i> Ciclos de Siembra
                    </a>
                </li>
            </ul>
            
            <!-- Contenido de las pestañas -->
            <div class="tab-content" id="biTabsContent">
                <!-- Pestaña 1: Indicador de Salud -->
                <div class="tab-pane fade show active" id="indicador-salud" role="tabpanel">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Indicador de Salud de Camas de Siembra</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="panel-camas" class="row">
                                        <!-- Paneles de camas se cargarán aquí mediante AJAX -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sección de historial de lecturas -->
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h5>Historial de Lecturas</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <form id="form-filtros-historial">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label for="filtro_cama">Seleccionar Cama:</label>
                                                        <select class="form-control" id="filtro_cama" name="cama">
                                                            <option value="ambas">Ambas Camas</option>
                                                            <option value="cama1">Cama 1</option>
                                                            <option value="cama2">Cama 2</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label for="filtro_fecha_inicio">Fecha Inicio:</label>
                                                        <input type="date" class="form-control" id="filtro_fecha_inicio" name="fecha_inicio">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label for="filtro_fecha_fin">Fecha Fin:</label>
                                                        <input type="date" class="form-control" id="filtro_fecha_fin" name="fecha_fin">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>&nbsp;</label>
                                                        <div>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fas fa-filter"></i> Filtrar
                                                            </button>
                                                            <button type="button" class="btn btn-secondary" id="btn-limpiar-filtros">
                                                                <i class="fas fa-eraser"></i> Limpiar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive mt-3">
                                        <table class="table table-striped" id="tabla-historial-lecturas">
                                            <thead>
                                                <tr>
                                                    <th>Cama</th>
                                                    <th>Humedad (%)</th>
                                                    <th>Temperatura (°C)</th>
                                                    <th>Fecha</th>
                                                    <th>Hora</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Datos se cargarán aquí -->
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div id="mensaje-no-historial" class="text-center mt-3" style="display: none;">
                                        <p>No hay lecturas registradas para los filtros seleccionados.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                

                
                <!-- Pestaña 3: Análisis Histórico -->
                <div class="tab-pane fade" id="analisis-historico" role="tabpanel">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Análisis Histórico Comparativo</h5>
                        </div>
                        <div class="card-body">
                            <form id="form-analisis">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="ciclo1">Primer Ciclo de Siembra:</label>
                                        <select class="form-control" id="ciclo1" name="ciclo1">
                                            <option value="">Seleccione un ciclo</option>
                                            <!-- Opciones se cargarán dinámicamente -->
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="ciclo2">Segundo Ciclo de Siembra:</label>
                                        <select class="form-control" id="ciclo2" name="ciclo2">
                                            <option value="">Seleccione un ciclo</option>
                                            <!-- Opciones se cargarán dinámicamente -->
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-primary form-control" id="btn-comparar">
                                            <i class="fas fa-sync-alt"></i> Comparar
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <label for="metrica">Métrica de Rendimiento:</label>
                                        <select class="form-control" id="metrica" name="metrica">
                                            <option value="humedad">Humedad del Suelo</option>
                                            <option value="temperatura">Temperatura</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                            
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <canvas id="graficoComparativo" height="100"></canvas>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-12 text-right">
                                    <button class="btn btn-success" id="btn-exportar-pdf">
                                        <i class="fas fa-file-pdf"></i> Exportar a PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pestaña 4: Ciclos de Siembra -->
                <div class="tab-pane fade" id="ciclos-siembra" role="tabpanel">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Gestión de Ciclos de Siembra</h5>
                        </div>
                        <div class="card-body">
                            <!-- Selector de Ciclos -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Seleccionar Ciclo de Siembra</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="ciclo_selector">Ciclo de Siembra:</label>
                                        <select class="form-control" id="ciclo_selector">
                                            <option value="">Seleccione un ciclo</option>
                                            <!-- Opciones se cargarán dinámicamente -->
                                        </select>
                                    </div>
                                    
                                    <button class="btn btn-primary" id="btn_cargar_datos">
                                        <i class="fas fa-sync-alt"></i> Cargar Datos
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Panel de Mensajes -->
                            <div id="panel_mensajes" style="display: none;">
                                <div class="alert alert-danger" id="mensaje_error">
                                    <!-- Mensaje de error se cargará aquí -->
                                </div>
                            </div>
                            
                            <!-- Panel de Información del Ciclo -->
                            <div id="panel_informacion" style="display: none;">
                                <div class="row">
                                    <!-- Tarjeta de Información del Ciclo -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0">Información del Ciclo</h5>
                                            </div>
                                            <div class="card-body">
                                                <h4 id="nombre_ciclo"></h4>
                                                <p><strong>Fecha de Inicio:</strong> <span id="fecha_inicio"></span></p>
                                                <p><strong>Fecha de Fin:</strong> <span id="fecha_fin"></span></p>
                                                <p><strong>Estado:</strong> <span id="estado"></span></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tarjeta de Días Transcurridos -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-info">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0">Días Transcurridos</h5>
                                            </div>
                                            <div class="card-body text-center">
                                                <h2 id="dias_transcurridos" class="display-4">0</h2>
                                                <p class="text-muted">días</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tarjeta de Días Restantes o Estado del Ciclo -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-success" id="card_dias_restantes">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">Días Restantes</h5>
                                            </div>
                                            <div class="card-body text-center">
                                                <h2 id="dias_restantes" class="display-4">0</h2>
                                                <p class="text-muted">días</p>
                                            </div>
                                        </div>
                                        <div class="card border-success d-none" id="card_ciclo_completado">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">Estado del Ciclo</h5>
                                            </div>
                                            <div class="card-body text-center">
                                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                                <h4 class="text-success">Ciclo Completado</h4>
                                                <p class="text-muted">Listo para cosecha</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tarjeta de Consumo de Agua (Riego Manual) -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-success">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">Consumo de Agua (Riego Manual)</h5>
                                            </div>
                                            <div class="card-body text-center">
                                                <h2 id="consumo_agua_total" class="display-4">0</h2>
                                                <p class="text-muted">litros totales</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tarjeta de Volumen de Agua por Valvula -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-info">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0">Volumen de Agua por Valvula</h5>
                                            </div>
                                            <div class="card-body text-center">
                                                <h2 id="volumen_agua_valvula" class="display-4">0</h2>
                                                <p class="text-muted">litros totales</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tarjeta de Estado -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning text-white">
                                                <h5 class="mb-0">Estado del Ciclo</h5>
                                            </div>
                                            <div class="card-body text-center">
                                                <div id="estado_ciclo">
                                                    <!-- El estado se cargará aquí dinámicamente -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para funcionalidad BI -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Variables globales para gráficos
    var graficoSalud, graficoSecado, graficoComparativo;
    
    // Cargar datos cuando el documento esté listo
    $(document).ready(function() {
        cargarIndicadorSalud();
        cargarCiclosSiembra();
        cargarCiclosSiembraSelector();
        
        // Cargar historial de lecturas al inicio
        cargarHistorialLecturas();
        
        // Event listeners para filtros de historial
        $('#form-filtros-historial').submit(function(e) {
            e.preventDefault();
            
            var filtros = {
                cama: $('#filtro_cama').val(),
                fecha_inicio: $('#filtro_fecha_inicio').val(),
                fecha_fin: $('#filtro_fecha_fin').val()
            };
            
            cargarHistorialLecturas(filtros);
        });
        
        $('#btn-limpiar-filtros').click(function() {
            $('#filtro_cama').val('ambas');
            $('#filtro_fecha_inicio').val('');
            $('#filtro_fecha_fin').val('');
            
            cargarHistorialLecturas();
        });
        
        // Event listeners para botones
        $('#btn-comparar').click(function() {
            compararCiclos();
        });
        
        $('#btn-exportar-pdf').click(function() {
            exportarPDF();
        });
        
        // Event listener para el botón de cargar datos de ciclos
        $('#btn_cargar_datos').click(function() {
            cargarDatosCiclo();
        });
    });
    
    // Función para cargar historial de lecturas
    function cargarHistorialLecturas(filtros = {}) {
        $.ajax({
            url: '/bi/historial-lecturas',
            method: 'GET',
            data: filtros,
            success: function(data) {
                var tbody = $('#tabla-historial-lecturas tbody');
                tbody.empty();
                
                if (data.lecturas && data.lecturas.length > 0) {
                    data.lecturas.forEach(function(lectura) {
                        // Determinar estado basado en humedad
                        var estado = '';
                        var estadoClass = '';
                        if (lectura.humedad < 30) {
                            estado = 'Crítico';
                            estadoClass = 'badge-danger';
                        } else if (lectura.humedad <= 50) {
                            estado = 'Advertencia';
                            estadoClass = 'badge-warning';
                        } else {
                            estado = 'Óptimo';
                            estadoClass = 'badge-success';
                        }
                        
                        var row = `
                            <tr>
                                <td>${lectura.cama}</td>
                                <td>${lectura.humedad}%</td>
                                <td>${lectura.temperatura || 'N/A'}</td>
                                <td>${lectura.fecha}</td>
                                <td>${lectura.hora}</td>
                                <td><span class="badge ${estadoClass}">${estado}</span></td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                    
                    $('#mensaje-no-historial').hide();
                } else {
                    $('#mensaje-no-historial').show();
                }
            },
            error: function(xhr, status, error) {
                console.log('Error al cargar historial de lecturas:', error);
                $('#mensaje-no-historial').show().html('<p class="text-center text-danger">Error al cargar los datos del historial: ' + error + '</p>');
            }
        });
    }
    
    // Función para cargar el indicador de salud
    function cargarIndicadorSalud() {
        $.ajax({
            url: '/bi/indicador-salud',
            method: 'GET',
            success: function(data) {
                // Crear paneles para cada cama
                var panelHtml = '';
                
                if (data.camas && data.camas.length > 0) {
                    data.camas.forEach(function(cama) {
                        // Determinar clase de color según el estado
                        var colorClass = '';
                        var bgColorClass = '';
                        if (cama.color === 'rojo') {
                            colorClass = 'text-danger';
                            bgColorClass = 'bg-danger';
                        } else if (cama.color === 'amarillo') {
                            colorClass = 'text-warning';
                            bgColorClass = 'bg-warning';
                        } else {
                            colorClass = 'text-success';
                            bgColorClass = 'bg-success';
                        }
                        
                        panelHtml += `
                            <div class="col-md-6 mb-4">
                                <div class="card border-${cama.color}">
                                    <div class="card-header ${bgColorClass} text-white">
                                        <h5 class="mb-0">${cama.nombre}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="${colorClass}">Estado: ${cama.estado}</h6>
                                                <p><strong>Cultivo:</strong> ${cama.cultivo}</p>
                                                <p><strong>Humedad:</strong> ${cama.humedad}%</p>
                                            </div>
                                            <div class="col-md-6 text-center">
                                                <div class="rounded-circle ${bgColorClass} d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; margin: 0 auto;">
                                                    <span class="text-white" style="font-size: 1.5rem; font-weight: bold;">${cama.humedad}%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">Última medición: ${cama.fecha} ${cama.hora}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    panelHtml = '<div class="col-md-12"><p class="text-center">No hay datos disponibles para mostrar</p></div>';
                }
                
                $('#panel-camas').html(panelHtml);
            },
            error: function(xhr, status, error) {
                console.log('Error al cargar el indicador de salud:', error);
                console.log('Status:', status);
                console.log('Response:', xhr.responseText);
                $('#panel-camas').html('<div class="col-md-12"><p class="text-center text-danger">Error al cargar los datos: ' + error + '</p></div>');
            }
        });
    }
    

    // Función para cargar ciclos de siembra
    function cargarCiclosSiembra() {
        $.ajax({
            url: '/bi/ciclos-siembra',
            method: 'GET',
            success: function(data) {
                console.log('Datos de ciclos para análisis:', data); // Depuración
                if (data.options) {
                    $('#ciclo1').html('<option value="">Seleccione un ciclo</option>' + data.options);
                    $('#ciclo2').html('<option value="">Seleccione un ciclo</option>' + data.options);
                } else {
                    $('#ciclo1').html('<option value="">No hay ciclos disponibles</option>');
                    $('#ciclo2').html('<option value="">No hay ciclos disponibles</option>');
                }
            },
            error: function(xhr, status, error) {
                console.log('Error al cargar ciclos para análisis:', xhr.responseText); // Depuración
                $('#ciclo1').html('<option value="">Error al cargar ciclos</option>');
                $('#ciclo2').html('<option value="">Error al cargar ciclos</option>');
            }
        });
    }
    
    // Función para cargar ciclos de siembra en el selector
    function cargarCiclosSiembraSelector() {
        $.ajax({
            url: '/bi/ciclos-siembra',
            method: 'GET',
            success: function(data) {
                console.log('Datos de ciclos:', data); // Depuración
                if (data.options) {
                    $('#ciclo_selector').html('<option value="">Seleccione un ciclo</option>' + data.options);
                } else {
                    $('#ciclo_selector').html('<option value="">No hay ciclos disponibles</option>');
                }
            },
            error: function(xhr, status, error) {
                console.log('Error al cargar ciclos:', xhr.responseText); // Depuración
                $('#ciclo_selector').html('<option value="">Error al cargar ciclos</option>');
            }
        });
    }
    
    // Función para comparar ciclos
    function compararCiclos() {
        var ciclo1 = $('#ciclo1').val();
        var ciclo2 = $('#ciclo2').val();
        var metrica = $('#metrica').val();
        
        if (!ciclo1 || !ciclo2) {
            alert('Por favor seleccione ambos ciclos para comparar');
            return;
        }
        
        $.ajax({
            url: '/bi/comparar-ciclos',
            method: 'POST',
            data: {
                ciclo1: ciclo1,
                ciclo2: ciclo2,
                metrica: metrica,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                // Crear gráfico comparativo
                if (graficoComparativo) graficoComparativo.destroy();
                
                var ctx = document.getElementById('graficoComparativo').getContext('2d');
                graficoComparativo = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.etiquetas,
                        datasets: [{
                            label: data.nombre_ciclo1,
                            data: data.datos_ciclo1,
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.1
                        }, {
                            label: data.nombre_ciclo2,
                            data: data.datos_ciclo2,
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            tension: 0.1
                        }]
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
        });
    }
    
    // Función para exportar a PDF
    function exportarPDF() {
        var ciclo1 = $('#ciclo1 option:selected').text();
        var ciclo2 = $('#ciclo2 option:selected').text();
        var metrica = $('#metrica option:selected').text();
        
        if (!ciclo1 || !ciclo2 || ciclo1 === 'Seleccione un ciclo' || ciclo2 === 'Seleccione un ciclo') {
            alert('Por favor seleccione ambos ciclos antes de exportar');
            return;
        }
        
        window.open('/bi/exportar-pdf?ciclo1=' + $('#ciclo1').val() + '&ciclo2=' + $('#ciclo2').val() + '&metrica=' + $('#metrica').val(), '_blank');
    }
    
    // Función para cargar datos del ciclo seleccionado
    function cargarDatosCiclo() {
        var cicloId = $('#ciclo_selector').val();
        
        console.log('Cargando datos para ciclo ID:', cicloId); // Depuración
        
        if (!cicloId) {
            alert('Por favor seleccione un ciclo de siembra');
            return;
        }
        
        // Ocultar paneles anteriores
        $('#panel_informacion').hide();
        $('#panel_mensajes').hide();
        
        // Cargar datos del ciclo seleccionado
        $.ajax({
            url: '/bi/datos-ciclo',
            method: 'POST',
            data: {
                ciclo_id: cicloId,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                console.log('Datos del ciclo recibidos:', data); // Depuración
                
                // Verificar si hay error en la respuesta
                if (data.error) {
                    $('#mensaje_error').text(data.error);
                    $('#panel_mensajes').show();
                    return;
                }
                
                // Mostrar el panel de información
                $('#panel_informacion').show();
                
                // Actualizar información del ciclo
                $('#nombre_ciclo').text(data.ciclo.descripcion);
                $('#fecha_inicio').text(data.ciclo.fechaInicio);
                $('#fecha_fin').text(data.ciclo.fechaFin || 'En curso');
                $('#estado').text(data.ciclo.estado);
                
                // Actualizar días transcurridos
                $('#dias_transcurridos').text(data.dias_transcurridos);
                
                // Actualizar consumo de agua total (riego manual)
                $('#consumo_agua_total').text(parseFloat(data.consumo_agua_total).toFixed(2));
                
                // Cargar también el volumen de agua de la tabla valvula para este ciclo
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/bi/volumen-agua-ciclo',
                    method: 'POST',
                    data: {
                        ciclo_id: cicloId
                    },
                    success: function(volumenData) {
                        $('#volumen_agua_valvula').text(parseFloat(volumenData.volumen_total || 0).toFixed(2));
                    },
                    error: function(xhr, status, error) {
                        console.log('Error al cargar volumen de agua por valvula:', error);
                        $('#volumen_agua_valvula').text('0.00');
                    }
                });
                
                // Actualizar estado del ciclo y mostrar días restantes o completado
                if (data.ciclo_completado) {
                    $('#card_dias_restantes').addClass('d-none');
                    $('#card_ciclo_completado').removeClass('d-none');
                    $('#estado_ciclo').html('<span class="badge badge-success" style="font-size: 1.2rem;">Ciclo completado - Listo para cosecha</span>');
                } else {
                    $('#card_dias_restantes').removeClass('d-none');
                    $('#card_ciclo_completado').addClass('d-none');
                    
                    // Mostrar días restantes si están disponibles
                    if (data.dias_restantes !== null) {
                        $('#dias_restantes').text(data.dias_restantes);
                        $('#estado_ciclo').html('<span class="badge badge-warning" style="font-size: 1.2rem;">Ciclo en progreso</span>');
                    } else {
                        $('#dias_restantes').text('N/A');
                        $('#estado_ciclo').html('<span class="badge badge-info" style="font-size: 1.2rem;">Ciclo en progreso</span>');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Error al cargar datos del ciclo:', xhr.responseText); // Depuración
                // Mostrar mensaje de error detallado
                var errorMessage = 'Error al cargar los datos del ciclo';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseText) {
                    errorMessage = 'Error: ' + xhr.responseText;
                }
                
                $('#mensaje_error').text(errorMessage);
                $('#panel_mensajes').show();
                console.log(xhr);
            }
        });
    }
</script>
@endsection