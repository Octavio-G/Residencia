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
                    <a class="nav-link" id="ciclos-siembra-tab" data-toggle="tab" href="#ciclos-siembra" role="tab">
                        <i class="fas fa-seedling"></i> Ciclos de Siembra
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" id="comparativa-historica-tab" data-toggle="tab" href="#comparativa-historica" role="tab">
                        <i class="fas fa-chart-line"></i> Comparativa Histórica
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

    <!-- Pestaña 2: Ciclos de Siembra -->
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

    <!-- Pestaña 3: Comparativa Histórica -->
    <div class="tab-pane fade" id="comparativa-historica" role="tabpanel">
        <div class="card mt-4">
            <div class="card-header">
                <h5>Comparativa Histórica de Ciclos de Siembra</h5>
            </div>
            <div class="card-body">

                
                <!-- Filtros de comparación -->
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
    
    // Evento para activar pestaña de comparativa histórica
    $('#comparativa-historica-tab').on('shown.bs.tab', function (e) {
        cargarCiclosFinalizados();
    });
    
    // Script de limpieza forzada para pestañas
    $(document).ready(function() {
        // Escuchar el evento cuando se hace clic en una pestaña
        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            // e.target es la nueva pestaña activada
            // e.relatedTarget es la pestaña anterior
            
            // FORZAR LIMPIEZA: Remover clases 'show' y 'active' de todos los paneles
            $('.tab-pane').removeClass('show active');
            
            // Asegurar que el panel destino tenga las clases correctas
            var targetId = $(e.target).attr('href');
            $(targetId).addClass('show active');
        });
    });
</script>
@endsection