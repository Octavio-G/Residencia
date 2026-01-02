@extends('layouts.app')

@section('title', 'Predicción de Secado - HortaView')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Predicción de Tiempo hasta Estrés Hídrico</h1>
    
    <div class="row">
        <!-- Tarjeta Izquierda: CamaSiembra -->
        <div class="col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Cama 1 - {{ $cama1['cultivo'] }}
                            </div>
                            <div class="mt-3">
                                <i class="fas fa-hourglass-half fa-3x text-gray-400 mb-3"></i>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Tiempo Restante
                                </div>
                                <div class="h3 mb-0 font-weight-bold 
                                    @if(is_numeric($cama1['tiempo_restante']) && $cama1['tiempo_restante'] < 120) 
                                        text-danger 
                                    @else 
                                        text-primary 
                                    @endif">
                                    {{ $cama1['tiempo_restante'] }}
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-xs font-weight-bold text-gray-500">
                                            Humedad Actual
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $cama1['humedad_actual'] }}%
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-xs font-weight-bold text-gray-500">
                                            Temperatura
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $cama1['temperatura'] }}°C
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-seedling fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tarjeta Derecha: Cama2 -->
        <div class="col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Cama 2 - {{ $cama2['cultivo'] }}
                            </div>
                            <div class="mt-3">
                                <i class="fas fa-hourglass-half fa-3x text-gray-400 mb-3"></i>
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Tiempo Restante
                                </div>
                                <div class="h3 mb-0 font-weight-bold 
                                    @if(is_numeric($cama2['tiempo_restante']) && $cama2['tiempo_restante'] < 120) 
                                        text-danger 
                                    @else 
                                        text-info 
                                    @endif">
                                    {{ $cama2['tiempo_restante'] }}
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-xs font-weight-bold text-gray-500">
                                            Humedad Actual
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $cama2['humedad_actual'] }}%
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-xs font-weight-bold text-gray-500">
                                            Temperatura
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $cama2['temperatura'] }}°C
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-seedling fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection