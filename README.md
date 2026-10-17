# Minimarket — Sistema de microservicios para la gestión de productos

**Estudiante:** Miguel Calle · 

Sistema compuesto por tres componentes independientes:

| Componente | Tecnología | Directorio | Puerto |
|---|---|---|---|
| Base de datos | MySQL 8.0 en Docker (`db_container_miguel`) | `docker-compose.yml`, `sql/` | 3310 (host) → 3306 (contenedor) |
| Backend (API REST) | Node.js + Express (patrón MVC) + mysql2 | `backend-mvc/` | 4500 |
| Frontend | PHP (servidor integrado) + cURL | `frontend-php/` | 8000 |

Flujo de comunicación: `Navegador → Frontend PHP → API REST (Express) → MySQL`.
El frontend nunca accede a MySQL: toda operación se realiza mediante HTTP al backend.

## Estructura del repositorio

```text
MiniMarket_MiguelCalle/
├── docker-compose.yml
├── README.md
├── sql/
│   └── init.sql
├── backend-mvc/
│   ├── package.json
│   ├── server.js
│   ├── config/db.js
│   ├── routes/productoRoutes.js
│   └── controllers/productoController.js
└── frontend-php/
    └── index.php
```

## Requisitos previos

- Docker Desktop (con Docker Compose v2)
- Node.js 18 o superior y npm
- PHP 8.x con la extensión **cURL** habilitada (`extension=curl` en `php.ini`)

Comprobación rápida:

```powershell
docker --version
node --version
php --version
php -m | Select-String curl
```

## Puesta en marcha (PowerShell)

Se usan tres ventanas de PowerShell: una para Docker y los comandos de verificación, una para el backend y una para el frontend. Reemplaza la ruta de la primera línea por la ubicación real del proyecto.

### 1. Levantar MySQL (ventana 1)

```powershell
$ruta = "C:\ruta\MiniMarket_MiguelCalle"
cd $ruta
docker compose up -d
docker logs db_container_miguel --tail 5
```

Esperar hasta que el registro indique `ready for connections` (puede tardar entre 20 y 40 segundos en el primer arranque).

### 2. Crear la tabla Producto (ventana 1, una sola vez)

```powershell
Get-Content .\sql\init.sql -Raw | docker exec -i db_container_miguel mysql -u user_miguel -ppassmiguel123
docker exec -it db_container_miguel mysql -u user_miguel -ppassmiguel123 db_minimarket_pro413 -e "DESCRIBE Producto;"
```

El script usa `CREATE TABLE IF NOT EXISTS`: si la tabla ya existía con otra estructura, no la modifica (ver *Problemas frecuentes*).

### 3. Iniciar el backend (ventana 2)

```powershell
cd C:\ruta\MiniMarket_MiguelCalle\backend-mvc
npm install
npm run dev
```

Debe mostrarse: `Servidor MVC de Miguel corriendo en el puerto 4500`. La API queda en `http://localhost:4500/api/productos`. Dejar la ventana abierta.

### 4. Iniciar el frontend (ventana 3)

```powershell
cd C:\ruta\MiniMarket_MiguelCalle\frontend-php
php -S localhost:8000
```

Abrir `http://localhost:8000` en el navegador. Dejar la ventana abierta.

## Endpoints de la API

| Método | Ruta | Acción | Respuesta exitosa |
|---|---|---|---|
| GET | `/api/productos` | Lista productos activos (`eliminado = 0`) | 200 |
| POST | `/api/productos` | Registra un producto | 201 |
| PUT | `/api/productos/:id` | Actualiza un producto | 200 |
| DELETE | `/api/productos/:id` | Baja lógica (`eliminado = 1`), sin borrado físico | 200 |

Cuerpo JSON para `POST` y `PUT`:

```json
{
  "nombre": "Fideos 500 g",
  "proveedor": "Distribuidora Central S.R.L.",
  "fecha_vencimiento": "2027-03-15",
  "precio": 8.50,
  "stock": 40
}
```

## Verificación de la baja lógica en MySQL

Todos los registros, incluidos los eliminados lógicamente:

```powershell
docker exec -it db_container_miguel mysql -u user_miguel -ppassmiguel123 db_minimarket_pro413 -e "SELECT id, nombre, eliminado FROM Producto ORDER BY id;"
```

Solo los activos (debe coincidir con la tabla del frontend):

```powershell
docker exec -it db_container_miguel mysql -u user_miguel -ppassmiguel123 db_minimarket_pro413 -e "SELECT id, nombre, precio, stock FROM Producto WHERE eliminado = 0;"
```

Un producto dado de baja conserva su fila con `eliminado = 1` y deja de aparecer en el listado.

## Detener los servicios

Backend y frontend: `Ctrl + C` en cada ventana. Luego, en la ventana 1:

```powershell
cd C:\ruta\MiniMarket_MiguelCalle
docker compose down        # conserva los datos (volumen mysql_data_miguel)
docker compose down -v     # elimina también el volumen y los datos
```

## Problemas frecuentes

| Síntoma | Causa probable | Solución |
|---|---|---|
| `ECONNREFUSED` al consultar la API | MySQL no está listo o no está en ejecución | `docker ps` y `docker logs db_container_miguel --tail 5` |
| `ER_NO_SUCH_TABLE` | No se ejecutó `sql/init.sql` | Ejecutar el paso 2 |
| La API acepta precios o stock negativos | La tabla se creó antes sin restricciones `CHECK` | Recrear la tabla con `init.sql` o agregar las restricciones con `ALTER TABLE` |
| `curl_init()` indefinida en PHP | Extensión cURL deshabilitada | Habilitar `extension=curl` en `php.ini` y reiniciar `php -S` |
| `EADDRINUSE` en el puerto 4500 | Otro proceso usa el puerto | `Get-NetTCPConnection -LocalPort 4500` y finalizar el proceso |
| Puerto 3310 ocupado | Otro servicio lo usa | Liberar el puerto o cambiar el mapeo en `docker-compose.yml` y en `backend-mvc/config/db.js` |

## Nota sobre credenciales

Las credenciales de `docker-compose.yml` y de `backend-mvc/config/db.js` son exclusivamente de desarrollo local y no deben reutilizarse en otros entornos.