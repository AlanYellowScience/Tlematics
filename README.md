# TELEMATICS DASHBOARD - README

## Descripción

Dashboard web para monitoreo de flota con MaterializeCSS, Leaflet y Chart.js,
consumiendo la API de Telematics Advance a través de un proxy PHP (cURL).
Incluye KPIs, lista de unidades, mapa, gráfica de velocidad, tablas de actividad
e incidencias, giroscopio (rumbo) y un loader visible durante la carga.

Puede consultar el proyecto en un ambiente productivo vía:
(https://apprendi.com.mx/PruebaTecnica/)

## Estructura de archivos

/ (raíz pública del sitio)

├─ index.html          - Frontend (UI + JS)

├─ proxy.php           - Proxy PHP con cURL hacia la API (whitelist de endpoints)

└─ /assets             - Opcional: imágenes/recursos estáticos

## Requisitos

* PHP 7+ con extensión cURL habilitada (hosting estandar).
* Acceso HTTPS saliente a [https://www.telematicsadvance.com/api/](https://www.telematicsadvance.com/api/)
* Navegador moderno.

## Instalación en producción (hosting)

1. Subir index.html y proxy.php a la carpeta pública (por ejemplo, public\_html/telematics/).
2. Editar proxy.php y configurar la API key en duro:
   \$API\_BASE = '[https://www.telematicsadvance.com/api/v1](https://www.telematicsadvance.com/api/v1)';
   \$API\_KEY  = 'PEGA\_AQUI\_TU\_API\_KEY';
3. (Recomendado) Restringir CORS a tu dominio en proxy.php:
   header('Access-Control-Allow-Origin: [https://tudominio.com](https://tudominio.com)');
4. Abrir la URL del dashboard en el navegador.

## Ejecución local (opcional para evaluación)

1. Instalar PHP (7+).
2. Desde el directorio del proyecto ejecutar:
   php -S localhost:8080
3. Abrir [http://localhost:8080/](http://localhost:8080/)
4. Asegurar que proxy.php contiene una API key válida.

## Endpoints permitidos en el proxy (whitelist)

proxy.php expone únicamente:

* unit/list                (GET)
* unit\_data/can\_period     (GET)
* unit\_data/can\_point      (GET)
* unit\_data/history\_point  (GET)

Si se requiere otro endpoint, agregarlo al arreglo \$allowed en proxy.php.

## Funciones del dashboard

* KPIs:

  * Unidades activas
  * Velocidad promedio (ver lógica más abajo)
  * Alertas actuales (incidencias operativas)
  * Uso de motor (% del día)
  * Giroscopio (rumbo a partir de direction/course/heading)
* Lista de unidades:

  * Búsqueda por nombre/placa
  * Ficha enriquecida por unidad: placa/shortcut, país, odómetro, coordenadas, icono
  * Chip de estado con colores: verde (activo), azul (standing reciente),
    ámbar (standing 5–30 min), gris (sin señal reciente / offline)
* Mapa:

  * Leaflet con OSM; centra y marca la unidad seleccionada
* Gráfica de velocidad:

  * Serie de últimos minutos para la unidad seleccionada, si hay datos (total\_distance)
  * Fallback: snapshot de velocidades actuales (Top 20)
* Tablas:

  * Actividad actual (conteos y métricas)
  * Incidencias: offline > 12 h y detenido > 6 h (alimenta el KPI de alertas)
* Loader:

  * Overlay visible durante la carga inicial y al presionar “Refrescar”
* Logging:

  * Si DEBUG=true, imprime en consola cada request y su tiempo

## Parámetros ajustables (en index.html)

En el script principal:

* const DEBUG = true;
* const LOOKBACK\_HOURS\_FOR\_CHART = 2;     // Ventana de la serie por unidad
* const STALE\_MINUTES\_OFFLINE   = 12\*60;  // Minutos para considerar offline
* const STANDING\_LONG\_MIN       = 6\*60;   // Minutos para considerar inactivo prolongado

## Lógica de KPIs y reglas operativas

* Unidades activas:

  * movement\_state/state distinto de 'standing' o 'stopped' o 'idle'
    O motor encendido (ignition/engine=true)
* Velocidad promedio:

  * Si hay serie de últimas 2 h para la unidad seleccionada: promedio de la serie
  * Si no hay serie: promedio de velocidades actuales solamente para unidades
    en movimiento (descarta 0)
* Alertas actuales:

  * Conteo de incidencias calculadas operativamente (no endpoint nativo):

    * Offline: última actualización > 12 h
    * Inactivo prolongado: estado detenido > 6 h
* Uso de motor (%):

  * Promedio de ignition\_total\_time / minutos transcurridos del día
* Giroscopio:

  * Usa direction/course/heading en grados y muestra cardinal (N, NE, E, etc.)

## Seguridad recomendada

* Mantener la API key solo en proxy.php (nunca en el frontend).
* Restringir Access-Control-Allow-Origin al dominio productivo.
* Desactivar DEBUG en producción.
* Opcional: validar Referer permitido en proxy.php.

## Solución de problemas

* Pantalla sin datos:

  * Revisar consola del navegador (con DEBUG=true verás URL, params y respuesta)
* Error 500 en proxy:

  * Verificar que cURL esté habilitado y que la API key sea válida
* CORS:

  * Ajustar Access-Control-Allow-Origin en proxy.php
* Velocidad promedio “baja” en snapshot:

  * Se promedian únicamente unidades con velocidad > 0; si pocas están moviéndose,
    la media tenderá a valores bajos

## Limitaciones actuales

* “Alertas” usan reglas operativas locales; no hay integración de eventos nativos
  (exceso de velocidad, geocercas, SOS) en esta versión
* Sin autenticación de usuarios
* Sin clustering de marcadores en mapas muy densos

## Roadmap

* Clustering de marcadores (Leaflet.markercluster)
* Filtros por país/tipo/estado en la lista
* Modal de ficha completa con más señales CAN y sparkline
* Exportación de incidencias a CSV
* Cache en proxy de unit/list (60–120 s) para aliviar la API

## Créditos: Alan Alcántara

Autor: \[Alan Yellow Science / contactoapprendi@gmail.com]

