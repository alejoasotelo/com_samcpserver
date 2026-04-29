# Cómo usar los builds?

Todos los builds hay que ejecutarlos en bash.

## Generar changelog.md

Ejecutar en bash

```bash
php build_changelog.php --from=TAG --version=TAG_ACTUAL
```

## Generar componente.zip para instalar en Joomla 3

El comando generará la carpeta *dist* con el archivo *com_base.zip* dentro.

```bash
php build/build-component.php
```
