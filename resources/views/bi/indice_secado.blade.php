@extends('layouts.app')

@section('title', 'Índice de Secado')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-wind"></i> Índice de Secado</h2>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <button class="btn btn-primary" id="btn-cargar-secado">
                <i class="fas fa-sync-alt"></i> Actualizar Datos
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Panel Cama 1 -->
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

        <!-- Panel Cama 2 -->
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Cargar datos iniciales
        cargarDatosSecado();
        
        // Evento para botón de actualización
        $('#btn-cargar-secado').click(function() {
            cargarDatosSecado();
        });
    });

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

    // Añadir clase de parpadeo para alertas críticas
    const style = document.createElement('style');
    style.textContent = `
        .blink {
            animation: blink 1s step-end infinite;
        }
        @keyframes blink {
            50% {
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endsection