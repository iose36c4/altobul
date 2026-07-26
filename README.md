# Altobul

**Altobul** es una aplicación web de encuentros y conexión social exclusiva para hombres, diseñada para facilitar el descubrimiento de personas cercanas dentro de zonas geográficas determinadas, con un sistema progresivo de interacción basado en **Tokés, Matches y Amistades**.

La aplicación combina geolocalización, perfiles configurables, privacidad granular, verificación opcional de identidad, chat condicionado por relaciones y publicaciones temporales.

---

## Concepto

Altobul está diseñada alrededor de una dinámica de interacción progresiva:

```text
DESCUBRIR
    ↓
TOKE
    ↓
TOKE MUTUO
    ↓
MATCH
    ↓
CONOCERSE
    ↓
AMISTAD
```

El sistema permite que los usuarios comiencen a utilizar la aplicación sin necesidad de verificar obligatoriamente su identidad, pero ofrece una insignia de **identidad verificada** para quienes completen el proceso de validación.

La verificación funciona como un mecanismo adicional de confianza y puede utilizarse como criterio de visibilidad y filtrado.

---

# Funcionalidades principales

## Zonas geográficas habilitadas

La aplicación no está necesariamente disponible en todo el mundo.

La administración puede definir las áreas donde Altobul puede utilizarse mediante un mapa interactivo.

El administrador puede:

* Crear zonas geográficas.
* Dibujar uno o más polígonos.
* Modificar los límites de los polígonos.
* Activar o desactivar zonas.
* Definir áreas de operación independientes.

La ubicación del usuario se utiliza para determinar si se encuentra dentro de una zona habilitada.

Esto permite crear una aplicación orientada a:

* Ciudades.
* Barrios.
* Comunidades locales.
* Eventos.
* Áreas geográficas específicas.

---

# Perfiles

Cada usuario puede crear y configurar su perfil.

El perfil puede incluir:

* Fotos.
* Título.
* Descripción.
* Características personales.
* Intereses.
* Preferencias.
* Otros metadatos definidos por la administración.

La administración puede crear nuevos campos de perfil sin necesidad de modificar la estructura principal de la aplicación.

---

# Campos dinámicos de perfil

Altobul utiliza un sistema de campos de perfil configurable.

La administración puede crear, editar, activar, desactivar y eliminar campos de metadatos.

Entre los tipos de campos posibles se incluyen:

* Línea de texto.
* Cuadro de texto.
* Número.
* Selector de opción única.
* Selector de múltiples opciones.
* Casillas de verificación.
* Radio buttons.
* Fechas.
* Rangos.
* Otros tipos definidos por el sistema.

Ejemplos:

```text
Título del perfil
Descripción
Altura
Color de ojos
Tipo corporal
Intereses
Preferencias
```

Cada campo puede tener sus propias reglas:

* Obligatorio u opcional.
* Visible u oculto.
* Filtrable o no filtrable.
* Opciones configurables.
* Reglas de validación.

---

# Privacidad de los datos

Cada usuario puede decidir quién puede ver cada dato de su perfil.

Los niveles de privacidad disponibles son:

```text
PÚBLICO
MATCH
AMIGOS
PRIVADO
```

Además, cada recurso puede requerir que el visitante tenga una identidad verificada.

Por ejemplo:

```text
Altura
└── Público + Cualquier usuario

Descripción
└── Solo Match + Cualquier usuario

Preferencias
└── Solo Amigos + Solo usuarios verificados

Dato privado
└── Acceso individual
```

Esto permite crear configuraciones como:

> Mostrar este dato a todos mis amigos, pero solamente si tienen identidad verificada.

---

# Fotografías

Cada usuario puede cargar hasta **32 fotografías**.

Las imágenes se procesan automáticamente antes de almacenarse.

El procesamiento incluye:

* Redimensionamiento.
* Optimización.
* Compresión.
* Eliminación de metadatos sensibles.
* Conservación de la relación de aspecto.

El lado más largo de la imagen se limita a:

```text
1024 píxeles
```

Por ejemplo:

```text
4000 × 3000
      ↓
1024 × 768
```

Las imágenes que ya tienen un tamaño inferior no se amplían innecesariamente.

---

## Privacidad de las fotografías

Cada fotografía puede tener su propia política de visibilidad:

```text
Público
Solo Match
Solo Amigos
Privado
```

Además, puede establecerse:

```text
Cualquier usuario
Solo usuarios verificados
```

Ejemplo:

```text
Foto 1 → Público + Cualquiera
Foto 2 → Público + Verificados
Foto 3 → Match + Cualquiera
Foto 4 → Match + Verificados
Foto 5 → Amigos + Verificados
Foto 6 → Privada + Acceso individual
```

El propietario puede autorizar o revocar el acceso a fotografías privadas para usuarios específicos.

---

# Verificación de identidad

La verificación de identidad es opcional.

Un usuario puede:

1. Registrarse.
2. Crear su perfil.
3. Utilizar la aplicación.
4. Solicitar posteriormente la verificación de identidad.

Los estados de verificación pueden incluir:

```text
NO VERIFICADO
    ↓
PENDIENTE
    ↓
VERIFICADO
```

Los perfiles verificados reciben una insignia visible.

La insignia indica que la identidad de la cuenta fue validada mediante el procedimiento establecido por la plataforma.

La verificación puede utilizarse para:

* Filtrar perfiles.
* Limitar la visibilidad de datos.
* Limitar la visibilidad de fotografías.
* Limitar la visibilidad de publicaciones.
* Aumentar la confianza entre usuarios.

La verificación no es un requisito obligatorio para comenzar a utilizar Altobul.

---

# Descubrimiento de perfiles

Altobul permite descubrir perfiles que el usuario tiene permiso de ver.

Las principales vistas incluyen:

## Todos los perfiles disponibles

Una grilla con los perfiles que cumplen las reglas de:

* Zona geográfica.
* Visibilidad.
* Preferencias.
* Bloqueos.
* Filtros de perfil.
* Requisitos de verificación.

---

## Perfiles conectados

Muestra los perfiles actualmente activos o recientemente conectados.

---

## Últimas 24 horas

Muestra perfiles que estuvieron activos durante las últimas 24 horas.

---

## Distancia

La aplicación puede mostrar la distancia aproximada entre usuarios, respetando las reglas de privacidad y seguridad correspondientes.

---

# Preferencias de búsqueda

Los usuarios pueden definir qué perfiles desean encontrar.

Las preferencias pueden utilizar los campos configurados por la administración.

Por ejemplo:

```text
Altura:
170–190 cm

Color de ojos:
☑ Marrón
☑ Verde

Identidad:
☑ Solo perfiles verificados
```

Las preferencias de búsqueda son independientes de los datos que el usuario muestra sobre sí mismo.

---

# El Toke

El **Toke** es la acción principal para llamar la atención de otro usuario.

Un usuario puede enviar un Toke:

* Desde una grilla.
* Desde un perfil.
* Como consecuencia de un Like en el juego de Match.

Los Tokés tienen una ventana temporal de **48 horas**.

Si dos usuarios se envían Tokés mutuamente dentro de ese período:

```text
Usuario A ─── Toke ───► Usuario B
Usuario A ◄── Toke ─── Usuario B
              ↓
            MATCH
```

---

# Match

Un Match se crea cuando dos usuarios se envían Tokés mutuamente dentro de una ventana de 48 horas.

La relación Match tiene una duración limitada de:

> **7 días**

Durante este período, los usuarios pueden conocerse y comunicarse.

Al finalizar el período:

```text
MATCH
  │
  ├── Se convierte en amistad
  │
  └── Expira
```

---

# Amistades

Dos usuarios que tienen un Match pueden establecer una relación de amistad.

La amistad:

* No caduca automáticamente.
* Permite mantener el chat.
* Permanece hasta que alguno de los usuarios la elimina.

La dinámica de relaciones es:

```text
TOKE
  ↓
MATCH TEMPORAL
  ↓
AMISTAD PERMANENTE
```

El objetivo es permitir que los usuarios se conozcan progresivamente antes de establecer una relación permanente dentro de la aplicación.

---

# Chat

El chat está disponible únicamente cuando existe una relación válida entre los usuarios.

Se permite conversar si existe:

```text
MATCH ACTIVO
        O
AMISTAD
```

Si un Match expira y no se convierte en amistad:

```text
MATCH EXPIRA
      ↓
CHAT SE DESACTIVA
```

Si el Match se convierte en amistad:

```text
MATCH
  ↓
AMISTAD
  ↓
CHAT CONTINÚA
```

---

# Bloqueos

Los usuarios pueden bloquear a otros usuarios.

El bloqueo tiene prioridad sobre cualquier otra relación o permiso.

Al bloquear a un usuario se interrumpe la interacción entre ambas cuentas.

El bloqueo puede afectar:

* Visibilidad.
* Tokés.
* Matches.
* Amistades.
* Chat.
* Acceso a contenido privado.

El bloqueo siempre debe impedir el acceso, incluso cuando existiera una autorización previa.

---

# Relaciones y configuración

Los usuarios pueden administrar sus relaciones desde la configuración.

Pueden:

* Eliminar Matches.
* Eliminar Amistades.
* Bloquear usuarios.
* Desbloquear usuarios.
* Revocar acceso a datos privados.
* Revocar acceso a fotografías privadas.

---

# Muro de publicaciones

Altobul incluye un tablero de publicaciones temporales.

Los usuarios pueden crear publicaciones breves con:

* Texto.
* Markdown.
* Una fotografía adjunta.

Las publicaciones son perecederas y se eliminan automáticamente después de:

> **24 horas**

---

## Privacidad de las publicaciones

Cada publicación puede establecer su propia política de visibilidad:

```text
Público
Solo Match
Solo Amigos
Privado
```

Y además:

```text
Cualquier usuario
Solo perfiles verificados
```

Ejemplos:

```text
Público + Cualquiera
Público + Verificados

Match + Cualquiera
Match + Verificados

Amigos + Cualquiera
Amigos + Verificados

Privado + Acceso individual
```

Esto permite, por ejemplo:

> Publicar algo visible para todos los amigos, pero solamente para aquellos cuya identidad haya sido verificada.

---

# Sistema unificado de autorización

Los diferentes recursos de Altobul utilizan un modelo común de control de acceso.

Este sistema se aplica a:

* Datos de perfil.
* Fotografías.
* Publicaciones.

La autorización considera:

```text
1. ¿El usuario está bloqueado?
        ↓
2. ¿Existe un acceso individual?
        ↓
3. ¿Cuál es la relación entre los usuarios?
        ↓
4. ¿La relación permite acceder al recurso?
        ↓
5. ¿Se requiere identidad verificada?
        ↓
6. ¿El usuario cumple ese requisito?
        ↓
      ACCESO
```

El objetivo es mantener una política de privacidad coherente en toda la aplicación.

---

# Arquitectura tecnológica

La primera versión de Altobul está pensada como una aplicación web responsive.

## Frontend

* Next.js.
* React.
* TypeScript.
* Tailwind CSS.
* Componentes de interfaz reutilizables.

## Backend

* Laravel.
* PHP.
* API.
* Sistema de autorización centralizado.
* Jobs y tareas programadas.
* WebSockets para comunicación en tiempo real.

## Base de datos

* PostgreSQL.
* PostGIS para funciones geográficas.

PostGIS permite:

* Almacenar polígonos.
* Verificar si un usuario está dentro de una zona.
* Calcular distancias.
* Buscar usuarios cercanos.

## Cache y procesamiento temporal

* Redis.
* Colas de trabajo.
* Tareas programadas.
* Gestión de expiraciones.

Se utiliza para funciones como:

```text
Tokes → 48 horas
Matches → 7 días
Posts → 24 horas
```

## Almacenamiento de imágenes

Las imágenes se almacenan en un sistema de almacenamiento de objetos compatible con S3.

La base de datos conserva la información de la imagen, mientras que el archivo binario se almacena separadamente.

## Mapas

La administración utiliza un mapa interactivo para crear y editar zonas geográficas.

La información espacial se almacena en PostGIS.

---

# Arquitectura general

```text
┌───────────────────────────────┐
│           NAVEGADOR            │
│       Next.js + React          │
└───────────────┬───────────────┘
                │
                ▼
┌───────────────────────────────┐
│            BACKEND             │
│      Laravel + PHP             │
│                               │
│ Usuarios                       │
│ Perfiles                       │
│ Privacidad                     │
│ Geografía                      │
│ Tokés                          │
│ Matches                        │
│ Amistades                      │
│ Chat                           │
│ Fotos                          │
│ Publicaciones                  │
│ Administración                 │
└───────┬──────────┬────────────┘
        │          │
        ▼          ▼
┌────────────┐ ┌────────────┐
│ PostgreSQL │ │   Redis    │
│ + PostGIS  │ │            │
└──────┬─────┘ └────────────┘
       │
       ▼
┌────────────────────────────┐
│     OBJECT STORAGE         │
│ Fotos y archivos adjuntos  │
└────────────────────────────┘
```

---

# Principios del proyecto

## Privacidad granular

Cada usuario debe poder decidir qué información comparte y con quién.

## Confianza progresiva

La aplicación debe permitir comenzar a utilizarla sin verificación obligatoria, pero ofrecer herramientas para aumentar la confianza.

## Interacción progresiva

La relación entre usuarios evoluciona:

```text
Descubrir
   ↓
Toke
   ↓
Match
   ↓
Conocerse
   ↓
Amistad
```

## Seguridad por defecto

El sistema debe priorizar:

* Bloqueos.
* Control de acceso.
* Protección de imágenes.
* Privacidad de la ubicación.
* Eliminación de metadatos sensibles.
* Validación en el backend.

## Geografía controlada

La aplicación opera dentro de zonas definidas por la administración.

## Contenido temporal

Las interacciones y publicaciones tienen ciclos de vida definidos:

```text
Toke   → 48 horas
Match  → 7 días
Post   → 24 horas
Amistad → Permanente
```

---

# Estado del proyecto

Altobul se encuentra en etapa de definición y diseño de producto.

El desarrollo se organizará inicialmente alrededor de un núcleo web modular que permita evolucionar posteriormente hacia aplicaciones móviles nativas o multiplataforma sin tener que reemplazar el backend.

---

# Licencia

La licencia del proyecto deberá definirse antes de la primera publicación pública del código.

