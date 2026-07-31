# Credenciales de Acceso — LescoKids (Entorno Local)

> Contraseña de todos los usuarios adultos: `password`

## Usuarios adultos

| Email | Password | Rol | Notas |
|---|---|---|---|
| `admin@lescokids.cr` | `password` | Admin | Acceso total al panel administrativo |
| `tutor@lescokids.cr` | `password` | Tutor | Niños: Sofía (PIN 1234), Marcos (PIN 5678) |
| `docente@lescokids.cr` | `password` | Docente | Aula Arco Iris — Sofía inscrita |

## Perfiles de niño

| Nombre | PIN | Tutor | Aulas |
|---|---|---|---|
| Sofía | `1234` | tutor@lescokids.cr | Aula Arco Iris (docente@lescokids.cr) |
| Marcos | `5678` | tutor@lescokids.cr | — |

## Base de datos

- **Host:** 127.0.0.1:3306
- **Base de datos:** `lescokids`
- **Usuario:** `root` / `root`

## URLs locales

| Panel | URL |
|---|---|
| Admin | `/admin` |
| Tutor | `/tutor` |
| Docente | `/docente` |
| Juego (niño) | `/play` |
| Login | `/login` |
