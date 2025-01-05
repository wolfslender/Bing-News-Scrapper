# Bing News Scraper

Plugin de WordPress que permite buscar y extraer automáticamente noticias desde Bing News basadas en palabras clave específicas.

## Características

- Búsqueda de noticias por palabras clave en Bing News
- Extracción automática del contenido de los artículos
- Creación de borradores de posts con el contenido extraído
- Interfaz simple y fácil de usar en el panel de administración
- Límite de 3 artículos por búsqueda para evitar sobrecarga

## Requisitos

- WordPress 5.0 o superior
- PHP 7.2 o superior
- Biblioteca Simple HTML DOM (incluida)

## Instalación

1. Descarga el archivo ZIP del plugin
2. Ve a tu panel de WordPress > Plugins > Añadir nuevo
3. Haz clic en "Subir Plugin" y selecciona el archivo ZIP
4. Activa el plugin

## Uso

1. Ve a "Bing News Scraper" en el menú lateral del panel de administración
2. Ingresa una palabra clave en el campo de búsqueda
3. Haz clic en "Buscar Noticias"
4. El plugin creará automáticamente borradores con las noticias encontradas
5. Revisa y edita los borradores antes de publicarlos

## Estructura del Contenido Extraído

Cada borrador creado incluirá:
- Título de la noticia
- Fecha de publicación
- Extracto de la noticia
- Contenido principal
- Enlace a la fuente original

## Notas Importantes

- Los artículos se crean como borradores para permitir su revisión
- Se conservan los enlaces a las fuentes originales
- El contenido extraído se limpia automáticamente de publicidad y elementos no deseados
- Se recomienda revisar y editar el contenido antes de publicar

## Solución de Problemas

Si encuentras algún error:
1. Verifica que simple_html_dom.php esté presente en el directorio del plugin
2. Asegúrate de tener permisos de escritura en la base de datos
3. Revisa los logs de error de WordPress para más detalles

## Soporte

Para soporte técnico o reportar problemas, por favor contacta a:
[oliverodev.com](https://oliverodev.com.com)

## Licencia

Este plugin es software libre y viene sin garantías.
