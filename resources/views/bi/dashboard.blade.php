@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            
            <ul class="nav nav-tabs" id="biTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="indicador-salud-tab" data-toggle="tab" href="#indicador-salud" role="tab">
                        <i class="fas fa-heartbeat"></i> Indicador de Salud
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="indice-secado-tab" data-toggle="tab" href="#indice-secado" role="tab">
                        <i class="fas fa-wind"></i> Índice de Secado
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="ciclos-siembra-tab" data-toggle="tab" href="#ciclos-siembra" role="tab">
                        <i class="fas fa-seedling"></i> Ciclos de Siembra
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" id="comparativa-historica-tab" data-toggle="tab" href="#comparativa-historica" role="tab">
                        <i class="fas fa-chart-line"></i> Comparativa Histórica
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" id="prediccion-agua-tab" data-toggle="tab" href="#prediccion-agua" role="tab">
                        <i class="fas fa-tint"></i> Predicción de Agua
                    </a>
                </li>
            </ul>
            
            <div class="tab-content" id="biTabsContent">
                
                <div class="tab-pane fade show active" id="indicador-salud" role="tabpanel">
                    <div class="card mt-0 border-top-0 rounded-0">
                        <div class="card-header">
                            <h5>Indicador de Salud de Camas de Siembra</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="panel-camas" class="row">
                                        </div>
                                </div>
                            </div>
                            
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
                                                    <th>Fecha</th>
                                                    <th>Hora</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
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

                <div class="tab-pane fade" id="indice-secado" role="tabpanel">
                    <div class="card mt-0 border-top-0 rounded-0">
                        <div class="card-header">
                            <h5>Índice de Secado</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <button class="btn btn-primary" id="btn-cargar-secado">
                                        <i class="fas fa-sync-alt"></i> Actualizar Datos
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-seedling"></i> 
                                                <span id="cama1-nombre">Cama 1</span>
                                                <small class="float-right">Cultivo: <span id="cama1-cultivo">Cargando...</span></small>
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <h3 id="cama1-tiempo-restante" class="text-success">
                                                    <i class="fas fa-clock"></i> Cargando...
                                                </h3>
                                                <div id="cama1-mensaje-estado" class="alert alert-info mt-2">
                                                    Cargando estado...
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <canvas id="grafica-cama1" height="200"></canvas>
                                            </div>
                                            
                                            <div class="text-center">
                                                <small class="text-muted">
                                                    Temperatura actual: <span id="cama1-temperatura">0°C</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-seedling"></i> 
                                                <span id="cama2-nombre">Cama 2</span>
                                                <small class="float-right">Cultivo: <span id="cama2-cultivo">Cargando...</span></small>
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <h3 id="cama2-tiempo-restante" class="text-success">
                                                    <i class="fas fa-clock"></i> Cargando...
                                                </h3>
                                                <div id="cama2-mensaje-estado" class="alert alert-info mt-2">
                                                    Cargando estado...
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <canvas id="grafica-cama2" height="200"></canvas>
                                            </div>
                                            
                                            <div class="text-center">
                                                <small class="text-muted">
                                                    Temperatura actual: <span id="cama2-temperatura">0°C</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="ciclos-siembra" role="tabpanel">
                    <div class="card mt-0 border-top-0 rounded-0">
                        <div class="card-header">
                            <h5>Gestión de Ciclos de Siembra</h5>
                        </div>
                        <div class="card-body">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Seleccionar Ciclo de Siembra</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="ciclo_selector">Ciclo de Siembra:</label>
                                        <select class="form-control" id="ciclo_selector">
                                            <option value="">Seleccione un ciclo</option>
                                            </select>
                                    </div>
                                    
                                    <button class="btn btn-primary" id="btn_cargar_datos">
                                        <i class="fas fa-sync-alt"></i> Cargar Datos
                                    </button>
                                </div>
                            </div>
                            
                            <div id="panel_mensajes" style="display: none;">
                                <div class="alert alert-danger" id="mensaje_error">
                                    </div>
                            </div>
                            
                            <div id="panel_informacion" style="display: none;">
                                <div class="row">
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
                                    
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning text-white">
                                                <h5 class="mb-0">Estado del Ciclo</h5>
                                            </div>
                                            <div class="card-body text-center">
                                                <div id="estado_ciclo">
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="comparativa-historica" role="tabpanel">
                    <div class="card mt-0 border-top-0 rounded-0">
                        <div class="card-header">
                            <h5>Comparativa Histórica de Ciclos de Siembra</h5>
                        </div>
                        <div class="card-body">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6>Filtros de Comparación</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="ciclo_a">Ciclo A:</label>
                                            <select class="form-control" id="ciclo_a" name="ciclo_a">
                                                <option value="">Seleccione un ciclo</option>
                                                </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="ciclo_b">Ciclo B:</label>
                                            <select class="form-control" id="ciclo_b" name="ciclo_b">
                                                <option value="">Seleccione un ciclo</option>
                                                </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="tipo_grafica">Tipo de Gráfica:</label>
                                            <select class="form-control" id="tipo_grafica" name="tipo_grafica">
                                                <option value="lineal">Lineal</option>
                                                <option value="barra">Barra</option>
                                                <option value="radar">Radar</option>
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
                            
                            <div id="panel_mensajes_comparativa" style="display: none;">
                                <div class="alert alert-danger" id="mensaje_error_comparativa">
                                    </div>
                            </div>
                            
                            <div id="graficas_comparativa" style="display: none;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5>Resultados de Comparación</h5>
                                    </div>
                                </div>
                                
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
                    </div>
                </div>

                <div class="tab-pane fade" id="prediccion-agua" role="tabpanel">
                    <div class="card mt-0 border-top-0 rounded-0">
                        <div class="card-header">
                            <h5>Predicción de Consumo de Agua</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-primary" onclick="cargarPrediccion('valvula')">Solo Válvula</button>
                                        <button type="button" class="btn btn-primary" onclick="cargarPrediccion('manual')">Solo Manual</button>
                                        <button type="button" class="btn btn-primary" onclick="cargarPrediccion('ambos')">Consumo Total</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 id="mensaje-prediccion">Seleccione un tipo de riego para ver la predicción</h3>
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <h5>Promedio Diario</h5>
                                                    <p class="text-muted" id="promedio-diario">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <h5>Temperatura Actual</h5>
                                                    <p class="text-muted" id="temperatura-actual">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <h5>Predicción</h5>
                                                    <p class="text-muted" id="prediccion-valor">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Historial y Predicción de Consumo de Agua</h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <canvas id="grafica-prediccion" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> </div> </div> </div> <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Variables globales para gráficos
    var graficoSalud, graficoSecado, graficoComparativo;
    
    // Cargar datos cuando el documento esté listo
    $(document).ready(function() {
        cargarIndicadorSalud();
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
                        if (lectura.humedad <= 30) {  // ROJO (Crítico): humedad <= 30
                            estado = 'Crítico';
                            estadoClass = 'estado-critico';
                        } else if (lectura.humedad > 30 && lectura.humedad <= 60) {  // AMARILLO (Advertencia): humedad > 30 Y humedad <= 60
                            estado = 'Advertencia';
                            estadoClass = 'estado-advertencia';
                        } else {  // VERDE (Óptimo): humedad > 60
                            estado = 'Óptimo';
                            estadoClass = 'estado-optimo';
                        }
                        
                        var row = `
                            <tr>
                                <td>${lectura.cama}</td>
                                <td>${lectura.humedad}%</td>
                                <td>${lectura.fecha}</td>
                                <td>${lectura.hora}</td>
                                <td><span class="estado-badge ${estadoClass}">${estado}</span></td>
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
                        var borderClass = '';
                        if (cama.color === 'rojo') {
                            colorClass = 'text-danger';
                            bgColorClass = 'estado-critico';
                            borderClass = 'border-danger';
                        } else if (cama.color === 'amarillo') {
                            colorClass = 'text-warning';
                            bgColorClass = 'estado-advertencia';
                            borderClass = 'border-warning';
                        } else {
                            colorClass = 'text-success';
                            bgColorClass = 'estado-optimo';
                            borderClass = 'border-success';
                        }
                        
                        panelHtml += `
                            <div class="col-md-6 mb-4">
                                <div class="card ${borderClass}">
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
                $('#panel-camas').html('<div class="col-md-12"><p class="text-center text-danger">Error al cargar los datos: ' + error + '</p></div>');
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
                
                // Actualizar consumo de agua total (inicialmente con el valor total)
                $('#consumo_agua_total').text(parseFloat(data.consumo_agua_total).toFixed(2));
                
                
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
                var etiquetaCicloA = response.ciclo_a_nombre;
                var etiquetaCicloB = response.ciclo_b_nombre;
                
                switch (tipoGrafica) {
                    case 'lineal':
                        crearGraficaLineal(response.datos.ciclo_a, response.datos.ciclo_b, titulo, etiquetaCicloA, etiquetaCicloB);
                        break;
                    case 'barras':
                        crearGraficaBarras(response.datos.ciclo_a, response.datos.ciclo_b, titulo, etiquetaCicloA, etiquetaCicloB);
                        break;
                    case 'radar':
                        crearGraficaRadar(response.datos.ciclo_a, response.datos.ciclo_b, titulo, etiquetaCicloA, etiquetaCicloB);
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
    
    // Función para crear gráfica de radar
    function crearGraficaRadar(datosCicloA, datosCicloB, titulo, etiquetaCicloA, etiquetaCicloB) {
        var ctx = document.getElementById('graficoComparativo').getContext('2d');
        
        // Destruir gráfica anterior si existe
        if (window.graficoComparativo) {
            window.graficoComparativo.destroy();
        }
        
        // Extraer labels y valores
        var labels = datosCicloA.map(item => 'Día ' + item.dia);
        var valoresCicloA = datosCicloA.map(item => item.valor);
        var valoresCicloB = datosCicloB.map(item => item.valor);
        
        window.graficoComparativo = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: etiquetaCicloA,
                        data: valoresCicloA,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgb(255, 99, 132)',
                        pointBackgroundColor: 'rgb(255, 99, 132)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(255, 99, 132)'
                    },
                    {
                        label: etiquetaCicloB,
                        data: valoresCicloB,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgb(54, 162, 235)',
                        pointBackgroundColor: 'rgb(54, 162, 235)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(54, 162, 235)'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: Math.max(...valoresCicloA, ...valoresCicloB) * 1.1 // Ajustar el máximo según los datos
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
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
    
    // Evento para el botón de comparar
    $('#btn-comparar-completo').click(function() {
        compararCiclos();
    });
    
    // Evento para activar pestaña de comparativa histórica
    $('#comparativa-historica-tab').on('shown.bs.tab', function (e) {
        cargarCiclosFinalizados();
    });
    
    // Función para cargar datos del índice de secado
    function cargarDatosSecado() {
        $.ajax({
            url: '/bi/indice-secado/calcular',
            method: 'GET',
            beforeSend: function() {
                $('#btn-cargar-secado').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
            },
            success: function(data) {
                // Actualizar Cama 1
                $('#cama1-nombre').text(data.cama1.nombre);
                $('#cama1-cultivo').text(data.cama1.cultivo);
                $('#cama1-temperatura').text(data.cama1.temperatura_actual + '°C');
                
                // Formatear tiempo restante para Cama 1
                let tiempoCama1 = '';
                if (data.cama1.tiempo_restante.horas > 0) {
                    tiempoCama1 = data.cama1.tiempo_restante.horas + 'h ';
                }
                tiempoCama1 += data.cama1.tiempo_restante.minutos + 'm';
                
                $('#cama1-tiempo-restante').text(tiempoCama1);
                
                // Actualizar estado para Cama 1
                let mensajeEstado1 = data.cama1.mensaje_estado;
                let claseEstado1 = 'alert-info';
                
                if (mensajeEstado1.includes('CRÍTICO')) {
                    claseEstado1 = 'alert-danger';
                    $('#cama1-tiempo-restante').removeClass('text-success text-danger').addClass('text-white blink');
                } else if (mensajeEstado1.includes('URGENTE')) {
                    claseEstado1 = 'alert-warning';
                    $('#cama1-tiempo-restante').removeClass('text-white text-success').addClass('text-danger');
                } else {
                    $('#cama1-tiempo-restante').removeClass('text-white text-danger').addClass('text-success');
                }
                
                $('#cama1-mensaje-estado').removeClass('alert-info alert-warning alert-danger').addClass(claseEstado1).text(mensajeEstado1);
                
                // Actualizar Cama 2
                $('#cama2-nombre').text(data.cama2.nombre);
                $('#cama2-cultivo').text(data.cama2.cultivo);
                $('#cama2-temperatura').text(data.cama2.temperatura_actual + '°C');
                
                // Formatear tiempo restante para Cama 2
                let tiempoCama2 = '';
                if (data.cama2.tiempo_restante.horas > 0) {
                    tiempoCama2 = data.cama2.tiempo_restante.horas + 'h ';
                }
                tiempoCama2 += data.cama2.tiempo_restante.minutos + 'm';
                
                $('#cama2-tiempo-restante').text(tiempoCama2);
                
                // Actualizar estado para Cama 2
                let mensajeEstado2 = data.cama2.mensaje_estado;
                let claseEstado2 = 'alert-info';
                
                if (mensajeEstado2.includes('CRÍTICO')) {
                    claseEstado2 = 'alert-danger';
                    $('#cama2-tiempo-restante').removeClass('text-success text-danger').addClass('text-white blink');
                } else if (mensajeEstado2.includes('URGENTE')) {
                    claseEstado2 = 'alert-warning';
                    $('#cama2-tiempo-restante').removeClass('text-white text-success').addClass('text-danger');
                } else {
                    $('#cama2-tiempo-restante').removeClass('text-white text-danger').addClass('text-success');
                }
                
                $('#cama2-mensaje-estado').removeClass('alert-info alert-warning alert-danger').addClass(claseEstado2).text(mensajeEstado2);
                
                // Dibujar gráficas
                dibujarGrafica('grafica-cama1', data.cama1.lecturas_historial, data.cama1.nombre);
                dibujarGrafica('grafica-cama2', data.cama2.lecturas_historial, data.cama2.nombre);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar datos de secado:', error);
                alert('Error al cargar los datos de secado');
            },
            complete: function() {
                $('#btn-cargar-secado').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar Datos');
            }
        });
    }
    
    // Función para dibujar gráficas de índice de secado
    function dibujarGrafica(canvasId, lecturas, nombreCama) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        // Destruir gráfica anterior si existe
        if (window[canvasId + '_chart']) {
            window[canvasId + '_chart'].destroy();
        }
        
        // Preparar datos
        const labels = lecturas.map(lectura => lectura.fecha + ' ' + lectura.hora);
        const data = lecturas.map(lectura => lectura.humedad);
        
        // Configurar datasets
        const datasets = [
            {
                label: 'Humedad (%)',
                data: data,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.1
            },
            {
                label: 'Umbral Crítico (30%)',
                data: Array(labels.length).fill(30),
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false,
                pointRadius: 0
            }
        ];
        
        // Crear gráfica
        window[canvasId + '_chart'] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Humedad (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Fecha y Hora'
                        }
                    }
                }
            }
        });
    }
    
    // Evento para activar pestaña de índice de secado
    $('#indice-secado-tab').on('shown.bs.tab', function (e) {
        cargarDatosSecado();
    });
    
    // Evento para botón de carga de índice de secado
    $('#btn-cargar-secado').click(function() {
        cargarDatosSecado();
    });
    
    // Script de limpieza forzada para pestañas
    $(document).ready(function() {
        // Escuchar el evento cuando se hace clic en una pestaña
        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            // FORZAR LIMPIEZA: Remover clases 'show' y 'active' de todos los paneles
            $('.tab-pane').removeClass('show active');
            
            // Asegurar que el panel destino tenga las clases correctas
            var targetId = $(e.target).attr('href');
            $(targetId).addClass('show active');
        });
    });
    
    // Función para predicción de agua
    let graficaPrediccion = null;

    function cargarPrediccion(tipo) {
        fetch(`/bi/prediccion-agua?tipo=${tipo}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error del servidor:', data.error);
                alert('Error del servidor: ' + data.error);
                return;
            }
            
            document.getElementById('mensaje-prediccion').textContent = data.mensaje;
            document.getElementById('promedio-diario').textContent = data.promedio_historico.toFixed(2) + ' L';
            document.getElementById('temperatura-actual').textContent = data.temperature + '°C';
            document.getElementById('prediccion-valor').textContent = data.prediction.toFixed(2) + ' L';
            
            actualizarGraficaPrediccion(data.labels, data.data);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de predicción');
        });
    }

    function actualizarGraficaPrediccion(labels, data) {
        const ctx = document.getElementById('grafica-prediccion').getContext('2d');
        
        if (graficaPrediccion) {
            graficaPrediccion.destroy();
        }
        
        graficaPrediccion = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Consumo por Ciclo',
                        data: data,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: false,
                        tension: 0.1,
                        pointRadius: function(context) {
                            var index = context.dataIndex;
                            return index === context.dataset.data.length - 1 ? 8 : 4;
                        },
                        pointBackgroundColor: function(context) {
                            var index = context.dataIndex;
                            return index === context.dataset.data.length - 1 ? 'rgb(255, 99, 132)' : 'rgb(54, 162, 235)';
                        },
                        pointHoverRadius: function(context) {
                            var index = context.dataIndex;
                            return index === context.dataset.data.length - 1 ? 10 : 6;
                        }
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Historial y Predicción de Consumo de Agua'
                    },
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Litros'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Ciclo'
                        }
                    }
                }
            }
        });
    }
</script>
@endsection