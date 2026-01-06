@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2>Predicción de Consumo de Agua</h2>
            <p class="text-muted">Sistema de predicción basado en regresión lineal y temperatura actual</p>
        </div>
    </div>

    <!-- Controles -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-primary" onclick="cargarPrediccion('valvula')">Solo Válvula</button>
                <button type="button" class="btn btn-primary" onclick="cargarPrediccion('manual')">Solo Manual</button>
                <button type="button" class="btn btn-primary" onclick="cargarPrediccion('ambos')">Consumo Total</button>
            </div>
        </div>
    </div>

    <!-- Panel de Resumen -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h3 id="mensaje-prediccion">Seleccione un tipo de riego para ver la predicción</h3>
                    <div class="row mt-3">
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

    <!-- Gráfica Principal -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Historial y Predicción de Consumo de Agua</h5>
                </div>
                <div class="card-body">
                    <canvas id="grafica-prediccion" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let grafica = null;

    function cargarPrediccion(tipo) {
        fetch(`/bi/prediccion-agua?tipo=${tipo}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            // Actualizar panel de resumen
            document.getElementById('mensaje-prediccion').textContent = data.mensaje;
            document.getElementById('promedio-diario').textContent = data.promedio_diario.toFixed(2) + ' L';
            document.getElementById('temperatura-actual').textContent = data.temperature + '°C';
            document.getElementById('prediccion-valor').textContent = data.prediction.toFixed(2) + ' L';
            
            // Actualizar gráfica
            actualizarGrafica(data.labels, data.data);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de predicción');
        });
    }

    function actualizarGrafica(labels, data) {
        const ctx = document.getElementById('grafica-prediccion').getContext('2d');
        
        // Destruir la gráfica anterior si existe
        if (grafica) {
            grafica.destroy();
        }
        
        // Separar datos históricos y predicción
        const datosHistoricos = data.slice(0, -1); // Todos menos el último (predicción)
        const prediccion = data[data.length - 1]; // Último valor (predicción)
        
        grafica = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Consumo Histórico',
                        data: datosHistoricos,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: 'Predicción',
                        data: [...Array(datosHistoricos.length), prediccion], // Datos históricos + predicción
                        pointBackgroundColor: 'rgb(255, 99, 132)',
                        pointRadius: 8,
                        pointHoverRadius: 10,
                        showLine: false
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
                            text: 'Fecha'
                        }
                    }
                }
            }
        });
    }

    // Cargar predicción por defecto al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        cargarPrediccion('ambos');
    });
</script>
@endsection