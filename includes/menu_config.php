<?php
/**
 * Configuración de visibilidad de menús por empresa
 */

// Función para verificar si se debe mostrar el módulo de prenómina
function mostrarPrenomina($app) {
    // Ocultar prenómina si la empresa activa es Custodios (ID = 3)
    if (isset($app->empresa_id) && $app->empresa_id == 3) {
        return false;
    }
    
    // También verificar por nombre de empresa por si acaso
    if (isset($app->empresa_nombre) && strtolower(trim($app->empresa_nombre)) == 'custodios') {
        return false;
    }
    
    // En todas las demás empresas, mostrar prenómina
    return true;
}

// Variable global para usar en los archivos de menú
$MOSTRAR_PRENOMINA = mostrarPrenomina($app);
?>
