# Instalación de PhpSpreadsheet

Para que la exportación a Excel funcione correctamente, necesitas instalar PhpSpreadsheet usando Composer.

## Pasos de instalación:

1. **Asegúrate de tener Composer instalado:**
   - Si no lo tienes, descárgalo desde: https://getcomposer.org/download/

2. **Navega a la carpeta del proyecto:**
   ```bash
   cd C:\Users\josue\Semestral\inventory-system
   ```

3. **Instala PhpSpreadsheet:**
   ```bash
   composer require phpoffice/phpspreadsheet
   ```

4. **Verifica la instalación:**
   - Debería crearse una carpeta `vendor/` en la raíz del proyecto
   - El archivo `public/export_excel.php` debería poder cargar PhpSpreadsheet automáticamente

## Nota importante:

- Si ya tienes un archivo `composer.json` en el proyecto, Composer lo actualizará
- Si no existe, Composer creará uno automáticamente
- La carpeta `vendor/` debe estar en la raíz del proyecto (no dentro de `public/`)

## Verificación:

Una vez instalado, prueba exportar un Excel desde "Equipos existentes". Si ves un error sobre PhpSpreadsheet no instalado, verifica que:
1. La carpeta `vendor/` existe en la raíz del proyecto
2. El archivo `vendor/autoload.php` existe
3. El servidor PHP tiene permisos para leer estos archivos

