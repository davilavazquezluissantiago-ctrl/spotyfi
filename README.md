# Spotyfi - Sistema de Gestión de Catálogo Musical

## Descripción del Proyecto
Este proyecto es una plataforma web inspirada en Spotify, diseñada para la gestión y reproducción de álbumes y canciones. El sistema cuenta con un control de accesos jerárquico que divide la experiencia en dos perfiles de usuario:

*   **Modo Oyente:** Permite a los usuarios finales registrarse, iniciar sesión y explorar el catálogo de álbumes recomendados para reproducir sus canciones de forma interactiva.
*   **Modo Administrativo:** Diseñado exclusivamente para el personal de la disquera. Cuenta con un doble factor de autenticación (requiere Número de Empleado único) y otorga acceso a paneles de control avanzados para realizar operaciones **CRUD** completas (Crear, Leer, Actualizar y Eliminar) sobre los álbumes y las pistas musicales.

###  Tecnologías Utilizadas
*   **Frontend:** HTML5, CSS3 (Diseño Premium Dark Mode basado en Figma), JavaScript para interactividad dinámica.
*   **Backend:** PHP 8 para la lógica de negocio, manejo de sesiones seguras (`$_SESSION`) y redirecciones.
*   **Base de Datos:** MySQL para la persistencia y relaciones de datos (Álbumes y Canciones).
*   **Servidor Local:** XAMPP (Apache).

##  Enlace de Publicación (Despliegue)
>  **Nota Técnica de Despliegue:** Al tratarse de una aplicación web dinámica desarrollada con arquitectura backend en **PHP** y persistencia de datos en **MySQL**, el proyecto requiere de un entorno de servidor activo con soporte para bases de datos para ejecutar sus funciones lógicas (como el inicio de sesión y los paneles CRUD). 
>
> Por esta razón, el código fuente completo y la estructura de la base de datos se encuentran disponibles para su clonación y ejecución local en este repositorio, mientras que la interfaz y el diseño visual del catálogo se pueden previsualizar de forma estática a través del siguiente enlace:
>
>  **[Ver Previsualización del Proyecto Aquí](https://TU_USUARIO.github.io/spotyfi/)**
