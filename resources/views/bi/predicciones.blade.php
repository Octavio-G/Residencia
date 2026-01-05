@extends('layouts.app')

@section('title', 'Predicciones')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-brain"></i> Predicciones</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-cog"></i> Configuración de Predicción</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="ciclo_prediccion">Seleccionar Ciclo:</label>
                        <select class="form-control" id="ciclo_prediccion">
                            <option value="">Seleccione un ciclo</option>
                            <!-- Opciones se cargarán dinámicamente -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tipo_prediccion">¿Qué desea predecir?</label>
                        <div id="opciones_prediccion" class="row">
                            <!-- Opciones de predicción se cargarán aquí -->
                        </div>
                    </div>

                    <button class="btn btn-primary btn-block" id="btn_calcular_prediccion" disabled>
                        <i class="fas fa-calculator"></i> Calcular Predicción
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar"></i> Resultados de Predicción</h5>
                </div>
                <div class="card-body">
                    <div id="resultado_prediccion" class="text-center" style="display: none;">
                        <!-- Resultados de la predicción se mostrarán aquí -->
                    </div>
                    <div id="mensaje_prediccion" class="alert alert-info" style="display: none;">
                        Seleccione un ciclo y un tipo de predicción para comenzar
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Cargar ciclos disponibles
    cargarCiclosPrediccion();
    
    // Cargar opciones de predicción
    cargarOpcionesPrediccion();
});

function cargarCiclosPrediccion() {
    $.ajax({
        url: '/bi/prediccion/ciclos',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#ciclo_prediccion').html('<option value="">Seleccione un ciclo</option>' + response.options);
            } else {
                $('#ciclo_prediccion').html('<option value="">Error al cargar ciclos</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar ciclos:', error);
            $('#ciclo_prediccion').html('<option value="">Error de conexión</option>');
        }
    });
}

function cargarOpcionesPrediccion() {
    $.ajax({
        url: '/bi/prediccion/opciones',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let html = '';
                response.predicciones.forEach(function(prediccion) {
                    html += `
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_prediccion" id="pred_${prediccion.id}" value="${prediccion.id}">
                                        <label class="form-check-label" for="pred_${prediccion.id}">
                                            <i class="fas ${prediccion.icono}"></i> 
                                            <strong>${prediccion.nombre}</strong>
                                            <div class="small text-muted">${prediccion.descripcion}</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#opciones_prediccion').html(html);
                
                // Añadir evento para habilitar el botón cuando se selecciona una opción
                $('input[name="tipo_prediccion"]').change(function() {
                    actualizarBotonCalcular();
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar opciones de predicción:', error);
            $('#opciones_prediccion').html('<div class="col-md-12"><div class="alert alert-danger">Error al cargar las opciones de predicción</div></div>');
        }
    });
}

function actualizarBotonCalcular() {
    const cicloSeleccionado = $('#ciclo_prediccion').val();
    const prediccionSeleccionada = $('input[name="tipo_prediccion"]:checked').val();
    
    if (cicloSeleccionado && prediccionSeleccionada) {
        $('#btn_calcular_prediccion').prop('disabled', false);
    } else {
        $('#btn_calcular_prediccion').prop('disabled', true);
    }
}

// Evento para el cambio de ciclo
$('#ciclo_prediccion').change(function() {
    actualizarBotonCalcular();
});

// Evento para calcular predicción
$('#btn_calcular_prediccion').click(function() {
    const cicloId = $('#ciclo_prediccion').val();
    const tipoPrediccion = $('input[name="tipo_prediccion"]:checked').val();
    
    if (!cicloId || !tipoPrediccion) {
        alert('Por favor seleccione un ciclo y un tipo de predicción');
        return;
    }
    
    calcularPrediccion(cicloId, tipoPrediccion);
});

function calcularPrediccion(cicloId, tipoPrediccion) {
    $.ajax({
        url: '/bi/prediccion/calcular',
        method: 'POST',
        data: {
            ciclo_id: cicloId,
            tipo_prediccion: tipoPrediccion,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $('#btn_calcular_prediccion').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Calculando...');
            $('#mensaje_prediccion').hide();
        },
        success: function(response) {
            if (response.success) {
                mostrarResultadoPrediccion(response);
            } else {
                alert('Error: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al calcular predicción:', error);
            alert('Error al calcular la predicción');
        },
        complete: function() {
            $('#btn_calcular_prediccion').prop('disabled', false).html('<i class="fas fa-calculator"></i> Calcular Predicción');
        }
    });
}

function mostrarResultadoPrediccion(response) {
    const tipo = response.tipo;
    const datos = response.datos;
    
    let html = '';
    
    switch(tipo) {
        case 'consumo_agua':
            html = `
                <div class="alert alert-info">
                    <h4><i class="fas fa-tint"></i> Predicción de Consumo de Agua</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>${datos.prediccion} L</h3>
                                    <p>Estimado para los ${datos.periodo}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>${datos.promedio_diario} L/día</h3>
                                    <p>Consumo promedio diario</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            break;
            
        case 'humedad':
            html = `
                <div class="alert alert-info">
                    <h4><i class="fas fa-water"></i> Predicción de Humedad</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>${datos.cama1.prediccion}%</h3>
                                    <p>Predicción Cama 1</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>${datos.cama2.prediccion}%</h3>
                                    <p>Predicción Cama 2</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>Promedio General: ${datos.promedio_general}%</h5>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            break;
            
        case 'rendimiento':
            html = `
                <div class="alert alert-info">
                    <h4><i class="fas fa-chart-line"></i> Predicción de Rendimiento</h4>
                    <div class="text-center">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h2>${datos.rendimiento_estimado}%</h2>
                                <p class="mb-0">Rendimiento estimado</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p><strong>Progreso del ciclo:</strong> ${datos.progreso_ciclo}%</p>
                            <p>${datos.descripcion}</p>
                        </div>
                    </div>
                </div>
            `;
            break;
            
        default:
            html = '<div class="alert alert-warning">Tipo de predicción no reconocido</div>';
    }
    
    $('#resultado_prediccion').html(html).show();
    $('#mensaje_prediccion').hide();
}
</script>
@endsection