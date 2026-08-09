# Indexado de Energía

Aplicación web para visualizar consumos y precios horarios de energía y calcular un precio indexado mediante una fórmula configurable.

La solución está compuesta por:

- **Backend:** API REST desarrollada con Laravel.
- **Frontend:** Vue 3 + TypeScript.
- **Base de datos:** MySQL.

La aplicación trabaja con valores horarios desde **H1 hasta H25** y utiliza el segmento **`[OMIE_MD]`** dentro de las fórmulas de cálculo.

---

## Tecnologías

### Backend

- PHP 8.5
- Laravel 13
- MySQL 8.4
- Composer 2.10
- PHPUnit
- Scramble / OpenAPI

### Frontend

- Vue 3
- TypeScript
- Vite
- Bootstrap 5
- Node.js 24
- npm 11
- Vitest
- Vue Test Utils

---

## Requisitos

El proyecto ha sido desarrollado y probado con:

- PHP 8.5
- Composer 2.10
- MySQL 8.4
- Node.js 24
- npm 11

---

## Instalación del backend

Entrar en la carpeta del backend:

```bash
cd backend
```

Instalar las dependencias:

```bash
composer install
```

Crear el archivo de entorno:

### Windows PowerShell

```powershell
Copy-Item .env.example .env
```

### Linux / macOS

```bash
cp .env.example .env
```

Generar la clave de Laravel:

```bash
php artisan key:generate
```

Configurar `backend/.env`:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=<MYSQL_HOST>
DB_PORT=3306
DB_DATABASE=energy_indexation
DB_USERNAME=<MYSQL_USERNAME>
DB_PASSWORD=<MYSQL_PASSWORD>

FRONTEND_URL=http://localhost:5173
API_VERSION=1.0.0
```

Los valores reales de conexión deben configurarse únicamente en el archivo `.env`, que no debe incluirse en el repositorio.

Crear la base de datos:

```sql
CREATE DATABASE energy_indexation
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Ejecutar las migraciones:

```bash
php artisan migrate
```

Importar los datos de ejemplo:

```text
backend/database/sample-data.sql
```

El archivo puede importarse mediante phpMyAdmin o cualquier cliente MySQL.

Iniciar Laravel:

```bash
php artisan serve
```

El backend estará disponible normalmente en:

```text
http://127.0.0.1:8000
```

---

## Instalación del frontend

Abrir otra terminal y entrar en:

```bash
cd frontend
```

Instalar las dependencias:

```bash
npm install
```

Crear el archivo de entorno:

### Windows PowerShell

```powershell
Copy-Item .env.example .env
```

### Linux / macOS

```bash
cp .env.example .env
```

Configurar `frontend/.env`:

```env
VITE_API_URL=http://127.0.0.1:8000
```

Iniciar Vue:

```bash
npm run dev
```

La aplicación estará disponible normalmente en:

```text
http://localhost:5173
```

---

## Uso

Con MySQL, Laravel y Vue en ejecución, abrir:

```text
http://localhost:5173
```

La aplicación permite:

- Visualizar consumos horarios.
- Visualizar precios horarios OMIE.
- Introducir fecha de inicio y fecha de fin.
- Introducir una fórmula.
- Calcular y mostrar el precio indexado.

Ejemplo de fórmula:

```text
([OMIE_MD] * 0.6) + 0.88
```

---

## Base de datos

La aplicación utiliza dos tablas.

### `consumptions`

```text
id
date
h1 ... h25
```

Los valores horarios representan consumos en `kWh`.

### `prices`

```text
id
date
h1 ... h25
```

Los valores horarios representan precios OMIE en `€/kWh`.

El campo `date` es único en ambas tablas.

Los datos se introducen manualmente mediante SQL.

---

## API REST

### Consultar consumos

```http
GET /consumptions
```

### Consultar precios

```http
GET /prices
```

### Calcular precio indexado

```http
POST /calculate
```

Ejemplo de petición:

```json
{
  "start_date": "2025-03-01",
  "end_date": "2025-03-02",
  "formula": "([OMIE_MD] * 0.6) + 0.88"
}
```

Ejemplo de respuesta:

```json
{
  "price_indexed": 1.0652881356
}
```

El resultado se expresa en `€/kWh`.

### Códigos de respuesta

- `200`: cálculo realizado correctamente.
- `400`: datos inválidos o incompletos.
- `404`: no existen consumos o precios para todo el período solicitado.
- `429`: límite de solicitudes superado.
- `500`: error durante el procesamiento del cálculo o de la fórmula.

---

## Lógica de cálculo

Para cada día y cada hora entre H1 y H25:

```text
importe_hora =
    precio obtenido al evaluar la fórmula × consumo_hora
```

Después:

```text
suma_importes = suma de los importes horarios
suma_consumos = suma de los consumos horarios
```

Finalmente:

```text
precio_indexado =
    suma_importes / suma_consumos
```

---

## Seguridad

La fórmula no se ejecuta mediante `eval()`.

Se utiliza un evaluador restringido que admite únicamente:

- `[OMIE_MD]`
- números
- `+`, `-`, `*`, `/`
- paréntesis

La API también aplica validación de entrada, respuestas de error controladas, CORS y rate limiting en el endpoint de cálculo.

Los archivos `.env` reales no deben publicarse en el repositorio.

---

## Documentación Swagger / OpenAPI

La API dispone de documentación interactiva generada con **Scramble** y basada en OpenAPI.

Con el backend en ejecución:

```text
http://127.0.0.1:8000/docs/api
```

Especificación OpenAPI en formato JSON:

```text
http://127.0.0.1:8000/docs/api.json
```

La documentación incluye:

- `GET /consumptions`
- `GET /prices`
- `POST /calculate`
- Parámetros de entrada
- Ejemplos
- Códigos de respuesta

---

## Tests

### Backend

```bash
cd backend
php artisan test
composer audit
```

### Frontend

```bash
cd frontend
npm run test:unit -- --run
npm run type-check
npm run lint
npm run build
```

---

## Producción

En producción deben configurarse las variables de entorno correspondientes al servidor.

En Laravel:

```env
APP_ENV=production
APP_DEBUG=false
```

Las credenciales y claves reales deben configurarse mediante las variables de entorno del servidor y no almacenarse en el repositorio.

---

## URLs


- **Aplicación:** https://energy-indexation.onrender.com
- **API REST:** https://energy-indexation-production.up.railway.app
- **Documentación Swagger / OpenAPI:** https://energy-indexation-production.up.railway.app/docs/api

---

## Autor

**Aziz Ayech**
