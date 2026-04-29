# SA MCP Server para Joomla

Componente open source para Joomla 3 y 4 que implementa el protocolo MCP (Model Context Protocol) y permite que asistentes de inteligencia artificial como Claude gestionen el contenido de tu sitio directamente desde una conversacion de chat.

Para una descripcion completa del proyecto, casos de uso y screenshots, lee el articulo oficial:
**[SA MCP Server para Joomla: controla tu sitio con inteligencia artificial](https://alejosotelo.com.ar/portal/blog/312-sa-mcp-server-para-joomla.html)**

## Que es el protocolo MCP

El Model Context Protocol (MCP) es un estandar abierto creado por Anthropic que permite a los asistentes de inteligencia artificial conectarse con herramientas y servicios externos de forma estructurada. En lugar de copiar y pegar contenido entre ventanas, la IA puede consultar y modificar datos directamente, con tu autorizacion.

SA MCP Server hace exactamente eso, pero para tu sitio Joomla. Si ya usas Claude Desktop, funciona igual que los conectores de Google Drive o Gmail, pero gestionando tus articulos y menus.

## Tools disponibles

La version actual incluye las siguientes herramientas:

### Articulos (`com_content`)
- `articles_list` — lista articulos con filtros por categoria, estado, busqueda y paginacion
- `articles_get` — detalle completo de un articulo por ID
- `articles_create` — crea un articulo nuevo con titulo, contenido HTML, categoria, alias SEF, idioma y metadescripcion
- `articles_update` — actualiza campos especificos de un articulo existente (parcial)
- `articles_delete` — envia a papelera o elimina definitivamente

### Menus
- `menus_list` — lista todos los menus del sitio con conteo de items
- `menu_items_list` — lista items de un menu con filtros
- `menu_items_get` — detalle de un item por ID
- `menu_items_create` — crea un nuevo item de menu
- `menu_items_update` — actualiza un item existente
- `menu_items_delete` — envia a papelera o elimina definitivamente

### Cache
- `cache_clean` — limpia la cache de Joomla (todos los grupos o uno especifico)

## Requisitos

- Joomla 3.x/4.x
- PHP compatible con tu instalacion Joomla
- Acceso de administrador al backend
- (Opcional) Cloudflare correctamente configurado para no desafiar el endpoint MCP (desactivar bot flight o crear regla de bypass)

## Instalacion del componente

### Opcion A: Instalar un ZIP del componente

1. Genera o descarga el paquete `com_samcpserver.zip` desde los releases del repositorio.
2. En Joomla, ve a:
   `Sistema -> Instalar -> Extensiones`
3. Sube el ZIP e instala.
4. Verifica que aparezca el menu:
   `Componentes -> SA MCP Server`

### Opcion B: Construir el ZIP desde este repositorio

El proyecto incluye script de build en:
- `build/build-component.php`

Ejemplo de ejecucion:

```bash
php build/build-component.php
```

Esto genera el paquete en `dist/com_samcpserver.zip` para instalarlo en Joomla.

## Crear usuario MCP y obtener URL

1. En el backend de Joomla entra en:
   `Componentes -> SA MCP Server`
2. Crea un registro nuevo de usuario MCP.
3. Selecciona el usuario Joomla que usara la conexion.
4. Guarda el registro.
5. El componente genera automaticamente un token y muestra la URL MCP.

Formato de URL MCP:

```text
https://TU-DOMINIO/index.php?option=com_samcpserver&task=mcp&token=TU_TOKEN
```

Si tu Joomla esta en subcarpeta (por ejemplo `/portal`), la URL sera:

```text
https://TU-DOMINIO/portal/index.php?option=com_samcpserver&task=mcp&token=TU_TOKEN
```

## Configurar en Claude Desktop

Edita `claude_desktop_config.json` y agrega el servidor MCP:

```json
{
  "mcpServers": {
    "joomla": {
      "url": "https://TU-DOMINIO/index.php?option=com_samcpserver&task=mcp&token=TU_TOKEN"
    }
  }
}
```

Luego reinicia Claude Desktop.

## Configurar en otras IAs o clientes MCP

Usa la misma URL MCP del usuario/token creado en Joomla.

Plantilla generica (ajusta segun el cliente):

```json
{
  "mcpServers": {
    "joomla": {
      "url": "https://TU-DOMINIO/index.php?option=com_samcpserver&task=mcp&token=TU_TOKEN"
    }
  }
}
```

Si tu cliente pide transporte, selecciona HTTP/Streamable HTTP.

## Verificacion rapida

- `initialize` debe responder correctamente.
- `tools/list` debe listar herramientas del servidor.
- Si el token es invalido o deshabilitado, debe devolver `401 Unauthorized`.

## Troubleshooting

### 1) Cloudflare devuelve "Just a moment..."

Tu endpoint MCP esta siendo desafiado por WAF/Bot protection. Crea una regla en Cloudflare para hacer `Skip` en la ruta MCP (`index.php?option=com_samcpserver&task=mcp`).

### 2) Responde 401 Unauthorized

- Revisa que el token sea correcto.
- Verifica que el usuario MCP este habilitado (`enabled = 1`).

### 3) No aparecen tools

- Asegurate de estar en una version actual del componente.
- Revisa logs del cliente MCP para validar handshake y `tools/list`.

## Seguridad recomendada

- Trata la URL con token como credencial secreta.
- Regenera token si se expone.
- Limita el acceso al endpoint MCP en WAF solo a las condiciones necesarias.
