
# TalentHub — Backend

Sistema de gestión de reclutamiento y selección de personal, desarrollado con Laravel y SQL Server.

## Tecnologías utilizadas

- Laravel y PHP
- Microsoft SQL Server
- Composer
- Laragon
- Git y GitHub

## Instalación y ejecución

### 1. Clonar el repositorio

```bash
git clone https://github.com/JOSDANGT/reclutamiento-service.git
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar el entorno

Crear el archivo `.env`:

```bash
copy .env.example .env
```

Configurar la conexión a SQL Server:

```env
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=dbreclutamiento
DB_USERNAME=USUARIO
DB_PASSWORD=CONTRASENA
```

### 4. Generar la clave de Laravel

```bash
php artisan key:generate
```

### 5. Ejecutar migraciones

```bash
php artisan migrate
```

### 5. Ejecutar seeder

```bash
php artisan db:seed DataInicialSeeder
```

El backend estará disponible en:

`http://127.0.0.1:8000`

