---
applyTo: '**'
---

# Instrucciones de Codificación para Copilot - Componente Joomla SA MCP Server

## Información del Proyecto
- **Tipo**: Componente de Joomla 3 en migración a Joomla 4
- **Nombre**: com_samcpserver
- **Autor**: Alejo Sotelo <soporte@alejosotelo.com.ar>
- **Experiencia**: Programando desde 2003

## Estándares de Código

### PSR-12 Compliance
- Seguir estrictamente los estándares PSR-12 para PHP
- Usar sintaxis de array corta: `[]` en lugar de `array()`
- Indentación con 4 espacios
- Líneas máximo 120 caracteres
- Declaraciones de tipo cuando sea apropiado

### Formato de Llaves (PSR-12)
- **Clases**: Llave de apertura en nueva línea
```php
class MiClase
{
    // contenido
}
```

- **Métodos/Funciones**: Llave de apertura en nueva línea
```php
public function miMetodo()
{
    // contenido
}
```

- **Estructuras de control**: Llave de apertura en la misma línea
```php
if ($condicion) {
    // contenido
}
```

### Headers de Archivo
Todos los archivos PHP deben incluir el siguiente header:

```php
<?php
/**
 * SA MCP Server Component for Joomla
 *
 * @package     SAMCPServer
 * @subpackage  Component
 * @author      Alejo Sotelo <soporte@alejosotelo.com.ar>
 * @copyright   Copyright (C) 2003-2026 Alejo Sotelo. All rights reserved.
 * @license     Proprietary License - No modification or distribution without explicit permission
 * @since       1.0.0
 */

defined('_JEXEC') or die;
```

### Convenciones de Naming

#### Para código de Joomla (estándar de Joomla):
- Clases de componente Joomla: PascalCase con prefijo (`SAMCPServerController`, `SAMCPServerModelUsers`)
- Métodos y variables: camelCase (`$userName`, `getUserData()`). Usar nombres descriptivos y claros usando prefijos como estandard: `has`, `ìs`, `can`, `should`, `exists`, `list`, `get`, `set`, `add`, `remove`, `update`, etc.
- Constantes: UPPER_SNAKE_CASE (`COMPONENT_VERSION`)
- Archivos de vistas/controladores: lowercase (`users.php`, `controller.php`)
- Nombres de tablas: snake_case con prefijo (`#__samcpserver_users`)

#### Para librerías propias (PSR-4/Composer):
- Clases: PascalCase puro (`UserManager`, `DatabaseConnector`)
- Namespaces: PascalCase (`AlejoSotelo\Samcpserver\Services`)
- Métodos y variables: camelCase (`$userData`, `processUser()`)
- Archivos: coinciden con el nombre de clase (`UserManager.php`)
- Directorios: coinciden con namespace (`src/Services/`)

#### Regla general:
- Si está en `/vendor/` o usa `composer.json`: seguir PSR-4 estricto
- Si está en estructura Joomla (`/admin/`, `/site/`): seguir convenciones Joomla
- Siempre usar camelCase para métodos y variables

### Estructura de Joomla 4
- Usar namespaces apropiados para Joomla 4
- Implementar las nuevas interfaces de Joomla 4
- Usar el patrón MVC de Joomla 4
- Aprovechar las nuevas características de Joomla 4 como Form Fields, Services, etc.

### PHP CS Fixer Configuration
Configuración específica para mantener:
```php
'array_syntax' => ['syntax' => 'short']
```

## Mejores Prácticas

### Estructura de Control y Flujo
- **Early Return/Guard Clauses**: Preferir múltiples returns tempranos para evitar anidamientos
```php
public function miFuncion($algo, $algo2)
{
    if (!$algo) {
        return false;
    }

    if (!$algo2) {
        return false;
    }

    return true;
}
```
- Evitar `else` después de `return` cuando sea posible
- Minimizar niveles de anidamiento usando guard clauses

### Seguridad
- Siempre validar y sanitizar inputs
- Usar prepared statements para consultas SQL
- Implementar verificaciones de permisos adecuadas
- Escape de salida apropiado

### Performance
- Optimizar consultas de base de datos
- Usar caché cuando sea apropiado
- Minimizar consultas en loops

### Compatibilidad
- Mantener compatibilidad con Joomla 4.x
- Considerar futuras actualizaciones a Joomla 5
- Usar APIs estables de Joomla

### Documentación
- Documentar métodos públicos con PHPDoc
- Incluir ejemplos de uso cuando sea relevante
- Mantener comentarios claros y concisos

## Estructura de Archivos
Respetar la estructura estándar de componentes Joomla:
- `/admin/` - Archivos del backend
- `/site/` - Archivos del frontend
- `/media/` - Assets (CSS, JS, imágenes)
- `/language/` - Archivos de idioma

## Notas Especiales
- Este es un proyecto propietario con licencia restrictiva
- Mantener compatibilidad con migraciones de Joomla 3 a 4
- Priorizar la calidad del código sobre la velocidad de desarrollo

