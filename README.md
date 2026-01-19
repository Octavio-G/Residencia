# Residencia
proyecto de residencia 
>>>>>>> c5448460f27523c9c7dd346e2fee7c5f7540ab50
# HortaDash - Sistema de Monitoreo Agrícola

Sistema de monitoreo agrícola avanzado desarrollado como proyecto de residencia profesional.

## 🌱 Descripción

HortaDash es una plataforma web integral para el monitoreo y gestión de sistemas de riego agrícola. Permite el seguimiento en tiempo real de condiciones del suelo, control de ciclos de siembra y análisis predictivo para optimizar el uso del agua en cultivos.

## 🚀 Características Principales

- **Dashboard BI Interactivo**: Visualización de indicadores clave de salud de cultivos
- **Monitoreo en Tiempo Real**: Seguimiento continuo de humedad del suelo y temperatura
- **Gestión de Ciclos de Siembra**: Control completo de etapas de cultivo
- **Alertas Predictivas**: Sistema inteligente de alertas de secado del suelo
- **Análisis Histórico**: Comparativas de rendimiento entre diferentes ciclos
- **Autenticación de Usuarios**: Sistema seguro de acceso basado en usuarios existentes
- **Reportes Personalizados**: Exportación de datos en formato PDF

## 🛠️ Tecnologías Utilizadas

- **Backend**: Laravel 12 (PHP 8.3+)
- **Frontend**: Blade Templates + Bootstrap 4
- **Base de Datos**: MySQL
- **Gráficos**: Chart.js
- **Autenticación**: Laravel Breeze/Sanctum

## 📁 Estructura del Proyecto

```
app/
├── Http/Controllers/
│   ├── BiController.php          # Controlador principal de BI
│   ├── AuthController.php        # Autenticación de usuarios
│   └── PrediccionController.php  # Controlador de predicciones
├── Models/
│   ├── CamaSiembra.php           # Modelo de camas de siembra
│   ├── Cama2.php                 # Modelo de segunda cama
│   ├── CicloSiembra.php          # Modelo de ciclos de siembra
│   ├── Cultivo.php               # Modelo de cultivos
│   └── Valvula.php               # Modelo de válvulas de riego
resources/
└── views/
    └── bi/
        ├── dashboard.blade.php   # Dashboard principal
        └── reporte_pdf.blade.php # Plantilla de reportes
```

## 🔧 Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/Octavio-G/Residencia.git
cd Residencia
```

2. Instalar dependencias de PHP:
```bash
composer install
```

3. Configurar el entorno:
```bash
cp .env.example .env
# Configurar las credenciales de base de datos en .env
```

4. Generar clave de aplicación:
```bash
php artisan key:generate
```

5. Ejecutar migraciones:
```bash
php artisan migrate
```

6. Iniciar el servidor de desarrollo:
```bash
php artisan serve
```

## 📊 Módulos Disponibles

### 1. Indicador de Salud
Panel de semáforo visual que muestra el estado de las camas de siembra con colores:
- 🟢 Verde: Óptimo (60-100% humedad)
- 🟡 Amarillo: Advertencia (30-59% humedad)
- 🔴 Rojo: Crítico (<30% humedad)

### 2. Alerta de Secado
Sistema predictivo que estima tiempo restante antes de alcanzar niveles críticos de humedad del suelo.

### 3. Análisis Histórico
Comparativas entre ciclos de siembra con métricas de rendimiento.

### 4. Gestión de Ciclos
Control de días transcurridos, consumo de agua y estado de completitud de ciclos.

## 👥 Autores

Proyecto desarrollado como parte de las actividades de residencia profesional.

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Para cambios importantes, por favor abre un issue primero para discutir lo que te gustaría cambiar.

---

Desarrollado con ❤️ para la optimización de recursos agrícolas
=======
# Residencia
proyecto de residencia 
>>>>>>> c5448460f27523c9c7dd346e2fee7c5f7540ab50
