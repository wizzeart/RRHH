-- ========================================
-- CORRECCIÓN DE CÁLCULO ING_PERS_3
-- ========================================

-- La lógica correcta es:
-- - Salario < 3260: ing_pers_3 = 0, ing_pers_5 = 0
-- - Salario 3260-9510: ing_pers_3 = 187 (fijo), ing_pers_5 = 0  
-- - Salario > 9510: ing_pers_3 = 187 (fijo), ing_pers_5 = (Salario - 9510) * 0.05

-- Corregir datos existentes
UPDATE prenomina 
SET 
    ing_pers_3 = CASE 
        WHEN salario_neto >= 3260 AND salario_neto <= 9510 
        THEN TRUNCATE((9510 - 3260) * 0.03, 0)  -- Siempre 187
        WHEN salario_neto > 9510 
        THEN TRUNCATE((9510 - 3260) * 0.03, 0)  -- Siempre 187
        ELSE 0
    END,
    ing_pers_5 = CASE 
        WHEN salario_neto > 9510 
        THEN TRUNCATE((salario_neto - 9510) * 0.05, 0)
        ELSE 0
    END
WHERE salario_neto IS NOT NULL AND salario_neto > 0;

-- Actualizar salario_pagar con los nuevos valores
UPDATE prenomina 
SET salario_pagar = salario_neto - (seg_social + ing_pers_3 + ing_pers_5 + COALESCE(ausencias_costo, 0))
WHERE salario_neto IS NOT NULL;

-- Verificar resultados
SELECT 
    'Verificación de cálculos' as titulo,
    salario_neto,
    ing_pers_3,
    ing_pers_5,
    CASE 
        WHEN salario_neto < 3260 THEN 'Bajo (< 3260): Debe ser 0, 0'
        WHEN salario_neto BETWEEN 3260 AND 9510 THEN 'Medio (3260-9510): Debe ser 187, 0'
        WHEN salario_neto > 9510 THEN CONCAT('Alto (> 9510): Debe ser 187, ', TRUNCATE((salario_neto - 9510) * 0.05, 0))
        ELSE 'Sin datos'
    END as esperado,
    CASE 
        WHEN salario_neto < 3260 AND ing_pers_3 = 0 AND ing_pers_5 = 0 THEN '✅ Correcto'
        WHEN salario_neto BETWEEN 3260 AND 9510 AND ing_pers_3 = 187 AND ing_pers_5 = 0 THEN '✅ Correcto'
        WHEN salario_neto > 9510 AND ing_pers_3 = 187 AND ing_pers_5 = TRUNCATE((salario_neto - 9510) * 0.05, 0) THEN '✅ Correcto'
        ELSE '❌ Incorrecto'
    END as estado
FROM prenomina 
WHERE salario_neto IS NOT NULL 
ORDER BY salario_neto 
LIMIT 20;

-- Estadísticas por rango
SELECT 
    CASE 
        WHEN salario_neto < 3260 THEN 'Bajo (< 3260)'
        WHEN salario_neto BETWEEN 3260 AND 9510 THEN 'Medio (3260-9510)'
        WHEN salario_neto > 9510 THEN 'Alto (> 9510)'
        ELSE 'Sin datos'
    END as rango_salarial,
    COUNT(*) as cantidad,
    AVG(ing_pers_3) as promedio_ing_pers_3,
    AVG(ing_pers_5) as promedio_ing_pers_5,
    MIN(ing_pers_3) as min_ing_pers_3,
    MAX(ing_pers_3) as max_ing_pers_3
FROM prenomina 
WHERE salario_neto IS NOT NULL
GROUP BY 
    CASE 
        WHEN salario_neto < 3260 THEN 'Bajo (< 3260)'
        WHEN salario_neto BETWEEN 3260 AND 9510 THEN 'Medio (3260-9510)'
        WHEN salario_neto > 9510 THEN 'Alto (> 9510)'
        ELSE 'Sin datos'
    END
ORDER BY 
    CASE 
        WHEN salario_neto < 3260 THEN 1
        WHEN salario_neto BETWEEN 3260 AND 9510 THEN 2
        WHEN salario_neto > 9510 THEN 3
        ELSE 4
    END;
