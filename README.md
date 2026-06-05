# 🛡️ IAM Core API - Identity & Access Management

¡Te doy la bienvenida al motor de autenticación y auditoría de mi portafolio!

Este proyecto actúa como el backend centralizado para gestionar identidades y permisos. Está diseñado bajo una arquitectura REST estricta para demostrar mis capacidades en seguridad de APIs, control de accesos basado en roles (RBAC) y diseño de bases de datos relacionales en entornos modernos.

## 🚀 Características del Proyecto & Arquitectura Backend

- **Seguridad JWT (JSON Web Tokens):** Implementación de autenticación sin estado (stateless) para asegurar las transacciones entre el cliente y el servidor.
- **Control de Accesos Basado en Roles (RBAC):** Arquitectura de middlewares personalizados que interceptan y validan privilegios granulares (`admin`, `auditor`, `operador`) antes de la ejecución de controladores.
- **Bitácora de Auditoría Inmutable:** Registro transaccional de eventos de seguridad. Utiliza campos JSON (`payload`) para almacenar metadatos históricos blindados contra eliminaciones en cascada.
- **Protección de Datos Activa:** Implementación de "Soft Deletes" para bajas lógicas de usuarios, manteniendo la integridad referencial de la base de datos sin perder el historial.
- **Testing Automatizado:** Cobertura de lógica de negocio y endpoints mediante **Pest**, garantizando flujos predecibles y a prueba de regresiones.

---

## 🛠️ Stack Tecnológico

| Tecnología | Versión | Propósito en el proyecto |
| :--- | :--- | :--- |
| **Laravel** | `^13.8` | Framework base, enrutamiento y ORM (Eloquent) |
| **PHP** | `^8.3` | Lenguaje de servidor con tipado estricto |
| **PostgreSQL** | `17` | Motor de base de datos relacional y gestión de secuencias |
| **jwt-auth** | `^2.3` | Generación y validación de tokens de acceso |
| **Pest** | `^4.7` | Framework de pruebas (Testing) |

---

## 💻 Comandos de Desarrollo y Despliegue

Instrucciones para levantar el entorno localmente. Se asume que PostgreSQL está corriendo en el puerto `5434`.

| Comando | Acción |
| :--- | :--- |
| `composer install` | Instala las dependencias del core |
| `cp .env.example .env` | Genera el archivo de variables de entorno |
| `php artisan key:generate` | Genera la llave de encriptación de la aplicación |
| `php artisan jwt:secret` | Firma la llave para la emisión de tokens JWT |
| `php artisan migrate:fresh --seed` | Reconstruye la BD, reinicia secuencias en Postgres y siembra datos inmutables |
| `php artisan serve` | Inicia el servidor de desarrollo local |
| `php artisan test` | Ejecuta la suite de pruebas automatizadas con Pest |

---

## 📡 Documentación de la API (Endpoints)

Todas las peticiones a rutas protegidas exigen el header: `Authorization: Bearer <token>`.

### Autenticación (`/api/auth`)
| Método | Endpoint | Descripción | Acceso |
| :--- | :--- | :--- | :--- |
| `POST` | `/login` | Autentica credenciales y devuelve el JWT | Público |
| `GET`  | `/me`    | Devuelve la información del usuario en sesión | Protegido |
| `POST` | `/logout`| Invalida el token actual en la lista negra | Protegido |

### Gestión de Accesos (`/api/users` & `/api/roles`)
| Método | Endpoint | Descripción | Roles Permitidos |
| :--- | :--- | :--- | :--- |
| `GET`  | `/roles` | Lista el catálogo de roles del sistema | `admin`, `auditor`, `operador` |
| `GET`  | `/users` | Obtiene el directorio y estado lógico de usuarios | `admin`, `auditor`, `operador` |
| `POST` | `/users` | Hashea credenciales y registra un nuevo usuario | `admin` |
| `DELETE`| `/users/{id}` | Ejecuta una baja lógica (Soft Delete) | `admin` |
| `POST` | `/users/{id}/restore` | Revierte la baja lógica de un usuario | `admin` |

### Bitácora y Tráfico (`/api/audit-logs` & `/api/demo`)
| Método | Endpoint | Descripción | Roles Permitidos |
| :--- | :--- | :--- | :--- |
| `GET`  | `/audit-logs` | Retorna los 50 eventos de seguridad más recientes | `admin`, `auditor` |
| `POST` | `/demo/simulate` | Inyecta tráfico cronológico simulando uso real | `admin` |