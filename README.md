# Plan de Fertilización — Grupo Impulsora

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-3.x-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![Tailwind Utilities](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

Sistema integral de nutrición vegetal y planes de fertilización agronómica por etapas fenológicas para asesores técnicos y productores de **Grupo Impulsora**.

## 🚀 Arquitectura y Tecnologías
- **Framework:** Laravel 12 + Livewire 3 + Alpine.js + Bootstrap 5.3 + Tailwind CSS Utilities
- **Clean Architecture / DDD:** Separación estricta en capas (UI, Application, Domain, Infrastructure)
- **Geolocalización:** Leaflet con OpenTopoMap y geocodificación Nominatim
- **Reportes y WhatsApp:** Generación de PDF (DomPDF) con envío directo vía UltraMSG WhatsApp API
- **Dark & Light Mode:** Soporte nativo y reactivo en todos los componentes y modales

## 📦 Módulos Principales
1. **Asistente de Creación de Planes (Wizard 3 Pasos):**
   - Paso 1: Datos generales, cliente, asesor, análisis de suelo y georreferenciación en mapa.
   - Paso 2: Histórico de cultivo anterior y configuración del cultivo actual con metas de rendimiento.
   - Paso 3: Propuesta agronómica por etapas fenológicas con productos Impulsora, dosificación y métodos de aplicación.
2. **Seguimiento Comercial y Comparativo Testigo:**
   - Captura de seguimiento de aplicaciones en campo y comparación vs lote testigo.
3. **Dictamen de Resultados:**
   - Registro de cosecha, rendimiento real obtenido y conclusiones agronómicas.
4. **Reportes Técnicos PDF:**
   - Propuesta técnica y dictamen final con exportación y envío automatizado.

---
Desarrollado para **Grupo Impulsora**.
